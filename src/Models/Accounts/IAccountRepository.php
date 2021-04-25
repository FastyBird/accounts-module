<?php declare(strict_types = 1);

/**
 * IAccountRepository.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Models\Accounts;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Account repository interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccountRepository
{

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 *
	 * @return Entities\Accounts\IAccount|null
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 */
	public function findOneBy(
		Queries\FindAccountsQuery $queryObject
	): ?Entities\Accounts\IAccount;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 *
	 * @return Entities\Accounts\IAccount[]
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 */
	public function findAllBy(
		Queries\FindAccountsQuery $queryObject
	): array;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Accounts\Account
	 * @phpstan-param    Queries\FindAccountsQuery<T> $queryObject
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindAccountsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
