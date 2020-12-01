<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiYTVkMTliNDktNjI3Mi00ZGJkLWI3YWItNTJhY2QwMzY3MWE5IiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiJlZmJmYmRlZi1iZmJkLTY4ZWYtYmZiZC03NzBiNDBlZmJmYmQiLCJyb2xlcyI6WyJ1c2VyIl19.wi_KC5aDT-y6wKbA4wG29KPHUqFyEcNTI-TUvwIH5yc';

const ADMINISTRATOR_ACCOUNT_ID = '5e79efbf-bd0d-5b7c-46ef-bfbdefbfbd34';
const USER_ACCOUNT_ID = 'efbfbdef-bfbd-68ef-bfbd-770b40efbfbd';

return [
	// Valid responses
	//////////////////
	'create'              => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/emails/emails.create.json',
	],

	// Invalid responses
	////////////////////
	'missingRequired'     => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/emails/emails.create.missing.required.json',
	],
	'missingRelation'     => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.missing.relation.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/emails/emails.create.missing.relation.json',
	],
	'invalidRelation'     => [
		'/v1/accounts/' . USER_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/emails/emails.create.invalid.relation.json',
	],
	'invalidType'         => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'identifierNotUnique' => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.identifier.notUnique.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/identifier.notUnique.json',
	],
	'invalidEmail'        => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.invalid.email.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/emails/emails.create.invalid.email.json',
	],
	'usedEmail'           => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.usedEmail.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/emails/emails.create.usedEmail.json',
	],
	'noToken'             => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		null,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'          => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'',
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'userToken'           => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . USER_TOKEN,
		file_get_contents(__DIR__ . '/responses/emails/emails.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'        => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken'        => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails',
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
