# FastyBird IoT accounts module

[![Build Status](https://badgen.net/github/checks/FastyBird/accounts-module/master?cache=300&style=flast-square)](https://github.com/FastyBird/accounts-module/actions)
[![Code coverage](https://badgen.net/coveralls/c/github/FastyBird/accounts-module?cache=300&style=flast-square)](https://coveralls.io/r/FastyBird/accounts-module)
![PHP](https://badgen.net/packagist/php/FastyBird/accounts-module?cache=300&style=flast-square)
[![Licence](https://badgen.net/packagist/license/FastyBird/accounts-module?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/accounts-module)
[![Downloads total](https://badgen.net/packagist/dt/FastyBird/accounts-module?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/accounts-module)
[![Latest stable](https://badgen.net/packagist/v/FastyBird/accounts-module/latest?cache=300&style=flast-square)](https://packagist.org/packages/FastyBird/accounts-module)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

## What is FastyBird IoT accounts module?

Accounts module is a [Nette framework](https://nette.org) extension for managing application accounts and sessions and exposing them for clients.

[FastyBird](https://www.fastybird.com) [IoT](https://en.wikipedia.org/wiki/Internet_of_things) accounts module is an [Apache2 licensed](http://www.apache.org/licenses/LICENSE-2.0) distributed extension, developed in [PHP](https://www.php.net) with [Nette framework](https://nette.org).

## Requirements

[FastyBird](https://www.fastybird.com) accounts module is tested against PHP 7.4 and [ReactPHP http](https://github.com/reactphp/http) 0.8 event-driven, streaming plaintext HTTP server and [Nette framework](https://nette.org/en/) 3.0 PHP framework for real programmers

## Getting started

The best way to install **fastybird/accounts-module** is using [Composer](https://getcomposer.org/).

> If you don't have Composer yet, [download it](https://getcomposer.org/download/) following the instructions.
> Then use command and install base Nette project. Everything required will be then installed in the provided folder.

```sh
$ composer create-project nette/web-project path/to/install
$ cd path/to/install
```

Module could be installed with command:

```sh
$ composer require fastybird/accounts-module
```

## Configuration

This module is dependent on other Nette extensions. All this extensions have to enabled and configured in NEON configuration file.

Example configuration could be found [here](https://github.com/FastyBird/accounts-module/blob/master/config/example.neon)

## Initialization

This module is using database, and need some initial data to be inserted into it. This could be done via shell command:

```sh
$ vendor/bin/fb-console fb:accounts-module:initialize
```

This console command is interactive and will ask for all required information.

## HTTP server

This module has built-in web server for serving module api to clients. This web server could be started with command:
```sh
$ vendor/bin/fb-console fb:web-server:start
```

After successful start, app is listening for incoming http api request messages from clients.

## Feedback

Use the [issue tracker](https://github.com/FastyBird/accounts-module/issues) for bugs or [mail](mailto:code@fastybird.com) or [Tweet](https://twitter.com/fastybird) us for any idea that can improve the project.

Thank you for testing, reporting and contributing.

## Changelog

For release info check [release page](https://github.com/FastyBird/accounts-module/releases)

## Maintainers

<table>
	<tbody>
		<tr>
			<td align="center">
				<a href="https://github.com/akadlec">
					<img width="80" height="80" src="https://avatars3.githubusercontent.com/u/1866672?s=460&amp;v=4">
				</a>
				<br>
				<a href="https://github.com/akadlec">Adam Kadlec</a>
			</td>
		</tr>
	</tbody>
</table>

***
Homepage [https://www.fastybird.com](https://www.fastybird.com) and repository [https://github.com/fastybird/accounts-module](https://github.com/fastybird/accounts-module).
