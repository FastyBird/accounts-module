<?php declare(strict_types = 1);

namespace Tests\Cases;

use FastyBird\AuthModule\Router;
use FastyBird\WebServer\Http;
use React\Http\Message\ServerRequest;
use Tester\Assert;
use Tests\Tools;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class AccessMiddlewareTest extends DbTestCase
{

	/**
	 * @param string $url
	 * @param string $method
	 * @param string $body
	 * @param string $token
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Middleware/permissionAnnotation.php
	 */
	public function testPermissionAnnotation(
		string $url,
		string $method,
		string $body,
		string $token,
		int $statusCode,
		string $fixture
	): void {
		/** @var Router\Router $router */
		$router = $this->getContainer()
			->getByType(Router\Router::class);

		$request = new ServerRequest(
			$method,
			$url,
			[
				'authorization' => $token,
			],
			$body
		);

		$response = $router->handle($request);

		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			(string) $response->getBody()
		);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(Http\Response::class, $response);
	}

}

$test_case = new AccessMiddlewareTest();
$test_case->run();
