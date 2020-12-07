<?php declare(strict_types = 1);

/**
 * Role.php
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

namespace FastyBird\AuthModule\Entities\Roles;

use Doctrine\Common;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\SimpleAuth\Constants as SimpleAuthConstants;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_acl_roles",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="ACL roles"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="role_name_unique", columns={"parent_id", "role_name"})
 *     }
 * )
 */
class Role implements IRole
{

	use DatabaseEntities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="role_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", name="role_name", length=100, nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="text", name="role_description", nullable=false)
	 */
	private $description;

	/**
	 * @var IRole|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\ManyToOne(targetEntity="FastyBird\AuthModule\Entities\Roles\Role", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="role_id", nullable=true, onDelete="set null")
	 */
	private $parent;

	/**
	 * @var Common\Collections\Collection<int, IRole>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\AuthModule\Entities\Roles\Role", mappedBy="parent")
	 */
	private $children;

	/**
	 * @param string $name
	 * @param string $description
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $name,
		string $description,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->name = $name;
		$this->description = $description;

		$this->children = new Common\Collections\ArrayCollection();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): ?IRole
	{
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParent(?IRole $parent = null): void
	{
		if ($parent !== null) {
			$parent->addChild($this);
		}

		$this->parent = $parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addChild(IRole $child): void
	{
		// Check if collection does not contain inserting entity
		if (!$this->children->contains($child)) {
			// ...and assign it to collection
			$this->children->add($child);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getChildren(): array
	{
		return $this->children->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setChildren(array $children): void
	{
		$this->children = new Common\Collections\ArrayCollection();

		// Process all passed entities...
		/** @var IRole $entity */
		foreach ($children as $entity) {
			if (!$this->children->contains($entity)) {
				// ...and assign them to collection
				$this->children->add($entity);
			}
		}

		/** @var IRole $entity */
		foreach ($this->children as $entity) {
			if (!in_array($entity, $children, true)) {
				// ...and remove it from collection
				$this->children->removeElement($entity);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnonymous(): bool
	{
		return $this->name === SimpleAuthConstants::ROLE_ANONYMOUS;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAuthenticated(): bool
	{
		return in_array($this->name, [
			SimpleAuthConstants::ROLE_MANAGER,
			SimpleAuthConstants::ROLE_USER,
			SimpleAuthConstants::ROLE_VISITOR,
		], true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAdministrator(): bool
	{
		return $this->name === SimpleAuthConstants::ROLE_ADMINISTRATOR;
	}

	/**
	 * Convert role object to string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

}
