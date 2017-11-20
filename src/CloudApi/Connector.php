<?php

namespace AcquiaCloudApi\CloudApi;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\HandlerStack;

class Connector
{
    /**
     * @var string BASE_URI
     */
    const BASE_URI = 'https://cloud.acquia.com/api';

    protected $client;

    protected $config;

    /**
     * Connector constructor.
     *
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client)
    {
        $key = new Key($this->config['key'], $this->config['secret']);
        $middleware = new HmacAuthMiddleware($key);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        $this->client = new GuzzleClient([
            'handler' => $stack,
        ]);
    }

    public function setConfig($config = []) {
        $this->config = $config;
    }

    /**
     * @param string $verb
     * @param string $path
     * @param array $query
     * @param array $options
     * @return array|object
     */
    public function makeRequest(string $verb, string $path, Array $query = [], array $options = array())
    {

        // @TODO sort, filter, limit, offset
        // Sortable by: 'name', 'label', 'weight'.
        // Filterable by: 'name', 'label', 'weight'.

        $options['query'] = $query;

        if (!empty($options['query']['filter']) && is_array($options['query']['filter'])) {
            // Default to an AND filter.
            $options['query']['filter'] = implode(',', $options['query']['filter']);
        }

        try {
            $response = $this->client->$verb(self::BASE_URI . $path, $options);
        } catch (ClientException $e) {
            print $e->getMessage();
            $response = $e->getResponse();
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return mixed|StreamInterface
     */
    public function processResponse(ResponseInterface $response)
    {

        // @TODO detect status code here and exit early.
        $body = $response->getBody();

        $object = json_decode($body);
        if (json_last_error() === JSON_ERROR_NONE) {
            // JSON is valid
            if (property_exists($object, '_embedded') && property_exists($object->_embedded, 'items')) {
                $return = $object->_embedded->items;
            } elseif (property_exists($object, 'error')) {
                $this->error = true;
                $return = $object->message;
            } else {
                $return = $object;
            }
        } else {
            $return = $body;
        }

        return $return;
    }

}