<?php namespace PulkitJalan\Requester;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Retry\RetrySubscriber;

class Client
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
    protected $url = '';

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
     * @param \GuzzleHttp\Client $guzzleClient
     * @param array              $config
     */
    public function __construct(GuzzleClient $guzzleClient, array $config = [])
    {
        $this->guzzleClient = $guzzleClient;
        $this->config = $config;

        $this->initilize();
    }

    /**
     * Getter for guzzle client
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * Set the url
     * will automatically append the protocol
     *
     * @param  string                        $base     the base url, can be url or something from config
     * @param  string                        $protocol custom protocol to add
     * @return \PulkitJalan\Requester\Client
     */
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Use secure endpoint or not
     *
     * @param  boolean                       $secure
     * @return \PulkitJalan\Requester\Client
     */
    public function secure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Verify ssl or not
     *
     * @param  boolean|string                $verify boolean or path to certificate
     * @return \PulkitJalan\Requester\Client
     */
    public function verify($verify)
    {
        $this->options = array_merge($this->options, ['verify' => $verify]);

        return $this;
    }

    /**
     * Set headers for the request
     *
     * @param  array                         $headers
     * @return \PulkitJalan\Requester\Client
     */
    public function headers(array $headers)
    {
        $this->options = array_merge_recursive($this->options, ['headers' => $headers]);

        return $this;
    }

    /**
     * Number if times to retry
     *
     * @param  int                           $retry times to retry
     * @return \PulkitJalan\Requester\Client
     */
    public function retry($retry)
    {
        $this->retry = $retry;

        return $this;
    }

    /**
     * Delay between retrying
     *
     * @param  int                           $retryDelay delay between retrying
     * @return \PulkitJalan\Requester\Client
     */
    public function every($retryDelay)
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    /**
     * Types of errors to retry on
     *
     * @param  array                         $retryOn errors to retry on
     * @return \PulkitJalan\Requester\Client
     */
    public function on(array $retryOn)
    {
        $this->retryOn = $retryOn;

        return $this;
    }

    /**
     * Add a file to the request
     *
     * @param  string                        $filepath path to file
     * @param  string                        $key      optional post key, default to file
     * @return \PulkitJalan\Requester\Client
     */
    public function addFile($filepath, $key = 'file')
    {
        $this->options = array_merge_recursive($this->options, [
            'body' => [
                $key => fopen($filepath, 'r')
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
        $url = $this->url;

        if (!parse_url($this->url, PHP_URL_SCHEME)) {
            $url = $this->getProtocol() . $url;
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
        $guzzle = $this->getGuzzleClient();

        if ($this->retry) {
            $guzzle = $this->addRetrySubscriber($guzzle);
        }

        $url = $this->getUrl();

        // merge options
        $options = array_merge_recursive($this->options, $options);

        // need to reset after every request
        $this->initilize();

        return $guzzle->$function($url, $options);
    }

    /**
     * Add the retry subscriber to the guzzle client
     */
    protected function addRetrySubscriber(GuzzleClient $guzzle)
    {
        // Build retry subscriber
        $retry = new RetrySubscriber([
            'filter' => RetrySubscriber::createStatusFilter($this->retryOn),
            'delay' => function ($number, $event) {
                return $this->retryDelay;
            },
            'max' => $this->retry
        ]);

        // add the retry emitter
        $guzzle->getEmitter()->attach($retry);

        return $guzzle;
    }

    /**
     * Get the protocol
     *
     * @return string
     */
    protected function getProtocol()
    {
        $protocol = 'http';

        if ($this->secure) {
            $protocol .= 's';
        }

        return $protocol . '://';
    }

    /**
     * Resets all variables to default values
     * required if using the same instance for multiple requests
     *
     * @return void
     */
    protected function initilize()
    {
        $this->url = '';
        $this->options = [];
        $this->secure = array_get($this->config, 'secure', true);
        $this->retryOn = array_get($this->config, 'retry.on', [500, 502, 503, 504]);
        $this->retryDelay = array_get($this->config, 'retry.delay', 10);
        $this->retry = array_get($this->config, 'retry.times', 5);
        $this->verify(array_get($this->config, 'verify', true));
    }
}
