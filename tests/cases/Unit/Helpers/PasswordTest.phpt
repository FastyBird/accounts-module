<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\AccountsModule\Helpers;
use Ninjify\Nunjuck\TestCase\BaseTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PasswordTest extends BaseTestCase
{

	public function testPassword(): void
	{
		$password = new Helpers\Password(null, 'somePassword');
		$hashedPassword = new Helpers\Password($password->getHash(), null, $password->getSalt());

		Assert::true($hashedPassword->isEqual('somePassword'));
	}

}

$test_case = new PasswordTest();
$test_case->run();
