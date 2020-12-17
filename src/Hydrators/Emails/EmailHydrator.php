<?php declare(strict_types = 1);

/**
 * EmailHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AuthModule\Hydrators\Emails;

use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Email entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EmailHydrator extends JsonApiHydrators\Hydrator
{

	use TEmailHydrator;

	/** @var string[] */
	protected array $attributes = [
		0 => 'address',
		1 => 'default',
		2 => 'verified',

		'private' => 'visibility',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Emails\EmailSchema::RELATIONSHIPS_ACCOUNT,
	];

	/** @var string */
	protected string $translationDomain = 'module.emails';

}
