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
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
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
	 * @return ModulesMetadataTypes\AccountStateType
	 */
	public function getState(): ModulesMetadataTypes\AccountStateType;

	/**
	 * @param ModulesMetadataTypes\AccountStateType $state
	 *
	 * @return void
	 */
	public function setState(ModulesMetadataTypes\AccountStateType $state): void;

	/**
	 * @return DateTimeInterface|null
	 */
	public function getLastVisit(): ?DateTimeInterface;

	/**
	 * @param DateTimeInterface $lastVisit
	 *
	 * @return void
	 */
	public function setLastVisit(DateTimeInterface $lastVisit): void;

	/**
	 * @return string|null
	 */
	public function getRequestHash(): ?string;

	/**
	 * @param string $requestHash
	 *
	 * @return void
	 */
	public function setRequestHash(string $requestHash): void;

	/**
	 * @return Entities\Details\IDetails
	 */
	public function getDetails(): Entities\Details\IDetails;

	/**
	 * @return Entities\Identities\IIdentity[]
	 */
	public function getIdentities(): array;

	/**
	 * @return Entities\Emails\IEmail[]
	 */
	public function getEmails(): array;

	/**
	 * @param string|null $id
	 *
	 * @return Entities\Emails\IEmail|null
	 */
	public function getEmail(?string $id = null): ?Entities\Emails\IEmail;

	/**
	 * @param Entities\Emails\IEmail[] $emails
	 *
	 * @return void
	 */
	public function setEmails(array $emails): void;

	/**
	 * @param Entities\Emails\IEmail $email
	 *
	 * @return void
	 */
	public function addEmail(Entities\Emails\IEmail $email): void;

	/**
	 * @param Entities\Emails\IEmail $email
	 *
	 * @return void
	 */
	public function removeEmail(Entities\Emails\IEmail $email): void;

	/**
	 * @return Entities\Roles\IRole[]
	 */
	public function getRoles(): array;

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
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getLanguage(): string;

	/**
	 * @return mixed[]
	 */
	public function toArray(): array;

}
