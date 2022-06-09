<?php declare(strict_types = 1);

namespace Tests\Cases;

use Fig\Http\Message\RequestMethodInterface;
use IPub\SlimRouter;
use IPub\SlimRouter\Http as SlimRouterHttp;
use Nette\Utils;
use React\Http\Message\ServerRequest;
use Tester\Assert;
use Tests\Tools;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../DbTestCase.php';

/**
 * @testCase
 */
final class SessionV1ControllerTest extends DbTestCase
{

	/**
	 * @param string $url
	 * @param string|null $token
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Controllers/sessionRead.php
	 */
	public function testRead(string $url, ?string $token, int $statusCode, string $fixture): void
	{
		/** @var SlimRouter\Routing\IRouter $router */
		$router = $this->getContainer()
			->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_GET,
			$url,
			$headers
		);

		$response = $router->handle($request);

		$body = (string) $response->getBody();

		Tools\JsonAssert::assertFixtureMatch($fixture, $body);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(SlimRouterHttp\Response::class, $response);
	}

	/**
	 * @param string $url
	 * @param string|null $token
	 * @param string $body
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Controllers/sessionCreate.php
	 */
	public function testCreate(string $url, ?string $token, string $body, int $statusCode, string $fixture): void
	{
		/** @var SlimRouter\Routing\IRouter $router */
		$router = $this->getContainer()
			->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_POST,
			$url,
			$headers,
			$body
		);

		$response = $router->handle($request);

		$body = (string) $response->getBody();

		$actual = Utils\Json::decode($body, Utils\Json::FORCE_ARRAY);

		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			$body,
			function (string $expectation) use ($actual): string {
				if (isset($actual['data']['attributes'])) {
					$expectation = str_replace('__ACCESS_TOKEN__', $actual['data']['attributes']['token'], $expectation);
					$expectation = str_replace('__REFRESH_TOKEN__', $actual['data']['attributes']['refresh'], $expectation);
					$expectation = str_replace('__ENTITY_ID__', $actual['data']['id'], $expectation);
				}

				return $expectation;
			}
		);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(SlimRouterHttp\Response::class, $response);
	}

	/**
	 * @param string $url
	 * @param string|null $token
	 * @param string $body
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Controllers/sessionUpdate.php
	 */
	public function testUpdate(string $url, ?string $token, string $body, int $statusCode, string $fixture): void
	{
		/** @var SlimRouter\Routing\IRouter $router */
		$router = $this->getContainer()
			->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_PATCH,
			$url,
			$headers,
			$body
		);

		$response = $router->handle($request);

		$body = (string) $response->getBody();

		$actual = Utils\Json::decode($body, Utils\Json::FORCE_ARRAY);

		Tools\JsonAssert::assertFixtureMatch(
			$fixture,
			$body,
			function (string $expectation) use ($actual): string {
				if (isset($actual['data']['attributes'])) {
					$expectation = str_replace('__ACCESS_TOKEN__', $actual['data']['attributes']['token'], $expectation);
					$expectation = str_replace('__REFRESH_TOKEN__', $actual['data']['attributes']['refresh'], $expectation);
					$expectation = str_replace('__ENTITY_ID__', $actual['data']['id'], $expectation);
				}

				return $expectation;
			}
		);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(SlimRouterHttp\Response::class, $response);
	}

	/**
	 * @param string $url
	 * @param string|null $token
	 * @param int $statusCode
	 * @param string $fixture
	 *
	 * @dataProvider ./../../../fixtures/Controllers/sessionDelete.php
	 */
	public function testDelete(string $url, ?string $token, int $statusCode, string $fixture): void
	{
		/** @var SlimRouter\Routing\IRouter $router */
		$router = $this->getContainer()
			->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [];

		if ($token !== null) {
			$headers['authorization'] = $token;
		}

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_DELETE,
			$url,
			$headers
		);

		$response = $router->handle($request);

		$body = (string) $response->getBody();

		Tools\JsonAssert::assertFixtureMatch($fixture, $body);
		Assert::same($statusCode, $response->getStatusCode());
		Assert::type(SlimRouterHttp\Response::class, $response);
	}

}

$test_case = new SessionV1ControllerTest();
$test_case->run();
