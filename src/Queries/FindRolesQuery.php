<?php declare(strict_types = 1);

/**
 * FindRolesQuery.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Queries
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Queries;

use Closure;
use Doctrine\ORM;
use FastyBird\AccountsModule\Entities;
use IPub\DoctrineOrmQuery;
use Ramsey\Uuid;

/**
 * Find roles entities query
 *
 * @package          FastyBird:AccountsModule!
 * @subpackage       Queries
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends  DoctrineOrmQuery\QueryObject<Entities\Roles\IRole>
 */
class FindRolesQuery extends DoctrineOrmQuery\QueryObject
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
			$qb->andWhere('r.id = :id')
				->setParameter('id', $id->getBytes());
		};
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function byName(string $name): void
	{
		$this->filter[] = function (ORM\QueryBuilder $qb) use ($name): void {
			$qb->andWhere('r.name = :name')
				->setParameter('name', $name);
		};
	}

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return void
	 */
	public function forParent(Entities\Roles\IRole $role): void
	{
		$this->select[] = function (ORM\QueryBuilder $qb): void {
			$qb->join('r.parent', 'parent');
		};

		$this->filter[] = function (ORM\QueryBuilder $qb) use ($role): void {
			$qb->andWhere('parent.id = :parent')
				->setParameter('parent', $role->getId(), Uuid\Doctrine\UuidBinaryType::NAME);
		};
	}

	/**
	 * @param ORM\EntityRepository $repository
	 *
	 * @return ORM\QueryBuilder
	 *
	 * @phpstan-param ORM\EntityRepository<Entities\Roles\IRole> $repository
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
	 * @phpstan-param ORM\EntityRepository<Entities\Roles\IRole> $repository
	 */
	private function createBasicDql(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		$qb = $repository->createQueryBuilder('r');

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
	 * @phpstan-param ORM\EntityRepository<Entities\Roles\IRole> $repository
	 */
	protected function doCreateCountQuery(ORM\EntityRepository $repository): ORM\QueryBuilder
	{
		return $this->createBasicDql($repository)
			->select('COUNT(r.id)');
	}

}
