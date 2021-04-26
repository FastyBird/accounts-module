<?php declare(strict_types = 1);

/**
 * Routes.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Router
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Router;

use FastyBird\AccountsModule;
use FastyBird\AccountsModule\Controllers;
use FastyBird\AccountsModule\Middleware;
use FastyBird\ModulesMetadata;
use FastyBird\SimpleAuth\Middleware as SimpleAuthMiddleware;
use FastyBird\WebServer\Router as WebServerRouter;
use IPub\SlimRouter\Routing;

/**
 * Module router configuration
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Routes implements WebServerRouter\IRoutes
{

	public const URL_ITEM_ID = 'id';

	public const URL_ACCOUNT_ID = 'account';

	public const RELATION_ENTITY = 'relationEntity';

	/** @var bool */
	private bool $usePrefix;

	/** @var Controllers\PublicV1Controller */
	private Controllers\PublicV1Controller $publicV1Controller;

	/** @var Controllers\SessionV1Controller */
	private Controllers\SessionV1Controller $sessionV1Controller;

	/** @var Controllers\AccountV1Controller */
	private Controllers\AccountV1Controller $accountV1Controller;

	/** @var Controllers\AccountEmailsV1Controller */
	private Controllers\AccountEmailsV1Controller $accountEmailsV1Controller;

	/** @var Controllers\AccountIdentitiesV1Controller */
	private Controllers\AccountIdentitiesV1Controller $accountIdentitiesV1Controller;

	/** @var Controllers\RolesV1Controller */
	private Controllers\RolesV1Controller $rolesV1Controller;

	/** @var Controllers\RoleChildrenV1Controller */
	private Controllers\RoleChildrenV1Controller $roleChildrenV1Controller;

	/** @var Controllers\AccountsV1Controller */
	private Controllers\AccountsV1Controller $accountsV1Controller;

	/** @var Controllers\EmailsV1Controller */
	private Controllers\EmailsV1Controller $emailsV1Controller;

	/** @var Controllers\IdentitiesV1Controller */
	private Controllers\IdentitiesV1Controller $identitiesV1Controller;

	/** @var Middleware\AccessMiddleware */
	private Middleware\AccessMiddleware $authAccessControlMiddleware;

	/** @var SimpleAuthMiddleware\AccessMiddleware */
	private SimpleAuthMiddleware\AccessMiddleware $accessControlMiddleware;

	/** @var SimpleAuthMiddleware\UserMiddleware */
	private SimpleAuthMiddleware\UserMiddleware $userMiddleware;

	public function __construct(
		bool $usePrefix,
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
		SimpleAuthMiddleware\UserMiddleware $userMiddleware
	) {
		$this->usePrefix = $usePrefix;

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
	 * @param Routing\IRouter $router
	 *
	 * @return void
	 */
	public function registerRoutes(Routing\IRouter $router): void
	{
		if ($this->usePrefix) {
			$routes = $router->group('/' . ModulesMetadata\Constants::MODULE_ACCOUNTS_PREFIX, function (Routing\RouteCollector $group): void {
				$this->buildRoutes($group);
			});

		} else {
			$routes = $this->buildRoutes($router);
		}

		$routes->addMiddleware($this->accessControlMiddleware);
		$routes->addMiddleware($this->userMiddleware);
		$routes->addMiddleware($this->authAccessControlMiddleware);
	}

	/**
	 * @param Routing\IRouter | Routing\IRouteCollector $group
	 *
	 * @return Routing\IRouteGroup
	 */
	private function buildRoutes($group): Routing\IRouteGroup
	{
		return $group->group('/v1', function (Routing\RouteCollector $group): void {
			$group->post('/reset-identity', [$this->publicV1Controller, 'resetIdentity']);

			$group->post('/register', [$this->publicV1Controller, 'register']);

			/**
			 * SESSION
			 */
			$group->group('/session', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->sessionV1Controller, 'read']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_SESSION);

				$group->post('', [$this->sessionV1Controller, 'create']);

				$group->patch('', [$this->sessionV1Controller, 'update']);

				$group->delete('', [$this->sessionV1Controller, 'delete']);

				$route = $group->get('/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->sessionV1Controller,
					'readRelationship',
				]);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_SESSION_RELATIONSHIP);
			});

			/**
			 * PROFILE
			 */
			$group->group('/me', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->accountV1Controller, 'read']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ME);

				$group->patch('', [$this->accountV1Controller, 'update']);

				$group->delete('', [$this->accountV1Controller, 'delete']);

				$route = $group->get('/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->accountV1Controller,
					'readRelationship',
				]);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_RELATIONSHIP);

				/**
				 * PROFILE EMAILS
				 */
				$group->group('/emails', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->accountEmailsV1Controller, 'index']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_EMAILS);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'read']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_EMAIL);

					$group->post('', [$this->accountEmailsV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'update']);

					$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->accountEmailsV1Controller, 'delete']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
						$this->accountEmailsV1Controller,
						'readRelationship',
					]);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_EMAIL_RELATIONSHIP);
				});

				/**
				 * PROFILE IDENTITIES
				 */
				$group->group('/identities', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->accountIdentitiesV1Controller, 'index']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_IDENTITIES);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [
						$this->accountIdentitiesV1Controller,
						'read',
					]);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_IDENTITY);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountIdentitiesV1Controller, 'update']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
						$this->accountIdentitiesV1Controller,
						'readRelationship',
					]);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ME_IDENTITY_RELATIONSHIP);
				});
			});

			/**
			 * ACCOUNTS
			 */
			$group->group('/accounts', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->accountsV1Controller, 'index']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNTS);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'read']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT);

				$group->post('', [$this->accountsV1Controller, 'create']);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'update']);

				$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->accountsV1Controller, 'delete']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->accountsV1Controller,
					'readRelationship',
				]);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_RELATIONSHIP);
			});

			$group->group('/accounts/{' . self::URL_ACCOUNT_ID . '}', function (Routing\RouteCollector $group): void {
				/**
				 * ACCOUNT IDENTITIES
				 */
				$group->group('/identities', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->identitiesV1Controller, 'index']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITIES);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->identitiesV1Controller, 'read']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY);

					$group->post('', [$this->identitiesV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->identitiesV1Controller, 'update']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
						$this->identitiesV1Controller,
						'readRelationship',
					]);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY_RELATIONSHIP);
				});

				/**
				 * ACCOUNT EMAILS
				 */
				$group->group('/emails', function (Routing\RouteCollector $group): void {
					$route = $group->get('', [$this->emailsV1Controller, 'index']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_EMAILS);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'read']);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_EMAIL);

					$group->post('', [$this->emailsV1Controller, 'create']);

					$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'update']);

					$group->delete('/{' . self::URL_ITEM_ID . '}', [$this->emailsV1Controller, 'delete']);

					$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
						$this->emailsV1Controller,
						'readRelationship',
					]);
					$route->setName(AccountsModule\Constants::ROUTE_NAME_ACCOUNT_EMAIL_RELATIONSHIP);
				});
			});

			/**
			 * ACCESS ROLES
			 */
			$group->group('/roles', function (Routing\RouteCollector $group): void {
				$route = $group->get('', [$this->rolesV1Controller, 'index']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ROLES);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}', [$this->rolesV1Controller, 'read']);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ROLE);

				$group->patch('/{' . self::URL_ITEM_ID . '}', [$this->rolesV1Controller, 'update']);

				$route = $group->get('/{' . self::URL_ITEM_ID . '}/relationships/{' . self::RELATION_ENTITY . '}', [
					$this->rolesV1Controller,
					'readRelationship',
				]);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ROLE_RELATIONSHIP);

				/**
				 * CHILDREN
				 */
				$route = $group->get('/{' . self::URL_ITEM_ID . '}/children', [
					$this->roleChildrenV1Controller,
					'index',
				]);
				$route->setName(AccountsModule\Constants::ROUTE_NAME_ROLE_CHILDREN);
			});

			$group->group('/authenticate', function (Routing\RouteCollector $group): void {
			});
		});
	}

}
