<?php declare(strict_types = 1);

/**
 * UserAccount.php
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

namespace FastyBird\AuthModule\Entities\Accounts;

use Doctrine\Common;
use FastyBird\AuthModule\Entities;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_accounts_users",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="User accounts"
 *     },
 *     indexes={
 *       @ORM\Index(name="account_request_hash_idx", columns={"account_request_hash"})
 *     }
 * )
 */
class UserAccount extends Account implements IUserAccount
{

	/**
	 * @var Entities\Details\IDetails
	 *
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\OneToOne(targetEntity="FastyBird\AuthModule\Entities\Details\Details", mappedBy="account", cascade={"persist", "remove"})
	 */
	private $details;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="account_request_hash", nullable=true, options={"default": null})
	 */
	private $requestHash = null;

	/**
	 * @var Common\Collections\Collection<int, Entities\Emails\IEmail>
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\OneToMany(targetEntity="FastyBird\AuthModule\Entities\Emails\Email", mappedBy="account", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $emails;

	/**
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($id);

		$this->emails = new Common\Collections\ArrayCollection();
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
	public function getName(): string
	{
		return $this->details->getLastName() . ' ' . $this->details->getFirstName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type'        => 'user',
			'first_name'  => $this->getDetails()
				->getFirstName(),
			'last_name'   => $this->getDetails()
				->getLastName(),
			'middle_name' => $this->getDetails()
				->getMiddleName(),
			'email'       => $this->getEmail() !== null ? $this->getEmail()
				->getAddress() : null,
			'language'    => $this->getLanguage(),
		]);
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
		if ($this->emails !== null) {
			$email = $this->emails
				->filter(function (Entities\Emails\IEmail $row) use ($id): bool {
					return $id !== null ? $row->getId()
						->equals(Uuid\Uuid::fromString($id)) : $row->isDefault();
				})
				->first();

			return $email !== false ? $email : null;
		}

		return null;
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
