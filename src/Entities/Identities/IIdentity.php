<?php declare(strict_types = 1);

/**
 * IIdentity.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Entities\Identities;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Helpers;
use FastyBird\Metadata\Types as MetadataTypes;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use IPub\DoctrineTimestampable;

/**
 * Identity entity interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentity extends Entities\IEntity,
	SimpleAuthSecurity\IIdentity,
	Entities\IEntityParams,
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
	 * @param MetadataTypes\IdentityStateType $state
	 *
	 * @return void
	 */
	public function setState(MetadataTypes\IdentityStateType $state): void;

	/**
	 * @return MetadataTypes\IdentityStateType
	 */
	public function getState(): MetadataTypes\IdentityStateType;

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
