<?php declare(strict_types = 1);

/**
 * IRefreshToken.php
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

namespace FastyBird\AuthModule\Entities\Tokens;

use DateTimeInterface;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineTimestampable;

/**
 * Security refresh token entity interface
 *
 * @package        FastyBird:AuthModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IRefreshToken extends SimpleAuthEntities\Tokens\IToken,
	DatabaseEntities\IEntity,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	public const TOKEN_EXPIRATION = '+3 days';

	/**
	 * @return IAccessToken
	 */
	public function getAccessToken(): IAccessToken;

	/**
	 * @return DateTimeInterface
	 */
	public function getValidTill(): ?DateTimeInterface;

	/**
	 * @param DateTimeInterface $dateTime
	 *
	 * @return bool
	 */
	public function isValid(DateTimeInterface $dateTime): bool;

}
