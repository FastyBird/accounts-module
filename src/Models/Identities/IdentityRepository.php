<?php declare(strict_types = 1);

/**
 * IIdentityRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Models\Identities;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\AccountsModule\Queries;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Account identity facade
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class IdentityRepository implements IIdentityRepository
{

	use Nette\SmartObject;

	/** @var Persistence\ManagerRegistry */
	private Persistence\ManagerRegistry $managerRegistry;

	/** @var ORM\EntityRepository<Entities\Identities\IIdentity>|null */
	private ?ORM\EntityRepository $repository = null;

	public function __construct(Persistence\ManagerRegistry $managerRegistry)
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneForAccount(
		Entities\Accounts\IAccount $account
	): ?Entities\Identities\IIdentity {
		$findQuery = new Queries\FindIdentitiesQuery();
		$findQuery->forAccount($account);
		$findQuery->inState(ModulesMetadataTypes\IdentityStateType::STATE_ACTIVE);

		return $this->findOneBy($findQuery);
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(
		Queries\FindIdentitiesQuery $queryObject
	): ?Entities\Identities\IIdentity {
		/** @var Entities\Identities\IIdentity|null $identity */
		$identity = $queryObject->fetchOne($this->getRepository());

		return $identity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneByUid(
		string $uid
	): ?Entities\Identities\IIdentity {
		$findQuery = new Queries\FindIdentitiesQuery();
		$findQuery->byUid($uid);
		$findQuery->inState(ModulesMetadataTypes\IdentityStateType::STATE_ACTIVE);

		return $this->findOneBy($findQuery);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindIdentitiesQuery $queryObject,
		string $type = Entities\Identities\Identity::class
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository());

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidStateException('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @return ORM\EntityRepository
	 *
	 * @phpstan-return ORM\EntityRepository<Entities\Identities\IIdentity>
	 */
	private function getRepository(): Persistence\ObjectRepository
	{
		if ($this->repository === null) {
			$repository = $this->managerRegistry->getRepository(Entities\Identities\Identity::class);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidStateException('Entity repository could not be loaded');
			}

			$this->repository = $repository;
		}

		return $this->repository;
	}

}
