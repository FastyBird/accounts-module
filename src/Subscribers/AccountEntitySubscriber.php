<?php declare(strict_types = 1);

/**
 * AccountEntitySubscriber.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           18.08.20
 */

namespace FastyBird\AuthModule\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\AuthModule;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\SimpleAuth;
use Nette;
use Throwable;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountEntitySubscriber implements Common\EventSubscriber
{

	use Nette\SmartObject;

	/** @var string[] */
	private $singleRoles = [
		SimpleAuth\Constants::ROLE_ADMINISTRATOR,
		SimpleAuth\Constants::ROLE_USER,
	];

	/** @var string[] */
	private $notAssignableRoles = [
		SimpleAuth\Constants::ROLE_VISITOR,
		SimpleAuth\Constants::ROLE_ANONYMOUS,
	];

	/** @var Models\Accounts\IAccountRepository */
	private $accountRepository;

	/** @var Models\Roles\IRoleRepository */
	private $roleRepository;

	public function __construct(
		Models\Accounts\IAccountRepository $accountRepository,
		Models\Roles\IRoleRepository $roleRepository
	) {
		$this->accountRepository = $accountRepository;
		$this->roleRepository = $roleRepository;
	}

	/**
	 * Register events
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::prePersist,
			ORM\Events::onFlush,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function prePersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach ($uow->getScheduledEntityInsertions() as $object) {
			if (
				$this->getAdministrator() === null
				&& !$object->hasRole(SimpleAuth\Constants::ROLE_ADMINISTRATOR)
			) {
				throw new Exceptions\InvalidStateException('First account have to be an administrator account');
			}
		}
	}

	/**
	 * @return Entities\Accounts\IUserAccount|null
	 *
	 * @throws Throwable
	 */
	private function getAdministrator(): ?Entities\Accounts\IUserAccount
	{
		$findRole = new Queries\FindRolesQuery();
		$findRole->byName(SimpleAuth\Constants::ROLE_ADMINISTRATOR);

		$role = $this->roleRepository->findOneBy($findRole);

		if ($role === null) {
			throw new Exceptions\InvalidStateException(sprintf('Role %s is not created', SimpleAuth\Constants::ROLE_ADMINISTRATOR));
		}

		$findAccount = new Queries\FindAccountsQuery();
		$findAccount->inRole($role);

		/** @var Entities\Accounts\IUserAccount|null $account */
		$account = $this->accountRepository->findOneBy($findAccount, Entities\Accounts\UserAccount::class);

		return $account;
	}

	/**
	 * @param ORM\Event\OnFlushEventArgs $eventArgs
	 *
	 * @return void
	 *
	 * @throws Throwable
	 */
	public function onFlush(ORM\Event\OnFlushEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach (array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()) as $object) {
			if ($object instanceof Entities\Accounts\IUserAccount) {
				/**
				 * If new account is without any role
				 * we have to assign default roles
				 */
				if (count($object->getRoles()) === 0) {
					$object->setRoles($this->getDefaultRoles(AuthModule\Constants::USER_ACCOUNT_DEFAULT_ROLES));
				}

				foreach ($object->getRoles() as $role) {
					/**
					 * Special roles like administrator or user
					 * can not be assigned to account with other roles
					 */
					if (
						in_array($role->getName(), $this->singleRoles, true)
						&& count($object->getRoles()) > 1
					) {
						throw new Exceptions\AccountRoleInvalidException(sprintf('Role %s could not be combined with other roles', $role->getName()));
					}

					/**
					 * Special roles like visitor or guest
					 * can not be assigned to account
					 */
					if (in_array($role->getName(), $this->notAssignableRoles, true)) {
						throw new Exceptions\AccountRoleInvalidException(sprintf('Role %s could not be assigned to account', $role->getName()));
					}
				}
			}

			if ($object instanceof Entities\Accounts\IMachineAccount) {
				/**
				 * Machine account has always only defined roles
				 */
				$object->setRoles($this->getDefaultRoles(AuthModule\Constants::MACHINE_ACCOUNT_DEFAULT_ROLES));
			}
		}
	}

	/**
	 * @param string[] $roleNames
	 *
	 * @return Entities\Roles\IRole[]
	 */
	private function getDefaultRoles(array $roleNames): array
	{
		$roles = [];

		foreach ($roleNames as $roleName) {
			$findRole = new Queries\FindRolesQuery();
			$findRole->byName($roleName);

			$role = $this->roleRepository->findOneBy($findRole);

			if ($role === null) {
				throw new Exceptions\InvalidStateException(sprintf('Role %s is not created', $roleName));
			}

			$roles[] = $role;
		}

		return $roles;
	}

}
