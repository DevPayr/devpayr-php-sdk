<?php

namespace DevPayr\Services;

use DevPayr\Config\Config;
use DevPayr\Http\HttpClient;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Exceptions\ApiResponseException;

/**
 * Class LicenseService
 *
 * Manages license operations scoped to a project.
 * Includes issuing, showing, revoking, reactivating, and deleting licenses.
 */
class LicenseService
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
     * List all licenses for a given project
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function list(string|int $projectId, array $query = []): array
    {
        return $this->http->get("project/{$projectId}/licenses", $query);
    }

    /**
     * Show a specific license record
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function show(string|int $projectId, string|int $licenseId): array
    {
        return $this->http->get("project/{$projectId}/licenses/{$licenseId}");
    }

    /**
     * Create a license for a given project
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function create(string|int $projectId, array $data): array
    {
        return $this->http->post("project/{$projectId}/licenses", $data);
    }

    /**
     * Revoke a license
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function revoke(string|int $projectId, string|int $licenseId): array
    {
        return $this->http->post("project/{$projectId}/licenses/{$licenseId}/revoke");
    }

    /**
     * Reactivate a revoked license
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function reactivate(string|int $projectId, string|int $licenseId): array
    {
        return $this->http->post("project/{$projectId}/licenses/{$licenseId}/reactivate");
    }

    /**
     * Delete a license
     *
     * @throws ApiResponseException|DevPayrException
     */
    public function delete(string|int $projectId, string|int $licenseId): array
    {
        return $this->http->delete("project/{$projectId}/licenses/{$licenseId}");
    }
}
