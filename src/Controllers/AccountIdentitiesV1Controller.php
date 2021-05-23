<?php declare(strict_types = 1);

/**
 * AccountIdentitiesV1Controller.php
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
use FastyBird\AccountsModule\Controllers;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Queries;
use FastyBird\AccountsModule\Router;
use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument\Objects as JsonAPIDocumentObjects;
use Nette\Utils;
use Psr\Http\Message;
use Throwable;

/**
 * Account identity controller
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountIdentitiesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TIdentityFinder;

	/** @var Models\Identities\IIdentityRepository */
	protected Models\Identities\IIdentityRepository $identityRepository;

	/** @var Models\Identities\IIdentitiesManager */
	private Models\Identities\IIdentitiesManager $identitiesManager;

	/** @var string */
	protected string $translationDomain = 'accounts-module.identities';

	public function __construct(
		Models\Identities\IIdentityRepository $identityRepository,
		Models\Identities\IIdentitiesManager $identitiesManager
	) {
		$this->identityRepository = $identityRepository;
		$this->identitiesManager = $identitiesManager;
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
	public function index(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$findQuery = new Queries\FindIdentitiesQuery();
		$findQuery->forAccount($this->findAccount());

		$identities = $this->identityRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($identities));
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
		// Find identity
		$identity = $this->findIdentity($request, $this->findAccount());

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($identity));
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
		$document = $this->createDocument($request);

		$identity = $this->findIdentity($request, $this->findAccount());

		$this->validateIdentifier($request, $document);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if (
				$document->getResource()->getType() === Schemas\Identities\IdentitySchema::SCHEMA_TYPE
				&& $identity instanceof Entities\Identities\IIdentity
			) {
				$attributes = $document->getResource()->getAttributes();

				$passwordAttribute = $attributes->get('password');

				if (
					!$passwordAttribute instanceof JsonAPIDocumentObjects\IStandardObject
					|| !$passwordAttribute->has('current')
					|| !is_scalar($passwordAttribute->get('current'))
				) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
						$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
						[
							'pointer' => '/data/attributes/password/current',
						]
					);
				}

				if (
					!$passwordAttribute instanceof JsonAPIDocumentObjects\IStandardObject
					|| !$passwordAttribute->has('new')
					|| !is_scalar($passwordAttribute->get('new'))
				) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
						$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
						[
							'pointer' => '/data/attributes/password/new',
						]
					);
				}

				if (!$identity->verifyPassword((string) $passwordAttribute->get('current'))) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//accounts-module.base.messages.invalidAttribute.heading'),
						$this->translator->translate('//accounts-module.base.messages.invalidAttribute.message'),
						[
							'pointer' => '/data/attributes/password/current',
						]
					);
				}

				$update = new Utils\ArrayHash();
				$update->offsetSet('password', (string) $passwordAttribute->get('new'));

				// Update item in database
				$this->identitiesManager->update($identity, $update);

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
			$this->logger->error('[FB:AUTH_MODULE:CONTROLLER] ' . $ex->getMessage(), [
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
		$identity = $this->findIdentity($request, $this->findAccount());

		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Routes::RELATION_ENTITY));

		if ($relationEntity === Schemas\Identities\IdentitySchema::RELATIONSHIPS_ACCOUNT) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($identity->getAccount()));
		}

		return parent::readRelationship($request, $response);
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

}
