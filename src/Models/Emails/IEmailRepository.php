<?php declare(strict_types = 1);

/**
 * IEmailRepository.php
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

namespace FastyBird\AccountsModule\Models\Emails;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Queries;
use IPub\DoctrineOrmQuery;

/**
 * Account email address repository interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IEmailRepository
{

	/**
	 * @param string $address
	 *
	 * @return Entities\Emails\IEmail|null
	 */
	public function findOneByAddress(string $address): ?Entities\Emails\IEmail;

	/**
	 * @param Queries\FindEmailsQuery $queryObject
	 *
	 * @return Entities\Emails\IEmail|null
	 *
	 * @phpstan-template T of Entities\Emails\Email
	 * @phpstan-param    Queries\FindEmailsQuery<T> $queryObject
	 */
	public function findOneBy(Queries\FindEmailsQuery $queryObject): ?Entities\Emails\IEmail;

	/**
	 * @param Queries\FindEmailsQuery $queryObject
	 *
	 * @return DoctrineOrmQuery\ResultSet
	 *
	 * @phpstan-template T of Entities\Emails\Email
	 * @phpstan-param    Queries\FindEmailsQuery<T> $queryObject
	 * @phpstan-return   DoctrineOrmQuery\ResultSet<T>
	 */
	public function getResultSet(
		Queries\FindEmailsQuery $queryObject
	): DoctrineOrmQuery\ResultSet;

}
