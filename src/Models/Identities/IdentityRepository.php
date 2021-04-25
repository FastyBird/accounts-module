<?php declare(strict_types = 1);

/**
 * IIdentityRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AuthModule\Models\Identities;

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Queries;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Account identity facade
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class IdentityRepository implements IIdentityRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private Common\Persistence\ManagerRegistry $managerRegistry;

	/** @var Persistence\ObjectRepository<Entities\Identities\Identity>|null */
	private ?Persistence\ObjectRepository $repository = null;

	public function __construct(Common\Persistence\ManagerRegistry $managerRegistry)
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
	 * @return Persistence\ObjectRepository<Entities\Identities\Identity>
	 */
	private function getRepository(): Persistence\ObjectRepository
	{
		if ($this->repository === null) {
			$this->repository = $this->managerRegistry->getRepository(Entities\Identities\Identity::class);
		}

		return $this->repository;
	}

}
