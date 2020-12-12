<?php declare(strict_types = 1);

/**
 * AccountHydrator.php
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

namespace FastyBird\AuthModule\Hydrators\Accounts;

use FastyBird\AuthModule\Types;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\JsonApi\Hydrators as JsonApiHydrators;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;

/**
 * Account entity hydrator
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class AccountHydrator extends JsonApiHydrators\Hydrator
{

	/** @var string */
	protected $entityIdentifier = self::IDENTIFIER_KEY;

	/** @var string[] */
	protected $attributes = [
		'state',
	];

	/** @var string */
	protected $translationDomain = 'module.accounts';

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject<mixed> $attributes
	 *
	 * @return Types\AccountStateType
	 */
	protected function hydrateStateAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): Types\AccountStateType {
		if (!Types\AccountStateType::isValidValue((string) $attributes->get('state'))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.invalidAttribute.heading'),
				$this->translator->translate('//module.base.messages.invalidAttribute.message'),
				[
					'pointer' => '/data/attributes/state',
				]
			);
		}

		return Types\AccountStateType::get((string) $attributes->get('state'));
	}

}
