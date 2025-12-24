<?php

namespace DevPayr;

use DevPayr\Config\Config;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Runtime\RuntimeValidator;
use DevPayr\Services\ProjectService;
use DevPayr\Services\LicenseService;
use DevPayr\Services\DomainService;
use DevPayr\Services\InjectableService;
use DevPayr\Services\PaymentService;

/**
 * Class DevPayr
 *
 * Primary entry point to the SDK.
 * Handles runtime license validation, injectable processing, and provides access to all core services.
 */
class DevPayr
{
    /**
     * Global configuration instance shared across all services.
     */
    protected static Config $config;

    /**
     * Bootstraps the SDK: sets config, performs runtime validation (license mode), and invokes onReady.
     *
     * @param array $config Configuration options
     * @return void
     * @throws DevPayrException
     */
    public static function bootstrap(array $config): void
    {
        self::$config = new Config($config);

        $data = null;

        try {
            if (self::$config->isLicenseMode()) {
                $validator = new RuntimeValidator(self::$config);
                $data = $validator->validate();
            }

            $callback = self::$config->get('onReady');
            if (is_callable($callback)) {
                $callback($data);
            }
        } catch (DevPayrException $e) {
            self::handleFailure($e->getMessage());
        } catch (\Throwable $e) {
            self::handleFailure('Unexpected error: ' . $e->getMessage());
        }
    }

    /**
     * Handle SDK bootstrap failure or license validation failure.
     *
     * Supported modes:
     * - redirect: redirects to redirectUrl (or default upgrade URL)
     * - log: logs error message using error_log
     * - silent: no output
     * - modal: prints HTML modal/view and exits
     *
     * @param string $message The error message
     * @return void
     */
    protected static function handleFailure(string $message): void
    {
        $mode = self::$config->get('invalidBehavior', 'modal');
        $finalMessage = self::$config->get('customInvalidMessage', $message) ?? $message;

        if ($mode === 'redirect') {
            $target = self::$config->get('redirectUrl', 'https://devpayr.com/upgrade') ?? 'https://devpayr.com/upgrade';

            if (!headers_sent()) {
                header('Location: ' . $target);
                exit;
            }

            error_log('[DevPayr] Redirect failed because headers were already sent. Target: ' . $target);
            return;
        }

        if ($mode === 'log') {
            error_log('[DevPayr] Invalid license: ' . $finalMessage);
            return;
        }

        if ($mode === 'silent') {
            return;
        }

        $customView = self::$config->get('customInvalidView', null);
        $defaultPath = __DIR__ . '/resources/views/devpayr/unlicensed.html';
        $htmlPath = is_string($customView) && trim($customView) !== '' ? $customView : $defaultPath;

        $escaped = htmlspecialchars((string) $finalMessage, ENT_QUOTES, 'UTF-8');

        if (is_string($htmlPath) && file_exists($htmlPath)) {
            $html = (string) file_get_contents($htmlPath);
            echo str_replace('{{message}}', $escaped, $html);
            exit;
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<h1>Unlicensed Software</h1><p>' . $escaped . '</p>';
        exit;
    }

    /**
     * Access the current global Config instance.
     *
     * @return Config
     */
    public static function config(): Config
    {
        return self::$config;
    }

    // ------------------------------------------------------------------
    // Core Services – accessible via DevPayr::serviceName() methods
    // ------------------------------------------------------------------

    /**
     *  Project Management API (list, create, update, delete)
     *
     * @return ProjectService
     * @throws DevPayrException
     */
    public static function projects(): ProjectService
    {
        return new ProjectService(self::$config);
    }

    /**
     * License Key API (issue, revoke, validate, etc.)
     *
     * @return LicenseService
     * @throws DevPayrException
     */
    public static function licenses(): LicenseService
    {
        return new LicenseService(self::$config);
    }

    /**
     * Domain Rules API (restrict usage per domain)
     *
     * @return DomainService
     * @throws DevPayrException
     */
    public static function domains(): DomainService
    {
        return new DomainService(self::$config);
    }

    /**
     * Injectable SDK content API (manage encrypted blobs)
     *
     * @return InjectableService
     * @throws DevPayrException
     */
    public static function injectables(): InjectableService
    {
        return new InjectableService(self::$config);
    }

    /**
     * Payment Status API (check if license/project has been paid for)
     *
     * @return PaymentService
     * @throws DevPayrException
     */
    public static function payments(): PaymentService
    {
        return new PaymentService(self::$config);
    }
}
