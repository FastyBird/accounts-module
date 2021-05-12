<?php declare(strict_types = 1);

/**
 * IIdentitiesManager.php
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

namespace FastyBird\AccountsModule\Models\Identities;

use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Models;
use Nette\Utils;

/**
 * Accounts identities entities manager interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IIdentitiesManager
{

	/**
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Identities\IIdentity
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Identities\IIdentity;

	/**
	 * @param Entities\Identities\IIdentity $entity
	 * @param Utils\ArrayHash $values
	 *
	 * @return Entities\Identities\IIdentity
	 */
	public function update(
		Entities\Identities\IIdentity $entity,
		Utils\ArrayHash $values
	): Entities\Identities\IIdentity;

	/**
	 * @param Entities\Identities\IIdentity $entity
	 *
	 * @return bool
	 */
	public function delete(
		Entities\Identities\IIdentity $entity
	): bool;

}
