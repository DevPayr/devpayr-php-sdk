<?php

namespace DevPayr\Http;

use DevPayr\Auth\ApiKeyAuth;
use DevPayr\Auth\LicenseAuth;
use DevPayr\Config\Config;
use DevPayr\Exceptions\ApiResponseException;
use DevPayr\Exceptions\DevPayrException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class HttpClient
 *
 * Handles all HTTP interactions with the DevPayr API using Guzzle.
 * - Automatically injects authentication headers (API key or License key)
 * - Supports JSON and multipart/form-data requests
 * - Throws structured exceptions on API errors
 */
class HttpClient
{
    protected Client $client;
    protected Config $config;

    /**
     * Initialize the HTTP client with configuration.
     *
     * @param Config $config DevPayr configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => rtrim($this->config->get('base_url'), '/') . '/',
            'timeout'  => $this->config->get('timeout', 10),
        ]);
    }

    /**
     * Main dispatcher for any HTTP request.
     *
     * @param string $method GET, POST, PUT, PATCH, DELETE
     * @param string $uri API endpoint (relative path)
     * @param array $options Guzzle options (json, query, multipart, headers, etc.)
     * @return array Parsed JSON response
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        $headers = $options['headers'] ?? [];

        // Always accept JSON responses
        $headers['Accept'] = 'application/json';

        // Set JSON Content-Type unless using multipart (e.g. file uploads)
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && !isset($options['multipart'])) {
            $headers['Content-Type'] ??= 'application/json';
        }

        // Inject authentication headers
        if ($this->config->isApiKeyMode()) {
            $headers = array_merge($headers, ApiKeyAuth::headers($this->config));
        }

        if ($this->config->isLicenseMode()) {
            $headers = array_merge($headers, LicenseAuth::headers($this->config));
        }

        $domain = $this->config->get('domain');
        if (is_string($domain) && trim($domain) !== '') {
            $headers['X-Devpayr-Domain'] = trim($domain);
        }

        $options['headers'] = $headers;

        // Append `include` and `action` from config to query if set
        $query = $options['query'] ?? [];

        $globalQuery = $this->config->get('query', []);
        if (is_array($globalQuery) && !empty($globalQuery)) {
            $query = array_replace($globalQuery, $query);
        }

        if ($this->config->get('injectables') && !isset($query['include'])) {
            $query['include'] = 'injectables';
        }

        if ($this->config->get('action') && !isset($query['action'])) {
            $query['action'] = $this->config->get('action');
        }

        if ($this->config->get('per_page') && !isset($query['per_page'])) {
            $query['per_page'] = $this->config->get('per_page');
        }

        $options['query'] = $query;

        try {
            $response = $this->client->request($method, $uri, $options);

            $status = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            if ($status >= 400 || !is_array($body)) {
                throw new ApiResponseException(
                    $body['message'] ?? "API Error",
                    $status,
                    $body ?: ['raw' => $response->getBody()->getContents()]
                );
            }

            return $body;
        } catch (RequestException $e) {
            $res = $e->getResponse();
            $code = $res?->getStatusCode() ?? 500;
            $json = json_decode((string) $res?->getBody(), true);

            throw new ApiResponseException(
                $json['message'] ?? "API Request Failed",
                $code,
                $json ?: ['raw' => $res?->getBody()?->getContents()]
            );
        } catch (GuzzleException $e) {
            throw new DevPayrException("Request failed: " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Send a GET request with optional query params.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function get(string $uri, array $query = [], array $extra = []): array
    {
        return $this->request('GET', $uri, array_replace_recursive(['query' => $query], $extra));
    }

    /**
     * Send a POST request with optional JSON or multipart payload.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function post(string $uri, array $data = [], array $extra = []): array
    {
        return $this->request('POST', $uri, array_replace_recursive(['json' => $data], $extra));
    }

    /**
     * Send a PUT request with JSON body.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function put(string $uri, array $data = [], array $extra = []): array
    {
        return $this->request('PUT', $uri, array_replace_recursive(['json' => $data], $extra));
    }

    /**
     * Send a PATCH request with JSON body.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function patch(string $uri, array $data = [], array $extra = []): array
    {
        return $this->request('PATCH', $uri, array_replace_recursive(['json' => $data], $extra));
    }

    /**
     * Send a DELETE request with optional query params.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function delete(string $uri, array $query = [], array $extra = []): array
    {
        return $this->request('DELETE', $uri, array_replace_recursive(['query' => $query], $extra));
    }
}
