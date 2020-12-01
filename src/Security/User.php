<?php declare(strict_types = 1);

/**
 * User.php
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
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use Ramsey\Uuid;

/**
 * Application user
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class User extends SimpleAuthSecurity\User
{

	/**
	 * @return Uuid\UuidInterface|null
	 */
	public function getId(): ?Uuid\UuidInterface
	{
		return $this->getAccount() !== null ? $this->getAccount()->getId() : null;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		if ($this->isLoggedIn()) {
			$account = $this->getAccount();

			return $account !== null ? ($account instanceof Entities\Accounts\IUserAccount ? $account->getName() : 'Machine') : 'Registered';
		}

		return 'Guest';
	}

	/**
	 * @return Entities\Accounts\IAccount|null
	 */
	public function getAccount(): ?Entities\Accounts\IAccount
	{
		if ($this->isLoggedIn()) {
			$identity = $this->getIdentity();

			if ($identity instanceof Entities\Identities\IIdentity) {
				return $identity->getAccount();
			}
		}

		return null;
	}

}
