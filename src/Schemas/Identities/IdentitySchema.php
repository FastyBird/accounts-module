<?php declare(strict_types = 1);

/**
 * IdentitySchema.php
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

use FastyBird\AuthModule;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Router;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Identity entity schema
 *
 * @package         FastyBird:AuthModule!
 * @subpackage      Schemas
 *
 * @author          Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Identities\IIdentity
 * @phpstan-extends  JsonApiSchemas\JsonApiSchema<T>
 */
abstract class IdentitySchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_ACCOUNT = 'account';

	/** @var Routing\IRouter */
	private $router;

	public function __construct(
		Routing\IRouter $router
	) {
		$this->router = $router;
	}

	/**
	 * @param Entities\Identities\IIdentity $identity
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string>
	 *
	 * @phpstan-param T $identity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($identity, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'uid'   => $identity->getUid(),
			'state' => $identity->getState()
				->getValue(),
		];
	}

	/**
	 * @param Entities\Identities\IIdentity $identity
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $identity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($identity): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY,
				[
					Router\Routes::URL_ACCOUNT_ID => $identity->getAccount()
						->getPlainId(),
					Router\Routes::URL_ITEM_ID    => $identity->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Identities\IIdentity $identity
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpstan-param T $identity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($identity, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			self::RELATIONSHIPS_ACCOUNT => [
				self::RELATIONSHIP_DATA          => $identity->getAccount(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];
	}

	/**
	 * @param Entities\Identities\IIdentity $identity
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($identity, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_ACCOUNT) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT,
					[
						Router\Routes::URL_ITEM_ID => $identity->getAccount()
							->getPlainId(),
					]
				),
				false
			);
		}

		return parent::getRelationshipRelatedLink($identity, $name);
	}

	/**
	 * @param Entities\Identities\IIdentity $identity
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $identity
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($identity, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_ACCOUNT) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ACCOUNT_IDENTITY_RELATIONSHIP,
					[
						Router\Routes::URL_ACCOUNT_ID  => $identity->getAccount()
							->getPlainId(),
						Router\Routes::URL_ITEM_ID     => $identity->getPlainId(),
						Router\Routes::RELATION_ENTITY => $name,
					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($identity, $name);
	}

}
