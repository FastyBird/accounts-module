<?php declare(strict_types = 1);

/**
 * Email.php
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

namespace FastyBird\AccountsModule\Entities\Emails;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\AccountsModule\Types;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts_module_emails",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Emails addresses"
 *     },
 *     uniqueConstraints={
 *       @ORM\UniqueConstraint(name="email_address_unique", columns={"email_address"})
 *     },
 *     indexes={
 *       @ORM\Index(name="email_address_idx", columns={"email_address"})
 *     }
 * )
 */
class Email implements IEmail
{

	use Entities\TEntity;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="email_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var Entities\Accounts\IAccount
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\ManyToOne(targetEntity="FastyBird\AccountsModule\Entities\Accounts\Account", inversedBy="emails")
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="cascade", nullable=false)
	 */
	private Entities\Accounts\IAccount $account;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="email_address", unique=true, length=150, nullable=false)
	 */
	private string $address;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="email_default", length=1, nullable=false, options={"default": false})
	 */
	private bool $default = false;

	/**
	 * @var bool
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="boolean", name="email_verified", length=1, nullable=false, options={"default": false})
	 */
	private bool $verified = false;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="email_verification_hash", length=150, nullable=true, options={"default": null})
	 */
	private ?string $verificationHash = null;

	/**
	 * @var DateTimeInterface|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="datetime", name="email_verification_created", nullable=true, options={"default": null})
	 */
	private ?DateTimeInterface $verificationCreated = null;

	/**
	 * @var DateTimeInterface|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="datetime", name="email_verification_completed", nullable=true, options={"default": null})
	 */
	private ?DateTimeInterface $verificationCompleted = null;

	/**
	 * @var Types\EmailVisibilityType
	 *
	 * @Enum(class=Types\EmailVisibilityType::class)
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string_enum", name="email_visibility", nullable=false, options={"default": "public"})
	 */
	private $visibility;

	/**
	 * @param Entities\Accounts\IAccount $account
	 * @param string $address
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		Entities\Accounts\IAccount $account,
		string $address,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->account = $account;
		$this->visibility = Types\EmailVisibilityType::get(Types\EmailVisibilityType::VISIBILITY_PUBLIC);

		$this->setAddress($address);

		$account->addEmail($this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVerificationHash(): ?string
	{
		return $this->verificationHash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVerificationHash(string $verificationHash): void
	{
		$this->verificationHash = $verificationHash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVerificationCreated(): ?DateTimeInterface
	{
		return $this->verificationCreated;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVerificationCreated(DateTimeInterface $verificationCreated): void
	{
		$this->verificationCreated = $verificationCreated;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVerificationCompleted(): ?DateTimeInterface
	{
		return $this->verificationCompleted;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVerificationCompleted(?DateTimeInterface $verificationCompleted = null): void
	{
		$this->verificationCompleted = $verificationCompleted;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'id'       => $this->getPlainId(),
			'account'  => $this->getAccount()->getPlainId(),
			'address'  => $this->getAddress(),
			'default'  => $this->isDefault(),
			'verified' => $this->isVerified(),
			'private'  => $this->isPrivate(),
			'public'   => $this->isPublic(),
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
	public function getAddress(): string
	{
		return $this->address;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAddress(string $address): void
	{
		if (!Utils\Validators::isEmail($address)) {
			throw new Exceptions\EmailIsNotValidException('Invalid email address given');
		}

		$this->address = Utils\Strings::lower($address);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDefault(): bool
	{
		return $this->default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(bool $default): void
	{
		$this->default = $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isVerified(): bool
	{
		return $this->verified;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVerified(bool $verified): void
	{
		$this->verified = $verified;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isPrivate(): bool
	{
		return $this->getVisibility()
			->equalsValue(Types\EmailVisibilityType::VISIBILITY_PRIVATE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVisibility(): Types\EmailVisibilityType
	{
		return $this->visibility;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setVisibility(Types\EmailVisibilityType $visibility): void
	{
		$this->visibility = $visibility;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isPublic(): bool
	{
		return $this->getVisibility()
			->equalsValue(Types\EmailVisibilityType::VISIBILITY_PUBLIC);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->address;
	}

}
