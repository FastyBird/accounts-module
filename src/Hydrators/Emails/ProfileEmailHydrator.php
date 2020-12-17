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

use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Profile email entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ProfileEmailHydrator extends JsonApiHydrators\Hydrator
{

	use TEmailHydrator;

	/** @var string[] */
	protected array $attributes = [
		0 => 'address',

		'default' => 'default',
		'private' => 'visibility',
	];

	/** @var string */
	protected string $translationDomain = 'auth-module.emails';

}
