<?php declare(strict_types = 1);

/**
 * AuthenticationFailedException.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Exceptions
 * @since          0.1.0
 *
 * @date           30.03.20
 */

namespace FastyBird\AccountsModule\Exceptions;

use FastyBird\SimpleAuth\Exceptions as SimpleAuthExceptions;

class AuthenticationFailedException extends SimpleAuthExceptions\AuthenticationException implements IException
{

}
