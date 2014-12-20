<?php

namespace PulkitJalan\Requester;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Retry\RetrySubscriber;
use GuzzleHttp\Subscriber\Cache\CacheSubscriber;
use PulkitJalan\Requester\Exceptions\InvalidUrlException;

class Requester
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Url
     *
     * @var string
     */
    protected $url = null;

    /**
     * Options for request
     *
     * @var array
     */
    protected $options = [];

    /**
     * Send secure request or not
     *
     * @var boolean
     */
    protected $secure = true;

    /**
     * Retry request on which types of errors
     *
     * @var array
     */
    protected $retryOn = [500, 502, 503, 504];

    /**
     * Delay between requests
     * In miliseconds
     *
     * @var integer
     */
    protected $retryDelay = 10;

    /**
     * Number of times to retry
     *
     * @var integer
     */
    protected $retry = 5;

    /**
     * Use cache subscriber
     *
     * @var boolean
     */
    protected $cache = false;

    /**
     * @param \GuzzleHttp\Client $guzzleClient
     * @param array              $config
     */
    public function __construct(GuzzleClient $guzzleClient, array $config = [])
    {
        $this->guzzleClient = $guzzleClient;
        $this->config = $config;

        $this->initialize();
    }

    /**
     * Getter for guzzle client
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        $guzzle = $this->guzzleClient;

        if ($this->retry) {
            $guzzle = $this->addRetrySubscriber($guzzle);
        }

        if ($this->cache) {
            $guzzle = $this->addCacheSubscriber($guzzle);
        }

        return $guzzle;
    }

    /**
     * Set the url
     * will automatically append the protocol
     *
     * @return \PulkitJalan\Requester\Requester
     */
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Use secure endpoint or not
     *
     * @param  boolean                          $secure
     * @return \PulkitJalan\Requester\Requester
     */
    public function secure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Verify ssl or not
     *
     * @param  boolean|string                   $verify boolean or path to certificate
     * @return \PulkitJalan\Requester\Requester
     */
    public function verify($verify)
    {
        $this->options = array_merge($this->options, ['verify' => $verify]);

        return $this;
    }

    /**
     * Set headers for the request
     *
     * @param  array                            $headers
     * @return \PulkitJalan\Requester\Requester
     */
    public function headers(array $headers)
    {
        $this->options = array_merge_recursive($this->options, ['headers' => $headers]);

        return $this;
    }

    /**
     * Number if times to retry
     *
     * @param  int                              $retry times to retry
     * @return \PulkitJalan\Requester\Requester
     */
    public function retry($retry)
    {
        $this->retry = $retry;

        return $this;
    }

    /**
     * Delay between retrying
     *
     * @param  int                              $retryDelay delay between retrying
     * @return \PulkitJalan\Requester\Requester
     */
    public function every($retryDelay)
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    /**
     * Types of errors to retry on
     *
     * @param  array                            $retryOn errors to retry on
     * @return \PulkitJalan\Requester\Requester
     */
    public function on(array $retryOn)
    {
        $this->retryOn = $retryOn;

        return $this;
    }

    /**
     * Enable or disable the cache subscriber
     *
     * @param  boolean                          $cache
     * @return \PulkitJalan\Requester\Requester
     */
    public function cache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Add a file to the request
     *
     * @param  string                           $filepath path to file
     * @param  string                           $key      optional post key, default to file
     * @return \PulkitJalan\Requester\Requester
     */
    public function addFile($filepath, $key = 'file')
    {
        $this->options = array_merge_recursive($this->options, [
            'body' => [
                $key => fopen($filepath, 'r'),
            ]
        ]);

        return $this;
    }

    /**
     * Send get request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function get(array $options = [])
    {
        return $this->send('get', $options);
    }

    /**
     * Send head request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function head(array $options = [])
    {
        return $this->send('head', $options);
    }

    /**
     * Send delete request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function delete(array $options = [])
    {
        return $this->send('delete', $options);
    }

    /**
     * Send put request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function put(array $options = [])
    {
        return $this->send('put', $options);
    }

    /**
     * Send patch request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function patch(array $options = [])
    {
        return $this->send('patch', $options);
    }

    /**
     * Send post request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function post(array $options = [])
    {
        return $this->send('post', $options);
    }

    /**
     * Send options request
     *
     * @param  array                                 $options
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    public function options(array $options = [])
    {
        return $this->send('options', $options);
    }

    /**
     * Getter for the url will append protocol if one does not exist
     * @return string
     */
    public function getUrl()
    {
        if (!$this->url) {
            throw new InvalidUrlException();
        }

        $url = $this->url;

        if (! parse_url($this->url, PHP_URL_SCHEME)) {
            $url = $this->getProtocol().$url;
        }

        return $url;
    }

    /**
     * Send the request using guzzle
     *
     * @param  string                                $function function to call on guzzle
     * @param  array                                 $options  options to pass
     * @return \GuzzleHttp\Message\ResponseInterface
     */
    protected function send($function, array $options = [])
    {
        $url = $this->getUrl();

        // merge options
        $options = array_merge_recursive($this->options, $options);

        // need to reset after every request
        $this->initialize();

        return $this->getGuzzleClient()->$function($url, $options);
    }

    /**
     * Add the retry subscriber to the guzzle client
     *
     * @param  \GuzzleHttp\Client $guzzle
     * @return \GuzzleHttp\Client
     */
    protected function addRetrySubscriber(GuzzleClient $guzzle)
    {
        // Build retry subscriber
        $retry = new RetrySubscriber([
            'filter' => RetrySubscriber::createStatusFilter($this->retryOn),
            'delay' => function ($number, $event) {
                return $this->retryDelay;
            },
            'max' => $this->retry,
        ]);

        // add the retry emitter
        $guzzle->getEmitter()->attach($retry);

        return $guzzle;
    }

    /**
     * Add the cache subscriber to the guzzle client
     *
     * @param  \GuzzleHttp\Client $guzzle
     * @return \GuzzleHttp\Client
     */
    protected function addCacheSubscriber(GuzzleClient $guzzle)
    {
        CacheSubscriber::attach($guzzle);

        return $guzzle;
    }

    /**
     * Get the protocol
     *
     * @return string
     */
    protected function getProtocol()
    {
        return 'http'.($this->secure ? 's' : '').'://';
    }

    /**
     * Resets all variables to default values
     * required if using the same instance for multiple requests
     *
     * @return void
     */
    protected function initialize()
    {
        $this->url = '';
        $this->options = [];
        $this->secure = array_get($this->config, 'secure', true);
        $this->retryOn = array_get($this->config, 'retry.on', [500, 502, 503, 504]);
        $this->retryDelay = array_get($this->config, 'retry.delay', 10);
        $this->retry = array_get($this->config, 'retry.times', 5);
        $this->cache = array_get($this->config, 'cache', false);
        $this->verify(array_get($this->config, 'verify', true));
    }
}
