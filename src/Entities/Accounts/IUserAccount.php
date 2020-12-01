<?php declare(strict_types = 1);

/**
 * IUserAccount.php
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

namespace FastyBird\AuthModule\Entities\Accounts;

use FastyBird\AuthModule\Entities;

/**
 * User account entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IUserAccount extends IAccount
{

	/**
	 * @return Entities\Details\IDetails
	 */
	public function getDetails(): Entities\Details\IDetails;

	/**
	 * @param string $requestHash
	 *
	 * @return void
	 */
	public function setRequestHash(string $requestHash): void;

	/**
	 * @return string|null
	 */
	public function getRequestHash(): ?string;

	/**
	 * @param Entities\Emails\IEmail[] $emails
	 *
	 * @return void
	 */
	public function setEmails(array $emails): void;

	/**
	 * @param Entities\Emails\IEmail $email
	 *
	 * @return void
	 */
	public function addEmail(Entities\Emails\IEmail $email): void;

	/**
	 * @return Entities\Emails\IEmail[]
	 */
	public function getEmails(): array;

	/**
	 * @param Entities\Emails\IEmail $email
	 *
	 * @return void
	 */
	public function removeEmail(Entities\Emails\IEmail $email): void;

	/**
	 * @param string|null $id
	 *
	 * @return Entities\Emails\IEmail|null
	 */
	public function getEmail(?string $id = null): ?Entities\Emails\IEmail;

	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getLanguage(): string;

}
