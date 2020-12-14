<?php declare(strict_types = 1);

/**
 * AccountIdentitiesV1Controller.php
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
use FastyBird\AuthModule\Controllers;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Utils;
use Psr\Http\Message;
use Throwable;

/**
 * Account identity controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountIdentitiesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TIdentityFinder;

	/** @var Models\Identities\IIdentityRepository */
	protected $identityRepository;

	/** @var string */
	protected $translationDomain = 'module.identities';

	/** @var Models\Identities\IIdentitiesManager */
	private $identitiesManager;

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
			$this->getOrmConnection()
				->beginTransaction();

			if (
				$document->getResource()
					->getType() === Schemas\Identities\UserAccountIdentitySchema::SCHEMA_TYPE
				&& $identity instanceof Entities\Identities\IUserAccountIdentity
			) {
				$attributes = $document->getResource()
					->getAttributes();

				if (
					!$attributes->has('password')
					|| !$attributes->get('password')
						->has('current')
				) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//module.base.messages.missingAttribute.heading'),
						$this->translator->translate('//module.base.messages.missingAttribute.message'),
						[
							'pointer' => '/data/attributes/password/current',
						]
					);
				}

				if (
					!$attributes->has('password')
					|| !$attributes->get('password')
						->has('new')
				) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//module.base.messages.missingAttribute.heading'),
						$this->translator->translate('//module.base.messages.missingAttribute.message'),
						[
							'pointer' => '/data/attributes/password/new',
						]
					);
				}

				if (!$identity->verifyPassword((string) $attributes->get('password')
					->get('current'))) {
					throw new JsonApiExceptions\JsonApiErrorException(
						StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
						$this->translator->translate('//module.base.messages.invalidAttribute.heading'),
						$this->translator->translate('//module.base.messages.invalidAttribute.message'),
						[
							'pointer' => '/data/attributes/password/current',
						]
					);
				}

				$update = new Utils\ArrayHash();
				$update->offsetSet('password', (string) $attributes->get('password')
					->get('new'));

				// Update item in database
				$this->identitiesManager->update($identity, $update);

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

}
