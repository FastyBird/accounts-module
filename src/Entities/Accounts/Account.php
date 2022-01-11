<?php declare(strict_types = 1);

/**
 * Account.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Entities\Accounts;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use DateTimeInterface;
use Doctrine\Common;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\AccountsModule\Entities;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Application accounts"
 *     }
 * )
 */
class Account implements IAccount
{

	use Entities\TEntity;
	use Entities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="account_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var MetadataTypes\AccountStateType
	 *
	 * @Enum(class=MetadataTypes\AccountStateType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="account_state", nullable=false, options={"default": "notActivated"})
	 */
	protected $state;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="account_request_hash", nullable=true, options={"default": null})
	 */
	protected ?string $requestHash = null;

	/**
	 * @var DateTimeInterface|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="datetime", name="account_last_visit", nullable=true, options={"default": null})
	 */
	protected ?DateTimeInterface $lastVisit = null;

	/**
	 * @var Entities\Details\IDetails
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\OneToOne(targetEntity="FastyBird\AccountsModule\Entities\Details\Details", mappedBy="account", cascade={"persist", "remove"})
	 */
	protected Entities\Details\IDetails $details;

	/**
	 * @var Common\Collections\Collection<int, Entities\Identities\IIdentity>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\AccountsModule\Entities\Identities\Identity", mappedBy="account")
	 */
	protected Common\Collections\Collection $identities;

	/**
	 * @var Common\Collections\Collection<int, Entities\Emails\IEmail>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\AccountsModule\Entities\Emails\Email", mappedBy="account", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected Common\Collections\Collection $emails;

	/**
	 * @var Common\Collections\Collection<int, Entities\Roles\IRole>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToMany(targetEntity="FastyBird\AccountsModule\Entities\Roles\Role")
	 * @ORM\JoinTable(name="fb_accounts_roles",
	 *    joinColumns={
	 *       @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="cascade")
	 *    },
	 *    inverseJoinColumns={
	 *       @ORM\JoinColumn(name="role_id", referencedColumnName="role_id", onDelete="cascade")
	 *    }
	 * )
	 */
	protected Common\Collections\Collection $roles;

	/**
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->state = MetadataTypes\AccountStateType::get(MetadataTypes\AccountStateType::STATE_NOT_ACTIVATED);

		$this->emails = new Common\Collections\ArrayCollection();
		$this->identities = new Common\Collections\ArrayCollection();
		$this->roles = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isActivated(): bool
	{
		return $this->state->equalsValue(MetadataTypes\AccountStateType::STATE_ACTIVE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBlocked(): bool
	{
		return $this->state->equalsValue(MetadataTypes\AccountStateType::STATE_BLOCKED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDeleted(): bool
	{
		return $this->state->equalsValue(MetadataTypes\AccountStateType::STATE_DELETED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isNotActivated(): bool
	{
		return $this->state->equalsValue(MetadataTypes\AccountStateType::STATE_NOT_ACTIVATED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isApprovalRequired(): bool
	{
		return $this->state->equalsValue(MetadataTypes\AccountStateType::STATE_APPROVAL_WAITING);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestHash(): ?string
	{
		return $this->requestHash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRequestHash(string $requestHash): void
	{
		$this->requestHash = $requestHash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentities(): array
	{
		return $this->identities->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEmails(): array
	{
		return $this->emails->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setEmails(array $emails): void
	{
		$this->emails = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var Entities\Emails\IEmail $entity */
		foreach ($emails as $entity) {
			if (!$this->emails->contains($entity)) {
				// ...and assign them to collection
				$this->emails->add($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addEmail(Entities\Emails\IEmail $email): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->emails->contains($email)) {
			// ...and assign it to collection
			$this->emails->add($email);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeEmail(Entities\Emails\IEmail $email): void
	{
		// Check if collection contain removing entity...
		if ($this->emails->contains($email)) {
			// ...and remove it from collection
			$this->emails->removeElement($email);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRole(Entities\Roles\IRole $role): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->roles->contains($role)) {
			// ...and assign it to collection
			$this->roles->add($role);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeRole(Entities\Roles\IRole $role): void
	{
		// Check if collection contain removing entity...
		if ($this->roles->contains($role)) {
			// ...and remove it from collection
			$this->roles->removeElement($role);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasRole(string $role): bool
	{
		$role = $this->roles
			->filter(function (Entities\Roles\IRole $row) use ($role): bool {
				return $role === $row->getName();
			})
			->first();

		return $role !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->details->getLastName() . ' ' . $this->details->getFirstName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'          => $this->getPlainId(),
			'first_name'  => $this->getDetails()->getFirstName(),
			'last_name'   => $this->getDetails()->getLastName(),
			'middle_name' => $this->getDetails()->getMiddleName(),
			'email'       => $this->getEmail() !== null ? $this->getEmail()->getAddress() : null,
			'state'       => $this->getState()->getValue(),
			'registered'  => $this->getCreatedAt() !== null ? $this->getCreatedAt()->format(DATE_ATOM) : null,
			'last_visit'  => $this->getLastVisit() !== null ? $this->getLastVisit()->format(DATE_ATOM) : null,
			'roles'       => array_map(function (Entities\Roles\IRole $role): string {
				return $role->getName();
			}, $this->getRoles()),
			'language'    => $this->getLanguage(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDetails(): Entities\Details\IDetails
	{
		return $this->details;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEmail(?string $id = null): ?Entities\Emails\IEmail
	{
		$email = $this->emails
			->filter(function (Entities\Emails\IEmail $row) use ($id): bool {
				return $id !== null && $id !== '' ? $row->getId()
					->equals(Uuid\Uuid::fromString($id)) : $row->isDefault();
			})
			->first();

		return $email !== false ? $email : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getState(): MetadataTypes\AccountStateType
	{
		return $this->state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setState(MetadataTypes\AccountStateType $state): void
	{
		$this->state = $state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastVisit(): ?DateTimeInterface
	{
		return $this->lastVisit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setLastVisit(DateTimeInterface $lastVisit): void
	{
		$this->lastVisit = $lastVisit;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoles(): array
	{
		return $this->roles->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRoles(array $roles): void
	{
		$this->roles = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var Entities\Roles\IRole $entity */
		foreach ($roles as $entity) {
			if (!$this->roles->contains($entity)) {
				// ...and assign them to collection
				$this->roles->add($entity);
			}
		}

		/** @var Entities\Roles\IRole $entity */
		foreach ($this->roles as $entity) {
			if (!in_array($entity, $roles, true)) {
				// ...and remove it from collection
				$this->roles->removeElement($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * TODO: Should be refactored
	 */
	public function getLanguage(): string
	{
		return 'en';
	}

}
