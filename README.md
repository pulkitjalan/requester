Requester
=========

[![Build Status](https://travis-ci.org/pulkitjalan/requester.svg)](https://travis-ci.org/pulkitjalan/requester)
[![Coverage Status](https://coveralls.io/repos/pulkitjalan/requester/badge.png)](https://coveralls.io/r/pulkitjalan/requester)

Simple Requester class to wrap guzzle and the retry subscriber

# Usage

The requester class has a dependency of `guzzle` and takes in an instance of `guzzle` as the first param.

```php
<?php

use PulkitJalan\Requester\Client;
use GuzzleHttp\Client as GuzzleClient;

$requester = new Client(new GuzzleClient());

// simple get request
$requester->url('github.com')->get();
```

Altering the default retry behaviour
```php
// retry 10 times, with a 1 second wait on a 404 error
$requester->url('github.com')->retry(10)->every(1000)->on([404])->get();

// disabling retry
$requester->url('github.com')->retry(false)->get();
```

Disabling ssl check
```php
// ssl check disabled
$requester->url('github.com')->veify(false)->get();
```

Use http instead of https
```php
// disable https and use http
$requester->url('github.com')->secure(false)->get();

// use http
$requester->url('http://github.com')->get();
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
