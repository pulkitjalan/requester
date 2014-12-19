<?php

namespace PulkitJalan\Requester\Tests;

use PHPUnit_Framework_TestCase;
use Mockery;
use PulkitJalan\Requester\Requester;
use GuzzleHttp\Client as GuzzleClient;

class RequesterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->requester = new Requester(new GuzzleClient(), []);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function test_url_getter()
    {
        $this->requester->url('github.com');

        $this->assertEquals('https://github.com', $this->requester->getUrl());

        $this->requester->secure(false);

        $this->assertEquals('http://github.com', $this->requester->getUrl());

        $this->requester->url('git://github.com');

        $this->assertEquals('git://github.com', $this->requester->getUrl());
    }

    public function test_invalid_url_exception()
    {
        $this->setExpectedException('PulkitJalan\Requester\Exceptions\InvalidUrlException');

        $this->requester->get();
    }

    public function test_guzzle_getter()
    {
        $this->assertInstanceOf('GuzzleHttp\Client', $this->requester->getGuzzleClient());
    }

    public function test_disabling_ssl_verification()
    {
        $this->requester->verify(false);

        $this->assertEquals(['verify' => false], $this->readAttribute($this->requester, 'options'));
    }

    public function test_setting_and_adding_headers()
    {
        $this->requester->headers(['Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==']);

        $this->assertEquals([
            'verify' => true,
            'headers' => [
                'Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
            ],
        ], $this->readAttribute($this->requester, 'options'));

        $this->requester->headers(['Cache-Control' => 'no-cache']);

        $this->assertEquals([
            'verify' => true,
            'headers' => [
                'Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                'Cache-Control' => 'no-cache',
            ],
        ], $this->readAttribute($this->requester, 'options'));
    }

    public function test_adding_file_to_request()
    {
        $this->requester->addFile(__FILE__);

        $this->assertNotEmpty($this->readAttribute($this->requester, 'options')['body']);

        $this->assertInternalType('resource', $this->readAttribute($this->requester, 'options')['body']['file']);

        $this->requester->addFile(__FILE__, 'image');

        $this->assertInternalType('resource', $this->readAttribute($this->requester, 'options')['body']['image']);
    }
}
