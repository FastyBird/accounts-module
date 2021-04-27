<?php declare(strict_types = 1);

/**
 * TRoleFinder.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           03.06.20
 */

namespace FastyBird\AccountsModule\Controllers\Finders;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use FastyBird\AccountsModule\Queries;
use FastyBird\AccountsModule\Router;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Psr\Http\Message;
use Ramsey\Uuid;

/**
 * @property-read Localization\ITranslator $translator
 * @property-read Models\Roles\IRoleRepository $roleRepository
 */
trait TRoleFinder
{

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return Entities\Roles\IRole
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function findRole(
		Message\ServerRequestInterface $request
	): Entities\Roles\IRole {
		if (!Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//accounts-module.base.messages.notFound.heading'),
				$this->translator->translate('//accounts-module.base.messages.notFound.message')
			);
		}

		$findQuery = new Queries\FindRolesQuery();
		$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID)));

		$role = $this->roleRepository->findOneBy($findQuery);

		if ($role === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//accounts-module.base.messages.notFound.heading'),
				$this->translator->translate('//accounts-module.base.messages.notFound.message')
			);
		}

		return $role;
	}

}
