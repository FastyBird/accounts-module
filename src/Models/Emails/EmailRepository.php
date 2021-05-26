<?php declare(strict_types = 1);

/**
 * EmailRepository.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Models\Emails;

use Doctrine\ORM;
use Doctrine\Persistence;
use FastyBird\AccountsModule\Entities;
use FastyBird\AccountsModule\Exceptions;
use FastyBird\AccountsModule\Queries;
use IPub\DoctrineOrmQuery;
use Nette;
use Throwable;

/**
 * Account email address repository
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Models
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class EmailRepository implements IEmailRepository
{

	use Nette\SmartObject;

	/** @var Persistence\ManagerRegistry */
	private Persistence\ManagerRegistry $managerRegistry;

	/** @var ORM\EntityRepository<Entities\Emails\IEmail>|null */
	private ?ORM\EntityRepository $repository = null;

	public function __construct(
		Persistence\ManagerRegistry $managerRegistry
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
	 * @return ORM\EntityRepository
	 *
	 * @phpstan-return ORM\EntityRepository<Entities\Emails\IEmail>
	 */
	private function getRepository(): ORM\EntityRepository
	{
		if ($this->repository === null) {
			$repository = $this->managerRegistry->getRepository(Entities\Emails\Email::class);

			if (!$repository instanceof ORM\EntityRepository) {
				throw new Exceptions\InvalidStateException('Entity repository could not be loaded');
			}

			$this->repository = $repository;
		}

		return $this->repository;
	}

}
