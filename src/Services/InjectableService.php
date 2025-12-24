<?php

namespace DevPayr\Services;

use DevPayr\Config\Config;
use DevPayr\Http\HttpClient;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Exceptions\ApiResponseException;

/**
 * Class InjectableService
 *
 * Manages CRUD and streaming operations on injectables under a project.
 */
class InjectableService
{
    protected Config $config;
    protected HttpClient $http;

    /**
     * @throws DevPayrException
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->http = new HttpClient($config);
    }

    /**
     * List all injectables under a project.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function list(string|int $projectId, array $query = []): array
    {
        return $this->http->get("project/{$projectId}/injectables", $query);
    }

    /**
     * Create a new injectable (JSON or multipart).
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function create(string|int $projectId, array $data): array
    {
        return $this->http->post("project/{$projectId}/injectables", $data);
    }

    /**
     * Retrieve a specific injectable.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function show(string|int $projectId, string|int $injectableId): array
    {
        return $this->http->get("project/{$projectId}/injectables/{$injectableId}");
    }

    /**
     * Update an existing injectable.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function update(string|int $projectId, string|int $injectableId, array $data): array
    {
        return $this->http->put("project/{$projectId}/injectables/{$injectableId}", $data);
    }

    /**
     * Delete an injectable.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function delete(string|int $projectId, string|int $injectableId): array
    {
        return $this->http->delete("project/{$projectId}/injectables/{$injectableId}");
    }

    /**
     * Stream encrypted injectables (based on license).
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function stream(): array
    {
        return $this->http->get("injectable/stream");
    }
}
