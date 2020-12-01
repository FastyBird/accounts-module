<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiYTVkMTliNDktNjI3Mi00ZGJkLWI3YWItNTJhY2QwMzY3MWE5IiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiJlZmJmYmRlZi1iZmJkLTY4ZWYtYmZiZC03NzBiNDBlZmJmYmQiLCJyb2xlcyI6WyJ1c2VyIl19.wi_KC5aDT-y6wKbA4wG29KPHUqFyEcNTI-TUvwIH5yc';

const ADMINISTRATOR_ACCOUNT_ID = '5e79efbf-bd0d-5b7c-46ef-bfbdefbfbd34';
const USER_ACCOUNT_ID = 'fae8d781-7e2c-4318-9c85-43ba637d14c5';
const ADMINISTRATOR_EMAIL_ID = '0b46d3d6-c980-494a-8b40-f19e6095e610';
const USER_EMAIL_ID = 'ed987404-f14c-40b4-9150-15b6590deb8c';
const UNKNOWN_ID = '83985c13-238c-46bd-aacb-2359d5c921a7';

return [
	// Valid responses
	//////////////////
	'update'          => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/emails/emails.update.json',
	],
	'verify'          => [
		'/v1/accounts/' . USER_ACCOUNT_ID . '/emails/' . USER_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.verify.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/emails/emails.verify.json',
	],

	// Invalid responses
	////////////////////
	'unknown'         => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . UNKNOWN_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.invalid.id.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'invalidRelation' => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.invalid.relation.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/emails/emails.update.invalid.relation.json',
	],
	'invalidType'     => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'idMismatch'      => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.invalid.id.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/generic/invalid.identifier.json',
	],
	'noToken'         => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		null,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'      => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'',
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'userToken'       => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . USER_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'    => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken'    => [
		'/v1/accounts/' . ADMINISTRATOR_ACCOUNT_ID . '/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/emails/emails.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
