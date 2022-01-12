<?php declare(strict_types = 1);

/**
 * AccountV1Controller.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Controllers;

use Doctrine;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Hydrators;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Router;
use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Throwable;

/**
 * Account controller
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountV1Controller extends BaseV1Controller
{

	/** @var Hydrators\Accounts\ProfileAccountHydrator */
	private Hydrators\Accounts\ProfileAccountHydrator $accountHydrator;

	/** @var Models\Accounts\IAccountsManager */
	private Models\Accounts\IAccountsManager $accountsManager;

	public function __construct(
		Hydrators\Accounts\ProfileAccountHydrator $accountHydrator,
		Models\Accounts\IAccountsManager $accountsManager
	) {
		$this->accountHydrator = $accountHydrator;

		$this->accountsManager = $accountsManager;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$account = $this->findAccount();

		return $this->buildResponse($request, $response, $account);
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
				$this->translator->translate('//accounts-module.base.messages.forbidden.heading'),
				$this->translator->translate('//accounts-module.base.messages.forbidden.message')
			);
		}

		return $this->user->getAccount();
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$account = $this->findAccount();

		$document = $this->createDocument($request);

		if ($account->getPlainId() !== $document->getResource()->getId()) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//accounts-module.base.messages.invalidIdentifier.heading'),
				$this->translator->translate('//accounts-module.base.messages.invalidIdentifier.message')
			);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if (
				$document->getResource()->getType() === Schemas\Accounts\AccountSchema::SCHEMA_TYPE
				&& $account instanceof Entities\Accounts\IAccount
			) {
				$account = $this->accountsManager->update(
					$account,
					$this->accountHydrator->hydrate($document, $account)
				);

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//accounts-module.base.messages.invalidType.heading'),
					$this->translator->translate('//accounts-module.base.messages.invalidType.message'),
					[
						'pointer' => '/data/type',
					]
				);
			}

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (JsonApiExceptions\IJsonApiException $ex) {
			throw $ex;

		} catch (Throwable $ex) {
			// Log caught exception
			$this->logger->error('An unhandled error occurred', [
				'source'    => 'accounts-module-account-controller',
				'type'      => 'update',
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//accounts-module.base.messages.notUpdated.heading'),
				$this->translator->translate('//accounts-module.base.messages.notUpdated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		return $this->buildResponse($request, $response, $account);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$this->findAccount();

		// TODO: Closing account not implemented yet

		return $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Message\ResponseInterface $response
	 *
	 * @return Message\ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		Message\ResponseInterface $response
	): Message\ResponseInterface {
		$account = $this->findAccount();

		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if (
			$relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_EMAILS
			&& $account instanceof Entities\Accounts\IAccount
		) {
			return $this->buildResponse($request, $response, $account->getEmails());

		} elseif ($relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_IDENTITIES) {
			return $this->buildResponse($request, $response, $account->getIdentities());

		} elseif ($relationEntity === Schemas\Accounts\AccountSchema::RELATIONSHIPS_ROLES) {
			return $this->buildResponse($request, $response, $account->getRoles());
		}

		return parent::readRelationship($request, $response);
	}

}
