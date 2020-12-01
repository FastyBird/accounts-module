<?php declare(strict_types = 1);

use FastyBird\AuthModule\Schemas;
use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiYTVkMTliNDktNjI3Mi00ZGJkLWI3YWItNTJhY2QwMzY3MWE5IiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiJlZmJmYmRlZi1iZmJkLTY4ZWYtYmZiZC03NzBiNDBlZmJmYmQiLCJyb2xlcyI6WyJ1c2VyIl19.wi_KC5aDT-y6wKbA4wG29KPHUqFyEcNTI-TUvwIH5yc';

return [
	// Valid responses
	//////////////////
	'read'                          => [
		'/v1/me',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.read.json',
	],
	'readUser'                      => [
		'/v1/me',
		'Bearer ' . USER_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.read.user.json',
	],
	'readWithIncluded'              => [
		'/v1/me?include=emails',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.read.included.json',
	],
	'readRelationshipsEmails'       => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.relationship.emails.json',
	],
	'readRelationshipsIdentities'   => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_IDENTITIES,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.relationship.identities.json',
	],
	'readRelationshipsRoles'        => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_ROLES,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/account/account.relationship.roles.json',
	],

	// Invalid responses
	////////////////////
	'readRelationshipsUnknown'      => [
		'/v1/me/relationships/unknown',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/relation.unknown.json',
	],
	'readNoToken'                   => [
		'/v1/me',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readEmptyToken'                => [
		'/v1/me',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readExpiredToken'              => [
		'/v1/me',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readInvalidToken'              => [
		'/v1/me',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readRelationshipsNoToken'      => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS,
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readRelationshipsEmptyToken'   => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS,
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readRelationshipsInvalidToken' => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS,
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readRelationshipsExpiredToken' => [
		'/v1/me/relationships/' . Schemas\Accounts\UserAccountSchema::RELATIONSHIPS_EMAILS,
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
