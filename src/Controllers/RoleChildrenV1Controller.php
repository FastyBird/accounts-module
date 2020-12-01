<?php declare(strict_types = 1);

/**
 * RoleChildrenV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           03.06.20
 */

namespace FastyBird\AuthModule\Controllers;

use FastyBird\AuthModule\Controllers;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Psr\Http\Message;

/**
 * Role children API controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @Secured
 * @Secured\User(loggedIn)
 */
final class RoleChildrenV1Controller extends BaseV1Controller
{

	use Controllers\Finders\TRoleFinder;

	/** @var Models\Roles\IRoleRepository */
	private $roleRepository;

	/** @var string */
	protected $translationDomain = 'module.roles';

	public function __construct(
		Models\Roles\IRoleRepository $roleRepository
	) {
		$this->roleRepository = $roleRepository;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function index(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// At first, try to load role
		$role = $this->findRole($request);

		$findQuery = new Queries\FindRolesQuery();
		$findQuery->forParent($role);

		$children = $this->roleRepository->getResultSet($findQuery);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($children));
	}

}
