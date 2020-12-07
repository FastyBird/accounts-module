<?php declare(strict_types = 1);

/**
 * BaseV1Controller.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AuthModule\Controllers;

use Contributte\Translation;
use Doctrine\DBAL\Connection;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Security;
use FastyBird\DateTimeFactory;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use IPub\JsonAPIDocument;
use Nette;
use Nette\Utils;
use Nettrine\ORM;
use Psr\Http\Message;
use Psr\Log;

/**
 * API base controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
abstract class BaseV1Controller
{

	use Nette\SmartObject;

	/** @var Security\User */
	protected $user;

	/** @var DateTimeFactory\DateTimeFactory */
	protected $dateFactory;

	/** @var Translation\PrefixedTranslator */
	protected $translator;

	/** @var ORM\ManagerRegistry */
	protected $managerRegistry;

	/** @var Log\LoggerInterface */
	protected $logger;

	/** @var string */
	protected $translationDomain = '';

	/**
	 * @param Security\User $user
	 *
	 * @return void
	 */
	public function injectUser(Security\User $user): void
	{
		$this->user = $user;
	}

	/**
	 * @param DateTimeFactory\DateTimeFactory $dateFactory
	 *
	 * @return void
	 */
	public function injectDateFactory(DateTimeFactory\DateTimeFactory $dateFactory): void
	{
		$this->dateFactory = $dateFactory;
	}

	/**
	 * @param Translation\Translator $translator
	 *
	 * @return void
	 */
	public function injectTranslator(Translation\Translator $translator): void
	{
		$this->translator = new Translation\PrefixedTranslator($translator, $this->translationDomain);
	}

	/**
	 * @param ORM\ManagerRegistry $managerRegistry
	 *
	 * @return void
	 */
	public function injectManagerRegistry(ORM\ManagerRegistry $managerRegistry): void
	{
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * @param Log\LoggerInterface|null $logger
	 *
	 * @return void
	 */
	public function injectLogger(?Log\LoggerInterface $logger): void
	{
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		// & relation entity name
		$relationEntity = strtolower($request->getAttribute(Router\Router::RELATION_ENTITY));

		if ($relationEntity !== '') {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_NOT_FOUND,
				$this->translator->translate('//module.base.messages.relationNotFound.heading'),
				$this->translator->translate('//module.base.messages.relationNotFound.message', ['relation' => $relationEntity])
			);
		}

		throw new JsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_NOT_FOUND,
			$this->translator->translate('//module.base.messages.unknownRelation.heading'),
			$this->translator->translate('//module.base.messages.unknownRelation.message')
		);
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return JsonAPIDocument\IDocument<JsonAPIDocument\Objects\StandardObject>
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	protected function createDocument(Message\ServerRequestInterface $request): JsonAPIDocument\IDocument
	{
		try {
			$document = new JsonAPIDocument\Document(Utils\Json::decode($request->getBody()
				->getContents()));

		} catch (Utils\JsonException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//module.base.messages.notValidJson.heading'),
				$this->translator->translate('//module.base.messages.notValidJson.message')
			);

		} catch (JsonAPIDocument\Exceptions\RuntimeException $ex) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//module.base.messages.notValidJsonApi.heading'),
				$this->translator->translate('//module.base.messages.notValidJsonApi.message')
			);
		}

		return $document;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param JsonAPIDocument\IDocument<JsonAPIDocument\Objects\StandardObject> $document
	 *
	 * @return bool
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 */
	protected function validateIdentifier(
		Message\ServerRequestInterface $request,
		JsonAPIDocument\IDocument $document
	): bool {
		if (
			in_array(strtoupper($request->getMethod()), [
				RequestMethodInterface::METHOD_POST,
				RequestMethodInterface::METHOD_PATCH,
			], true)
			&& $request->getAttribute(Router\Router::URL_ITEM_ID, null) !== null
			&& $request->getAttribute(Router\Router::URL_ITEM_ID) !== $document->getResource()
				->getIdentifier()
				->getId()
		) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_BAD_REQUEST,
				$this->translator->translate('//module.base.messages.invalidIdentifier.heading'),
				$this->translator->translate('//module.base.messages.invalidIdentifier.message')
			);
		}

		return true;
	}

	/**
	 * @param Utils\ArrayHash $data
	 * @param Entities\Accounts\IAccount $account
	 * @param bool $required
	 *
	 * @return bool
	 *
	 * @throws JsonApiExceptions\JsonApiErrorException
	 */
	protected function validateAccountRelation(
		Utils\ArrayHash $data,
		Entities\Accounts\IAccount $account,
		bool $required = false
	): bool {
		if (
			(
				$required && !$data->offsetExists('account')
				|| $data->offsetExists('account')
			) && (
				!$data->offsetGet('account') instanceof Entities\Accounts\IAccount
				|| !$account->getId()
					->equals($data->offsetGet('account')
						->getId())
			)
		) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.invalidRelation.heading'),
				$this->translator->translate('//module.base.messages.invalidRelation.message'),
				[
					'pointer' => '/data/relationships/account/data/id',
				]
			);
		}

		return true;
	}

	/**
	 * @return Connection
	 */
	protected function getOrmConnection(): Connection
	{
		$connection = $this->managerRegistry->getConnection();

		if ($connection instanceof Connection) {
			return $connection;
		}

		throw new Exceptions\RuntimeException('Entity manager could not be loaded');
	}

}
