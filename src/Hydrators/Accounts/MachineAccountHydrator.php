<?php declare(strict_types = 1);

/**
 * MachineAccountHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           15.08.20
 */

namespace FastyBird\AuthModule\Hydrators\Accounts;

use FastyBird\AuthModule\Entities;

/**
 * Machine account entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class MachineAccountHydrator extends AccountHydrator
{

	/** @var string[] */
	protected $attributes = [
		'device',
		'state',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Accounts\MachineAccount::class;
	}

}
