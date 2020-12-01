<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

return [
	// Valid responses
	//////////////////
	'request'         => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.json'),
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/public/account.identities.passwordRequest.json',
	],

	// Invalid responses
	////////////////////
	'missingRequired' => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/public/account.identities.passwordRequest.missing.required.json',
	],
	'invalidType'     => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.invalidType.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'unknown'         => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.invalid.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'deleted'         => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.deleted.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'blocked'         => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.blocked.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/public/account.identities.passwordRequest.blocked.json',
	],
	'notActivated'    => [
		'/v1/reset-identity',
		file_get_contents(__DIR__ . '/requests/public/account.identities.passwordRequest.notActivated.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/public/account.identities.passwordRequest.notActivated.json',
	],
];
