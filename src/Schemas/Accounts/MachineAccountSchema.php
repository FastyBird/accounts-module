<?php declare(strict_types = 1);

/**
 * MachineAccountSchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           15.08.20
 */

namespace FastyBird\AuthModule\Schemas\Accounts;

use FastyBird\AuthModule\Entities;
use Neomerx\JsonApi;

/**
 * Account entity schema
 *
 * @package         FastyBird:AuthModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends AccountSchema<Entities\Accounts\IMachineAccount>
 */
final class MachineAccountSchema extends AccountSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'auth-module/machine-account';

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Accounts\MachineAccount::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Accounts\IMachineAccount $account
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($account, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return array_merge((array) parent::getAttributes($account, $context), [
			'device' => $account->getDevice(),
		]);
	}

}
