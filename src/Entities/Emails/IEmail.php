<?php declare(strict_types = 1);

/**
 * IEmail.php
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

namespace FastyBird\AuthModule\Entities\Emails;

use DateTimeInterface;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Types;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineTimestampable;

/**
 * Account email entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IEmail extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @return Entities\Accounts\IUserAccount
	 */
	public function getAccount(): Entities\Accounts\IUserAccount;

	/**
	 * @param string $address
	 *
	 * @return void
	 */
	public function setAddress(string $address): void;

	/**
	 * @return string
	 */
	public function getAddress(): string;

	/**
	 * @param bool $default
	 *
	 * @return void
	 */
	public function setDefault(bool $default): void;

	/**
	 * @return bool
	 */
	public function isDefault(): bool;

	/**
	 * @param bool $verified
	 *
	 * @return void
	 */
	public function setVerified(bool $verified): void;

	/**
	 * @return bool
	 */
	public function isVerified(): bool;

	/**
	 * @param string $verificationHash
	 *
	 * @return void
	 */
	public function setVerificationHash(string $verificationHash): void;

	/**
	 * @return string|null
	 */
	public function getVerificationHash(): ?string;

	/**
	 * @param DateTimeInterface $verificationCreated
	 *
	 * @return void
	 */
	public function setVerificationCreated(DateTimeInterface $verificationCreated): void;

	/**
	 * @return DateTimeInterface|null
	 */
	public function getVerificationCreated(): ?DateTimeInterface;

	/**
	 * @param DateTimeInterface|null $verificationCompleted
	 *
	 * @return void
	 */
	public function setVerificationCompleted(?DateTimeInterface $verificationCompleted = null): void;

	/**
	 * @return DateTimeInterface|null
	 */
	public function getVerificationCompleted(): ?DateTimeInterface;

	/**
	 * @param Types\EmailVisibilityType $visibility
	 *
	 * @return void
	 */
	public function setVisibility(Types\EmailVisibilityType $visibility): void;

	/**
	 * @return Types\EmailVisibilityType
	 */
	public function getVisibility(): Types\EmailVisibilityType;

	/**
	 * @return bool
	 */
	public function isPublic(): bool;

	/**
	 * @return bool
	 */
	public function isPrivate(): bool;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
