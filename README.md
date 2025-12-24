# DevPayr PHP SDK

A lightweight and developer-friendly PHP SDK for integrating the [DevPayr](https://devpayr.com) platform — allowing you to validate license keys, fetch injectables, manage projects, control domains, enforce payments, and more.

Designed for software creators, SaaS founders, and digital product developers who want to secure and monetize their code with minimal effort.

## 🔰 Introduction

DevPayr is a modern software licensing and payment enforcement platform built to help developers protect their code, enforce payment, and control how their software is used — across PHP, JavaScript, desktop, and SaaS environments.

This SDK enables seamless integration with DevPayr directly from your PHP application. Whether you're selling a script, distributing software, or building a SaaS, this SDK gives you the tools to:

- Validate license keys and enforce runtime restrictions
- Download and decrypt injectables (e.g., encrypted config or code blobs)
- Interact with your DevPayr projects, licenses, and domains
- Enforce project payment before allowing usage

### 🚀 Why Use DevPayr SDK?

Manually enforcing license checks, domain locks, and payment validation can be error-prone, time-consuming, and easily bypassed.

The DevPayr SDK solves this with:

✅ **One-Line Bootstrapping** – Validate license, check payment, auto-handle injectables in a single call  
✅ **Secure Runtime Validation** – Block unauthorized use and unpaid projects at runtime  
✅ **Customizable Failure Modes** – Redirect, log, silently fail, or show a branded modal on license failure  
✅ **Injectable Decryption** – Distribute sensitive data like config, templates, or code snippets securely  
✅ **Developer-Friendly API** – Create, revoke, validate licenses and domains easily from your app  
✅ **Built-in Caching** – Avoid repeated license checks with intelligent cache

Whether you're distributing self-hosted scripts, WordPress plugins, SaaS dashboards, or internal tools — DevPayr SDK brings enforcement, control, and peace of mind.

### 🔧 Features

- **License Verification**  
  Securely validate license keys issued via DevPayr API.

- **Payment Enforcement**  
  Block access to features or full app when payment is not completed.

- **Injectables Support**  
  Automatically download, decrypt, and store encrypted files (templates, configs, snippets) tied to the license.

- **Domain Restriction**  
  Verify if a request or instance is coming from an approved domain or subdomain.

- **Runtime Enforcement**  
  Stop bootstrapping or execution on invalid/unpaid licenses with flexible fallback strategies (modal, redirect, log, silent).

- **Extensible Processor Hooks**  
  Customize how injectables are handled via your own class (e.g., save to S3, memory, etc).

- **Lightweight & Framework-Agnostic**  
  Built in pure PHP without framework dependencies. Works in Laravel, WordPress, or any PHP app.

- **Fully Typed & Exception-Driven**  
  Catch invalid responses, network failures, and API violations with structured exceptions.

- **Built-in Caching**  
  Automatically caches daily validation results in the OS temp folder to improve performance.

- **Custom Messaging & Views**  
  Show your own branded modal or custom message when a license is invalid.

### 📦 Installation

#### Install via Composer

To install the DevPayr PHP SDK in your project, run the following command:

```bash
composer require xultech/devpayr-php-sdk
```
This will pull in the latest version of the SDK from Packagist and make it available for use in your PHP application.
#### 📋 Minimum Requirements

Before installing the SDK, ensure your environment meets the following requirements:

- PHP **8.1** or higher
- Composer **2.0+**
- **OpenSSL** extension enabled (used for secure decryption)
- Either `cURL` **or** `allow_url_fopen` (for HTTP requests)

## ⚡ Quick Start

The DevPayr PHP SDK is designed for simple drop-in usage.

Below is a minimal working example that:

1. Validates the license
2. Checks if the project is paid
3. Fetches and handles any associated injectables

```php
<?php

require_once 'vendor/autoload.php';

use DevPayr\DevPayr;
use DevPayr\Exceptions\DevPayrException;

try {
    DevPayr::bootstrap([
        'license'        => 'YOUR-LICENSE-KEY',
        'injectables'    => true,
        'invalidBehavior'=> 'modal', // Options: modal, redirect, silent, log
    ]);
    
    // Your protected logic continues here...
    echo "License valid. Project is paid.";
} catch (DevPayrException $e) {
    // Handle critical exceptions
    echo " DevPayr error: " . $e->getMessage();
}
```

> This will automatically:
> - Validate the license
> - Check for active payment
> - Download and decrypt injectables (if enabled)
> - Handle failures based on your configuration

## 🛠 Configuration Options

The `DevPayr::bootstrap()` method accepts a flexible configuration array that tailors how validation, payment enforcement, and injectables are handled.

### ✅ Required Keys

| Key        | Type   | Description                                                                             |
|------------|--------|-----------------------------------------------------------------------------------------|
| `base_url` | string | The base API URL (defaults to `https://api.devpayr.com/api/v1/`)                        |
| `secret`   | string | The secret key used to decrypt injectables (this is created when you add an injectable) |

---

### 🧰 Optional Keys (with Defaults)

| Key                      | Type     | Default       | Description |
|---------------------------|----------|---------------|-------------|
| `recheck`                 | bool     | `true`        | Revalidate license on every load (false enables caching) |
| `injectables`            | bool     | `true`        | Whether to fetch injectables from the server |
| `injectablesVerify`      | bool     | `true`        | Verify HMAC signature on injectables |
| `injectablesPath`        | string\|null | `null`     | Directory where injectables are saved (default: system temp path) |
| `invalidBehavior`        | string   | `'modal'`     | Behavior on invalid license: `modal`, `redirect`, `log`, `silent` |
| `redirectUrl`            | string\|null | `null`     | Redirect URL on invalid license (used when `invalidBehavior = redirect`) |
| `timeout`                | int      | `1000`        | Request timeout in milliseconds |
| `action`                 | string   | `'check_project'` | Action passed to DevPayr (for tracking/analytics) |
| `onReady`                | callable\|null | `null`   | Callback function executed on successful license validation |
| `handleInjectables`      | bool     | `false`       | If `true`, SDK will auto-process injectables into your file system |
| `injectablesProcessor`   | string\|null | `null`     | Fully qualified class name to handle injectable processing manually |
| `customInvalidView`      | string\|null | `null`     | Absolute path to your custom HTML view to show on license failure |
| `customInvalidMessage`   | string   | `'This copy is not licensed for production use.'` | Message displayed on failure when no view is provided |
| `license`                | string\|null | `null`     | License key for project-scoped validation |
| `api_key`                | string\|null | `null`     | API key (global or project scoped) for backend operations |
| `per_page`               | int\|null | `null`       | Number of items to return for paginated results (used in services) |

---

> 🔒 You only need to set what's relevant to your use-case. Defaults will handle most basic setups.

## ⚙️ Runtime Boot & Validation

The SDK provides a simple bootstrapping method to handle license validation, payment enforcement, and injectable fetching in **one unified call**.

### ✅ `DevPayr::bootstrap(array $config): void`

This is the recommended entry point. It performs:

1. **License Key or API Key Detection** (if not explicitly set)
2. **Domain Validation** (if enforced via DevPayr)
3. **Project Payment Check** 
4. **Injectables Retrieval & (Optional) Processing**
5. **Runtime Enforcement** based on your `invalidBehavior`

### 📦 Example Usage

```php
use DevPayr\DevPayr;

DevPayr::bootstrap([
    'license' => $_ENV['LICENSE_KEY'],
    'secret' => $_ENV['LICENSE_KEY'],
    'injectablesPath' => __DIR__ . '/injectables',
    'handleInjectables' => true,
    'invalidBehavior' => 'modal', // or 'redirect', 'log', 'silent'
    'customInvalidMessage' => 'Your license is invalid or has expired.',
]);
```
> This will block further execution automatically if the license is invalid or payment is not confirmed.

### 🔒 License or API Key
> DevPayr will always utilize license key when validating a project; however, for every other actions, the API Key would be required.
> Consult our Documentation for more information about this - [DevPayr Doc](https://docs.devpayr.com)

## 📥 Injectables Handling

Injectables are encrypted files or snippets tied to a project or license, securely managed through the DevPayr platform. These could include:

- Config files
- HTML templates
- Scripts or code snippets
- Markdown docs
- JSON configs
- Custom logic modules

The SDK automatically fetches injectables during `DevPayr::bootstrap()` **if** the `injectables` option is `true`.

### 🧩 How It Works

When bootstrapping:

1. The SDK requests injectables from DevPayr.
2. They are decrypted using your provided `secret` key.
3. If `handleInjectables` is enabled, each injectable is **written to the target location** based on its `target_path`, `slug`, and `mode`.

You may choose to either:

- Let the SDK **auto-handle** them (`handleInjectables => true`)
- Or define your own logic using a **custom processor class**

---

### ⚙️ Modes Supported

Each injectable supports a `mode`, which determines how it’s written:

| Mode          | Description                                 |
|---------------|---------------------------------------------|
| `replace`     | Replaces the entire file with new content   |
| `append`      | Appends to the end of the target file       |
| `prepend`     | Prepends to the beginning of the file       |
| `inject`      | (Reserved for future support: marker-based) |
| `inline_render`| Intended for rendering, not saving         |
| `stream`      | Stream to output, not saved                 |
| Others        | Default to `replace`                        |

---

### 📂 File Types

Injectables support multiple types including:

- `file`
- `snippet`
- `html`, `markdown`, `css`, `json`, `config`
- `sdk_module`, `template`, `docs`, `component`, etc.

> Only `file` types support actual file uploads. Others are content-based.

---

### 🛠 Custom Injectable Processor (Optional)

> If you need to control how injectables are stored (e.g. save to DB, S3, cache, etc), define a custom processor class, which 
> implements the `InjectableProcessorInterface` contract

```php
class MyCustomProcessor {
    public static function handle(array $injectable, string $secret, string $basePath, bool $verify = true): string
    {
        // Decrypt and save however you want
        $decrypted = CryptoHelper::decrypt($injectable['encrypted_content'], $secret);
        // Example: save to cache or custom location
        return '/custom/storage/path/' . $injectable['slug'];
    }
}
```
Then reference it in config:

```php
DevPayr::bootstrap([
    'secret' => $key,
    'injectablesProcessor' => MyCustomProcessor::class,
    'handleInjectables' => true
]);
```

## 🔐 Handling License Failure

When a license is invalid, expired, unverified, or the associated project has not paid, the SDK halts normal execution and responds according to the `invalidBehavior` config option.

You can choose what happens in such scenarios using:

```php
'invalidBehavior' => 'modal' // or: 'redirect', 'log', 'silent'
```

### 🎭 Supported Behaviors

| Behavior   | Description                                                 |
| ---------- | ----------------------------------------------------------- |
| `modal`    | Displays a branded HTML message or modal (default)          |
| `redirect` | Redirects the user to a custom URL                          |
| `log`      | Logs the failure to `error_log()` or stdout (if applicable) |
| `silent`   | Fails silently — no output, just skips execution            |

### 🧩 Additional Options
> You can configure fallback behaviors further:

| Config Key             | Description                                                                   |
| ---------------------- | ----------------------------------------------------------------------------- |
| `redirectUrl`          | Where to redirect when behavior is `redirect`                                 |
| `customInvalidMessage` | Message to display when using the default `modal` view                        |
| `customInvalidView`    | Path to your own custom HTML to show instead of the default `unlicensed.html` |

Example:

```php
DevPayr::bootstrap([
    'secret' => $secret,
    'license' => $license,
    'invalidBehavior' => 'redirect',
    'redirectUrl' => 'https://yourdomain.com/license-invalid',
]);
```
### 💡 Pro Tip

You can also define an `onReady()` callback that will only run if validation **passes**:

```php
DevPayr::bootstrap([
    'secret' => $secret,
    'license' => $license,
    'onReady' => function ($response) {
        // License valid — proceed with boot logic
        echo "✅ Welcome, licensed user!";
    },
]);
```

## 🧰 Available Services

DevPayr provides dedicated service classes for working with various parts of your licensing system — including projects, licenses, domains, injectables, and payment verification.

Each service class can be used independently or accessed via the `DevPayr::` static methods after `bootstrap()`.

### ✅ Accessing Services After Bootstrapping

Once you've bootstrapped with a valid configuration:

```php
use DevPayr\DevPayr;

DevPayr::bootstrap([
    'license' => 'your-license-key',
    'secret' => 'your-decryption-secret',
]);

$projects = DevPayr::projects();
$licenses = DevPayr::licenses();
$domains = DevPayr::domains();
$injectables = DevPayr::injectables();
$payments = DevPayr::payments();
```
> Each of these returns a strongly typed service class:

| Service                  | Class               | Description                                   |
| ------------------------ | ------------------- | --------------------------------------------- |
| `DevPayr::projects()`    | `ProjectService`    | Manage projects: list, create, update, delete |
| `DevPayr::licenses()`    | `LicenseService`    | Create, revoke, or validate license keys      |
| `DevPayr::domains()`     | `DomainService`     | Manage domain restrictions for your projects  |
| `DevPayr::injectables()` | `InjectableService` | Fetch and manage encrypted injectables        |
| `DevPayr::payments()`    | `PaymentService`    | Check payment status for licenses or projects |

> 🧠 These services are authenticated using the same config object passed to DevPayr::bootstrap(). You can also instantiate them manually if needed.

```php
use DevPayr\Services\LicenseService;
use DevPayr\Config\Config;

$config = new Config([...]);
$licenseService = new LicenseService($config);
```

## 📦 Service API Examples

Below are real-world examples of how to use each core service within the DevPayr SDK to interact with your projects, licenses, domains, injectables, and payment status.

---

### 🔹 ProjectService

```php
$projectService = DevPayr::projects($config[
    'per_page' =>50
]);

// List all projects
$projects = $projectService->list();

// Create a new project
$created = $projectService->create([
    'name' => 'My Awesome App',
]);

// Update a project
$projectService->update($projectId, [
    'name' => 'Updated Name',
]);

// Get a project
$projectService->get($projectId);

// Delete a project
$projectService->delete($projectId);
```
### 🔹 LicenseService

```php
$licenseService = DevPayr::licenses($config);

// Issue a new license
$newLicense = $licenseService->create([
    'project_id' => $projectId,
    'expires_at' => '2025-12-31',
]);

// Delete an existing license
$licenseService->delete($projectId, $licenseId);

// Revoke a license
$licenseService->revoke($projectId, $licenseId);

// Reactivate a license
$licenseService->reactivate($projectId, $licenseId);

// Get a license
$licenseService->show($projectId, $licenseId);

// List all project's license
$licenseService->list($projectId);
```

### 🔹 DomainService
```php
$domainService = DevPayr::domains();

// List allowed domains for a project
$domains = $domainService->list($projectId);

// Get an allowed domain for a project
$domains = $domainService->show($projectId, $domainId);

// Add a domain
$domainService->create($projectId, [
    'domain' => 'example.com',
]);

// Update a domain
$domainService->create($projectId, $domainId, [
    'domain' => 'example.com',
]);

// Delete a domain
$domainService->delete($domainId);
```

### 🔹 InjectableService

```php
$injectableService = DevPayr::injectables();

// List injectables for a project
$injectables = $injectableService->list($projectId);

// Get an injectables for a project
$injectables = $injectableService->list($projectId, $injectableId);

// Create a new injectable
$injectableService->create($projectId, [
    'slug' => 'header-snippet',
    'content' => '<?= "Hello World"; ?>',
    'type' => 'snippet',
    'mode' => 'replace',
    'target_path' => 'partials/',
]);

// Update an injectable
$injectableService->create($projectId, $injectableId, [
    'slug' => 'header-snippet',
    'content' => '<?= "Hello World"; ?>',
    'type' => 'snippet',
    'mode' => 'replace',
    'target_path' => 'partials/',
]);

// Delete an injectable
$injectableService->delete($injectableId);
```

### 🔹 PaymentService

```php
$paymentService = DevPayr::payments($config);

// Check if a license has been paid for
$paid = $paymentService->checkWithLicenseKey([
    'license' => 'YOUR-LICENSE-KEY',
]);

// Check if a license has been paid for
$paid = $paymentService->checkWithApiKey($projectId);

if (!$paid) {
    echo "⚠️ Payment required to continue.";
}
```
> These examples show how you can build powerful integrations and admin 
> tools around your software licensing — right from your own dashboard or script.

## 🧩 Custom Injectable Processor
DevPayr allows you to define your own injectable processor — so you can decide what to do with each decrypted file (e.g. store in memory, write to disk, upload to cloud).

Instead of using the default file-based processor, you can implement the following interface:
```php
namespace DevPayr\Contracts;

/**
 * Interface InjectableProcessorInterface
 *
 * Allows for custom processing of SDK injectables (e.g. decrypt, verify, and save elsewhere).
 */
interface InjectableProcessorInterface
{
    /**
     * Handle a single injectable payload.
     *
     * @param array $injectable Raw injectable data from API
     * @param string $secret Shared secret (typically the license key)
     * @param string $basePath Base path for writing (if file-based)
     * @param bool $verifySignature Whether to verify HMAC signature
     *
     * @return string Path or identifier of the saved injectable
     */
    public static function handle(array $injectable, string $secret, string $basePath, bool $verifySignature = true): string;
}
```
#### 🧱 Example: Create Your Custom Processor
```php
namespace App\Licensing;

use DevPayr\Contracts\InjectableProcessorInterface;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Support\CryptoHelper;

class CustomInjectableHandler implements InjectableProcessorInterface
{
    public static function handle(array $injectable, string $secret, string $basePath, bool $verifySignature = true): string
    {
        $slug = $injectable['slug'];
        $encrypted = $injectable['encrypted_content'] ?? $injectable['content'];
        $decrypted = CryptoHelper::decrypt($encrypted, $secret);

        // ✅ Save to a database, Redis, S3, or custom location
        // Example: Save to temporary file
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "{$slug}.tmp";

        if (file_put_contents($path, $decrypted) === false) {
            throw new DevPayrException("Failed to store injectable: {$slug}");
        }

        return $path;
    }
}
```
#### ⚙️ Registering Your Processor
Pass your class via the `injectablesProcessor` config key:
```php
DevPayr::bootstrap([
    'secret' => $secret,
    'license' => $license,
    'handleInjectables' => true,
    'injectablesProcessor' => \App\Licensing\CustomInjectableHandler::class,
]);
```
### 💡 Use Cases

Here are some ways you can use a **custom injectable processor**:

- ✅ Save injectables to memory or cache (e.g. **Redis**, **Memcached**)
- ☁️ Upload injectables to **cloud storage** (e.g. **AWS S3**, **Dropbox**)
- 🔄 Dynamically inject content into **application runtime**
- 🖼️ Render injectables directly in **views** or **templates**

> 🔄 **Note:** This method is invoked *per injectable*, allowing you to handle each file individually and flexibly.

## ❗ Error Handling

The DevPayr PHP SDK uses structured exceptions to help you handle issues like license failure, API errors, and decryption problems gracefully.

### 🔹 Common Exceptions

| Exception                  | Description                                                                 |
|---------------------------|-----------------------------------------------------------------------------|
| `DevPayrException`        | Base exception for all SDK-related logic or configuration errors            |
| `ApiResponseException`    | Thrown when the DevPayr API returns an error, bad payload, or HTTP failure  |

> ✅ Both exceptions implement standard PHP `\Throwable`, so you can catch them with `try/catch`.

---

### 🔸 Example: Catching Runtime Errors

```php
use DevPayr\DevPayr;
use DevPayr\Exceptions\DevPayrException;
use DevPayr\Exceptions\ApiResponseException;

try {
    DevPayr::bootstrap([
        'license' => 'my-license-key',
        'secret' => 'my-secret-key',
    ]);
} catch (ApiResponseException $e) {
    // API responded with an error (e.g. invalid license, quota exceeded)
    echo "API Error: " . $e->getMessage();
} catch (DevPayrException $e) {
    // SDK misconfigured or failed to decrypt/process injectables
    echo "SDK Error: " . $e->getMessage();
}
```

## ⚙️ Advanced Usage

For developers building more dynamic or complex applications, the DevPayr PHP SDK provides several advanced options for customization and integration.

---

### 🔁 Enable/Disable Revalidation

By default, DevPayr caches validation results for a short period (using your OS temp folder) to avoid repeated network requests.

You can disable rechecking and force revalidation on every boot:

```php
DevPayr::bootstrap([
    'license'  => 'YOUR-LICENSE-KEY',
    'secret'   => 'YOUR-SECRET',
    'recheck'  => true, // ← Set false to use cache only
]);
```
### 📦 Custom Injectables Folder
By default, injectables are saved to the system temp folder. You can change where they’re stored:

```php
DevPayr::bootstrap([
    'secret'           => $secret,
    'license'          => $license,
    'injectablesPath'  => __DIR__ . '/config/devpayr/',
]);
```
### 🧩 Handle Injectables Yourself
If you want full control over how injectables are stored or interpreted:
```php
DevPayr::bootstrap([
    'secret'              => $secret,
    'license'             => $license,
    'handleInjectables'   => true,
    'injectablesProcessor'=> \App\Licensing\CustomHandler::class,
]);
```
> 💡 **See** [Custom Injectable Processor](#custom-injectable-processor) **for how to define your own handler.**

### 🧠 Runtime Callback (onReady)
Run custom logic only if validation succeeds:

```php
DevPayr::bootstrap([
    'secret'   => $secret,
    'license'  => $license,
    'onReady'  => function ($response) {
        // Your app boot logic
    },
]);
```
### 🔐 License Key vs API Key
> 🔐 You can use either:

- **`license`**: Runtime key for validation and injectables (best for public or distributed software).
- **`api_key`**: Authenticated API access (for listing, creating, managing resources via backend).

✅ Both can be used separately or together, depending on your need .


## 🤝 Contributing

We welcome contributions to the **DevPayr PHP SDK**! Whether you want to fix a bug, add a feature, improve documentation, or share feedback — your input is valuable and appreciated.

### 🛠 How to Contribute

1. **Fork the Repository**

   Start by clicking the "Fork" button on GitHub and clone your forked repository:

```bash
   git clone https://github.com/your-username/devpayr-php-sdk.git
   cd devpayr-php-sdk
  ```
2. **Install Dependencies**
   Make sure you have [Composer](https://getcomposer.org/) installed, then run:

```bash
composer install
```
3. **Create a New Branch**

Use a descriptive name for your branch based on the type of contribution:

```bash
git checkout -b fix-invalid-cache-handling
```
4.  **Make Your Changes**

Write clear, concise, and well-documented code. Please ensure the following:

- ✅ Follow the **PSR-12** coding standard.
- 📝 Include **inline comments** where appropriate to explain non-obvious logic.
- 📁 Maintain the **existing file structure and naming conventions** used in the SDK.
- 🔁 If modifying existing logic, ensure **backward compatibility** unless explicitly intended.

5. **Test Your Changes**

If your change is code-related, add tests if possible and run:

```bash
composer test
```

6. **Commit and Push**

Use meaningful commit messages:

```bash
git add .
git commit -m "🐛 Fix cache invalidation issue on license bootstrap"
git push origin fix-invalid-cache-handling
```

7. **Open a Pull Request**

Once you're satisfied with your changes, go to your fork on GitHub and click **"Compare & pull request"**. Please include:

- ✅ A clear title and description of what you changed
- 🔗 Link to any related issues or discussions
- 📌 Any important notes for reviewers (e.g., areas to focus on)

---

### 🧪 Guidelines

- 📝 Write descriptive and clear commit messages
- 🧩 Keep pull requests focused — if you’re making multiple unrelated changes, consider **splitting them into separate PRs**
- 🧪 Include tests or test instructions where applicable
- 🚫 Avoid breaking backward compatibility unless absolutely necessary — and justify it in your PR

---

### 💬 Need Help?

If you're unsure how or what to contribute:

- Browse the [issues page](https://github.com/xultech/devpayr-php-sdk/issues) for `help-wanted` tags
- Reach out via **support@devpayr.com**
- Or open a **GitHub discussion** to chat with the maintainers
- You can also suggest documentation improvements or ideas — even if you're not a coder!

---

### 🛡 Code of Conduct

We follow the [Contributor Covenant](https://www.contributor-covenant.org/) Code of Conduct. By participating, you agree to:

- Treat everyone with respect and empathy
- Foster an inclusive and supportive environment
- Help keep this project a welcoming space for all

### 🔐 Security

We take the security of DevPayr and its SDKs seriously.

If you discover any security vulnerability, please **do not disclose it publicly**. Instead, follow responsible disclosure by reporting it directly to us.

#### 📬 Report a Vulnerability

- Email: **security@devpayr.com**
- Subject: `[Security Report] DevPayr PHP SDK`
- Include as much detail as possible:
  - Description of the vulnerability
  - Steps to reproduce
  - Potential impact
  - Suggested remediation (if any)

We will respond promptly to investigate, validate, and address the issue.

#### 🔐 Responsible Disclosure

We appreciate ethical researchers and developers who help make the platform safer for everyone. Your report will be treated with respect, and we may acknowledge your contribution (unless you prefer otherwise).

Thank you for helping keep DevPayr secure.