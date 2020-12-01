<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';

const ADMINISTRATOR_IDENTITY_ID = '77331268-efbf-bd34-49ef-bfbdefbfbd04';
const USER_IDENTITY_ID = 'faf7a863-a49c-4428-a757-1de537773355';
const UNKNOWN_ID = '83985c13-238c-46bd-aacb-2359d5c921a7';

return [
	// Valid responses
	//////////////////
	'update'          => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.json'),
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/account/identities/account.identities.update.json',
	],

	// Invalid responses
	////////////////////
	'unknown'         => [
		'/v1/me/identities/' . UNKNOWN_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.invalid.id.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'invalidPassword' => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.invalid.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/account/identities/account.identities.update.invalid.json',
	],
	'missingRequired' => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/account/identities/account.identities.update.missing.required.json',
	],
	'fromOtherUser'   => [
		'/v1/me/identities/' . USER_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.otherUser.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'invalidType'     => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'idMismatch'      => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.invalid.id.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/generic/invalid.identifier.json',
	],
	'noToken'         => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		null,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'      => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'',
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'    => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken'    => [
		'/v1/me/identities/' . ADMINISTRATOR_IDENTITY_ID,
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/account/identities/account.identities.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
