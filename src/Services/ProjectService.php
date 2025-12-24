<?php

namespace DevPayr\Services;

use DevPayr\Config\Config;
use DevPayr\Http\HttpClient;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Exceptions\ApiResponseException;

/**
 * Class ProjectService
 *
 * Handles project-related endpoints:
 * - create
 * - update
 * - delete
 * - get
 * - list
 */
class ProjectService
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
     * Create a new project
     *
     * @param array $data
     * @return array
     * @throws ApiResponseException|DevPayrException
     */
    public function create(array $data): array
    {
        return $this->http->post('project', $data);
    }

    /**
     * Update an existing project
     *
     * @param string|int $projectId
     * @param array $data
     * @return array
     * @throws ApiResponseException|DevPayrException
     */
    public function update(string|int $projectId, array $data): array
    {
        return $this->http->put("project/{$projectId}", $data);
    }

    /**
     * Delete a project
     *
     * @param string|int $projectId
     * @return array
     * @throws ApiResponseException|DevPayrException
     */
    public function delete(string|int $projectId): array
    {
        return $this->http->delete("project/{$projectId}");
    }

    /**
     * Retrieve a single project by ID
     *
     * @param string|int $projectId
     * @return array
     * @throws ApiResponseException|DevPayrException
     */
    public function get(string|int $projectId): array
    {
        return $this->http->get("project/{$projectId}");
    }

    /**
     * List all projects available to the current API key
     *
     * @return array
     * @throws ApiResponseException|DevPayrException
     */
    public function list(array $query = []): array
    {
        return $this->http->get("projects", $query);
    }
}
