<?php declare(strict_types = 1);

/**
 * TAccountFinder.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           22.06.20
 */

namespace FastyBird\AuthModule\Controllers\Finders;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Queries;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Security;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Localization;
use Psr\Http\Message;
use Ramsey\Uuid;

/**
 * @property-read Localization\ITranslator $translator
 * @property-read Security\User $user
 * @property-read Models\Accounts\IAccountRepository $accountRepository
 */
trait TAccountFinder
{

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return Entities\Accounts\IAccount
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 */
	protected function findAccount(
		Message\ServerRequestInterface $request
	): Entities\Accounts\IAccount {
		if (!Uuid\Uuid::isValid($request->getAttribute(Router\Routes::URL_ACCOUNT_ID, null))) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//module.base.messages.notFound.heading'),
				$this->translator->translate('//module.base.messages.notFound.message')
			);
		}

		$findQuery = new Queries\FindAccountsQuery();
		$findQuery->byId(Uuid\Uuid::fromString($request->getAttribute(Router\Routes::URL_ACCOUNT_ID, null)));

		$account = $this->accountRepository->findOneBy($findQuery);

		if ($account === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//module.base.messages.notFound.heading'),
				$this->translator->translate('//module.base.messages.notFound.message')
			);
		}

		return $account;
	}

}
