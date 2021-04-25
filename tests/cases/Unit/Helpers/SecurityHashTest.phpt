<?php declare(strict_types = 1);

namespace Tests\Cases;

use DateTimeImmutable;
use FastyBird\AccountsModule\Helpers;
use FastyBird\DateTimeFactory;
use Mockery;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SecurityHashTest extends BaseMockeryTestCase
{

	public function testPassword(): void
	{
		$dateFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);
		$dateFactory
			->shouldReceive('getNow')
			->withNoArgs()
			->andReturn(new DateTimeImmutable('2020-04-01T12:00:00+00:00'));

		$hashHelper = new Helpers\SecurityHash($dateFactory);

		$hash = $hashHelper->createKey();

		Assert::true(is_string($hash));

		Assert::true($hashHelper->isValid($hash));

		$dateFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);
		$dateFactory
			->shouldReceive('getNow')
			->withNoArgs()
			->andReturn(new DateTimeImmutable('2021-04-01T12:00:00+00:00'));

		$hashHelper = new Helpers\SecurityHash($dateFactory);

		Assert::false($hashHelper->isValid($hash));

		$dateFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);
		$dateFactory
			->shouldReceive('getNow')
			->withNoArgs()
			->andReturn(new DateTimeImmutable('2020-04-01T12:59:00+00:00'));

		$hashHelper = new Helpers\SecurityHash($dateFactory);

		Assert::true($hashHelper->isValid($hash));

		$dateFactory = Mockery::mock(DateTimeFactory\DateTimeFactory::class);
		$dateFactory
			->shouldReceive('getNow')
			->withNoArgs()
			->andReturn(new DateTimeImmutable('2020-04-01T13:01:00+00:00'));

		$hashHelper = new Helpers\SecurityHash($dateFactory);

		Assert::false($hashHelper->isValid($hash));
	}

}

$test_case = new SecurityHashTest();
$test_case->run();
