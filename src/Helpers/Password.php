<?php declare(strict_types = 1);

/**
 * Password.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AuthModule\Helpers;

use FastyBird\AuthModule\Exceptions;
use Nette;
use Nette\Utils;

/**
 * Password generator and verification
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Password
{

	use Nette\SmartObject;

	private const SEPARATOR = '##';

	/** @var string */
	private string $hash;

	/** @var string */
	private string $salt;

	/** @var string */
	private string $password;

	public function __construct(
		?string $hash = null,
		?string $password = null,
		?string $salt = null
	) {
		if ($password !== null && $hash !== null) {
			throw new Exceptions\InvalidStateException('Only password string or hash could be provided');
		}

		if ($salt !== null) {
			$this->salt = $salt;

		} else {
			$this->createSalt();
		}

		if ($password !== null) {
			$this->setPassword($password, $salt);

		} elseif ($hash !== null) {
			$this->hash = $hash;

		} else {
			throw new Exceptions\InvalidStateException('Password or hash have to be provided');
		}
	}

	/**
	 * @return string
	 */
	public function createSalt(): string
	{
		return $this->salt = Utils\Random::generate(5);
	}

	/**
	 * @param int $length
	 *
	 * @return Password
	 */
	public static function createRandom(int $length = 4): Password
	{
		return new self(null, Utils\Random::generate($length));
	}

	/**
	 * @param string $password
	 *
	 * @return Password
	 */
	public static function createFromString(string $password): Password
	{
		return new self(null, $password);
	}

	/**
	 * @return string
	 */
	public function getSalt(): string
	{
		return $this->salt;
	}

	/**
	 * @param string $salt
	 *
	 * @return void
	 */
	public function setSalt(string $salt): void
	{
		$this->salt = $salt;
	}

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * @return string|null
	 */
	public function getPassword(): ?string
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @param string|null $salt
	 *
	 * @return void
	 */
	public function setPassword(string $password, ?string $salt = null): void
	{
		$this->password = $password;
		$this->salt = $salt ?? $this->createSalt();
		$this->hash = $this->hashPassword($password, $this->salt);
	}

	/**
	 * @param string $password
	 * @param string|null $salt
	 *
	 * @return bool
	 */
	public function isEqual(string $password, ?string $salt = null): bool
	{
		if ($salt !== null) {
			$this->salt = $salt;
		}

		return $this->hash === $this->hashPassword($password, $this->salt);
	}

	/**
	 * @param string $password
	 * @param string|null $salt
	 *
	 * @return string
	 */
	private function hashPassword(string $password, ?string $salt = null): string
	{
		return hash('sha512', $salt . self::SEPARATOR . $password);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->hash;
	}

}
