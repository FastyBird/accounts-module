<?php declare(strict_types = 1);

/**
 * AccountSchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           19.08.20
 */

namespace FastyBird\AuthModule\Schemas\Accounts;

use FastyBird\AuthModule;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Router;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Account entity schema
 *
 * @package         FastyBird:AuthModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends JsonApiSchemas\JsonApiSchema<Entities\Accounts\IAccount>
 */
final class AccountSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'auth-module/account';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_IDENTITIES = 'identities';
	public const RELATIONSHIPS_ROLES = 'roles';
	public const RELATIONSHIPS_EMAILS = 'emails';

	/** @var Routing\IRouter */
	protected Routing\IRouter $router;

	public function __construct(
		Routing\IRouter $router
	) {
		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Accounts\Account::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($account, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'name' => $account->getName(),

			'details' => [
				'first_name'  => $account->getDetails()->getFirstName(),
				'last_name'   => $account->getDetails()->getLastName(),
				'middle_name' => $account->getDetails()->getMiddleName(),
			],

			'language' => $account->getLanguage(),

			'week_start' => $account->getParam('datetime.week_start', 1),
			'datetime'   => [
				'timezone'    => $account->getParam('datetime.zone', 'Europe/London'),
				'date_format' => $account->getParam('datetime.format.date', 'DD.MM.YYYY'),
				'time_format' => $account->getParam('datetime.format.time', 'HH:mm'),
			],

			'state' => $account->getState()->getValue(),

			'last_visit' => $account->getLastVisit() !== null ? $account->getLastVisit()->format(DATE_ATOM) : null,
			'registered' => $account->getCreatedAt() !== null ? $account->getCreatedAt()->format(DATE_ATOM) : null,
		];
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($account): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				AuthModule\Constants::ROUTE_NAME_ACCOUNT,
				[
					Router\Routes::URL_ITEM_ID => $account->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($account, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_IDENTITIES => [
				self::RELATIONSHIP_DATA          => $account->getIdentities(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
			self::RELATIONSHIPS_ROLES      => [
				self::RELATIONSHIP_DATA          => $account->getRoles(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => false,
			],
			self::RELATIONSHIPS_EMAILS => [
				self::RELATIONSHIP_DATA          => $account->getEmails(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($account, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_IDENTITIES) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITIES,
					[
						Router\Routes::URL_ACCOUNT_ID => $account->getPlainId(),
					]
				),
				true,
				[
					'count' => count($account->getIdentities()),
				]
			);

		} elseif ($name === self::RELATIONSHIPS_EMAILS) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT_EMAILS,
					[
						Router\Routes::URL_ACCOUNT_ID => $account->getPlainId(),
					]
				),
				true,
				[
					'count' => count($account->getEmails()),
				]
			);
		}

		return parent::getRelationshipRelatedLink($account, $name);
	}

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($account, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_IDENTITIES
			|| $name === self::RELATIONSHIPS_ROLES
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT_RELATIONSHIP,
					[
						Router\Routes::URL_ITEM_ID     => $account->getPlainId(),
						Router\Routes::RELATION_ENTITY => $name,
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_EMAILS) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT_RELATIONSHIP,
					[
						Router\Routes::URL_ITEM_ID     => $account->getPlainId(),
						Router\Routes::RELATION_ENTITY => $name,
					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($account, $name);
	}

}
