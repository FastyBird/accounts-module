<?php declare(strict_types = 1);

/**
 * EmailHydrator.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Hydrators\Emails;

use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Email entity hydrator
 *
 * @package        FastyBird:AccountsModule!
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
	protected string $translationDomain = 'accounts-module.emails';

}
