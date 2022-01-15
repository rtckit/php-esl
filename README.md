# FreeSWITCH Event Socket Layer library for PHP

[![Build Status](https://app.travis-ci.com/rtckit/php-esl.svg?branch=main)](https://app.travis-ci.com/rtckit/php-esl)
[![Latest Stable Version](https://poser.pugx.org/rtckit/esl/v/stable.png)](https://packagist.org/packages/rtckit/esl)
[![Test Coverage](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/test_coverage)](https://codeclimate.com/github/rtckit/php-esl/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/maintainability)](https://codeclimate.com/github/rtckit/php-esl/maintainability)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

## Quickstart

[FreeSWITCH](https://github.com/signalwire/freeswitch)'s Event Socket Layer is a TCP control interface enabling the development of complex dynamic dialplans/workflows. You can learn more about its [inbound mode](https://freeswitch.org/confluence/display/FREESWITCH/mod_event_socket) as well as its [outbound mode](https://freeswitch.org/confluence/display/FREESWITCH/Event+Socket+Outbound) on the FreeSWITCH website.

This library provides an I/O agnostic implementation of the ESL protocol.

#### ESL Message Parsing

The authentication stage of an ESL connection can be summarized as follows:

```php
/* This is a typical FreeSWITCH ESL server greeting */
$response = \RTCKit\ESL\Response::Parse("Content-Type: auth/request\n\n");

echo 'A server sends: ' . get_class($response) . PHP_EOL;

/* Since we've been told to authenticate, let's prepare our auth request */
$request = \RTCKit\ESL\Request::parse("auth ClueCon\n\n");

echo 'A client responds with: ' . get_class($request) . '; ';
echo 'password: ' . $request->getParameters() . PHP_EOL;

/* If our secret is correct, the ESL server should confirm that */
$followup = \RTCKit\ESL\Response::parse("Content-Type: command/reply\nReply-Text: +OK accepted\n\n");

echo 'Then the server replies with: ' . get_class($followup) . '; ';
echo ($followup->isSuccessful() ? 'Success!' : 'Yikes!') . PHP_EOL;
```

#### ESL Message Rendering

The reverse procedure, rendering to string, is straightforward:

```php
$response = new \RTCKit\ESL\Response\AuthRequest;

echo 'A server sends: "' . $response->render() . '"' . PHP_EOL;

$request = new \RTCKit\ESL\Request\Auth;
$request->setParameters('ClueCon');

echo 'A client responds with: "' . $request->render() . '"' . PHP_EOL;

$followup = new \RTCKit\ESL\Response\CommandReply;
$followup->setHeader('reply-text', '+OK accepted');

echo 'Then the server replies with: "' . $followup->render() . '"' . PHP_EOL;
```

#### ESL Connection

Although this library is I/O independent, a Connection [interface](src/ConnectionInterface.php) and [base class](src/Connection.php) are being provided; since ESL runs over TCP, a stream oriented transport, it behooves to handle the message framing in a higher level library. An implementing project would simply invoke the `ConnectionInterface::consume()` method when input is available and would implement a `ConnectionInterface::emitBytes()` method which performs the corresponding I/O-specific write operations.

The Connection constructor requires a `$role` argument, which must be one of the following:

* `ConnectionInterface::INBOUND_CLIENT` to be used by ESL clients connecting to FreeSWITCH ESL servers;
* `ConnectionInterface::OUTBOUND_SERVER` to be used by ESL servers FreeSWITCH connects to in outbound mode;

The other two options are less common:

* `ConnectionInterface::INBOUND_SERVER` to impersonate a FreeSWITCH ESL server;
* `ConnectionInterface::OUTBOUND_CLIENT` to impersonate FreeSWITCH connecting to a remote ESL endpoint in outbound mode;

The latter two roles can be useful in test suites, implementing message relays, security research etc. Please note the inbound and outbound terms are relative to the FreeSWITCH endpoint (matching the [mod_event_socket](https://freeswitch.org/confluence/display/FREESWITCH/mod_event_socket) nomenclature).

## Requirements

**RTCKit\ESL** is compatible with PHP 7.4+ and has no external library and extension dependencies.

## Installation

You can add the library as project dependency using [Composer](https://getcomposer.org/):

```sh
composer require rtckit/esl
```

If you only need the library during development, for instance when used in your test suite, then you should add it as a development-only dependency:

```sh
composer require --dev rtckit/esl
```

## Tests

To run the test suite, clone this repository and then install dependencies via Composer:

```sh
composer install
```

Then, go to the project root and run:

```bash
composer phpunit
```

### Static Analysis

In order to ensure high code quality, **RTCKit\ESL** uses [PHPStan](https://github.com/phpstan/phpstan) and [Psalm](https://github.com/vimeo/psalm):

```sh
composer phpstan
composer psalm
```

## License

MIT, see [LICENSE file](LICENSE).

### Acknowledgments

* [FreeSWITCH](https://github.com/signalwire/freeswitch), FreeSWITCH is a registered trademark of Anthony Minessale II

### Contributing

Bug reports (and small patches) can be submitted via the [issue tracker](https://github.com/rtckit/php-esl/issues). Forking the repository and submitting a Pull Request is preferred for substantial patches.
