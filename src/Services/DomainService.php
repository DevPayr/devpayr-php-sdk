<?php

namespace DevPayr\Services;

use DevPayr\Config\Config;
use DevPayr\Http\HttpClient;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Exceptions\ApiResponseException;

/**
 * Class DomainService
 *
 * Manages domains tied to a specific project (whitelisted or license-based).
 */
class DomainService
{
    protected Config $config;
    protected HttpClient $http;

    /**
     * @throws DevPayrException
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->http   = new HttpClient($config);
    }

    /**
     * List all domains under a project.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function list(string|int $projectId, array $query = []): array
    {
        return $this->http->get("project/{$projectId}/domains", $query);
    }

    /**
     * Create a domain under a project.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function create(string|int $projectId, array $data): array
    {
        return $this->http->post("project/{$projectId}/domains", $data);
    }

    /**
     * Show a specific domain entry.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function show(string|int $projectId, string|int $domainId): array
    {
        return $this->http->get("project/{$projectId}/domain/{$domainId}");
    }

    /**
     * Update a domain for a project.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function update(string|int $projectId, string|int $domainId, array $data): array
    {
        return $this->http->put("project/{$projectId}/domain/{$domainId}", $data);
    }

    /**
     * Delete a domain entry from a project.
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function delete(string|int $projectId, string|int $domainId): array
    {
        return $this->http->delete("project/{$projectId}/domains/{$domainId}");
    }
}
