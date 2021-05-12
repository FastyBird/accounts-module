<?php declare(strict_types = 1);

/**
 * AccountHydrator.php
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

namespace FastyBird\AccountsModule\Hydrators\Accounts;

use FastyBird\AccountsModule\Schemas;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;

/**
 * Account entity hydrator
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class AccountHydrator extends JsonApiHydrators\Hydrator
{

	use TAccountHydrator;

	/** @var string[] */
	protected array $attributes = [
		0 => 'details',
		1 => 'state',

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
	protected string $translationDomain = 'accounts-module.accounts';

}
