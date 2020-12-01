<?php declare(strict_types = 1);

/**
 * IAccountsManager.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AuthModule\Models\Accounts;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use Nette\Utils;

/**
 * Accounts entities manager interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccountsManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Accounts\IAccount
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Accounts\IAccount;

	/**
	 * @param Entities\Accounts\IAccount $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Accounts\IAccount
	 */
	public function update(
		Entities\Accounts\IAccount $entity,
		Utils\ArrayHash $values
	): Entities\Accounts\IAccount;

	/**
	 * @param Entities\Accounts\IAccount $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Accounts\IAccount $entity
	): bool;

}
