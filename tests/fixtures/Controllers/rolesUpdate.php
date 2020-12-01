<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const USER_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiYTVkMTliNDktNjI3Mi00ZGJkLWI3YWItNTJhY2QwMzY3MWE5IiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiJlZmJmYmRlZi1iZmJkLTY4ZWYtYmZiZC03NzBiNDBlZmJmYmQiLCJyb2xlcyI6WyJ1c2VyIl19.wi_KC5aDT-y6wKbA4wG29KPHUqFyEcNTI-TUvwIH5yc';

const ROLE_ID = 'efbfbdef-bfbd-efbf-bd0f-efbfbd5c4f61';
const UNKNOWN_ID = '83985c13-238c-46bd-aacb-2359d5c921a7';

return [
	// Valid responses
	//////////////////
	'update'       => [
		'/v1/roles/' . ROLE_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/roles/roles.update.json',
	],

	// Invalid responses
	////////////////////
	'unknown'      => [
		'/v1/roles/' . UNKNOWN_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.invalid.id.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'invalidType'  => [
		'/v1/roles/' . ROLE_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	'idMismatch'   => [
		'/v1/roles/' . ROLE_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.invalid.id.json'),
		StatusCodeInterface::STATUS_BAD_REQUEST,
		__DIR__ . '/responses/generic/invalid.identifier.json',
	],
	'noToken'      => [
		'/v1/roles/' . ROLE_ID,
		null,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'   => [
		'/v1/roles/' . ROLE_ID,
		'',
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'userToken'    => [
		'/v1/roles/' . USER_TOKEN,
		null,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken' => [
		'/v1/roles/' . ROLE_ID,
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken' => [
		'/v1/roles/' . ROLE_ID,
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/roles/roles.update.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
