<?php declare(strict_types = 1);

/**
 * UrlFormatMiddleware.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:AccountsModule!
 * @subpackage     Middleware
 * @since          0.1.0
 *
 * @date           21.06.20
 */

namespace FastyBird\AccountsModule\Middleware;

use Contributte\Translation;
use FastyBird\AccountsModule\Security;
use FastyBird\JsonApi\Exceptions as JsonApiExceptions;
use Fig\Http\Message\StatusCodeInterface;
use IPub\SlimRouter\Http;
use Nette\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Access token check middleware
 *
 * @package        FastyBird:AccountsModule!
 * @subpackage     Middleware
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class UrlFormatMiddleware implements MiddlewareInterface
{

	/** @var Security\User */
	private Security\User $user;

	/** @var Translation\Translator */
	private Translation\Translator $translator;

	public function __construct(
		Security\User $user,
		Translation\Translator $translator
	) {
		$this->user = $user;

		$this->translator = $translator;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 *
	 * @throws JsonApiExceptions\IJsonApiException
	 * @throws Translation\Exceptions\InvalidArgument
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);

		if (
			$this->user->isLoggedIn()
			&& (
				Utils\Strings::startsWith($request->getUri()
					->getPath(), '/v1/session')
				|| Utils\Strings::startsWith($request->getUri()
					->getPath(), '/v1/me')
			)
		) {
			if ($this->user->getAccount() === null) {
				throw new JsonApiExceptions\JsonApiErrorException(
					StatusCodeInterface::STATUS_BAD_REQUEST,
					$this->translator->translate('//accounts-module.base.messages.failed.heading'),
					$this->translator->translate('//accounts-module.base.messages.failed.message')
				);
			}

			$body = $response->getBody();
			$body->rewind();

			$content = $body->getContents();
			$content = str_replace('\/v1\/emails', '\/v1\/me\/emails', $content);
			$content = str_replace('\/v1\/identities', '\/v1\/me\/identities', $content);
			$content = str_replace('\/v1\/accounts\/' . $this->user->getAccount()->getPlainId(), '\/v1\/me', $content);

			$response = $response->withBody(Http\Stream::fromBodyString($content));
		}

		return $response;
	}

}
