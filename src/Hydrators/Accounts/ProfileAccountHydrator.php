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

/**
 * User account entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class ProfileAccountHydrator extends AccountHydrator
{

	use TUserAccountHydrator;

	/** @var string[] */
	protected $attributes = [
		0 => 'details',

		'first_name'  => 'firstName',
		'last_name'   => 'lastName',
		'middle_name' => 'middleName',
	];

	/** @var string[] */
	protected $compositedAttributes = [
		'params',
	];

}
