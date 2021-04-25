<?php declare(strict_types = 1);

/**
 * RoleHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           03.06.20
 */

namespace FastyBird\AccountsModule\Hydrators\Roles;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Role entity hydrator
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class RoleHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string[] */
	protected array $attributes = [
		'description',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Roles\RoleSchema::RELATIONSHIPS_PARENT,
	];

	/** @var string */
	protected string $translationDomain = 'accounts-module.roles';

	/**
	 * {@inheritDoc}
	 */
	protected function getEntityName(): string
	{
		return Entities\Roles\Role::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateDescriptionAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		if ($attributes->get('description') === null || (string) $attributes->get('description') === '') {
			return null;
		}

		return (string) $attributes->get('description');
	}

}
