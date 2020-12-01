<?php declare(strict_types = 1);

/**
 * UserAccountIdentitySchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           03.04.20
 */

namespace FastyBird\AuthModule\Schemas\Identities;

use FastyBird\AuthModule\Entities;

/**
 * User account identity entity schema
 *
 * @package         FastyBird:AuthModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends IdentitySchema<Entities\Identities\IUserAccountIdentity>
 */
final class UserAccountIdentitySchema extends IdentitySchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'auth-module/user-account-identity';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Identities\UserAccountIdentity::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

}
