<?php declare(strict_types = 1);

/**
 * RefreshToken.php
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
use FastyBird\AuthModule\Exceptions;
use FastyBird\Database\Entities as DatabaseEntities;
use FastyBird\SimpleAuth\Entities as SimpleAuthEntities;
use IPub\DoctrineTimestampable;
use Ramsey\Uuid;
use Throwable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="fb_security_tokens_refresh",
 *     options={
 *       "collate"="utf8mb4_general_ci",
 *       "charset"="utf8mb4",
 *       "comment"="Account refresh tokens"
 *     }
 * )
 */
class RefreshToken extends SimpleAuthEntities\Tokens\Token implements IRefreshToken
{

	use DatabaseEntities\TEntity;
	use DatabaseEntities\TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var DateTimeInterface|null
	 *
	 * @IPubDoctrine\Crud(is={"writable"})
	 * @ORM\Column(name="token_valid_till", type="datetime", nullable=true)
	 */
	private $validTill;

	/**
	 * @param IAccessToken $accessToken
	 * @param string $token
	 * @param DateTimeInterface|null $validTill
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		IAccessToken $accessToken,
		string $token,
		?DateTimeInterface $validTill,
		?Uuid\UuidInterface $id = null
	) {
		parent::__construct($token, $id);

		$this->validTill = $validTill;

		$this->setParent($accessToken);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAccessToken(): IAccessToken
	{
		$token = parent::getParent();

		if (!$token instanceof IAccessToken) {
			throw new Exceptions\InvalidStateException(
				sprintf(
					'Access token for refresh token is not valid type. Instance of %s expected, %s provided',
					IAccessToken::class,
					$token !== null ? get_class($token) : 'null'
				)
			);
		}

		return $token;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValidTill(): ?DateTimeInterface
	{
		return $this->validTill;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isValid(DateTimeInterface $dateTime): bool
	{
		if ($this->validTill === null) {
			return true;
		}

		return $this->validTill >= $dateTime;
	}

}
