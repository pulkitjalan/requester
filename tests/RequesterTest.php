<?php

namespace PulkitJalan\Requester\Tests;

use PHPUnit_Framework_TestCase;
use Mockery;
use PulkitJalan\Requester\Requester;

class RequesterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->guzzle = Mockery::mock('GuzzleHttp\Client')->makePartial();
        $this->requester = new Requester($this->guzzle, []);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function test_url_getter()
    {
        $this->requester->url('example.com');

        $this->assertEquals('https://example.com', $this->requester->getUrl());

        $this->requester->secure(false);

        $this->assertEquals('http://example.com', $this->requester->getUrl());

        $this->requester->url('git://example.com');

        $this->assertEquals('git://example.com', $this->requester->getUrl());
    }

    public function test_invalid_url_exception()
    {
        $this->setExpectedException('PulkitJalan\Requester\Exceptions\InvalidUrlException');

        $this->requester->getUrl();
    }

    public function test_guzzle_getter()
    {
        $this->assertInstanceOf('GuzzleHttp\Client', $this->requester->getGuzzleClient());
    }

    public function test_disabling_ssl_verification()
    {
        $this->requester->verify(false);

        $this->assertEquals(['verify' => false], $this->readAttribute($this->requester, 'options'));

        $this->guzzle->shouldReceive('get')->once()->with('http://example.com', [
            'verify' => false,
        ]);

        $this->requester->url('example.com')->secure(false)->verify(false)->cache(true)->get();
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

    public function test_changing_retry_options()
    {
        $this->requester->retry(10);

        $this->assertEquals(10, $this->readAttribute($this->requester, 'retry'));

        $this->requester->every(100);

        $this->assertEquals(100, $this->readAttribute($this->requester, 'retryDelay'));

        $this->requester->on([500]);

        $this->assertEquals([500], $this->readAttribute($this->requester, 'retryOn'));

        $this->requester->retry(false);

        $this->assertEquals(false, $this->readAttribute($this->requester, 'retry'));
    }

    public function test_changing_cache_options()
    {
        $this->requester->cache(false);

        $this->assertEquals(false, $this->readAttribute($this->requester, 'cache'));

        $this->requester->cache(true);

        $this->assertEquals(true, $this->readAttribute($this->requester, 'cache'));
    }

    public function test_sending_get_request()
    {
        $this->guzzle->shouldReceive('get')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->get();
    }

    public function test_sending_head_request()
    {
        $this->guzzle->shouldReceive('head')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->head();
    }

    public function test_sending_options_request()
    {
        $this->guzzle->shouldReceive('options')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->options();
    }

    public function test_sending_post_request()
    {
        $this->guzzle->shouldReceive('post')->once()->with('https://example.com', [
            'verify' => true,
            'body'    => [
                'title' => 'some title',
            ],
        ]);

        $this->requester->url('example.com')->post([
            'body' => [
                'title' => 'some title',
            ],
        ]);
    }

    public function test_sending_put_request()
    {
        $this->guzzle->shouldReceive('put')->once()->with('https://example.com', [
            'verify' => true,
            'body'    => [
                'title' => 'some title',
            ],
        ]);

        $this->requester->url('example.com')->put([
            'body' => [
                'title' => 'some title',
            ],
        ]);
    }

    public function test_sending_patch_request()
    {
        $this->guzzle->shouldReceive('patch')->once()->with('https://example.com', [
            'verify' => true,
            'body'    => [
                'title' => 'some title',
            ],
        ]);

        $this->requester->url('example.com')->patch([
            'body' => [
                'title' => 'some title',
            ],
        ]);
    }

    public function test_sending_delete_request()
    {
        $this->guzzle->shouldReceive('delete')->once()->with('https://example.com', [
            'verify' => true,
            'body'    => [
                'id' => 1,
            ],
        ]);

        $this->requester->url('example.com')->delete([
            'body' => [
                'id' => 1,
            ],
        ]);
    }
}
