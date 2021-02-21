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

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Account identity repository interface
 *
 * @package        FastyBird:AuthModule!
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
	 *
	 * @phpstan-template T of Entities\Identities\Identity
	 * @phpstan-param    Queries\FindIdentitiesQuery<T> $queryObject
	 */
	public function findOneBy(
		Queries\FindIdentitiesQuery $queryObject
	): ?Entities\Identities\IIdentity;

	/**
	 * @param Queries\FindIdentitiesQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Identities\Identity
	 * @phpstan-param    Queries\FindIdentitiesQuery<T> $queryObject
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindIdentitiesQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
