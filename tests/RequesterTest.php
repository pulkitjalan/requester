<?php

namespace PulkitJalan\Requester\Tests;

use PHPUnit_Framework_TestCase;
use Mockery;

class RequesterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->guzzle = Mockery::mock('GuzzleHttp\Client')->makePartial();
        $this->requester = Mockery::mock('PulkitJalan\Requester\Requester', [$this->guzzle, []])->makePartial();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testUrlGetter()
    {
        $this->requester->url('example.com');

        $this->assertEquals('https://example.com', $this->requester->getUrl());

        $this->requester->secure(false);

        $this->assertEquals('http://example.com', $this->requester->getUrl());

        $this->requester->url('git://example.com');

        $this->assertEquals('git://example.com', $this->requester->getUrl());
    }

    public function testInvalidUrlException()
    {
        $this->setExpectedException('PulkitJalan\Requester\Exceptions\InvalidUrlException');

        $this->requester->getUrl();
    }

    public function testGuzzleGetter()
    {
        $this->assertInstanceOf('GuzzleHttp\Client', $this->requester->getGuzzleClient());
    }

    public function testDisablingSslVerification()
    {
        $this->guzzle->shouldReceive('get')->once()->with('https://example.com', [
            'verify' => false,
        ]);

        $this->requester->url('example.com')->verify(false)->get();
    }

    public function testSettingAsync()
    {
        $this->guzzle->shouldReceive('get')->once()->with('https://example.com', [
            'verify' => true,
            'future' => true
        ]);

        $this->requester->url('example.com')->async(true)->get();
    }

    public function testSettingAndAddingHeaders()
    {
        $this->requester->headers(['Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==']);

        $this->assertEquals([
            'headers' => [
                'Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
            ],
        ], $this->readAttribute($this->requester, 'options'));

        $this->requester->headers(['Cache-Control' => 'no-cache']);

        $this->assertEquals([
            'headers' => [
                'Authorization' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                'Cache-Control' => 'no-cache',
            ],
        ], $this->readAttribute($this->requester, 'options'));
    }

    public function testAddingFileToRequest()
    {
        $this->requester->addFile(__FILE__);

        $this->assertNotEmpty($this->readAttribute($this->requester, 'options')['body']);

        $this->assertInternalType('resource', $this->readAttribute($this->requester, 'options')['body']['file']);

        $this->requester->addFile(__FILE__, 'image');

        $this->assertInternalType('resource', $this->readAttribute($this->requester, 'options')['body']['image']);
    }

    public function testChangingRetryOptions()
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

    public function testSendingGetRequest()
    {
        $this->guzzle->shouldReceive('get')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->get();
    }

    public function testSendingHeadRequest()
    {
        $this->guzzle->shouldReceive('head')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->head();
    }

    public function testSendingOptionsRequest()
    {
        $this->guzzle->shouldReceive('options')->once()->with('https://example.com', [
            'verify' => true,
        ]);

        $this->requester->url('example.com')->options();
    }

    public function testSendingPostRequest()
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

    public function testSendingPutRequest()
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

    public function testSendingPatchRequest()
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

    public function testSendingDeleteRequest()
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
