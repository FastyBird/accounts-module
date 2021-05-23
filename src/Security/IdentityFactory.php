<?php declare(strict_types = 1);

/**
 * IdentityFactory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\AccountsModule\Security;

use FastyBird\AccountsModule\Entities;
use FastyBird\SimpleAuth\Models as SimpleAuthModels;
use FastyBird\SimpleAuth\Queries as SimpleAuthQueries;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use Lcobucci\JWT;

/**
 * Application identity factory
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityFactory implements SimpleAuthSecurity\IIdentityFactory
{

	/**
	 * @var SimpleAuthModels\Tokens\ITokenRepository
	 *
	 * @phpstan-var SimpleAuthModels\Tokens\ITokenRepository<Entities\Tokens\AccessToken>
	 */
	private SimpleAuthModels\Tokens\ITokenRepository $tokenRepository;

	/**
	 * @phpstan-param SimpleAuthModels\Tokens\ITokenRepository<Entities\Tokens\AccessToken> $tokenRepository
	 */
	public function __construct(
		SimpleAuthModels\Tokens\ITokenRepository $tokenRepository
	) {
		$this->tokenRepository = $tokenRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(JWT\Token $token): ?SimpleAuthSecurity\IIdentity
	{
		/** @phpstan-var SimpleAuthQueries\FindTokensQuery<Entities\Tokens\AccessToken> $findToken */
		$findToken = new SimpleAuthQueries\FindTokensQuery();
		$findToken->byToken($token->toString());

		$accessToken = $this->tokenRepository->findOneBy($findToken, Entities\Tokens\AccessToken::class);

		if ($accessToken instanceof Entities\Tokens\IAccessToken) {
			return $accessToken->getIdentity();
		}

		return null;
	}

}
