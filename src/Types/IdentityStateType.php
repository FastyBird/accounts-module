<?php declare(strict_types = 1);

/**
 * IdentityStateType.php
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

namespace FastyBird\AuthModule\Types;

use Consistence;

/**
 * Doctrine2 DB type for identity state column
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityStateType extends Consistence\Enum\Enum
{

	/**
	 * Define states
	 */
	public const STATE_ACTIVE = 'active';
	public const STATE_BLOCKED = 'blocked';
	public const STATE_DELETED = 'deleted';
	public const STATE_INVALID = 'invalid';

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
