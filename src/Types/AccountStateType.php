<?php declare(strict_types = 1);

/**
 * AccountStateType.php
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
 * Doctrine2 DB type for account state column
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class AccountStateType extends Consistence\Enum\Enum
{

	/**
	 * Define states
	 */
	public const STATE_ACTIVE = 'active';
	public const STATE_BLOCKED = 'blocked';
	public const STATE_DELETED = 'deleted';
	public const STATE_NOT_ACTIVATED = 'notActivated';
	public const STATE_APPROVAL_WAITING = 'approvalWaiting';

	/**
	 * List of allowed states
	 *
	 * @var string[]
	 */
	public static $allowedStates = [
		self::STATE_ACTIVE,
		self::STATE_BLOCKED,
		self::STATE_DELETED,
	];

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) self::getValue();
	}

}
