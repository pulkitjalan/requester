Requester
=========

> Simple Requester class to wrap guzzle and the retry subscriber

[![Build Status](http://img.shields.io/travis/pulkitjalan/requester.svg?style=flat-square)](https://travis-ci.org/pulkitjalan/requester)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/pulkitjalan/requester/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/pulkitjalan/requester/)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/pulkitjalan/requester/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/pulkitjalan/requester/code-structure/master)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](http://www.opensource.org/licenses/MIT)
[![Latest Version](http://img.shields.io/packagist/v/pulkitjalan/requester.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/requester)
[![Total Downloads](https://img.shields.io/packagist/dt/pulkitjalan/requester.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/requester)

This package requires PHP >=5.4

## Installation

Install via composer - edit your `composer.json` to require the package.

```js
"require": {
    "pulkitjalan/requester": "1.*"
}
```

Then run `composer update` in your terminal to pull it in.

### Laravel

There is a Laravel service provider and facade available.

Add the following to the `providers` array in your `config/app.php`

```php
'PulkitJalan\Requester\RequesterServiceProvider'
```

Next add the following to the `aliases` array in your `config/app.php`

```php
'Requester' => 'PulkitJalan\Requester\Facades\Requester'
```

Next run `php artisan config:publish pulkitjalan/requester` to publish the config file.

## Usage

The requester class has a dependency of `guzzle` and takes in an instance of `guzzle` as the first param.

This package also uses a few guzzle subscribers. `https://github.com/guzzle/retry-subscriber` for retry functionality and `https://github.com/guzzle/log-subscriber` for logging.

Available request methods: `get`, `head`, `delete`, `put`, `patch`, `post`, `options`

```php
<?php

use PulkitJalan\Requester\Requester;
use GuzzleHttp\Client as GuzzleClient;

$requester = new Requester(new GuzzleClient());

// simple get request
$requester->url('example.com')->get();
```

Altering the default retry behaviour. See [retry-subscriber](https://github.com/guzzle/retry-subscriber) for more info.
```php
// retry 10 times, with a 1 second wait on a 503 error
$requester->url('example.com')->retry(10)->every(1000)->on([503])->get();

// disabling retry
$requester->url('example.com')->retry(false)->get();
```

Disabling ssl check
```php
// ssl check disabled
$requester->url('example.com')->veify(false)->get();
```

Use http instead of https
```php
// disable https and use http
$requester->url('example.com')->secure(false)->get();

// use http
$requester->url('http://example.com')->get();
```

Create a Post request
```php
// Create a post request
$requester->url('example.com/update/1')->post([
    'body' => [
        'title' => 'some title'
    ]
]);

// Upload a file
$requester->url('example.com/upload')->addFile('/tmp/image.jpg')->post([
    'body' => [
        'title' => 'Some image',
        'description' => 'Some image description'
    ]
]);
```

Guzzle 5 uses RingPHP and has the added functionality of performing request asynchronously.

Performing asynchronous requests
```php
// Create a post request
$response = $requester->url('example.com')->async(true)->get();

// Use the response asynchronously
$this->response = $response->then(function ($response) {
    return $response->getBody();
});

// Use the response synchronously
$this->response = $response->getBody();
```

Logging guzzle requests to file. See [log-subscriber](https://github.com/guzzle/log-subscriber) for more info.
```php
use PulkitJalan\Requester\Requester;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler('/path/to/your.log', Logger::INFO));

$requester = new Requester(new GuzzleClient());
$requester->addLogger($log);

// request and response logged to file
$requester->url('example.com')->get();

// Use the second param to update the format
$requester = new Requester(new GuzzleClient());
$requester->addLogger($log, 'DEBUG');
```
