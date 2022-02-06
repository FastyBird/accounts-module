<?php declare(strict_types = 1);

/**
 * Identity.php
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

namespace FastyBird\AccountsModule\Entities\Identities;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Helpers;
use FastyBird\Metadata\Types as MetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts_module__identities",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Accounts identities"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="identity_uid_unique", columns={"identity_uid"})
 *     },
 *     indexes={
 *       @ORM\Index(name="identity_uid_idx", columns={"identity_uid"}),
 *       @ORM\Index(name="identity_state_idx", columns={"identity_state"})
 *     }
 * )
 */
class Identity implements IIdentity
{

	use Entities\TEntity;
	use Entities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="identity_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var Entities\Accounts\IAccount
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\AccountsModule\Entities\Accounts\Account", inversedBy="identities", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="cascade", nullable=false)
	 */
	protected Entities\Accounts\IAccount $account;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="identity_uid", length=50, nullable=false)
	 */
	protected string $uid;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="text", name="identity_token", nullable=false)
	 */
	protected string $password;

	/** @var string|null */
	protected ?string $plainPassword = null;

	/**
	 * @var MetadataTypes\IdentityStateType
	 *
	 * @Enum(class=MetadataTypes\IdentityStateType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="identity_state", nullable=false, options={"default": "active"})
	 */
	protected $state;

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param string $uid
	 * @param string $password
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Accounts\IAccount $account,
		string $uid,
		string $password,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->account = $account;
		$this->uid = $uid;

		$this->state = MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_ACTIVE);

		$this->setPassword($password);
	}

	/**
	 * {@inheritDoc}
	 */
	public function verifyPassword(string $rawPassword): bool
	{
		return $this->getPassword()
			->isEqual($rawPassword, $this->getSalt());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPassword(): Helpers\Password
	{
		return $this->plainPassword !== null ?
			new Helpers\Password(null, $this->plainPassword, $this->getSalt()) :
			new Helpers\Password($this->password, null, $this->getSalt());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setPassword($password): void
	{
		if ($password instanceof Helpers\Password) {
			$this->password = $password->getHash();

		} else {
			$password = Helpers\Password::createFromString($password);

			$this->password = $password->getHash();
			$this->plainPassword = $password->getPassword();
		}

		$this->setSalt($password->getSalt());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSalt(): ?string
	{
		return $this->getParam('salt');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setSalt(string $salt): void
	{
		$this->setParam('salt', $salt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isActive(): bool
	{
		return $this->state === MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_ACTIVE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBlocked(): bool
	{
		return $this->state === MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_BLOCKED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDeleted(): bool
	{
		return $this->state === MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_DELETED);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isInvalid(): bool
	{
		return $this->state === MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_INVALID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRoles(): array
	{
		return array_map(function (Entities\Roles\IRole $role): string {
			return $role->getName();
		}, $this->account->getRoles());
	}

	/**
	 * {@inheritDoc}
	 */
	public function invalidate(): void
	{
		$this->state = MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_INVALID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'      => $this->getPlainId(),
			'account' => $this->getAccount()->getPlainId(),
			'uid'     => $this->getUid(),
			'state'   => $this->getState()->getValue(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAccount(): Entities\Accounts\IAccount
	{
		return $this->account;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUid(): string
	{
		return $this->uid;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getState(): MetadataTypes\IdentityStateType
	{
		return $this->state;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setState(MetadataTypes\IdentityStateType $state): void
	{
		$this->state = $state;
	}

	/**
	 * @return void
	 *
	 * @throws Throwable
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
	 */
	public function __clone()
	{
		$this->id = Uuid\Uuid::uuid4();
		$this->createdAt = new Utils\DateTime();
		$this->state = MetadataTypes\IdentityStateType::get(MetadataTypes\IdentityStateType::STATE_ACTIVE);
	}

}
