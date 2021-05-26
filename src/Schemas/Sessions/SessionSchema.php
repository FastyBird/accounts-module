<?php declare(strict_types = 1);

/**
 * SessionSchema.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Schemas\Sessions;

use FastyBird\AccountsModule;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Router;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Session entity schema
 *
 * @package            FastyBird:AccountsModule!
 * @subpackage         Schemas
 *
 * @author             Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-extends    JsonApiSchemas\JsonApiSchema<Entities\Tokens\IAccessToken>
 */
final class SessionSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'accounts-module/session';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_ACCOUNT = 'account';

	/** @var Routing\IRouter */
	private Routing\IRouter $router;

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
		return Entities\Tokens\IAccessToken::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Tokens\IAccessToken $accessToken
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|null>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($accessToken, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'token'      => $accessToken->getToken(),
			'expiration' => $accessToken->getValidTill() !== null ? $accessToken->getValidTill()
				->format(DATE_ATOM) : null,
			'token_type' => 'Bearer',
			'refresh'    => $accessToken->getRefreshToken() !== null ? $accessToken->getRefreshToken()
				->getToken() : null,
		];
	}

	/**
	 * @param Entities\Tokens\IAccessToken $accessToken
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($accessToken): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(AccountsModule\Constants::ROUTE_NAME_SESSION),
			false
		);
	}

	/**
	 * @param Entities\Tokens\IAccessToken $accessToken
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($accessToken, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_ACCOUNT => [
				self::RELATIONSHIP_DATA          => $accessToken->getIdentity()
					->getAccount(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Tokens\IAccessToken $accessToken
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($accessToken, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_ACCOUNT) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AccountsModule\Constants::ROUTE_NAME_ACCOUNT,
					[
						Router\Routes::URL_ITEM_ID => $accessToken->getIdentity()
							->getAccount()
							->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($accessToken, $name);
	}

	/**
	 * @param Entities\Tokens\IAccessToken $accessToken
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($accessToken, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_ACCOUNT) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AccountsModule\Constants::ROUTE_NAME_SESSION_RELATIONSHIP,
					[
						Router\Routes::RELATION_ENTITY => $name,
					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($accessToken, $name);
	}

}
