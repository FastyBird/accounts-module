<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const ADMINISTRATOR_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';

const ADMINISTRATOR_EMAIL_ID = '32ebe3c3-0238-482e-ab79-6b1d9ee2147c';
const ADMINISTRATOR_PRIMARY_EMAIL_ID = '0b46d3d6-c980-494a-8b40-f19e6095e610';
const USER_EMAIL_ID = '73efbfbd-efbf-bd36-44ef-bfbdefbfbd7a';
const UNKNOWN_ID = '83985c13-238c-46bd-aacb-2359d5c921a7';

return [
	// Valid responses
	//////////////////
	'delete'        => [
		'/v1/me/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_NO_CONTENT,
		__DIR__ . '/responses/account/emails/account.emails.delete.json',
	],

	// Invalid responses
	////////////////////
	'default'       => [
		'/v1/me/emails/' . ADMINISTRATOR_PRIMARY_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/account/emails/account.emails.delete.default.json',
	],
	'unknown'       => [
		'/v1/me/emails/' . UNKNOWN_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'fromOtherUser' => [
		'/v1/me/emails/' . USER_EMAIL_ID,
		'Bearer ' . ADMINISTRATOR_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'noToken'       => [
		'/v1/me/emails/' . ADMINISTRATOR_EMAIL_ID,
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'emptyToken'    => [
		'/v1/me/emails/' . ADMINISTRATOR_EMAIL_ID,
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'  => [
		'/v1/me/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'expiredToken'  => [
		'/v1/me/emails/' . ADMINISTRATOR_EMAIL_ID,
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
