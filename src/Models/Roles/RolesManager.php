<?php declare(strict_types = 1);

/**
 * RolesManager.php
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

namespace FastyBird\AuthModule\Models\Roles;

use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Models;
use IPub\DoctrineCrud\Crud;
use Nette;
use Nette\Utils;

/**
 * ACL roles entities manager
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class RolesManager implements IRolesManager
{

	use Nette\SmartObject;

	/** @var Crud\IEntityCrud */
	private $entityCrud;

	public function __construct(
		Crud\IEntityCrud $entityCrud
	) {
		// Entity CRUD for handling entities
		$this->entityCrud = $entityCrud;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(
		Utils\ArrayHash $values
	): Entities\Roles\IRole {
		/** @var Entities\Roles\IRole $entity */
		$entity = $this->entityCrud->getEntityCreator()
			->create($values);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(
		Entities\Roles\IRole $entity,
		Utils\ArrayHash $values
	): Entities\Roles\IRole {
		/** @var Entities\Roles\IRole $entity */
		$entity = $this->entityCrud->getEntityUpdater()
			->update($values, $entity);

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(
		Entities\Roles\IRole $entity
	): bool {
		// Delete entity from database
		return $this->entityCrud->getEntityDeleter()
			->delete($entity);
	}

}
