<?php

namespace DevPayr\Config;

use DevPayr\Exceptions\DevPayrException;

class Config
{
    protected array $config;
    protected array $required = [
        'secret'  // your secret key used for encryption of injectables
    ];
    protected array $defaults = [
        'base_url'           =>'https://api.devpayr.dev/api/v1/',
        'recheck'            => true,   // Use cache or always revalidate
        'injectables'        => true,    // Fetch & save injectables - if false, injectables will not be returned
        'injectablesVerify'  => true,   // HMAC signature check - should we verify injectable signature
        'injectablesPath'    => null,       // Base Path to inject the injectables - if null, we will utilize system path
        'invalidBehavior'    => 'modal',     // log | modal | redirect | silent
        'redirectUrl'        => null,       // Url to redirect on failure
        'timeout'            => 1000,       // Optional: request timeout in ms
        'action'             => 'check_project',  // Optional action - check official documentation docs.devpayr.com
        'onReady'            => null,   // call back function on success - you will receive successful response here
        'handleInjectables'  => false,  // true | false -- when true, SDK auto-processes the injectables
        'injectablesProcessor'=> null,  // your class which processes the injectables,
        'customInvalidView'  => null,  // optional
        'customInvalidMessage'=> 'This copy is not licensed for production use.',  // optional
        'license'            => null,
        'api_key'            => null,
        'per_page'           => null, // number of list to return
        'domain'             => null, // optional: preferred; sent as X-Devpayr-Domain
        'cachePath'          => null,
        'query'              => [],
    ];

    public function __construct(array $userConfig)
    {
        $this->config = array_merge($this->defaults, $userConfig);

        if (! isset($this->config['license']) && ! isset($this->config['api_key'])) {
            throw new DevPayrException('Either "license" or "api_key" must be provided in configuration.');
        }

        foreach ($this->required as $field) {
            if (empty($this->config[$field])) {
                throw new DevPayrException("Missing required config field: {$field}");
            }
        }

        // Normalize trailing slash in base_url
        $this->config['base_url'] = rtrim($this->config['base_url'], '/') . '/';
        $this->config['domain'] = $this->resolveDomain($this->config);

    }

    /**
     * Get a config value with optional default fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get full config array (e.g., to pass to other internal classes).
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Check if a config key is truthy.
     */
    public function isEnabled(string $key): bool
    {
        return !empty($this->config[$key]);
    }

    /**
     * Determine if license mode is enabled.
     */
    public function isLicenseMode(): bool
    {
        return isset($this->config['license']) && !empty($this->config['license']);
    }

    /**
     * Determine if API key mode is enabled.
     */
    public function isApiKeyMode(): bool
    {
        return isset($this->config['api_key']) && !empty($this->config['api_key']);
    }

    /**
     * Get the auth credential (license or api_key).
     */
    public function getAuthCredential(): string
    {
        return $this->config['license'] ?? $this->config['api_key'];
    }

    /**
     * Resolve domain to use for DevPayr domain-aware checks.
     * Priority:
     * 1) User-supplied config['domain']
     * 2) SERVER host headers (HTTP_HOST / SERVER_NAME)
     * 3) APP_URL (if present)
     * 4) Stable fingerprint stored in project cache folder
     */
    protected function resolveDomain(array $config): string
    {
        $userDomain = isset($config['domain']) ? trim((string) $config['domain']) : '';
        if ($userDomain !== '') {
            $normalized = $this->normalizeDomain($userDomain);
            if ($normalized !== null) return $normalized;
        }

        $host = '';
        if (!empty($_SERVER['HTTP_X_DEVPAYR_DOMAIN'])) {
            $host = (string) $_SERVER['HTTP_X_DEVPAYR_DOMAIN'];
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $host = (string) $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = (string) $_SERVER['SERVER_NAME'];
        }

        $host = trim($host);
        if ($host !== '') {
            $normalized = $this->normalizeDomain($host);
            if ($normalized !== null) return $normalized;
        }

        // Optional: try APP_URL (common in frameworks)
        $appUrl = getenv('APP_URL') ?: '';
        if (is_string($appUrl) && trim($appUrl) !== '') {
            $normalized = $this->normalizeDomain($appUrl);
            if ($normalized !== null) return $normalized;
        }

        // fallback: stable fingerprint
        return $this->getOrCreateFingerprint($config);
    }

    /**
     * Normalize domain:
     * - strips scheme, ports, paths
     * - lowercases
     * - returns null if invalid
     */
    protected function normalizeDomain(string $value): ?string
    {
        $value = trim($value);

        // If it's a URL, parse host out
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $host = parse_url($value, PHP_URL_HOST);
            if (!is_string($host) || trim($host) === '') {
                return null;
            }
            $value = $host;
        }

        // remove port if present
        $value = preg_replace('/:\d+$/', '', $value) ?? $value;

        $value = strtolower(trim($value));

        // Very small sanity: allow localhost and normal hostnames
        if ($value === 'localhost' || filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }

        // Validate domain-ish hostnames
        $isValid = (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $value);
        return $isValid ? $value : null;
    }

    /**
     * Where cache lives. Default: project-local ".devpayr-cache"
     */
    protected function resolveCacheDir(array $config): string
    {
        $configured = isset($config['cachePath']) ? trim((string) $config['cachePath']) : '';

        if ($configured !== '') {
            if (!str_starts_with($configured, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:[\/\\\\]/', $configured)) {
                // relative path -> resolve from cwd
                return rtrim(getcwd() . DIRECTORY_SEPARATOR . $configured, DIRECTORY_SEPARATOR);
            }
            return rtrim($configured, DIRECTORY_SEPARATOR);
        }

        return rtrim(getcwd() . DIRECTORY_SEPARATOR . '.devpayr-cache', DIRECTORY_SEPARATOR);
    }

    /**
     * Create/reuse a stable fingerprint. Stored on disk so it persists across restarts.
     */
    protected function getOrCreateFingerprint(array $config): string
    {
        $dir = $this->resolveCacheDir($config);

        // make directory if possible (never crash if cannot)
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $file = $dir . DIRECTORY_SEPARATOR . 'fingerprint.txt';

        if (is_file($file)) {
            $existing = trim((string) @file_get_contents($file));
            if ($existing !== '') return $existing;
        }

        // Generate stable-ish fingerprint (random once, then persisted)
        $fp = 'fp_' . bin2hex(random_bytes(16));

        @file_put_contents($file, $fp);

        return $fp;
    }

}
