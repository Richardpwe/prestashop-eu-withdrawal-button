# Contributing

Thanks for your interest in improving EU Withdrawal Button for PrestaShop.

This project is in pre-`1.0.0` release hardening. Please keep changes focused, documented, and compatible with the `1.0.0` support target: PrestaShop 8.0, 8.1, and 8.2 first, with PrestaShop 9.x compatibility considered experimental until milestone `1.2`.

## Before Opening An Issue

- Search existing issues first.
- For security vulnerabilities, do not open a public issue. Follow `SECURITY.md` instead.
- For legal interpretation questions, use normal issues only for implementation discussion. This project does not provide legal advice.

## Bug Reports

Please include:

- Module version or commit hash
- PrestaShop version
- PHP version
- Theme or relevant module overrides, if any
- Steps to reproduce
- Expected behavior
- Actual behavior
- Logs or screenshots, if helpful

Avoid posting personal customer data, order data, email addresses, access tokens, or shop credentials.

## Pull Requests

- Keep pull requests small and focused.
- Preserve the no-SaaS, no-telemetry, no-core-overrides design.
- Do not add automatic order cancellation, refunds, payment-state changes, or return-label creation without an explicit feature discussion first.
- Add or update tests when behavior changes.
- Update README or Back Office help text when merchant-facing behavior changes.
- Keep compatibility with PHP 7.4+ unless the supported PrestaShop matrix changes.

## Local Checks

Run these from the module folder when PHP and Composer are available:

```bash
composer install
vendor/bin/phpunit
```

Before packaging a release:

```bash
composer install --no-dev --prefer-dist
composer dump-autoload -o --no-dev
```

The release ZIP must contain the `euwithdrawalbutton/` module folder and the generated production autoloader files under `vendor/`.
