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
     * @param \GuzzleHttp\Client  $guzzleClient
     */
    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Set the url
     * will automatically append the protocol
     *
     * @param string $base     the base url, can be url or something from config
     * @param string $protocol custom protocol to add
     *
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
     * @param boolean $secure
     *
     * @return \PulkitJalan\Requester\Client
     */
    public function secure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * verify ssl or not
     *
     * @param boolean|string $verify boolean or path to certificate
     *
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
     * @param array $headers
     *
     * @return \PulkitJalan\Requester\Client
     */
    public function headers(array $headers)
    {
        $this->options = array_merge($this->options, ['headers' => $headers]);

        return $this;
    }

    /**
     * times to retry
     *
     * @param int $retry times to retry
     *
     * @return \PulkitJalan\Requester\Client
     */
    public function retry($retry)
    {
        $this->retry = $retry;

        return $this;
    }

    /**
     * delay between retrying
     *
     * @param int $retryDelay delay between retrying
     *
     * @return \PulkitJalan\Requester\Client
     */
    public function every($retryDelay)
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    /**
     * types of errors to retry on
     *
     * @param  array  $retryOn errors to retry on
     * @return \PulkitJalan\Requester\Client
     */
    public function on(array $retryOn)
    {
        $this->retryOn = $retryOn;

        return $this;
    }

    /**
     * Send get request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function get(array $options = [])
    {
        return $this->send('get', $options);
    }

    /**
     * Send head request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function head(array $options = [])
    {
        return $this->send('head', $options);
    }

    /**
     * Send delete request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function delete(array $options = [])
    {
        return $this->send('delete', $options);
    }

    /**
     * Send put request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function put(array $options = [])
    {
        return $this->send('put', $options);
    }

    /**
     * Send patch request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function patch(array $options = [])
    {
        return $this->send('patch', $options);
    }

    /**
     * Send post request
     *
     * @param  array  $options
     * @return guzzle response
     */
    public function post(array $options = [])
    {
        return $this->send('post', $options);
    }

    /**
     * Send options request
     *
     * @param array  $options
     * @return guzzle response
     */
    public function options(array $options = [])
    {
        return $this->send('options', $options);
    }

    /**
     * Getter for the url will append protocol if one does not exist
     *
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
     * @param string  $function function to call on guzzle
     * @param array   $options  options to pass
     *
     * @return guzzle response
     */
    protected function send($function, array $options = [])
    {
        if ($this->retry) {
            $this->addRetrySubscriber();
        }

        // merge options
        $options = array_merge($this->options, $options);

        return $this->guzzleClient->$function($this->getUrl(), $options);
    }

    /**
     * Add the retry subscriber to the guzzle client
     */
    protected function addRetrySubscriber()
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
        $this->guzzleClient->getEmitter()->attach($retry);
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
}
