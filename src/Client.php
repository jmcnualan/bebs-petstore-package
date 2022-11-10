<?php

namespace Bebs\Petstore;

use Bebs\Petstore\Exceptions\SuccessFullsException;
use Bebs\Petstore\Models\Pet;
use Bebs\Petstore\PetStore;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;

class Client implements PetStore
{
    protected $httpClient;

    protected $baseUri;

    protected $guzzle;

    /**
     * Construct
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->baseUri = $config['base_uri'];
        $this->guzzle = $config['guzzle'];
    }

    /**
     * HTTP Client
     *
     * @return HttpClient
     */
    protected function httpClient(): HttpClient
    {
        $this->guzzle['base_uri'] = $this->baseUri;

        if (false === $this->httpClient instanceof HttpClient) {
            $this->httpClient = new HttpClient($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function add(
        string $name,
        string $status,
        ?array $photoUrls = null,
        ?string $category = null,
        ?array $tags = null
    ): Pet {
        $payload = [
            'name' => $name,
            'status' => $status,
            'photoUrls' => $photoUrls,
            'category' => ['name' => $category],
            'tags' => array_map(fn ($value) => ['name' => $value], $tags),
        ];

        $response = $this->request('pet', 'POST', $payload);

        return new Pet(
            $response['id'],
            $response['name'],
            $response['status'],
            $response['category'],
            $response['photoUrls'],
            $response['tags']
        );
    }

    /**
     * Request
     *
     * @param string $uri
     * @param string $method
     * @param array $payload
     * @param array $headers
     *
     * @return array
     */
    protected function request(
        string $uri,
        string $method = 'GET',
        array $payload = [],
        array $headers = []
    ): array {
        try { // 2xx
            $response =  $this->httpClient()->request($method, $uri, [
                'json' => $payload,
                'headers' => $headers,
            ]);

            $jsonResponse = json_decode($response->getBody()->getContents(), true);

            if (false === $jsonResponse['success']) {
                throw new SuccessFullsException();
            }
        } catch (ClientException $exception) { // 4xx
            throw $exception;
        } catch (ServerException $exception) { //5xx
            throw $exception;
        }

        return $jsonResponse;
    }
}
