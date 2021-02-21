<?php declare(strict_types = 1);

/**
 * IdentityHydrator.php
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
use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Identity entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string[] */
	protected array $attributes = [
		'uid',
		'password',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Identities\IdentitySchema::RELATIONSHIPS_ACCOUNT,
	];

	/** @var string */
	protected string $translationDomain = 'auth-module.identities';

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Identities\Identity::class;
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
