<?php declare(strict_types = 1);

/**
 * User.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Security
 * @since          0.1.0
 *
 * @date           31.03.20
 */

namespace FastyBird\AccountsModule\Security;

use FastyBird\AccountsModule\Entities;
use FastyBird\SimpleAuth\Security as SimpleAuthSecurity;
use Ramsey\Uuid;

/**
 * Application user
 *
 * @package        FastyBird:AccountsModule!
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
		return $this->getAccount() !== null ? $this->getAccount()
			->getId() : null;
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

	/**
	 * @return string
	 */
	public function getName(): string
	{
		if ($this->isLoggedIn()) {
			$account = $this->getAccount();

			return $account !== null ? $account->getName() : 'Registered';
		}

		return 'Guest';
	}

}
