<?php declare(strict_types = 1);

/**
 * CreateCommand.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Commands
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Commands\Accounts;

use Contributte\Translation;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Queries;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use FastyBird\SimpleAuth;
use Nette\Utils;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;
use Throwable;

/**
 * Account creation command
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class CreateCommand extends Console\Command\Command
{

	/** @var Models\Accounts\IAccountsManager */
	private Models\Accounts\IAccountsManager $accountsManager;

	/** @var Models\Emails\IEmailRepository */
	private Models\Emails\IEmailRepository $emailRepository;

	/** @var Models\Emails\IEmailsManager */
	private Models\Emails\IEmailsManager $emailsManager;

	/** @var Models\Identities\IIdentitiesManager */
	private Models\Identities\IIdentitiesManager $identitiesManager;

	/** @var Models\Roles\IRoleRepository */
	private Models\Roles\IRoleRepository $roleRepository;

	/** @var Persistence\ManagerRegistry */
	private Persistence\ManagerRegistry $managerRegistry;

	/** @var Translation\PrefixedTranslator */
	private Translation\PrefixedTranslator $translator;

	/** @var string */
	private string $translationDomain = 'commands.accountCreate';

	public function __construct(
		Models\Accounts\IAccountsManager $accountsManager,
		Models\Emails\IEmailRepository $emailRepository,
		Models\Emails\IEmailsManager $emailsManager,
		Models\Identities\IIdentitiesManager $identitiesManager,
		Models\Roles\IRoleRepository $roleRepository,
		Translation\Translator $translator,
		Persistence\ManagerRegistry $managerRegistry,
		?string $name = null
	) {
		$this->accountsManager = $accountsManager;
		$this->emailRepository = $emailRepository;
		$this->emailsManager = $emailsManager;
		$this->identitiesManager = $identitiesManager;
		$this->roleRepository = $roleRepository;

		$this->managerRegistry = $managerRegistry;

		$this->translator = new Translation\PrefixedTranslator($translator, $this->translationDomain);

		parent::__construct($name);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void
	{
		$this
			->setName('fb:accounts-module:create:account')
			->addArgument('lastName', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.lastName.title'))
			->addArgument('firstName', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.firstName.title'))
			->addArgument('email', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.email.title'))
			->addArgument('password', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.password.title'))
			->addArgument('role', Input\InputArgument::OPTIONAL, $this->translator->translate('inputs.role.title'))
			->addOption('noconfirm', null, Input\InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->addOption('injected', null, Input\InputOption::VALUE_NONE, 'do not show all outputs')
			->setDescription('Create account.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): int
	{
		$io = new Style\SymfonyStyle($input, $output);

		if (!$input->hasOption('injected')) {
			$io->title('FB accounts module - create account');
		}

		if (
			$input->hasArgument('lastName')
			&& is_string($input->getArgument('lastName'))
			&& $input->getArgument('lastName') !== ''
		) {
			$lastName = $input->getArgument('lastName');
		} else {
			$lastName = $io->ask($this->translator->translate('inputs.lastName.title'));
		}

		if (
			$input->hasArgument('firstName')
			&& is_string($input->getArgument('firstName'))
			&& $input->getArgument('firstName') !== ''
		) {
			$firstName = $input->getArgument('firstName');
		} else {
			$firstName = $io->ask($this->translator->translate('inputs.firstName.title'));
		}

		if (
			$input->hasArgument('email')
			&& is_string($input->getArgument('email'))
			&& $input->getArgument('email') !== ''
		) {
			$emailAddress = $input->getArgument('email');
		} else {
			$emailAddress = $io->ask($this->translator->translate('inputs.email.title'));
		}

		do {
			if (!Utils\Validators::isEmail($emailAddress)) {
				$io->error($this->translator->translate('validation.email.invalid', ['email' => $emailAddress]));

				$repeat = true;
			} else {
				$email = $this->emailRepository->findOneByAddress($emailAddress);

				$repeat = $email !== null;

				if ($repeat) {
					$io->error($this->translator->translate('validation.email.taken', ['email' => $emailAddress]));
				}
			}

			if ($repeat) {
				$emailAddress = $io->ask($this->translator->translate('inputs.email.title'));
			}
		} while ($repeat);

		$repeat = true;

		if ($input->hasArgument('role') && in_array($input->getArgument('role'), [
				SimpleAuth\Constants::ROLE_USER,
				SimpleAuth\Constants::ROLE_MANAGER,
				SimpleAuth\Constants::ROLE_ADMINISTRATOR,
			], true)) {
			$findRole = new Queries\FindRolesQuery();
			$findRole->byName($input->getArgument('role'));

			$role = $this->roleRepository->findOneBy($findRole);

			if ($role === null) {
				$io->error('Entered unknown role name.');

				return 1;
			}
		} else {
			do {
				$roleName = $io->choice(
					$this->translator->translate('inputs.role.title'),
					[
						'U' => $this->translator->translate('inputs.role.values.user'),
						'M' => $this->translator->translate('inputs.role.values.manager'),
						'A' => $this->translator->translate('inputs.role.values.administrator'),
					],
					'U'
				);

				switch ($roleName) {
					case 'U':
						$roleName = SimpleAuth\Constants::ROLE_USER;
						break;

					case 'M':
						$roleName = SimpleAuth\Constants::ROLE_MANAGER;
						break;

					case 'A':
						$roleName = SimpleAuth\Constants::ROLE_ADMINISTRATOR;
						break;
				}

				$findRole = new Queries\FindRolesQuery();
				$findRole->byName($roleName);

				$role = $this->roleRepository->findOneBy($findRole);

				if ($role !== null) {
					$repeat = false;
				}
			} while ($repeat);
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			$create = new Utils\ArrayHash();
			$create->offsetSet('entity', Entities\Accounts\Account::class);
			$create->offsetSet('state', ModulesMetadataTypes\AccountStateType::get(ModulesMetadataTypes\AccountStateType::STATE_ACTIVE));
			$create->offsetSet('roles', [$role]);

			$details = new Utils\ArrayHash();
			$details->offsetSet('entity', Entities\Details\Details::class);
			$details->offsetSet('firstName', $firstName);
			$details->offsetSet('lastName', $lastName);

			$create->offsetSet('details', $details);

			$account = $this->accountsManager->create($create);

			// Create new email entity for user
			$create = new Utils\ArrayHash();
			$create->offsetSet('account', $account);
			$create->offsetSet('address', $emailAddress);
			$create->offsetSet('default', true);

			// Create new email entity
			$email = $this->emailsManager->create($create);

			// Commit all changes into database
			$this->getOrmConnection()->commit();
		} catch (Throwable $ex) {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}

			$io->error($ex->getMessage());

			$io->error($this->translator->translate('validation.account.wasNotCreated', ['error' => $ex->getMessage()]));

			return $ex->getCode();
		}

		if (
			$input->hasArgument('password')
			&& is_string($input->getArgument('password'))
			&& $input->getArgument('password') !== ''
		) {
			$password = $input->getArgument('password');
		} else {
			$password = $io->askHidden($this->translator->translate('inputs.password.title'));
		}

		$email = $account->getEmail();

		if ($email === null) {
			$io->warning($this->translator->translate('validation.identity.noEmail'));

			return 0;
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			// Create new email entity for user
			$create = new Utils\ArrayHash();
			$create->offsetSet('entity', Entities\Identities\Identity::class);
			$create->offsetSet('account', $account);
			$create->offsetSet('uid', $email->getAddress());
			$create->offsetSet('password', $password);
			$create->offsetSet('state', ModulesMetadataTypes\IdentityStateType::get(ModulesMetadataTypes\IdentityStateType::STATE_ACTIVE));

			$this->identitiesManager->create($create);

			// Commit all changes into database
			$this->getOrmConnection()->commit();
		} catch (Throwable $ex) {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}

			$io->error($ex->getMessage());

			$io->error($this->translator->translate('validation.identity.wasNotCreated', ['error' => $ex->getMessage()]));

			return $ex->getCode();
		}

		$io->success($this->translator->translate('success', ['name' => $account->getName()]));

		return 0;
	}

	/**
	 * @return Connection
	 */
	protected function getOrmConnection(): Connection
	{
		$connection = $this->managerRegistry->getConnection();

		if ($connection instanceof Connection) {
			return $connection;
		}

		throw new Exceptions\RuntimeException('Entity manager could not be loaded');
	}

}
