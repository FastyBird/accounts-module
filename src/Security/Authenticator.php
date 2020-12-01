<?php declare(strict_types = 1);

/**
 * Authenticator.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AuthModule\Security;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Models;
use FastyBird\AuthModule\Types;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;

/**
 * Account authentication
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Authenticator implements SimpleAuthSecurity\IAuthenticator
{

	public const IDENTITY_UID_NOT_FOUND = 110;

	public const INVALID_CREDENTIAL_FOR_UID = 120;

	public const ACCOUNT_PROFILE_BLOCKED = 210;
	public const ACCOUNT_PROFILE_DELETED = 220;
	public const ACCOUNT_PROFILE_OTHER_ERROR = 230;

	/** @var Models\Identities\IIdentityRepository */
	private $identityRepository;

	public function __construct(
		Models\Identities\IIdentityRepository $identityRepository
	) {
		$this->identityRepository = $identityRepository;
	}

	/**
	 * Performs an system authentication.
	 *
	 * @param mixed[] $credentials
	 *
	 * @return Entities\Identities\UserAccountIdentity
	 *
	 * @throws Exceptions\AccountNotFoundException
	 * @throws Exceptions\AuthenticationFailedException
	 */
	public function authenticate(array $credentials): SimpleAuthSecurity\IIdentity
	{
		[$username, $password] = $credentials + [null, null];

		/** @var Entities\Identities\UserAccountIdentity|null $identity */
		$identity = $this->identityRepository->findOneByUid($username, Entities\Identities\UserAccountIdentity::class);

		if ($identity === null) {
			throw new Exceptions\AccountNotFoundException('The identity identifier is incorrect', self::IDENTITY_UID_NOT_FOUND);
		}

		// Check if password is ok
		if (!$identity->verifyPassword((string) $password)) {
			throw new Exceptions\AuthenticationFailedException('The password is incorrect', self::INVALID_CREDENTIAL_FOR_UID);
		}

		$account = $identity->getAccount();

		if ($account->getState()->equalsValue(Types\AccountStateType::STATE_ACTIVE)) {
			return $identity;
		}

		if ($account->getState()->equalsValue(Types\AccountStateType::STATE_BLOCKED)) {
			throw new Exceptions\AuthenticationFailedException('Account profile is blocked', self::ACCOUNT_PROFILE_BLOCKED);

		} elseif ($account->getState()->equalsValue(Types\AccountStateType::STATE_DELETED)) {
			throw new Exceptions\AuthenticationFailedException('Account profile is deleted', self::ACCOUNT_PROFILE_DELETED);
		}

		throw new Exceptions\AuthenticationFailedException('Account profile is not available', self::ACCOUNT_PROFILE_OTHER_ERROR);
	}

}
