<?php declare(strict_types = 1);

/**
 * IMachineAccountIdentity.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           21.06.20
 */

namespace FastyBird\AuthModule\Entities\Identities;

/**
 * Machine identity entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IMachineAccountIdentity extends IIdentity
{

	/**
	 * @param string $password
	 *
	 * @return void
	 */
	public function setPassword(string $password): void;

	/**
	 * @return string
	 */
	public function getPassword(): string;

	/**
	 * @param string $password
	 *
	 * @return bool
	 */
	public function verifyPassword(string $password): bool;

}
