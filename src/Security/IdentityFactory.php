<?php declare(strict_types = 1);

/**
 * IdentityFactory.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           15.07.20
 */

namespace FastyBird\AuthModule\Security;

use FastyBird\AuthModule\Entities;
use FastyBird\SimpleAuth\Models as SimpleAuthModels;
use FastyBird\SimpleAuth\Queries as SimpleAuthQueries;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use Lcobucci\JWT;

/**
 * Application identity factory
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class IdentityFactory implements SimpleAuthSecurity\IIdentityFactory
{

	/** @var SimpleAuthModels\Tokens\ITokenRepository */
	private $tokenRepository;

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
		$findToken = new SimpleAuthQueries\FindTokensQuery();
		$findToken->byToken($token->toString());

		$accessToken = $this->tokenRepository->findOneBy($findToken, Entities\Tokens\AccessToken::class);

		if ($accessToken instanceof Entities\Tokens\IAccessToken) {
			return $accessToken->getIdentity();
		}

		return null;
	}

}
