<?php declare(strict_types = 1);

/**
 * EntitiesSubscriber.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Subscribers
 * @since          0.1.0
 *
 * @date           22.03.20
 */

namespace FastyBird\AccountsModule\Subscribers;

use Consistence;
use Doctrine\Common;
use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\AccountsModule;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\DateTimeFactory;
use FastyBird\ExchangePlugin\Publisher as ExchangePluginPublisher;
use FastyBird\ModulesMetadata;
use Nette;
use Nette\Utils;
use Ramsey\Uuid;
use ReflectionClass;
use ReflectionException;

/**
 * Doctrine entities events
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Subscribers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EntitiesSubscriber implements Common\EventSubscriber
{

	private const ACTION_CREATED = 'created';
	private const ACTION_UPDATED = 'updated';
	private const ACTION_DELETED = 'deleted';

	use Nette\SmartObject;

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	/** @var ExchangePluginPublisher\IPublisher */
	private ExchangePluginPublisher\IPublisher $publisher;

	/** @var ORM\EntityManagerInterface */
	private ORM\EntityManagerInterface $entityManager;

	public function __construct(
		DateTimeFactory\DateTimeFactory $dateTimeFactory,
		ExchangePluginPublisher\IPublisher $publisher,
		ORM\EntityManagerInterface $entityManager
	) {
		$this->dateTimeFactory = $dateTimeFactory;
		$this->publisher = $publisher;
		$this->entityManager = $entityManager;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getSubscribedEvents(): array
	{
		return [
			ORM\Events::onFlush,
			ORM\Events::postPersist,
			ORM\Events::postUpdate,
		];
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function postPersist(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Check for valid entity
		if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_CREATED);
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $action
	 *
	 * @return void
	 */
	private function processEntityAction(Entities\IEntity $entity, string $action): void
	{
		$publishRoutingKey = null;

		switch ($action) {
			case self::ACTION_CREATED:
				foreach (AccountsModule\Constants::MESSAGE_BUS_CREATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = ModulesMetadata\Types\RoutingKeyType::get($routingKey);
					}
				}

				break;

			case self::ACTION_UPDATED:
				foreach (AccountsModule\Constants::MESSAGE_BUS_UPDATED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = ModulesMetadata\Types\RoutingKeyType::get($routingKey);
					}
				}

				break;

			case self::ACTION_DELETED:
				foreach (AccountsModule\Constants::MESSAGE_BUS_DELETED_ENTITIES_ROUTING_KEYS_MAPPING as $class => $routingKey) {
					if ($this->validateEntity($entity, $class)) {
						$publishRoutingKey = ModulesMetadata\Types\RoutingKeyType::get($routingKey);
					}
				}

				break;
		}

		if ($publishRoutingKey !== null) {
			$this->publisher->publish(
				ModulesMetadata\Types\ModuleOriginType::get(ModulesMetadata\Types\ModuleOriginType::ORIGIN_MODULE_ACCOUNTS),
				$publishRoutingKey,
				Utils\ArrayHash::from($this->toArray($entity))
			);
		}
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $class
	 *
	 * @return bool
	 */
	private function validateEntity(Entities\IEntity $entity, string $class): bool
	{
		$result = false;

		if (get_class($entity) === $class) {
			$result = true;
		}

		if (is_subclass_of($entity, $class)) {
			$result = true;
		}

		return $result;
	}

	/**
	 * @param Entities\IEntity $entity
	 *
	 * @return mixed[]
	 */
	private function toArray(Entities\IEntity $entity): array
	{
		if (method_exists($entity, 'toArray')) {
			return $entity->toArray();
		}

		$metadata = $this->entityManager->getClassMetadata(get_class($entity));

		$fields = [];

		foreach ($metadata->fieldMappings as $field) {
			if (isset($field['fieldName'])) {
				$fields[] = $field['fieldName'];
			}
		}

		try {
			$rc = new ReflectionClass(get_class($entity));

			foreach ($rc->getProperties() as $property) {
				$fields[] = $property->getName();
			}
		} catch (ReflectionException $ex) {
			// Nothing to do, reflection could not be loaded
		}

		$fields = array_unique($fields);

		$values = [];

		foreach ($fields as $field) {
			try {
				$value = $this->getPropertyValue($entity, $field);

				if ($value instanceof Consistence\Enum\Enum) {
					$value = $value->getValue();
				} elseif ($value instanceof Uuid\UuidInterface) {
					$value = $value->toString();
				}

				if (is_object($value)) {
					continue;
				}

				$key = preg_replace('/(?<!^)[A-Z]/', '_$0', $field);

				if ($key !== null) {
					$values[strtolower($key)] = $value;
				}
			} catch (Exceptions\PropertyNotExistsException $ex) {
				// No need to do anything
			}
		}

		return $values;
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param string $property
	 *
	 * @return mixed
	 *
	 * @throws Exceptions\PropertyNotExistsException
	 */
	private function getPropertyValue(Entities\IEntity $entity, string $property)
	{
		$ucFirst = ucfirst($property);

		$methods = [
			'get' . $ucFirst,
			'is' . $ucFirst,
			'has' . $ucFirst,
		];

		foreach ($methods as $method) {
			$callable = [$entity, $method];

			if (is_callable($callable)) {
				return call_user_func($callable);
			}
		}

		if (!property_exists($entity, $property)) {
			throw new Exceptions\PropertyNotExistsException(sprintf('Property "%s" does not exists on entity', $property));
		}

		return $entity->{$property};
	}

	/**
	 * @param ORM\Event\LifecycleEventArgs $eventArgs
	 *
	 * @return void
	 */
	public function postUpdate(ORM\Event\LifecycleEventArgs $eventArgs): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getObject();

		// Get changes => should be already computed here (is a listener)
		$changeset = $uow->getEntityChangeSet($entity);

		// If we have no changes left => don't create revision log
		if (count($changeset) === 0) {
			return;
		}

		// Check for valid entity
		if (
			!$entity instanceof Entities\IEntity
			|| !$this->validateNamespace($entity)
			|| $uow->isScheduledForDelete($entity)
		) {
			return;
		}

		$this->processEntityAction($entity, self::ACTION_UPDATED);
	}

	/**
	 * @return void
	 */
	public function onFlush(): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		$processedEntities = [];

		$processEntities = [];

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			// Check for valid entity
			if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			// Doctrine is fine deleting elements multiple times. We are not.
			$hash = $this->getHash($entity, $uow->getEntityIdentifier($entity));

			if (in_array($hash, $processedEntities, true)) {
				continue;
			}

			$processedEntities[] = $hash;
			$processEntities[] = $entity;
		}

		foreach ($processEntities as $entity) {
			// Check for valid entity
			if (!$entity instanceof Entities\IEntity || !$this->validateNamespace($entity)) {
				continue;
			}

			$this->processEntityAction($entity, self::ACTION_DELETED);
		}
	}

	/**
	 * @param Entities\IEntity $entity
	 * @param mixed[] $identifier
	 *
	 * @return string
	 */
	private function getHash(Entities\IEntity $entity, array $identifier): string
	{
		return implode(
			' ',
			array_merge(
				[$this->getRealClass(get_class($entity))],
				$identifier
			)
		);
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function getRealClass(string $class): string
	{
		$pos = strrpos($class, '\\' . Persistence\Proxy::MARKER . '\\');

		if ($pos === false) {
			return $class;
		}

		return substr($class, $pos + Persistence\Proxy::MARKER_LENGTH + 2);
	}

	/**
	 * @param object $entity
	 *
	 * @return bool
	 */
	private function validateNamespace(object $entity): bool
	{
		try {
			$rc = new ReflectionClass($entity);

		} catch (ReflectionException $ex) {
			return false;
		}

		return str_starts_with($rc->getNamespaceName(), 'FastyBird\AccountsModule');
	}

}
