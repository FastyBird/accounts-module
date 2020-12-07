<?php declare(strict_types = 1);

/**
 * ProfileEmailHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           21.08.20
 */

namespace FastyBird\AuthModule\Hydrators\Emails;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Types;
use IPub\JsonAPIDocument;

/**
 * Profile email entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
trait TEmailHydrator
{

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return Entities\Emails\Email::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return Types\EmailVisibilityType
	 */
	protected function hydrateVisibilityAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): Types\EmailVisibilityType
	{
		$isPrivate = (bool) $attributes->get('private');

		return Types\EmailVisibilityType::get($isPrivate ? Types\EmailVisibilityType::VISIBILITY_PRIVATE : Types\EmailVisibilityType::VISIBILITY_PUBLIC);
	}

}
