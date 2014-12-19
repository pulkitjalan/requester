<?php

namespace PulkitJalan\Requester\Tests;

use PHPUnit_Framework_TestCase;
use Mockery;

class RequesterTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_url_getter()
    {
        $client = Mockery::mock('PulkitJalan\Requester\Requester')->makePartial();

        $client->url('github.com');

        $this->assertEquals('https://github.com', $client->getUrl());

        $client->secure(false);

        $this->assertEquals('http://github.com', $client->getUrl());

        $client->url('git://github.com');

        $this->assertEquals('git://github.com', $client->getUrl());
    }
}
