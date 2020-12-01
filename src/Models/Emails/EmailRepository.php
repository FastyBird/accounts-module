<?php declare(strict_types = 1);

/**
 * EmailRepository.php
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

namespace FastyBird\AuthModule\Models\Emails;

use Doctrine\Common;
use Doctrine\Persistence;
use FastyBird\AuthModule\Entities;
use FastyBird\AuthModule\Exceptions;
use FastyBird\AuthModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Account email address repository
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EmailRepository implements IEmailRepository
{

	use Nette\SmartObject;

	/** @var Common\Persistence\ManagerRegistry */
	private $managerRegistry;

	/** @var Persistence\ObjectRepository<Entities\Emails\Email>|null */
	private $repository;

	public function __construct(
		Common\Persistence\ManagerRegistry $managerRegistry
	) {
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneByAddress(string $address): ?Entities\Emails\IEmail
	{
		$findQuery = new Queries\FindEmailsQuery();
		$findQuery->byAddress($address);

		return $this->findOneBy($findQuery);
	}

	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(Queries\FindEmailsQuery $queryObject): ?Entities\Emails\IEmail
	{
		/** @var Entities\Emails\IEmail|null $email */
		$email = $queryObject->fetchOne($this->getRepository());

		return $email;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Throwable
	 */
	public function getResultSet(
		Queries\FindEmailsQuery $queryObject
	): DoctrineOrmQuery\ResultSet {
		$result = $queryObject->fetch($this->getRepository());

		if (!$result instanceof DoctrineOrmQuery\ResultSet) {
			throw new Exceptions\InvalidStateException('Result set for given query could not be loaded.');
		}

		return $result;
	}

	/**
	 * @return Persistence\ObjectRepository<Entities\Emails\Email>
	 */
	private function getRepository(): Persistence\ObjectRepository
	{
		if ($this->repository === null) {
			$this->repository = $this->managerRegistry->getRepository(Entities\Emails\Email::class);
		}

		return $this->repository;
	}

}
