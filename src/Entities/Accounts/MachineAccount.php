<?php declare(strict_types = 1);

/**
 * MachineAccount.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           11.06.20
 */

namespace FastyBird\AuthModule\Entities\Accounts;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\AuthModule\Entities;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts_machines",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Machine accounts"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="account_device_unique", columns={"account_device"})
 *     }
 * )
 */
class MachineAccount extends Account implements IMachineAccount
{

	/**
	 * @var Entities\Accounts\IUserAccount
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\AuthModule\Entities\Accounts\UserAccount")
	 * @ORM\JoinColumn(name="owner_id", referencedColumnName="account_id", onDelete="cascade", nullable=false)
	 */
	private $owner;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="account_device", length=150, nullable=false)
	 */
	private $device;

	/**
	 * @param string $device
	 * @param Entities\Accounts\IUserAccount $owner
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $device,
		Entities\Accounts\IUserAccount $owner,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($id);

		$this->owner = $owner;
		$this->device = $device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOwner(): IUserAccount
	{
		return $this->owner;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDevice(): string
	{
		return $this->device;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type'   => 'machine',
			'device' => $this->getDevice(),
			'owner'  => $this->getOwner()->getPlainId(),
		]);
	}

}
