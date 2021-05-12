<?php declare(strict_types = 1);

/**
 * IAccessToken.php
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

namespace FastyBird\AccountsModule\Entities\Tokens;

use DateTimeInterface;
use FastyBird\AccountsModule\Entities;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineTimestampable;

/**
 * Account access token entity interface
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
interface IAccessToken extends SimpleAuthEntities\Tokens\IToken,
	DatabaseEntities\IEntity,
	DatabaseEntities\IEntityParams,
	DoctrineTimestampable\Entities\IEntityCreated,
	DoctrineTimestampable\Entities\IEntityUpdated
{

	public const TOKEN_EXPIRATION = '+6 hours';

	/**
	 * @param IRefreshToken $refreshToken
	 *
	 * @return void
	 */
	public function setRefreshToken(IRefreshToken $refreshToken): void;

	/**
	 * @return IRefreshToken|null
	 */
	public function getRefreshToken(): ?IRefreshToken;

	/**
	 * @return Entities\Identities\IIdentity
	 */
	public function getIdentity(): Entities\Identities\IIdentity;

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
