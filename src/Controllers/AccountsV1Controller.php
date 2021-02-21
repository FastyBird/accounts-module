<?php declare(strict_types = 1);

/**
 * AccountsV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           21.06.20
 */

namespace FastyBird\AuthModule\Controllers;

use Doctrine;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Hydrators;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\AuthModule\Types;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use IPub\DoctrineCrud\Exceptions as DoctrineCrudExceptions;
use Nette\Utils;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * Accounts controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\Role(manager,administrator)
 */
final class AccountsV1Controller extends BaseV1Controller
{

	/** @var Hydrators\Accounts\AccountHydrator */
	private Hydrators\Accounts\AccountHydrator $accountHydrator;

	/** @var Models\Accounts\IAccountRepository */
	private Models\Accounts\IAccountRepository $accountRepository;

	/** @var Models\Accounts\IAccountsManager */
	private Models\Accounts\IAccountsManager $accountsManager;

	/** @var Models\Identities\IIdentitiesManager */
	private Models\Identities\IIdentitiesManager $identitiesManager;

	/** @var string */
	protected string $translationDomain = 'auth-module.accounts';

	public function __construct(
		Hydrators\Accounts\AccountHydrator $accountHydrator,
		Models\Accounts\IAccountRepository $accountRepository,
		Models\Accounts\IAccountsManager $accountsManager,
		Models\Identities\IIdentitiesManager $identitiesManager
	) {
		$this->accountHydrator = $accountHydrator;

		$this->accountRepository = $accountRepository;
		$this->accountsManager = $accountsManager;
		$this->identitiesManager = $identitiesManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 */
	public function index(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$findQuery = new Queries\FindAccountsQuery();

		$accounts = $this->accountRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($accounts));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function read(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// Find account
		$account = $this->findAccount($request);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($account));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function create(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Accounts\AccountSchema::SCHEMA_TYPE) {
				$createData = $this->accountHydrator->hydrate($document);

				// Store item into database
				$account = $this->accountsManager->create($createData);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//auth-module.base.messages.invalidType.heading'),
					$this->translator->translate('//auth-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Exceptions\AccountRoleInvalidException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.invalidRelation.heading'),
				$this->translator->translate('//auth-module.base.messages.invalidRelation.message'),
				[
					'pointer' => '/data/relationships/roles/data/id',
				]
			);

		} catch (DoctrineCrudExceptions\EntityCreationException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//auth-module.base.messages.missingAttribute.message'),
				[
					'pointer' => 'data/attributes/' . $ex->getField(),
				]
			);

		} catch (Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
			if (preg_match("%PRIMARY'%", $ex->getMessage(), $match) === 1) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//auth-module.base.messages.uniqueIdentifier.heading'),
					$this->translator->translate('//auth-module.base.messages.uniqueIdentifier.message'),
					[
						'pointer' => '/data/id',
					]
				);

			} elseif (preg_match("%key '(?P<key>.+)_unique'%", $ex->getMessage(), $match) === 1) {
				$columnParts = explode('.', $match['key']);
				$columnKey = end($columnParts);

				if (is_string($columnKey) && Utils\Strings::startsWith($columnKey, 'account_')) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//auth-module.base.messages.uniqueAttribute.heading'),
						$this->translator->translate('//auth-module.base.messages.uniqueAttribute.message'),
						[
							'pointer' => '/data/attributes/' . Utils\Strings::substring($columnKey, 8),
						]
					);
				}
			}

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.uniqueAttribute.heading'),
				$this->translator->translate('//auth-module.base.messages.uniqueAttribute.message')
			);

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[FB:AUTH_MODULE:CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.notCreated.heading'),
				$this->translator->translate('//auth-module.base.messages.notCreated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withEntity(WebServerHttp\ScalarEntity::from($account))
			->withStatus(StatusCodeInterface::STATUS_CREATED);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$account = $this->findAccount($request);

		$this->validateIdentifier($request, $document);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if (
				$document->getResource()->getType() === Schemas\Accounts\AccountSchema::SCHEMA_TYPE
				&& $account instanceof Entities\Accounts\IAccount
			) {
				$updateAccountData = $this->accountHydrator->hydrate($document, $account);

				$account = $this->accountsManager->update($account, $updateAccountData);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//auth-module.base.messages.invalidType.heading'),
					$this->translator->translate('//auth-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Exceptions\AccountRoleInvalidException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.invalidRelation.heading'),
				$this->translator->translate('//auth-module.base.messages.invalidRelation.message'),
				[
					'pointer' => '/data/relationships/roles/data/id',
				]
			);

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[FB:AUTH_MODULE:CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.notUpdated.heading'),
				$this->translator->translate('//auth-module.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withEntity(WebServerHttp\ScalarEntity::from($account));

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$account = $this->findAccount($request);

		if (
			$this->user->getAccount() !== null
			&& $account->getId()->equals($this->user->getAccount()->getId())
		) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.selfNotDeletable.heading'),
				$this->translator->translate('messages.selfNotDeletable.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			$updateData = Utils\ArrayHash::from([
				'state' => Types\AccountStateType::get(Types\AccountStateType::STATE_DELETED),
			]);

			$this->accountsManager->update($account, $updateData);

			foreach ($account->getIdentities() as $identity) {
				$updateIdentity = Utils\ArrayHash::from([
					'state' => Types\IdentityStateType::get(Types\IdentityStateType::STATE_DELETED),
				]);

				$this->identitiesManager->update($identity, $updateIdentity);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[FB:AUTH_MODULE:CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//auth-module.base.messages.notDeleted.heading'),
				$this->translator->translate('//auth-module.base.messages.notDeleted.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// At first, try to load account
		$account = $this->findAccount($request);

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_IDENTITIES) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($account->getIdentities()));

		} elseif ($relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_ROLES) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($account->getRoles()));
		}

		if ($account instanceof Entities\Accounts\IAccount) {
			if ($relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_EMAILS) {
				return $response
					->withEntity(WebServerHttp\ScalarEntity::from($account->getEmails()));
			}
		}

		return parent::readRelationship($request, $response);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return Entities\Accounts\IAccount
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function findAccount(
		Message\ServerRequestInterface $request
	): Entities\Accounts\IAccount {
		if (!Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID, null))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//auth-module.base.messages.notFound.heading'),
				$this->translator->translate('//auth-module.base.messages.notFound.message')
			);
		}

		$findQuery = new Queries\FindAccountsQuery();
		$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID, null)));

		$account = $this->accountRepository->findOneBy($findQuery);

		if ($account === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//auth-module.base.messages.notFound.heading'),
				$this->translator->translate('//auth-module.base.messages.notFound.message')
			);
		}

		return $account;
	}

}
