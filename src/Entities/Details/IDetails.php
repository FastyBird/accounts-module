<?php declare(strict_types = 1);

/**
 * IDetails.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AuthModule\Entities\Details;

use FastyBird\AuthModule\Entities;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineTimestampable;

/**
 * Account details entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IDetails extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @param string $firstName
	 *
	 * @return void
	 */
	public function setFirstName(string $firstName): void;

	/**
	 * @return string
	 */
	public function getFirstName(): string;

	/**
	 * @param string $lastName
	 *
	 * @return void
	 */
	public function setLastName(string $lastName): void;

	/**
	 * @return string
	 */
	public function getLastName(): string;

	/**
	 * @param string|null $middleName
	 *
	 * @return void
	 */
	public function setMiddleName(?string $middleName): void;

	/**
	 * @return string|null
	 */
	public function getMiddleName(): ?string;

}
