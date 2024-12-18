<?php declare(strict_types = 1);

namespace FastyBird\Module\Accounts\Tests\Cases\Unit\Router;

use Error;
use FastyBird\Core\Application\Exceptions as ApplicationExceptions;
use FastyBird\Library\Metadata;
use FastyBird\Module\Accounts\Exceptions;
use FastyBird\Module\Accounts\Tests;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use IPub\SlimRouter;
use Nette;
use React\Http\Message\ServerRequest;
use RuntimeException;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class RouterTest extends Tests\Cases\Unit\DbTestCase
{

	public function setUp(): void
	{
		$this->registerNeonConfigurationFile(__DIR__ . '/prefixedRoutes.neon');

		parent::setUp();
	}

	/**
	 * @throws ApplicationExceptions\InvalidArgument
	 * @throws Exceptions\InvalidArgument
	 * @throws InvalidArgumentException
	 * @throws Nette\DI\MissingServiceException
	 * @throws RuntimeException
	 * @throws Error
	 *
	 * @dataProvider prefixedRoutes
	 */
	public function testPrefixedRoutes(string $url, string $token, int $statusCode): void
	{
		$router = $this->getContainer()->getByType(SlimRouter\Routing\IRouter::class);

		$headers = [
			'authorization' => $token,
		];

		$request = new ServerRequest(
			RequestMethodInterface::METHOD_GET,
			$url,
			$headers,
		);

		$response = $router->handle($request);

		self::assertSame($statusCode, $response->getStatusCode());
	}

	/**
	 * @return array<string, array<int|string>>
	 */
	public static function prefixedRoutes(): array
	{
		return [
			'readAllValid' => [
				'/api/v1/me',
				'Bearer ' . self::ADMINISTRATOR_TOKEN,
				StatusCodeInterface::STATUS_OK,
			],
			'readAllInvalid' => [
				'/api/' . Metadata\Constants::MODULE_ACCOUNTS_PREFIX . '/v1/me',
				'Bearer ' . self::ADMINISTRATOR_TOKEN,
				StatusCodeInterface::STATUS_NOT_FOUND,
			],
		];
	}

}
