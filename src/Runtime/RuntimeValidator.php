<?php

namespace DevPayr\Runtime;

use DevPayr\Config\Config;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Services\PaymentService;
use DevPayr\Utils\InjectableHandler;

class RuntimeValidator
{
    protected Config $config;
    protected string $license;
    protected string $cacheKey;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $license = (string) $config->get('license');
        $license = trim($license);

        if ($license === '') {
            throw new DevPayrException('License key is required for runtime validation.');
        }

        $this->license = $license;

        // Domain-aware cache key (domain can be a hostname OR a stable fingerprint)
        $domain = (string) $this->config->get('domain', '');
        $domain = trim($domain);

        if ($domain === '') {
            // Config should already resolve a fingerprint, but just in case:
            $domain = 'unknown';
        }

        $this->cacheKey = 'devpayr_' . hash('sha256', $this->license . '::' . $domain);
    }

    /**
     * Perform license validation and optionally auto-process injectables.
     *
     * @return array
     * @throws DevPayrException
     */
    public function validate(): array
    {
        if (! $this->config->get('recheck') && $this->isCached()) {
            return ['cached' => true, 'message' => 'License validated from cache'];
        }

        $response = (new PaymentService($this->config))->checkWithLicenseKey();

        if (! ($response['data']['has_paid'] ?? false)) {
            throw new DevPayrException('Project is unpaid or unauthorized.');
        }

        $this->cacheSuccess();

        // Register custom injectable processor if defined
        if ($processor = $this->config->get('injectablesProcessor')) {
            InjectableHandler::setProcessor($processor);
        }

        // Auto-process injectables if allowed
        if (
            $this->config->get('injectables') &&
            $this->config->get('handleInjectables', true) &&
            !empty($response['data']['injectables']) &&
            is_array($response['data']['injectables'])
        ) {
            $this->handleInjectables($response['data']['injectables']);
        }

        return $response;
    }

    /**
     * Process and write injectables to disk (or delegate to custom handler).
     *
     * @param array $injectables
     * @throws DevPayrException
     */
    protected function handleInjectables(array $injectables): void
    {
        InjectableHandler::process($injectables, [
            'secret' => $this->config->get('secret'),
            'path'   => $this->config->get('injectablesPath', sys_get_temp_dir()),
            'verify' => $this->config->get('injectablesVerify', true),
        ]);
    }

    /**
     * Resolve cache directory.
     * Default: project-local ".devpayr-cache" in the current working directory.
     */
    protected function resolveCacheDir(): string
    {
        $configured = (string) $this->config->get('cachePath', '');
        $configured = trim($configured);

        if ($configured !== '') {
            // If relative, resolve from cwd
            if (! $this->isAbsolutePath($configured)) {
                $configured = rtrim(getcwd() . DIRECTORY_SEPARATOR . $configured, DIRECTORY_SEPARATOR);
            }
            return rtrim($configured, DIRECTORY_SEPARATOR);
        }

        return rtrim(getcwd() . DIRECTORY_SEPARATOR . '.devpayr-cache', DIRECTORY_SEPARATOR);
    }

    /**
     * Resolve the cache file path.
     */
    protected function resolveCacheFile(): string
    {
        return $this->resolveCacheDir() . DIRECTORY_SEPARATOR . $this->cacheKey . '.txt';
    }

    /**
     * Cache success status to project-local file (YYYY-MM-DD).
     * Never throws: caching must not break runtime validation.
     */
    protected function cacheSuccess(): void
    {
        try {
            $dir = $this->resolveCacheDir();

            if (! is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $file = $this->resolveCacheFile();
            @file_put_contents($file, date('Y-m-d'));
        } catch (\Throwable $e) {
            // ignore caching failures silently
        }
    }

    /**
     * Check if the cache is still valid (based on today's date).
     */
    protected function isCached(): bool
    {
        try {
            $file = $this->resolveCacheFile();

            if (! is_file($file)) return false;

            $content = trim((string) @file_get_contents($file));
            return $content === date('Y-m-d');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if a path is absolute (Linux/Unix + Windows).
     */
    protected function isAbsolutePath(string $path): bool
    {
        // Unix absolute
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return true;
        }

        // Windows absolute: C:\ or C:/
        return (bool) preg_match('/^[A-Za-z]:[\/\\\\]/', $path);
    }
}
