<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';

return [
	// Valid responses
	//////////////////
	'create'                 => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/session/session.create.json',
	],
	'createWithEmptyToken'   => [
		'/v1/session',
		'',
		file_get_contents(__DIR__ . '/requests/session/session.create.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/session/session.create.json',
	],

	// Invalid responses
	////////////////////
	'createWithToken'        => [
		'/v1/session',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/session/session.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'createWithExpiredToken' => [
		'/v1/session',
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/session/session.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'createWithInvalidToken' => [
		'/v1/session',
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/session/session.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'missingRequired'        => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/session/session.create.missing.required.json',
	],
	'unknown'                => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.unknown.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/session/session.create.unknown.json',
	],
	'invalid'                => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.invalid.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/session/session.create.invalid.json',
	],
	'deleted'                => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.deleted.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/session/session.create.deleted.json',
	],
	'blocked'                => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.blocked.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/session/session.create.blocked.json',
	],
	'notActivated'           => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.notActivated.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/session/session.create.notActivated.json',
	],
	'approvalWaiting'        => [
		'/v1/session',
		null,
		file_get_contents(__DIR__ . '/requests/session/session.create.approvalWaiting.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/session/session.create.approvalWaiting.json',
	],
];
