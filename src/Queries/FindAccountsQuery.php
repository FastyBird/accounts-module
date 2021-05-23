<?php declare(strict_types = 1);

/**
 * FindAccountsQuery.php
 *
 * @license        More in LICENSE.md
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
 * Find accounts entities query
 *
 * @package          FastyBird:AccountsModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Accounts\Account>
 */
class FindAccountsQuery extends DoctrineOrmQuery\QueryObject
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
			$qb->andWhere('a.id = :id')
				->setParameter('id', $id->getBytes());
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
		if (!ModulesMetadataTypes\AccountStateType::isValidValue($state)) {
			throw new Exceptions\InvalidArgumentException('Invalid account state given');
		}

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($state): void {
			$qb->andWhere('a.state = :state')
				->setParameter('state', $state);
		};
	}

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return void
	 */
	public function inRole(Entities\Roles\IRole $role): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('a.roles', 'roles');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($role): void {
			$qb->andWhere('roles.id = :role')
				->setParameter('role', $role->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 */
	protected function doCreateQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository);
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('a');

		foreach ($this->select as $modifier) {
			$modifier($qb);
		}

		foreach ($this->filter as $modifier) {
			$modifier($qb);
		}

		return $qb;
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Accounts\Account> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)
			->select('COUNT(a.id)');
	}

}
