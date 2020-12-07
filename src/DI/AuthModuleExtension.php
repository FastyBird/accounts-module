<?php declare(strict_types = 1);

/**
 * AuthModuleExtension.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           30.11.20
 */

namespace FastyBird\AuthModule\DI;

use Contributte\Translation;
use Doctrine\Persistence;
use FastyBird\AuthModule\Commands;
use FastyBird\AuthModule\Controllers;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Helpers;
use FastyBird\AuthModule\Hydrators;
use FastyBird\AuthModule\Middleware;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\AuthModule\Security;
use FastyBird\AuthModule\Subscribers;
use IPub\DoctrineCrud;
use Nette;
use Nette\DI;
use Nette\PhpGenerator;

/**
 * Auth module extension container
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AuthModuleExtension extends DI\CompilerExtension implements Translation\DI\TranslationProviderInterface
{

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'fbAuthModule'
	): void {
		$config->onCompile[] = function (
			Nette\Configurator $config,
			DI\Compiler $compiler
		) use ($extensionName): void {
			$compiler->addExtension($extensionName, new AuthModuleExtension());
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// Http router
		$builder->addDefinition(null)
			->setType(Middleware\AccessMiddleware::class);

		$builder->addDefinition(null)
			->setType(Middleware\UrlFormatMiddleware::class)
			->addTag('middleware', ['priority' => 150]);

		$builder->addDefinition(null)
			->setType(Router\Router::class)
			->addSetup('registerRoutes');

		// Console commands
		$builder->addDefinition(null)
			->setType(Commands\Accounts\CreateCommand::class);

		$builder->addDefinition(null)
			->setType(Commands\InitializeCommand::class);

		// Database repositories
		$builder->addDefinition(null)
			->setType(Models\Accounts\AccountRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Emails\EmailRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Identities\IdentityRepository::class);

		$builder->addDefinition(null)
			->setType(Models\Roles\RoleRepository::class);

		// Database managers
		$builder->addDefinition($this->prefix('doctrine.accountsManager'))
			->setType(Models\Accounts\AccountsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.emailsManager'))
			->setType(Models\Emails\EmailsManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.identitiesManager'))
			->setType(Models\Identities\IdentitiesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		$builder->addDefinition($this->prefix('doctrine.rolesManager'))
			->setType(Models\Roles\RolesManager::class)
			->setArgument('entityCrud', '__placeholder__');

		// Events subscribers
		$builder->addDefinition(null)
			->setType(Subscribers\AccountEntitySubscriber::class);

		$builder->addDefinition(null)
			->setType(Subscribers\EmailEntitySubscriber::class);

		// API controllers
		$builder->addDefinition(null)
			->setType(Controllers\SessionV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\AccountV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\AccountEmailsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\AccountIdentitiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\AccountsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\EmailsV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\IdentitiesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\RolesV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\RoleChildrenV1Controller::class)
			->addTag('nette.inject');

		$builder->addDefinition(null)
			->setType(Controllers\PublicV1Controller::class)
			->addTag('nette.inject');

		// API schemas
		$builder->addDefinition(null)
			->setType(Schemas\Accounts\UserAccountSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Accounts\MachineAccountSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Emails\EmailSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Identities\UserAccountIdentitySchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Identities\MachineAccountIdentitySchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Roles\RoleSchema::class);

		$builder->addDefinition(null)
			->setType(Schemas\Sessions\SessionSchema::class);

		// API hydrators
		$builder->addDefinition(null)
			->setType(Hydrators\Accounts\ProfileAccountHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Accounts\UserAccountHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Accounts\MachineAccountHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Emails\ProfileEmailHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Emails\EmailHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Identities\UserAccountIdentityHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Identities\MachineAccountIdentityHydrator::class);

		$builder->addDefinition(null)
			->setType(Hydrators\Roles\RoleHydrator::class);

		// Security
		$builder->addDefinition(null)
			->setType(Helpers\SecurityHash::class);

		$builder->addDefinition(null)
			->setType(Security\IdentityFactory::class);

		$builder->addDefinition(null)
			->setType(Security\Authenticator::class);

		// Nette services overwrite
		$builder->addDefinition('security.user')
			->setType(Security\User::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		/**
		 * Doctrine entities
		 */

		$ormAnnotationDriverService = $builder->getDefinition('nettrineOrmAnnotations.annotationDriver');

		if ($ormAnnotationDriverService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverService->addSetup('addPaths', [[__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Entities']]);
		}

		$ormAnnotationDriverChainService = $builder->getDefinitionByType(Persistence\Mapping\Driver\MappingDriverChain::class);

		if ($ormAnnotationDriverChainService instanceof DI\Definitions\ServiceDefinition) {
			$ormAnnotationDriverChainService->addSetup('addDriver', [
				$ormAnnotationDriverService,
				'FastyBird\AuthModule\Entities',
			]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function afterCompile(
		PhpGenerator\ClassType $class
	): void {
		$builder = $this->getContainerBuilder();

		$entityFactoryServiceName = $builder->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class, true);

		$accountsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__accountsManager');
		$accountsManagerService->setBody('return new ' . Models\Accounts\AccountsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Accounts\Account::class . '\'));');

		$emailsManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__emailsManager');
		$emailsManagerService->setBody('return new ' . Models\Emails\EmailsManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Emails\Email::class . '\'));');

		$identitiesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__identitiesManager');
		$identitiesManagerService->setBody('return new ' . Models\Identities\IdentitiesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Identities\Identity::class . '\'));');

		$rolesManagerService = $class->getMethod('createService' . ucfirst($this->name) . '__doctrine__rolesManager');
		$rolesManagerService->setBody('return new ' . Models\Roles\RolesManager::class . '($this->getService(\'' . $entityFactoryServiceName . '\')->create(\'' . Entities\Roles\Role::class . '\'));');
	}

	/**
	 * @return string[]
	 */
	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../Translations',
		];
	}

}
