# Laravel Command Assistant

A secure Laravel plugin that allows GPT-based interfaces to execute **approved artisan commands only**. All commands must define the `--laravel-assistant` option to be eligible.

---

## ✨ Features

- ✅ Restricts GPT to safe, whitelisted commands
- ✅ Only runs commands explicitly designed for GPT usage
- ✅ Logs all executions with user/email
- ✅ Supports GPT plugin metadata files for `.well-known`
- ✅ No risk of overwriting host Laravel config or routes

---

## 🚀 Installation

In your Laravel project:

```bash
composer require nikolaynesov/laravel-command-assistant
```

---

## 🛠️ Publish Assets

To publish the config file and plugin metadata:

```bash
php artisan vendor:publish --tag=laravel-command-assistant
```

This will publish:

| From (package)                                                         | To (your app)                                                              |
|------------------------------------------------------------------------|----------------------------------------------------------------------------|
| `config/command-assistant.php`                                        | `config/command-assistant.php`                                            |
| `public/.well-known/ai-plugin.command-assistant.json`                 | `public/vendor/laravel-command-assistant/.well-known/ai-plugin.json`      |
| `public/.well-known/openapi.command-assistant.yaml`                   | `public/vendor/laravel-command-assistant/.well-known/openapi.yaml`        |

---

## 📁 How to Enable GPT Plugin

To make your assistant plugin work with GPT:

1. Create a `.well-known/` folder in your app’s `public/` directory if it doesn't exist.

2. Copy files from the published vendor directory:

```bash
cp public/vendor/laravel-command-assistant/.well-known/* public/.well-known/
```

> ✅ This avoids overwriting any `.well-known` files from other plugins you may already have.

3. Your plugin should now be reachable at:

```
https://your-domain.com/.well-known/ai-plugin.json
https://your-domain.com/.well-known/openapi.yaml
```

---

## ✏️ Making Commands GPT-Safe

To allow a Laravel command to be executed via the assistant, define this option:

```php
protected $signature = 'your:command {--laravel-assistant}';
```

Or, using Symfony input directly:

```php
$this->addOption('laravel-assistant', null, InputOption::VALUE_NONE, 'Assistant execution enabled');
```

> 🧠 The flag does nothing functionally — it’s a safety requirement for access.

---

## 🔐 Security

- Requires valid Bearer token via `Authorization: Bearer <key>`
- Command is rejected unless it:
  - Exists in the app
  - Defines the `--laravel-assistant` option

---

## 📝 License

MIT © [Nikolay Nesov](https://github.com/nikolaynesov)