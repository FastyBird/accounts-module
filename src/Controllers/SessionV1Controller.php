<?php declare(strict_types = 1);

/**
 * SessionV1Controller.php
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

use DateTimeImmutable;
use Doctrine;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Router;
use FastyBird\AuthModule\Schemas;
use FastyBird\AuthModule\Security;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use FastyBird\SimpleAuth\Models as SimpleAuthModels;
use FastyBird\SimpleAuth\Queries as SimpleAuthQueries;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use FastyBird\SimpleAuth\Types as SimpleAuthTypes;
use FastyBird\WebServer\Http as WebServerHttp;
use Fig\Http\Message\StatusCodeInterface;
use Nette\Utils;
use Psr\Http\Message;
use Ramsey\Uuid;
use Throwable;

/**
 * User session controller
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Controllers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class SessionV1Controller extends BaseV1Controller
{

	/** @var SimpleAuthModels\Tokens\ITokenRepository */
	private $tokenRepository;

	/** @var SimpleAuthModels\Tokens\ITokensManager */
	private $tokensManager;

	/** @var SimpleAuthSecurity\TokenReader */
	private $tokenReader;

	/** @var SimpleAuthSecurity\TokenBuilder */
	private $tokenBuilder;

	/** @var string */
	protected $translationDomain = 'module.session';

	public function __construct(
		SimpleAuthModels\Tokens\ITokenRepository $tokenRepository,
		SimpleAuthModels\Tokens\ITokensManager $tokensManager,
		SimpleAuthSecurity\TokenReader $tokenReader,
		SimpleAuthSecurity\TokenBuilder $tokenBuilder
	) {
		$this->tokenRepository = $tokenRepository;
		$this->tokensManager = $tokensManager;

		$this->tokenReader = $tokenReader;
		$this->tokenBuilder = $tokenBuilder;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function read(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$accessToken = $this->getToken($request);

		return $response
			->withEntity(WebServerHttp\ScalarEntity::from($accessToken));
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\User(guest)
	 */
	public function create(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$attributes = $document->getResource()->getAttributes();

		if (!$attributes->has('uid')) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//module.base.messages.missingAttribute.message'),
				[
					'pointer' => '/data/attributes/uid',
				]
			);
		}

		if (!$attributes->has('password')) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//module.base.messages.missingAttribute.message'),
				[
					'pointer' => '/data/attributes/password',
				]
			);
		}

		try {
			// Login user with system authenticator
			$this->user->login((string) $attributes->get('uid'), (string) $attributes->get('password'));

		} catch (Throwable $ex) {
			if ($ex instanceof Exceptions\AccountNotFoundException) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('messages.unknownAccount.heading'),
					$this->translator->translate('messages.unknownAccount.message')
				);

			} elseif ($ex instanceof Exceptions\AuthenticationFailedException) {
				switch ($ex->getCode()) {
					case Security\Authenticator::ACCOUNT_PROFILE_BLOCKED:
					case Security\Authenticator::ACCOUNT_PROFILE_DELETED:
						throw new JsonApiExceptions\JsonApiErrorException(
							StatusCodeInterface::STATUS_FORBIDDEN,
							$this->translator->translate('//module.base.messages.forbidden.heading'),
							$this->translator->translate('//module.base.messages.forbidden.message')
						);

					default:
						throw new JsonApiExceptions\JsonApiErrorException(
							StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
							$this->translator->translate('messages.unknownAccount.heading'),
							$this->translator->translate('messages.unknownAccount.message')
						);
				}

			} else {
				// Log catched exception
				$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
					'exception' => [
						'message' => $ex->getMessage(),
						'code'    => $ex->getCode(),
					],
				]);

				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
					$this->translator->translate('//module.base.messages.notCreated.heading'),
					$this->translator->translate('//module.base.messages.notCreated.message')
				);
			}
		}

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			$validTill = $this->getNow()->modify(Entities\Tokens\IAccessToken::TOKEN_EXPIRATION);

			$values = Utils\ArrayHash::from([
				'id'        => Uuid\Uuid::uuid4(),
				'entity'    => Entities\Tokens\AccessToken::class,
				'token'     => $this->createToken($this->user->getId() ?? Uuid\Uuid::uuid4(), $this->user->getRoles(), $validTill),
				'validTill' => $validTill,
				'state'     => SimpleAuthTypes\TokenStateType::get(SimpleAuthTypes\TokenStateType::STATE_ACTIVE),
				'identity'  => $this->user->getIdentity(),
			]);

			$accessToken = $this->tokensManager->create($values);

			$validTill = $this->getNow()->modify(Entities\Tokens\IRefreshToken::TOKEN_EXPIRATION);

			$values = Utils\ArrayHash::from([
				'id'          => Uuid\Uuid::uuid4(),
				'entity'      => Entities\Tokens\RefreshToken::class,
				'accessToken' => $accessToken,
				'token'       => $this->createToken($this->user->getId() ?? Uuid\Uuid::uuid4(), [], $validTill),
				'validTill'   => $validTill,
				'state'       => SimpleAuthTypes\TokenStateType::get(SimpleAuthTypes\TokenStateType::STATE_ACTIVE),
			]);

			$this->tokensManager->create($values);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.notCreated.heading'),
				$this->translator->translate('//module.base.messages.notCreated.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withEntity(WebServerHttp\ScalarEntity::from($accessToken))
			->withStatus(StatusCodeInterface::STATUS_CREATED);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\User(guest)
	 */
	public function update(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$document = $this->createDocument($request);

		$attributes = $document->getResource()->getAttributes();

		if (!$attributes->has('refresh')) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('//module.base.messages.missingAttribute.heading'),
				$this->translator->translate('//module.base.messages.missingAttribute.message'),
				[
					'pointer' => '/data/attributes/refresh',
				]
			);
		}

		/** @var Entities\Tokens\IRefreshToken|null $refreshToken */
		$refreshToken = $this->tokenRepository->findOneByToken((string) $attributes->get('refresh'), Entities\Tokens\RefreshToken::class);

		if ($refreshToken === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.invalidRefreshToken.heading'),
				$this->translator->translate('messages.invalidRefreshToken.message'),
				[
					'pointer' => '/data/attributes/refresh',
				]
			);
		}

		if (
			$refreshToken->getValidTill() !== null
			&& $refreshToken->getValidTill() < $this->getNow()
		) {
			// Remove expired tokens
			$this->tokensManager->delete($refreshToken->getAccessToken());

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.refreshTokenExpired.heading'),
				$this->translator->translate('messages.refreshTokenExpired.message'),
				[
					'pointer' => '/data/attributes/refresh',
				]
			);
		}

		$accessToken = $refreshToken->getAccessToken();

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			// Auto-login user
			$this->user->login($accessToken->getIdentity());

			$validTill = $this->getNow()->modify(Entities\Tokens\IAccessToken::TOKEN_EXPIRATION);

			$values = Utils\ArrayHash::from([
				'id'        => Uuid\Uuid::uuid4(),
				'entity'    => Entities\Tokens\AccessToken::class,
				'token'     => $this->createToken($this->user->getId() ?? Uuid\Uuid::uuid4(), $this->user->getRoles(), $validTill),
				'validTill' => $validTill,
				'state'     => SimpleAuthTypes\TokenStateType::get(SimpleAuthTypes\TokenStateType::STATE_ACTIVE),
				'identity'  => $this->user->getIdentity(),
			]);

			$newAccessToken = $this->tokensManager->create($values);

			$validTill = $this->getNow()->modify(Entities\Tokens\IRefreshToken::TOKEN_EXPIRATION);

			$values = Utils\ArrayHash::from([
				'id'          => Uuid\Uuid::uuid4(),
				'entity'      => Entities\Tokens\RefreshToken::class,
				'accessToken' => $newAccessToken,
				'token'       => $this->createToken($this->user->getId() ?? Uuid\Uuid::uuid4(), [], $validTill),
				'validTill'   => $validTill,
				'state'       => SimpleAuthTypes\TokenStateType::get(SimpleAuthTypes\TokenStateType::STATE_ACTIVE),
			]);

			$this->tokensManager->create($values);

			$this->tokensManager->delete($refreshToken);
			$this->tokensManager->delete($accessToken);

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.refreshingTokenFailed.heading'),
				$this->translator->translate('messages.refreshingTokenFailed.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withEntity(WebServerHttp\ScalarEntity::from($newAccessToken))
			->withStatus(StatusCodeInterface::STATUS_CREATED);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Doctrine\DBAL\ConnectionException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function delete(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$accessToken = $this->getToken($request);

		try {
			// Start transaction connection to the database
			$this->getOrmConnection()->beginTransaction();

			if ($accessToken->getRefreshToken() !== null) {
				$this->tokensManager->delete($accessToken->getRefreshToken());
			}

			$this->tokensManager->delete($accessToken);

			$this->user->logout();

			// Commit all changes into database
			$this->getOrmConnection()->commit();

		} catch (Throwable $ex) {
			// Log catched exception
			$this->logger->error('[CONTROLLER] ' . $ex->getMessage(), [
				'exception' => [
					'message' => $ex->getMessage(),
					'code'    => $ex->getCode(),
				],
			]);

			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
				$this->translator->translate('messages.destroyingSessionFailed.heading'),
				$this->translator->translate('messages.destroyingSessionFailed.message')
			);

		} finally {
			// Revert all changes when error occur
			if ($this->getOrmConnection()->isTransactionActive()) {
				$this->getOrmConnection()->rollBack();
			}
		}

		/** @var WebServerHttp\Response $response */
		$response = $response
			->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);

		return $response;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 * @param WebServerHttp\Response $response
	 *
	 * @return WebServerHttp\Response
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 *
	 * @Secured
	 * @Secured\User(loggedIn)
	 */
	public function readRelationship(
		Message\ServerRequestInterface $request,
		WebServerHttp\Response $response
	): WebServerHttp\Response {
		$relationEntity = strtolower($request->getAttribute(Router\Router::RELATION_ENTITY));

		if ($relationEntity === Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT) {
			return $response
				->withEntity(WebServerHttp\ScalarEntity::from($this->user->getAccount()));
		}

		return parent::readRelationship($request, $response);
	}

	/**
	 * @return DateTimeImmutable
	 */
	private function getNow(): DateTimeImmutable
	{
		/** @var DateTimeImmutable $now */
		$now = $this->dateFactory->getNow();

		return $now;
	}

	/**
	 * @param Message\ServerRequestInterface $request
	 *
	 * @return Entities\Tokens\IAccessToken
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 */
	private function getToken(Message\ServerRequestInterface $request): Entities\Tokens\IAccessToken
	{
		$token = $this->tokenReader->read($request);

		if ($token === null) {
			throw new JsonApiExceptions\JsonApiErrorException(
				StatusCodeInterface::STATUS_FORBIDDEN,
				$this->translator->translate('//module.base.messages.forbidden.heading'),
				$this->translator->translate('//module.base.messages.forbidden.message')
			);
		}

		$findToken = new SimpleAuthQueries\FindTokensQuery();
		$findToken->byToken($token->toString());

		$accessToken = $this->tokenRepository->findOneBy($findToken, Entities\Tokens\AccessToken::class);

		if (
			$this->user->getAccount() !== null
			&& $accessToken instanceof Entities\Tokens\IAccessToken
			&& $accessToken->getIdentity()->getAccount()->getId()->equals($this->user->getAccount()->getId())
		) {
			return $accessToken;
		}

		throw new JsonApiExceptions\JsonApiErrorException(
			StatusCodeInterface::STATUS_FORBIDDEN,
			$this->translator->translate('//module.base.messages.forbidden.heading'),
			$this->translator->translate('//module.base.messages.forbidden.message')
		);
	}

	/**
	 * @param Uuid\UuidInterface $userId
	 * @param string[] $roles
	 * @param DateTimeImmutable|null $validTill
	 *
	 * @return string
	 *
	 * @throws Throwable
	 */
	private function createToken(
		Uuid\UuidInterface $userId,
		array $roles,
		?DateTimeImmutable $validTill
	): string {
		return $this->tokenBuilder->build($userId->toString(), $roles, $validTill)->toString();
	}

}
