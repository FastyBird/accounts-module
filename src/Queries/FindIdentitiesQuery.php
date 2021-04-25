<?php declare(strict_types = 1);

/**
 * FindEmailsQuery.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find identities entities query
 *
 * @package          FastyBird:AccountsModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Identities\Identity
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<T>
 */
class FindIdentitiesQuery extends DoctrineOrmQuery\QueryObject
{

	/** @var Closure[] */
	private array $filter = [];

	/** @var Closure[] */
	private array $select = [];

	/**
	 * @param Uuid\UuidInterface $id
	 *
	 * @return void
	 */
	public function byId(Uuid\UuidInterface $id): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($id): void {
			$qb->andWhere('i.id = :id')
				->setParameter('id', $id->getBytes());
		};
	}

	/**
	 * @param string $uid
	 *
	 * @return void
	 */
	public function byUid(string $uid): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($uid): void {
			$qb->andWhere('i.uid = :uid')
				->setParameter('uid', $uid);
		};
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 *
	 * @return void
	 */
	public function forAccount(Entities\Accounts\IAccount $account): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('i.account', 'account');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($account): void {
			$qb->andWhere('account.id = :account')
				->setParameter('account', $account->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param string $state
	 *
	 * @return void
	 *
	 * @throw Exceptions\InvalidArgumentException
	 */
	public function inState(string $state): void
	{
		if (!ModulesMetadataTypes\IdentityStateType::isValidValue($state)) {
			throw new Exceptions\InvalidArgumentException('Invalid identity state given');
		}

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('i.state = :state')
				->setParameter('state', $state);
		};
	}

	/**
	 * @param ORM\EntityRepository<Entities\Identities\Identity> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
	 * @param ORM\EntityRepository<Entities\Identities\Identity> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('i');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository<Entities\Identities\Identity> $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<T> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)
			->select('COUNT(i.id)');
	}

}
