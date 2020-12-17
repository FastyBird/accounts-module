<?php declare(strict_types = 1);

/**
 * TIdentityFinder.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           25.06.20
 */

namespace FastyBird\AuthModule\Controllers\Finders;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Psr\Http\Message;
use Ramsey\Uuid;

/**
 * @property-read Localization\ITranslator $translator
 * @property-read Models\Identities\IIdentityRepository $identityRepository
 */
trait TIdentityFinder
{

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param Entities\Accounts\IAccount|null $account
	 *
	 * @return Entities\Identities\IIdentity
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function findIdentity(
		Message\ServerRequestInterface $request,
		?Entities\Accounts\IAccount $account = null
	): Entities\Identities\IIdentity {
		if (!Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ITEM_ID, null))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//auth-module.base.messages.notFound.heading'),
				$this->translator->translate('//auth-module.base.messages.notFound.message')
			);
		}

		$findQuery = new Queries\FindIdentitiesQuery();
		$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ITEM_ID, null)));

		if ($account !== null) {
			$findQuery->forAccount($account);
		}

		$identity = $this->identityRepository->findOneBy($findQuery);

		if ($identity === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//auth-module.base.messages.notFound.heading'),
				$this->translator->translate('//auth-module.base.messages.notFound.message')
			);
		}

		return $identity;
	}

}
