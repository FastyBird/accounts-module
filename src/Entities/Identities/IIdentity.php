<?php declare(strict_types = 1);

/**
 * IIdentity.php
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

namespace FastyBird\AuthModule\Entities\Identities;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Helpers;
use FastyBird\AuthModule\Types;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use IPub\DoctrineTimestampable;

/**
 * Identity entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentity extends DatabaseEntities\IEntity,
	SimpleAuthSecurity\IIdentity,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Accounts\IAccount
	 */
	public function getAccount(): Entities\Accounts\IAccount;

	/**
	 * @return string
	 */
	public function getUid(): string;

	/**
	 * @param string|Helpers\Password $password
	 *
	 * @return void
	 */
	public function setPassword($password): void;

	/**
	 * @return Helpers\Password
	 */
	public function getPassword(): Helpers\Password;

	/**
	 * @param string $rawPassword
	 *
	 * @return bool
	 */
	public function verifyPassword(string $rawPassword): bool;

	/**
	 * @param string $salt
	 *
	 * @return void
	 */
	public function setSalt(string $salt): void;

	/**
	 * @return string|null
	 */
	public function getSalt(): ?string;

	/**
	 * @param Types\IdentityStateType $state
	 *
	 * @return void
	 */
	public function setState(Types\IdentityStateType $state): void;

	/**
	 * @return Types\IdentityStateType
	 */
	public function getState(): Types\IdentityStateType;

	/**
	 * @return bool
	 */
	public function isActive(): bool;

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
	public function isInvalid(): bool;

	/**
	 * @return void
	 */
	public function invalidate(): void;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
