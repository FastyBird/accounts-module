<?php declare(strict_types = 1);

/**
 * RoleSchema.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Schemas
 * @since          0.1.0
 *
 * @date           26.05.20
 */

namespace FastyBird\AuthModule\Schemas\Roles;

use FastyBird\AuthModule;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\JsonApi\Schemas as JsonApiSchemas;
use IPub\SlimRouter\Routing;
use Neomerx\JsonApi;

/**
 * Role entity schema
 *
 * @package          FastyBird:AuthModule!
 * @subpackage       Schemas
 *
 * @author           Adam Kadlec <adam.kadlec@fastybird.com>
 *
 * @phpstan-template T of Entities\Roles\IRole
 * @phpstan-extends  JsonApiSchemas\JsonApiSchema<T>
 */
final class RoleSchema extends JsonApiSchemas\JsonApiSchema
{

	/**
	 * Define entity schema type string
	 */
	public const SCHEMA_TYPE = 'auth-module/role';

	/**
	 * Define relationships names
	 */
	public const RELATIONSHIPS_PARENT = 'parent';
	public const RELATIONSHIPS_CHILDREN = 'children';

	/** @var Models\Roles\IRoleRepository */
	private $roleRepository;

	/** @var Routing\IRouter */
	private $router;

	public function __construct(
		Models\Roles\IRoleRepository $roleRepository,
		Routing\IRouter $router
	) {
		$this->roleRepository = $roleRepository;

		$this->router = $router;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityClass(): string
	{
		return Entities\Roles\Role::class;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return self::SCHEMA_TYPE;
	}

	/**
	 * @param Entities\Roles\IRole $role
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, string|bool>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getAttributes($role, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		return [
			'name'        => $role->getName(),
			'description' => $role->getDescription(),

			'anonymous'     => $role->isAnonymous(),
			'authenticated' => $role->isAuthenticated(),
			'administrator' => $role->isAdministrator(),
		];
	}

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getSelfLink($role): JsonApi\Contracts\Schema\LinkInterface
	{
		return new JsonApi\Schema\Link(
			false,
			$this->router->urlFor(
				AuthModule\Constants::ROUTE_NAME_ROLE,
				[
					Router\Router::URL_ITEM_ID => $role->getPlainId(),
				]
			),
			false
		);
	}

	/**
	 * @param Entities\Roles\IRole $role
	 * @param JsonApi\Contracts\Schema\ContextInterface $context
	 *
	 * @return iterable<string, mixed>
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationships($role, JsonApi\Contracts\Schema\ContextInterface $context): iterable
	{
		$relationships = [
			self::RELATIONSHIPS_CHILDREN => [
				self::RELATIONSHIP_DATA          => $this->getChildren($role),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			],
		];

		if ($role->getParent() !== null) {
			$relationships[self::RELATIONSHIPS_PARENT] = [
				self::RELATIONSHIP_DATA          => $role->getParent(),
				self::RELATIONSHIP_LINKS_SELF    => true,
				self::RELATIONSHIP_LINKS_RELATED => true,
			];
		}

		return $relationships;
	}

	/**
	 * @param Entities\Roles\IRole $role
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipRelatedLink($role, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if ($name === self::RELATIONSHIPS_PARENT && $role->getParent() !== null) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ROLE,
					[
						Router\Router::URL_ITEM_ID => $role->getPlainId(),
					]
				),
				false
			);

		} elseif ($name === self::RELATIONSHIPS_CHILDREN) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ROLE_CHILDREN,
					[
						Router\Router::URL_ITEM_ID => $role->getPlainId(),
					]
				),
				true,
				[
					'count' => count($role->getChildren()),
				]
			);
		}

		return parent::getRelationshipRelatedLink($role, $name);
	}

	/**
	 * @param Entities\Roles\IRole $role
	 * @param string $name
	 *
	 * @return JsonApi\Contracts\Schema\LinkInterface
	 *
	 * @phpstan-param T $role
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function getRelationshipSelfLink($role, string $name): JsonApi\Contracts\Schema\LinkInterface
	{
		if (
			$name === self::RELATIONSHIPS_CHILDREN
			|| ($name === self::RELATIONSHIPS_PARENT && $role->getParent() !== null)
		) {
			return new JsonApi\Schema\Link(
				false,
				$this->router->urlFor(
					AuthModule\Constants::ROUTE_NAME_ROLE_RELATIONSHIP,
					[
						Router\Router::URL_ITEM_ID     => $role->getPlainId(),
						Router\Router::RELATION_ENTITY => $name,

					]
				),
				false
			);
		}

		return parent::getRelationshipSelfLink($role, $name);
	}

	/**
	 * @param Entities\Roles\IRole $role
	 *
	 * @return Entities\Roles\IRole[]
	 */
	private function getChildren(Entities\Roles\IRole $role): array
	{
		$findQuery = new Queries\FindRolesQuery();
		$findQuery->forParent($role);

		return $this->roleRepository->findAllBy($findQuery);
	}

}
