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

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Account identity repository interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentityRepository
{

	/**
	 * @param Entities\Accounts\IAccount $account
	 *
	 * @return Entities\Identities\IIdentity|null
	 */
	public function findOneForAccount(
		Entities\Accounts\IAccount $account
	): ?Entities\Identities\IIdentity;

	/**
	 * @param string $uid
	 *
	 * @return Entities\Identities\IIdentity|null
	 */
	public function findOneByUid(
		string $uid
	): ?Entities\Identities\IIdentity;

	/**
	 * @param Queries\FindIdentitiesQuery $queryObject
	 *
	 * @return Entities\Identities\IIdentity|null
	 */
	public function findOneBy(
		Queries\FindIdentitiesQuery $queryObject
	): ?Entities\Identities\IIdentity;

	/**
	 * @param Queries\FindIdentitiesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<Entities\Identities\Identity>
	 */
	public function getResultSet(
		Queries\FindIdentitiesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
