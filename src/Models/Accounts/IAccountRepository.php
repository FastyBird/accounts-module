<?php declare(strict_types = 1);

/**
 * IAccountRepository.php
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
	 */
	public function findOneBy(
		Queries\FindAccountsQuery $queryObject
	): ?Entities\Accounts\IAccount;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 *
	 * @return Entities\Accounts\IAccount[]
	 */
	public function findAllBy(
		Queries\FindAccountsQuery $queryObject
	): array;

	/**
	 * @param Queries\FindAccountsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<Entities\Accounts\Account>
	 */
	public function getResultSet(
		Queries\FindAccountsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
