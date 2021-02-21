<?php declare(strict_types = 1);

/**
 * ProfileAccountHydrator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 * @since          0.1.0
 *
 * @date           19.08.20
 */

namespace FastyBird\AuthModule\Hydrators\Accounts;

use FastyBird\AuthModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Profile account entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ProfileAccountHydrator extends JsonApiHydrators\Hydrator
{

	use TAccountHydrator;

	/** @var string[] */
	protected array $attributes = [
		0 => 'details',

		'first_name'  => 'firstName',
		'last_name'   => 'lastName',
		'middle_name' => 'middleName',
	];

	/** @var string[] */
	protected array $compositedAttributes = [
		'params',
	];

	/** @var string[] */
	protected array $relationships = [
		Schemas\Accounts\AccountSchema::RELATIONSHIPS_ROLES,
	];

	/** @var string */
	protected string $translationDomain = 'auth-module.accounts';

}
