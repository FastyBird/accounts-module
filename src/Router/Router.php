<?php declare(strict_types = 1);

/**
 * RouterFactory.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Router
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AuthModule\Router;

use FastyBird\AuthModule;
use FastyBird\AuthModule\Controllers;
use FastyBird\AuthModule\Middleware;
use FastyBird\SimpleAuth\Middleware as SimpleAuthMiddleware;
use IPub\SlimRouter\Routing;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Module router configuration
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Router extends Routing\Router
{

	public const URL_ITEM_ID = 'id';

	public const URL_ACCOUNT_ID = 'account';

	public const RELATION_ENTITY = 'relationEntity';

	/** @var Controllers\PublicV1Controller */
	private $publicV1Controller;

	/** @var Controllers\SessionV1Controller */
	private $sessionV1Controller;

	/** @var Controllers\AccountV1Controller */
	private $accountV1Controller;

	/** @var Controllers\AccountEmailsV1Controller */
	private $accountEmailsV1Controller;

	/** @var Controllers\AccountIdentitiesV1Controller */
	private $accountIdentitiesV1Controller;

	/** @var Controllers\RolesV1Controller */
	private $rolesV1Controller;

	/** @var Controllers\RoleChildrenV1Controller */
	private $roleChildrenV1Controller;

	/** @var Controllers\AccountsV1Controller */
	private $accountsV1Controller;

	/** @var Controllers\EmailsV1Controller */
	private $emailsV1Controller;

	/** @var Controllers\IdentitiesV1Controller */
	private $identitiesV1Controller;

	/** @var Middleware\AccessMiddleware */
	private $authAccessControlMiddleware;

	/** @var SimpleAuthMiddleware\AccessMiddleware */
	private $accessControlMiddleware;

	/** @var SimpleAuthMiddleware\UserMiddleware */
	private $userMiddleware;

	public function __construct(
		Controllers\PublicV1Controller $publicV1Controller,
		Controllers\SessionV1Controller $sessionV1Controller,
		Controllers\AccountV1Controller $accountV1Controller,
		Controllers\AccountEmailsV1Controller $accountEmailsV1Controller,
		Controllers\AccountIdentitiesV1Controller $accountIdentitiesV1Controller,
		Controllers\AccountsV1Controller $accountsV1Controller,
		Controllers\EmailsV1Controller $emailsV1Controller,
		Controllers\IdentitiesV1Controller $identitiesV1Controller,
		Controllers\RolesV1Controller $rolesV1Controller,
		Controllers\RoleChildrenV1Controller $roleChildrenV1Controller,
		Middleware\AccessMiddleware $authAccessControlMiddleware,
		SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware,
		SimpleAuthMiddleware\UserMiddleware $userMiddleware,
		?ResponseFactoryInterface $responseFactory = null
	) {
		parent::__construct($responseFactory, null);

		$this->publicV1Controller = $publicV1Controller;

		$this->sessionV1Controller = $sessionV1Controller;

		$this->accountV1Controller = $accountV1Controller;
		$this->accountEmailsV1Controller = $accountEmailsV1Controller;
		$this->accountIdentitiesV1Controller = $accountIdentitiesV1Controller;

		$this->accountsV1Controller = $accountsV1Controller;
		$this->emailsV1Controller = $emailsV1Controller;
		$this->identitiesV1Controller = $identitiesV1Controller;
		$this->rolesV1Controller = $rolesV1Controller;
		$this->roleChildrenV1Controller = $roleChildrenV1Controller;

		$this->authAccessControlMiddleware = $authAccessControlMiddleware;
		$this->accessControlMiddleware = $accessControlMiddleware;
		$this->userMiddleware = $userMiddleware;
	}

	/**
	 * @return void
	 */
	public function registerRoutes(): void
	{
		$routes = $this->group('/v1', function (Routing\RouteCollector $group): void {
			$group->post('/reset-identity', [$this->publicV1Controller, 'resetIdentity']);

			$group->post('/register', [$this->publicV1Controller, 'register']);

			/**
			 * SESSION
			 */
			$group->group('/session', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->sessionV1Controller, 'read']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_SESSION);

				$group->post('', [$this->sessionV1Controller, 'create']);

				$group->patch('', [$this->sessionV1Controller, 'update']);

				$group->delete('', [$this->sessionV1Controller, 'delete']);

				$route = $group->get('/relationships/{' . self::RELATION_ENTITY . '}', [$this->sessionV1Controller, 'readRelationship']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_SESSION_RELATIONSHIP);
			});

			/**
			 * PROFILE
			 */
			$group->group('/me', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->accountV1Controller, 'read']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ME);

				$group->patch('', [$this->accountV1Controller, 'update']);

				$group->delete('', [$this->accountV1Controller, 'delete']);

				$route = $group->get('/relationships/{' . self::RELATION_ENTITY . '}', [$this->accountV1Controller, 'readRelationship']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ME_RELATIONSHIP);

				/**
				 * PROFILE EMAILS
				 */
				$group->group('/emails', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->accountEmailsV1Controller, 'index']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_EMAILS);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'read']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_EMAIL);

					$group->post('', [$this->accountEmailsV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'update']);

					$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'delete']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->accountEmailsV1Controller, 'readRelationship']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_EMAIL_RELATIONSHIP);
				});

				/**
				 * PROFILE IDENTITIES
				 */
				$group->group('/identities', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->accountIdentitiesV1Controller, 'index']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_IDENTITIES);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->accountIdentitiesV1Controller, 'read']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_IDENTITY);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountIdentitiesV1Controller, 'update']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->accountIdentitiesV1Controller, 'readRelationship']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ME_IDENTITY_RELATIONSHIP);
				});
			});

			/**
			 * ACCOUNTS
			 */
			$group->group('/accounts', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->accountsV1Controller, 'index']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNTS);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'read']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT);

				$group->post('', [$this->accountsV1Controller, 'create']);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'update']);

				$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'delete']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->accountsV1Controller, 'readRelationship']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_RELATIONSHIP);
			});

			$group->group('/accounts/{' . self::URL_ACCOUNT_ID . '}', function (Routing\RouteCollector $group): void {
				/**
				 * ACCOUNT IDENTITIES
				 */
				$group->group('/identities', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->identitiesV1Controller, 'index']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITIES);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->identitiesV1Controller, 'read']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY);

					$group->post('', [$this->identitiesV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->identitiesV1Controller, 'update']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->identitiesV1Controller, 'readRelationship']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY_RELATIONSHIP);
				});

				/**
				 * ACCOUNT EMAILS
				 */
				$group->group('/emails', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->emailsV1Controller, 'index']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_EMAILS);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'read']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_EMAIL);

					$group->post('', [$this->emailsV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'update']);

					$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'delete']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->emailsV1Controller, 'readRelationship']);
					$route->setName(AuthModule\Constants::ROUTE_NAME_ACCOUNT_EMAIL_RELATIONSHIP);
				});
			});

			/**
			 * ACCESS ROLES
			 */
			$group->group('/roles', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->rolesV1Controller, 'index']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ROLES);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->rolesV1Controller, 'read']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ROLE);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->rolesV1Controller, 'update']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [$this->rolesV1Controller, 'readRelationship']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ROLE_RELATIONSHIP);

				/**
				 * CHILDREN
				 */
				$route = $group->get('/{' . self::URL_ITEM_ID . '}/children', [$this->roleChildrenV1Controller, 'index']);
				$route->setName(AuthModule\Constants::ROUTE_NAME_ROLE_CHILDREN);
			});

			$group->group('/authenticate', function (Routing\RouteCollector $group): void {
			});
		});

		$routes->addMiddleware($this->accessControlMiddleware);
		$routes->addMiddleware($this->userMiddleware);
		$routes->addMiddleware($this->authAccessControlMiddleware);
	}

}
