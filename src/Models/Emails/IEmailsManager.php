<?php declare(strict_types = 1);

/**
 * IEmailsManager.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Models\Emails;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use Nette\Utils;

/**
 * Accounts emails address entities manager interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IEmailsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Emails\IEmail
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Emails\IEmail;

	/**
	 * @param Entities\Emails\IEmail $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Emails\IEmail
	 */
	public function update(
		Entities\Emails\IEmail $entity,
		Utils\ArrayHash $values
	): Entities\Emails\IEmail;

	/**
	 * @param Entities\Emails\IEmail $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Emails\IEmail $entity
	): bool;

}
