<?php declare(strict_types = 1);

/**
 * MachineAccountIdentityHydrator.php
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

namespace FastyBird\AuthModule\Hydrators\Identities;

use FastyBird\AuthModule\Entities;

/**
 * Machine account identity entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class MachineAccountIdentityHydrator extends IdentityHydrator
{

	/** @var string[] */
	protected $attributes = [
		'uid',
		'password',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Identities\MachineAccountIdentity::class;
	}

}
