<?php declare(strict_types = 1);

/**
 * EmailEntitySubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Subscribers;

use Doctrine\Common;
use Doctrine\ORM;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\AccountsModule\Models;
use Nette;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EmailEntitySubscriber implements Common\EventSubscriber
{

	use Nette\SmartObject;

	/** @var Models\Emails\IEmailRepository */
	private Models\Emails\IEmailRepository $emailRepository;

	public function __construct(
		Models\Emails\IEmailRepository $emailRepository
	) {
		$this->emailRepository = $emailRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::prePersist,
			ORM\Events::onFlush,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function prePersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach ($uow->getScheduledEntityInsertions() as $object) {
			if (!$object instanceof Entities\Emails\IEmail) {
				continue;
			}

			$foundEmail = $this->emailRepository->findOneByAddress($object->getAddress());

			if ($foundEmail !== null && !$foundEmail->getId()->equals($object->getId())) {
				throw new Exceptions\EmailAlreadyTakenException('Given email is already taken');
			}
		}
	}

	/**
	 * @param ORM\Event\OnFlushEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function onFlush(ORM\Event\OnFlushEventArgs $eventArgs): void
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		// Check all scheduled updates
		foreach (array_merge($uow->getScheduledEntityInsertions(), $uow->getScheduledEntityUpdates()) as $object) {
			$changeSet = $uow->getEntityChangeSet($object);

			if (
				array_key_exists('default', $changeSet)
				&& count($changeSet['default']) === 2
				&& $changeSet['default'][0] === true
				&& $changeSet['default'][1] === false
			) {
				throw new Exceptions\EmailHaveToBeDefaultException('Default email address can not be made not default');
			}

			if ($object instanceof Entities\Emails\IEmail && $object->isDefault()) {
				/** @phpstan-var ORM\Mapping\ClassMetadata<Entities\Emails\IEmail> $classMetadata */
				$classMetadata = $em->getClassMetadata(get_class($object));

				// Check if entity was set as default
				if (array_key_exists('default', $changeSet)) {
					$this->setAsDefault($uow, $classMetadata, $object);
				}
			}
		}
	}

	/**
	 * @param ORM\UnitOfWork $uow
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 * @param Entities\Emails\IEmail $email
	 *
	 * @return void
	 *
	 * @phpstan-param ORM\Mapping\ClassMetadata<Entities\Emails\IEmail> $classMetadata
	 */
	private function setAsDefault(
		ORM\UnitOfWork $uow,
		ORM\Mapping\ClassMetadata $classMetadata,
		Entities\Emails\IEmail $email
	): void {
		$property = $classMetadata->getReflectionProperty('default');

		foreach ($email->getAccount()->getEmails() as $accountEmail) {
			// Deactivate all other user emails
			if (
				!$accountEmail->getId()
					->equals($email->getId())
				&& $accountEmail->isDefault()
			) {
				$accountEmail->setDefault(false);

				$oldValue = $property->getValue($email);

				$uow->propertyChanged($accountEmail, 'default', $oldValue, true);
				$uow->scheduleExtraUpdate($accountEmail, [
					'default' => [$oldValue, false],
				]);
			}
		}
	}

}
