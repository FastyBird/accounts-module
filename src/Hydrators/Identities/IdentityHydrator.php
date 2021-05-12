<?php declare(strict_types = 1);

/**
 * IdentityHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           15.08.20
 */

namespace FastyBird\AccountsModule\Hydrators\Identities;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Helpers;
use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Identity entity hydrator
 *
 * @package        FastyBird:AccountsModule!
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
	protected string $translationDomain = 'accounts-module.identities';

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
