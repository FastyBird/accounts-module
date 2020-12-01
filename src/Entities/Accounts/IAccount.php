<?php declare(strict_types = 1);

/**
 * IAccount.php
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

namespace FastyBird\AuthModule\Entities\Accounts;

use DateTimeInterface;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Types;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineTimestampable;

/**
 * Application account entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccount extends DatabaseEntities\IEntity,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @param Types\AccountStateType $state
	 *
	 * @return void
	 */
	public function setState(Types\AccountStateType $state): void;

	/**
	 * @return Types\AccountStateType
	 */
	public function getState(): Types\AccountStateType;

	/**
	 * @return bool
	 */
	public function isActivated(): bool;

	/**
	 * @return bool
	 */
	public function isBlocked(): bool;

	/**
	 * @return bool
	 */
	public function isDeleted(): bool;

	/**
	 * @return bool
	 */
	public function isNotActivated(): bool;

	/**
	 * @return bool
	 */
	public function isApprovalRequired(): bool;

	/**
	 * @param DateTimeInterface $lastVisit
	 *
	 * @return void
	 */
	public function setLastVisit(DateTimeInterface $lastVisit): void;

	/**
	 * @return DateTimeInterface|null
	 */
	public function getLastVisit(): ?DateTimeInterface;

	/**
	 * @return Entities\Identities\IIdentity[]
	 */
	public function getIdentities(): array;

	/**
	 * @param Entities\Roles\IRole[] $roles
	 *
	 * @return void
	 */
	public function setRoles(array $roles): void;

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return void
	 */
	public function addRole(Entities\Roles\IRole $role): void;

	/**
	 * @return Entities\Roles\IRole[]
	 */
	public function getRoles(): array;

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return void
	 */
	public function removeRole(Entities\Roles\IRole $role): void;

	/**
	 * @param string $role
	 *
	 * @return bool
	 */
	public function hasRole(string $role): bool;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
