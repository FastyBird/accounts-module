<?php declare(strict_types = 1);

/**
 * TAccountHydrator.php
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

namespace FastyBird\AccountsModule\Hydrators\Accounts;

use Contributte\Translation;
use FastyBird\AccountsModule\Entities;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\Metadata\Types as MetadataTypes;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Nette\Utils;

/**
 * Account entity hydrator trait
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Hydrators
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @property-read Translation\Translator $translator
 */
trait TAccountHydrator
{

	/**
	 * {@inheritDoc}
	 */
	public function getEntityName(): string
	{
		return Entities\Accounts\Account::class;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 * @throws Translation\Exceptions\InvalidArgument
	 */
	protected function hydrateFirstNameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): string
	{
		if (!$attributes->has('first_name') || !is_scalar($attributes->get('first_name'))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
				[
					'pointer' => '/data/attributes/details/first_name',
				]
			);
		}

		return (string) $attributes->get('first_name');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Translation\Exceptions\InvalidArgument
	 */
	protected function hydrateLastNameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): string
	{
		if (!$attributes->has('last_name') || !is_scalar($attributes->get('last_name'))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
				[
					'pointer' => '/data/attributes/details/last_name',
				]
			);
		}

		return (string) $attributes->get('last_name');
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return string|null
	 */
	protected function hydrateMiddleNameAttribute(JsonAPIDocument\Objects\IStandardObject $attributes): ?string
	{
		return $attributes->has('middle_name') && is_scalar($attributes->get('middle_name')) && (string) $attributes->get('middle_name') !== '' ? (string) $attributes->get('middle_name') : null;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return Utils\ArrayHash|null
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 * @throws Translation\Exceptions\InvalidArgument
	 */
	protected function hydrateDetailsAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): ?Utils\ArrayHash {
		if (
			$attributes->has('details')
			&& $attributes->get('details') instanceof JsonAPIDocument\Objects\IStandardObject
		) {
			/** @var JsonAPIDocument\Objects\IStandardObject $details */
			$details = $attributes->get('details');

			$update = new Utils\ArrayHash();
			$update['entity'] = Entities\Details\Details::class;

			if ($details->has('first_name')) {
				$update->offsetSet('firstName', $details->get('first_name'));

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
					$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
					[
						'pointer' => '/data/attributes/details/first_name',
					]
				);
			}

			if ($details->has('last_name')) {
				$update->offsetSet('lastName', $details->get('last_name'));

			} else {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//accounts-module.base.messages.missingAttribute.heading'),
					$this->translator->translate('//accounts-module.base.messages.missingAttribute.message'),
					[
						'pointer' => '/data/attributes/details/last_name',
					]
				);
			}

			if ($details->has('middle_name') && $details->get('middle_name') !== '') {
				$update->offsetSet('middleName', $details->get('middle_name'));

			} else {
				$update->offsetSet('middleName', null);
			}

			return $update;
		}

		return null;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return Utils\ArrayHash|null
	 */
	protected function hydrateParamsAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): ?Utils\ArrayHash {
		$params = Utils\ArrayHash::from([
			'datetime' => [
				'format' => [],
			],
		]);

		if ($attributes->has('week_start') && is_scalar($attributes->get('week_start'))) {
			$params['datetime']->offsetSet('week_start', (int) $attributes->get('week_start'));
		}

		if (
			$attributes->has('datetime')
			&& $attributes->get('datetime') instanceof JsonAPIDocument\Objects\IStandardObject
		) {
			/** @var JsonAPIDocument\Objects\IStandardObject $datetime */
			$datetime = $attributes->get('datetime');

			if ($datetime->has('timezone') && is_scalar($datetime->get('timezone'))) {
				$params['datetime']->offsetSet('zone', (string) $datetime->get('timezone'));
			}

			if ($datetime->has('date_format') && is_scalar($datetime->get('date_format'))) {
				$params['datetime']['format']->offsetSet('date', (string) $datetime->get('date_format'));
			}

			if ($datetime->has('time_format') && is_scalar($datetime->get('time_format'))) {
				$params['datetime']['format']->offsetSet('time', (string) $datetime->get('time_format'));
			}
		}

		return $params;
	}

	/**
	 * @param JsonAPIDocument\Objects\IStandardObject $attributes
	 *
	 * @return MetadataTypes\AccountStateType
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 * @throws Translation\Exceptions\InvalidArgument
	 */
	protected function hydrateStateAttribute(
		JsonAPIDocument\Objects\IStandardObject $attributes
	): MetadataTypes\AccountStateType {
		if (
			!is_scalar($attributes->get('state'))
			|| !MetadataTypes\AccountStateType::isValidValue((string) $attributes->get('state'))
		) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//accounts-module.base.messages.invalidAttribute.heading'),
				$this->translator->translate('//accounts-module.base.messages.invalidAttribute.message'),
				[
					'pointer' => '/data/attributes/state',
				]
			);
		}

		return MetadataTypes\AccountStateType::get((string) $attributes->get('state'));
	}

}
