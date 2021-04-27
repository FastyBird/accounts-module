<?php declare(strict_types = 1);

/**
 * SecurityHash.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Helpers;

use DateTimeImmutable;
use Exception;
use FastyBird\DateTimeFactory;
use Nette;
use Nette\Utils;

/**
 * Verification hash helper
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class SecurityHash
{

	use Nette\SmartObject;

	private const SEPARATOR = '##';

	/** @var DateTimeFactory\DateTimeFactory */
	private DateTimeFactory\DateTimeFactory $dateTimeFactory;

	public function __construct(
		DateTimeFactory\DateTimeFactory $dateTimeFactory
	) {
		$this->dateTimeFactory = $dateTimeFactory;
	}

	/**
	 * @param string $interval
	 *
	 * @return string
	 */
	public function createKey(string $interval = '+ 1 hour'): string
	{
		/** @var DateTimeImmutable $now */
		$now = $this->dateTimeFactory->getNow();

		$datetime = $now->modify($interval);

		return base64_encode(Utils\Random::generate(12) . self::SEPARATOR . $datetime->getTimestamp());
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function isValid(string $key): bool
	{
		$encoded = base64_decode($key, true);

		if ($encoded === false) {
			return false;
		}

		$pieces = explode(self::SEPARATOR, $encoded);

		if (count($pieces) === 2) {
			[, $timestamp] = $pieces;

			$datetime = Utils\DateTime::from($timestamp);

			if ($datetime >= $this->dateTimeFactory->getNow()) {
				return true;
			}
		}

		return false;
	}

}
