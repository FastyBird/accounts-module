<?php declare(strict_types = 1);

/**
 * RoleHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           03.06.20
 */

namespace FastyBird\AuthModule\Hydrators\Roles;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use IPub\JsonAPIDocument;

/**
 * Role entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class RoleHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $attributes = [
		'description',
	];

	/** @var string[] */
	protected $relationships = [
		Schemas\Roles\RoleSchema::RELATIONSHIPS_PARENT,
	];

	/** @var string */
	protected $translationDomain = 'module.roles';

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
