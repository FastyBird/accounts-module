<?php declare(strict_types = 1);

/**
 * RolesV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           02.04.20
 */

namespace FastyBird\AuthModule\Controllers;

use Doctrine;
use FastyBird\AuthModule\Controllers;
use FastyBird\AuthModule\Hydrators;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message;
use Throwable;

/**
 * ACL roles controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class RolesV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TRoleFinder;

	/** @var Hydrators\Roles\RoleHydrator */
	private $roleHydrator;

	/** @var Models\Roles\IRoleRepository */
	private $roleRepository;

	/** @var Models\Roles\IRolesManager */
	private $rolesManager;

	/** @var string */
	protected $translationDomain = 'module.roles';

	public function __construct(
		Models\Roles\IRoleRepository $roleRepository,
		Models\Roles\IRolesManager $rolesManager,
		Hydrators\Roles\RoleHydrator $roleHydrator
	) {
		$this->roleRepository = $roleRepository;
		$this->rolesManager = $rolesManager;
		$this->roleHydrator = $roleHydrator;
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
		$findQuery = new Queries\FindRolesQuery();

		$roles = $this->roleRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($roles));
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
		$role = $this->findRole($request);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($role));
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
	 * @Secured\Role(manager,administrator)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$role = $this->findRole($request);

		$this->validateIdentifier($request, $document);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($document->getResource()->getType() === Schemas\Roles\RoleSchema::SCHEMA_TYPE) {
				$updateRoleData = $this->roleHydrator->hydrate($document, $role);

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

			$role = $this->rolesManager->update($role, $updateRoleData);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

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
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($role));
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
		$role = $this->findRole($request);

		$relationEntity = strtolower($request->getAttribute(Router\Router::RELATION_ENTITY));

		if ($relationEntity === Schemas\Roles\RoleSchema::RELATIONSHIPS_PARENT) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($role->getParent()));

		} elseif ($relationEntity === Schemas\Roles\RoleSchema::RELATIONSHIPS_CHILDREN) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($role->getChildren()));
		}

		return parent::readRelationship($request, $response);
	}

}
