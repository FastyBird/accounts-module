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

use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Identity entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class IdentityHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $attributes = [
		'uid',
	];

	/** @var string[] */
	protected $relationships = [
		Schemas\Identities\IdentitySchema::RELATIONSHIPS_ACCOUNT,
	];

	/** @var string */
	protected $translationDomain = 'module.identities';

}
