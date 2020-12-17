<?php declare(strict_types = 1);

/**
 * UserAccountIdentityHydrator.php
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
use FastyBird\AuthModule\Helpers;
use IPub\JsonAPIDocument;

/**
 * User account identity entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UserAccountIdentityHydrator extends IdentityHydrator
{

	/** @var string[] */
	protected array $attributes = [
		'uid',
		'password',
	];

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Identities\UserAccountIdentity::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return Helpers\Password
	 */
	protected function hydratePasswordAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): Helpers\Password {
		return Helpers\Password::createFromString((string) $attributes->get('password'));
	}

}
