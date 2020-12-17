<?php declare(strict_types = 1);

/**
 * UserAccountHydrator.php
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

namespace FastyBird\AuthModule\Hydrators\Accounts;

use FastyBird\AuthModule\Schemas;

/**
 * User account entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class UserAccountHydrator extends AccountHydrator
{

	use TUserAccountHydrator;

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
		Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_ROLES,
	];

}
