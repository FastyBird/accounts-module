<?php declare(strict_types = 1);

/**
 * MachineAccountIdentitySchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           21.06.20
 */

namespace FastyBird\AuthModule\Schemas\Identities;

use FastyBird\AuthModule\Entities;
use Neomerx\JsonApi;

/**
 * Machine account identity entity schema
 *
 * @package         FastyBird:AuthModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends IdentitySchema<Entities\Identities\IMachineAccountIdentity>
 */
final class MachineAccountIdentitySchema extends IdentitySchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'auth-module/machine-account-identity';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Identities\MachineAccountIdentity::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Identities\IMachineAccountIdentity $identity
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($identity, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($identity, $context), [
			'password' => $identity->getPassword(),
		]);
	}

}
