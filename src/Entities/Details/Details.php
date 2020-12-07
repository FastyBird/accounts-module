<?php declare(strict_types = 1);

/**
 * Details.php
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

namespace FastyBird\AuthModule\Entities\Details;

use FastyBird\AuthModule\Entities;
use FastyBird\Database\Entities as DatabaseEntities;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts_details",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Accounts details"
 *     }
 * )
 */
class Details implements IDetails
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="detail_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var Entities\Accounts\IAccount
	 *
	 * @ORM\OneToOne(targetEntity="FastyBird\AuthModule\Entities\Accounts\UserAccount", inversedBy="details")
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", unique=true, onDelete="cascade", nullable=false)
	 *
	 * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements.WriteOnlyProperty
	 */
	private $account;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="detail_first_name", length=100, nullable=false)
	 */
	private $firstName;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string", name="detail_last_name", length=100, nullable=false)
	 */
	private $lastName;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="detail_middle_name", length=100, nullable=true, options={"default": null})
	 */
	private $middleName = null;

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param string $firstName
	 * @param string $lastName
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Accounts\IAccount $account,
		string $firstName,
		string $lastName
	) {
		$this->id = Uuid\Uuid::uuid4();

		$this->account = $account;

		$this->setFirstName($firstName);
		$this->setLastName($lastName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFirstName(): string
	{
		return $this->firstName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setFirstName(string $firstName): void
	{
		$this->firstName = $firstName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastName(): string
	{
		return $this->lastName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setLastName(string $lastName): void
	{
		$this->lastName = $lastName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMiddleName(): ?string
	{
		return $this->middleName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMiddleName(?string $middleName): void
	{
		$this->middleName = $middleName;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->firstName . ' ' . $this->lastName;
	}

}
