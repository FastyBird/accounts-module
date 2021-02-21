<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiYTVkMTliNDktNjI3Mi00ZGJkLWI3YWItNTJhY2QwMzY3MWE5IiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiJlZmJmYmRlZi1iZmJkLTY4ZWYtYmZiZC03NzBiNDBlZmJmYmQiLCJyb2xlcyI6WyJ1c2VyIl19.wi_KC5aDT-y6wKbA4wG29KPHUqFyEcNTI-TUvwIH5yc';

const ADMINISTRATOR_ACCOUNT_ID = '5e79efbf-bd0d-5b7c-46ef-bfbdefbfbd34';

return [
	// Valid responses
	//////////////////
	'createUser'          => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/identities/identities.create.user.json',
	],

	// Invalid responses
	////////////////////
	'missingRequired'     => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/identities/identities.create.missing.required.json',
	],
	'missingRelation'     => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.missing.relation.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/identities/identities.create.missing.relation.json',
	],
	'invalidType'         => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'identifierNotUnique' => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.identifier.notUnique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/identifier.notUnique.json',
	],
	'usedUid'             => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.usedUid.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/identities/identities.create.usedUid.json',
	],
	'noToken'             => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		null,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'          => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'',
		file_get_contents(__DIR__ . '/requests/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'userToken'           => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . USER_TOKEN,
		file_get_contents(__DIR__ . '/responses/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'        => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken'        => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/identities',
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/identities/identities.create.user.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
