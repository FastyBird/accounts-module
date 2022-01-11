<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\AccountsModule\Commands;
use FastyBird\AccountsModule\Controllers;
use FastyBird\AccountsModule\DI;
use FastyBird\AccountsModule\Hydrators;
use FastyBird\AccountsModule\Middleware;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Router;
use FastyBird\AccountsModule\Schemas;
use FastyBird\AccountsModule\Subscribers;
use Nette;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ServicesTest extends BaseTestCase
{

	public function testServicesRegistration(): void
	{
		$container = $this->createContainer();

		Assert::notNull($container->getByType(Middleware\AccessMiddleware::class));
		Assert::notNull($container->getByType(Middleware\UrlFormatMiddleware::class));

		Assert::notNull($container->getByType(Commands\Accounts\CreateCommand::class));
		Assert::notNull($container->getByType(Commands\InitializeCommand::class));

		Assert::notNull($container->getByType(Subscribers\EntitiesSubscriber::class));
		Assert::notNull($container->getByType(Subscribers\AccountEntitySubscriber::class));
		Assert::notNull($container->getByType(Subscribers\EmailEntitySubscriber::class));

		Assert::notNull($container->getByType(Models\Accounts\AccountRepository::class));
		Assert::notNull($container->getByType(Models\Emails\EmailRepository::class));
		Assert::notNull($container->getByType(Models\Identities\IdentityRepository::class));
		Assert::notNull($container->getByType(Models\Roles\RoleRepository::class));

		Assert::notNull($container->getByType(Models\Accounts\AccountsManager::class));
		Assert::notNull($container->getByType(Models\Emails\EmailsManager::class));
		Assert::notNull($container->getByType(Models\Identities\IdentitiesManager::class));
		Assert::notNull($container->getByType(Models\Roles\RolesManager::class));

		Assert::notNull($container->getByType(Controllers\AccountV1Controller::class));
		Assert::notNull($container->getByType(Controllers\AccountEmailsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\SessionV1Controller::class));
		Assert::notNull($container->getByType(Controllers\AccountIdentitiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\AccountsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\EmailsV1Controller::class));
		Assert::notNull($container->getByType(Controllers\IdentitiesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\RolesV1Controller::class));
		Assert::notNull($container->getByType(Controllers\RoleChildrenV1Controller::class));

		Assert::notNull($container->getByType(Router\Validator::class));
		Assert::notNull($container->getByType(Router\Routes::class));

		Assert::notNull($container->getByType(Schemas\Accounts\AccountSchema::class));
		Assert::notNull($container->getByType(Schemas\Emails\EmailSchema::class));
		Assert::notNull($container->getByType(Schemas\Sessions\SessionSchema::class));
		Assert::notNull($container->getByType(Schemas\Identities\IdentitySchema::class));
		Assert::notNull($container->getByType(Schemas\Roles\RoleSchema::class));

		Assert::notNull($container->getByType(Hydrators\Accounts\ProfileAccountHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Accounts\AccountHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Identities\IdentityHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Emails\ProfileEmailHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Emails\EmailHydrator::class));
		Assert::notNull($container->getByType(Hydrators\Roles\RoleHydrator::class));
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer(): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../..';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../../common.neon');

		DI\AccountsModuleExtension::register($config);

		return $config->createContainer();
	}

}

$test_case = new ServicesTest();
$test_case->run();
