<?php declare(strict_types = 1);

namespace FastyBird\Module\Accounts\Tests\Cases\Unit\Commands;

use Contributte\Translation;
use Doctrine\Persistence;
use Error;
use FastyBird\Core\Application\Exceptions as ApplicationExceptions;
use FastyBird\Module\Accounts\Commands;
use FastyBird\Module\Accounts\Exceptions;
use FastyBird\Module\Accounts\Helpers;
use FastyBird\Module\Accounts\Models;
use FastyBird\Module\Accounts\Queries;
use FastyBird\Module\Accounts\Tests;
use FastyBird\SimpleAuth;
use FastyBird\SimpleAuth\Models as SimpleAuthModels;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use IPub\DoctrineOrmQuery\Exceptions as DoctrineOrmQueryExceptions;
use Nette;
use RuntimeException;
use Symfony\Component\Console;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class CreateAccountTest extends Tests\Cases\Unit\DbTestCase
{

	/**
	 * @throws ApplicationExceptions\InvalidArgument
	 * @throws DoctrineOrmQueryExceptions\InvalidStateException
	 * @throws DoctrineOrmQueryExceptions\QueryException
	 * @throws Console\Exception\CommandNotFoundException
	 * @throws Console\Exception\LogicException
	 * @throws Exceptions\InvalidArgument
	 * @throws Exceptions\InvalidState
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 */
	public function testExecute(): void
	{
		$accountsManager = $this->getContainer()->getByType(Models\Entities\Accounts\AccountsManager::class);

		$emailsRepository = $this->getContainer()->getByType(Models\Entities\Emails\EmailsRepository::class);

		$emailsManager = $this->getContainer()->getByType(Models\Entities\Emails\EmailsManager::class);

		$identitiesManager = $this->getContainer()->getByType(Models\Entities\Identities\IdentitiesManager::class);

		$enforcerFactory = $this->getContainer()->getByType(SimpleAuthSecurity\EnforcerFactory::class);

		$policiesRepository = $this->getContainer()->getByType(SimpleAuthModels\Policies\Repository::class);

		$identitiesRepository = $this->getContainer()->getByType(
			Models\Entities\Identities\IdentitiesRepository::class,
		);

		$translator = $this->getContainer()->getByType(Translation\Translator::class);

		$managerRegistry = $this->getContainer()->getByType(Persistence\ManagerRegistry::class);

		$application = new Application();
		$application->add(new Commands\Accounts\Create(
			$accountsManager,
			$emailsRepository,
			$emailsManager,
			$identitiesManager,
			$translator,
			$enforcerFactory,
			$policiesRepository,
			$managerRegistry,
		));

		$command = $application->get(Commands\Accounts\Create::NAME);

		$commandTester = new CommandTester($command);
		$result = $commandTester->execute([
			'lastName' => 'Balboa',
			'firstName' => 'Rocky',
			'email' => 'rocky@balboa.com',
			'password' => 'someRandomPassword',
			'role' => SimpleAuth\Constants::ROLE_USER,
		]);

		self::assertSame(0, $result);

		$findEmailQuery = new Queries\Entities\FindEmails();
		$findEmailQuery->byAddress('rocky@balboa.com');

		$email = $emailsRepository->findOneBy($findEmailQuery);

		self::assertNotNull($email);
		self::assertSame('Balboa Rocky', $email->getAccount()->getName());

		$findIdentity = new Queries\Entities\FindIdentities();
		$findIdentity->byUid('rocky@balboa.com');

		$identity = $identitiesRepository->findOneBy($findIdentity);

		self::assertNotNull($identity);

		$password = new Helpers\Password(
			null,
			'someRandomPassword',
			$identity->getSalt(),
		);

		self::assertSame($password->getHash(), $identity->getPassword()->getHash());
	}

}
