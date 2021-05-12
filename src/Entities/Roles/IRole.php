<?php declare(strict_types = 1);

/**
 * IRole.php
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

namespace FastyBird\AccountsModule\Entities\Roles;

use FastyBird\AccountsModule\Entities;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineTimestampable;

/**
 * ACL role entity interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRole extends DatabaseEntities\IEntity,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function setName(string $name): void;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @param string $description
	 *
	 * @return void
	 */
	public function setDescription(string $description): void;

	/**
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * @param IRole|null $parent
	 *
	 * @return void
	 */
	public function setParent(?IRole $parent = null): void;

	/**
	 * @return IRole|null
	 */
	public function getParent(): ?IRole;

	/**
	 * @param IRole[] $children
	 *
	 * @return void
	 */
	public function setChildren(array $children): void;

	/**
	 * @param IRole $child
	 *
	 * @return void
	 */
	public function addChild(IRole $child): void;

	/**
	 * @return IRole[]
	 */
	public function getChildren(): array;

	/**
	 * Check if role is guest
	 *
	 * @return bool
	 */
	public function isAnonymous(): bool;

	/**
	 * Check if role is authenticated
	 *
	 * @return bool
	 */
	public function isAuthenticated(): bool;

	/**
	 * Check if role is administrator
	 *
	 * @return bool
	 */
	public function isAdministrator(): bool;

}
