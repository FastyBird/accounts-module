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
		'/v1/session',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/session/session.read.json',
	],
	'readUser'                      => [
		'/v1/session',
		'Bearer ' . USER_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/session/session.read.user.json',
	],
	'readRelationshipsAccount'      => [
		'/v1/session/relationships/' . Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/session/session.relationship.account.json',
	],

	// Invalid responses
	////////////////////
	'readRelationshipsUnknown'      => [
		'/v1/session/relationships/unknown',
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/relation.unknown.json',
	],
	'readNoToken'                   => [
		'/v1/session',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readEmptyToken'                => [
		'/v1/session',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readExpiredToken'              => [
		'/v1/session',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readInvalidToken'              => [
		'/v1/session',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readRelationshipsNoToken'      => [
		'/v1/session/relationships/' . Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT,
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readRelationshipsEmptyToken'   => [
		'/v1/session/relationships/' . Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT,
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readRelationshipsExpiredToken' => [
		'/v1/session/relationships/' . Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT,
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readRelationshipsInvalidToken' => [
		'/v1/session/relationships/' . Schemas\Sessions\SessionSchema::RELATIONSHIPS_ACCOUNT,
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
