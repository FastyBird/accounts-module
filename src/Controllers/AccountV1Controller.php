<?php declare(strict_types = 1);

/**
 * AccountV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AuthModule\Controllers;

use Doctrine;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Hydrators;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Throwable;

/**
 * Account controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountV1Controller extends BaseV1Controller
{

	/** @var string */
	protected $translationDomain = 'module.account';

	/** @var Hydrators\Accounts\ProfileAccountHydrator */
	private $accountHydrator;

	/** @var Models\Accounts\IAccountsManager */
	private $accountsManager;

	public function __construct(
		Hydrators\Accounts\ProfileAccountHydrator $accountHydrator,
		Models\Accounts\IAccountsManager $accountsManager
	) {
		$this->accountHydrator = $accountHydrator;

		$this->accountsManager = $accountsManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$account = $this->findAccount();

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($account));
	}

	/**
	 * @return Entities\Accounts\IAccount
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 */
	private function findAccount(): Entities\Accounts\IAccount
	{
		if ($this->user->getAccount() === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_FORBIDDEN,
				$this->translator->translate('//module.base.messages.forbidden.heading'),
				$this->translator->translate('//module.base.messages.forbidden.message')
			);
		}

		return $this->user->getAccount();
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$account = $this->findAccount();

		$document = $this->createDocument($request);

		if ($account->getPlainId() !== $document->getResource()->getIdentifier()->getId()) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//module.base.messages.invalidIdentifier.heading'),
				$this->translator->translate('//module.base.messages.invalidIdentifier.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()
				->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Accounts\UserAccountSchema::SCHEMA_TYPE) {
				$account = $this->accountsManager->update(
					$account,
					$this->accountHydrator->hydrate($document, $account)
				);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//module.base.messages.invalidType.heading'),
					$this->translator->translate('//module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()
				->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.notUpdated.heading'),
				$this->translator->translate('//module.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()
				->isTransactionActive()) {
				$this->getOrmConnection()
					->rollBack();
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
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$account = $this->findAccount();

		// TODO: Closing account not implemented yet

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
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$account = $this->findAccount();

		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (
			$relationEntity === Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS
			&& $account instanceof Entities\Accounts\IUserAccount
		) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($account->getEmails()));

		} elseif ($relationEntity === Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_IDENTITIES) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($account->getIdentities()));

		} elseif ($relationEntity === Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_ROLES) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($account->getRoles()));
		}

		return parent::readRelationship($request, $response);
	}

}
