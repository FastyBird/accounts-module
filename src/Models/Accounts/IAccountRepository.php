<?php declare(strict_types = 1);

/**
 * IAccountRepository.php
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

namespace FastyBird\AuthModule\Models\Accounts;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Account repository interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccountRepository
{

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Accounts\IAccount|null
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findOneBy(
		Queries\FindAccountsQuery $queryObject,
		string $type = Entities\Accounts\Account::class
	): ?Entities\Accounts\IAccount;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 * @param string $type
	 *
	 * @return Entities\Accounts\IAccount[]
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 */
	public function findAllBy(
		Queries\FindAccountsQuery $queryObject,
		string $type = Entities\Accounts\Account::class
	): array;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 * @param string $type
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 * @phpstan-param    class-string<T> $type
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindAccountsQuery $queryObject,
		string $type = Entities\Accounts\Account::class
	): DoctrineOrmQuery\ResultSet;

}
