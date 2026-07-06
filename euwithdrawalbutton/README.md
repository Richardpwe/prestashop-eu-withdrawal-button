# EU Withdrawal Button for PrestaShop

`euwithdrawalbutton` is an open-source PrestaShop module for the EU/German online withdrawal function (`Widerrufsbutton`).

## Compatibility target

- Version `1.0`: official support for PrestaShop `8.0`, `8.1`, and `8.2`.
- Version `1.0`: PrestaShop `9.x` compatibility is tested as experimental where possible.
- Version `1.2`: PrestaShop `9.x` becomes an official support target after route, service, grid, mail, install, and E2E hardening.
- PHP target follows PrestaShop support. The module code is written for PHP `7.4+` and avoids newer PHP-only language features.

## Design constraints

- No core overrides.
- No SaaS dependency.
- No telemetry.
- No external paid service dependency.
- No automatic order cancellation, refund, payment-state change, or return-label creation.
- Failed order matching, email mismatch, configured expiry, exclusions, or unknown order data never block a declaration.

## Legal notice

This module is a technical implementation aid. It is not legal advice. Merchants must have the public wording, email templates, retention period, privacy notice, deadline settings, exclusion settings, and operational workflow reviewed by qualified legal counsel before production use.

## Features in this implementation

- Public withdrawal entry link rendered through PrestaShop hooks.
- Guest-compatible two-step withdrawal flow.
- Final confirmation button wording: `Widerruf bestätigen`.
- Durable-medium acknowledgement email using PrestaShop mail infrastructure.
- Admin notification email.
- Structured storage of declarations, optional structured items, and audit logs.
- Back Office route skeleton for configuration, list/detail, and compliance checks.
- Data minimization defaults with optional hash-only abuse metadata.
- Retention/anonymization service.
- PrestaShop 8-first architecture with PrestaShop 9 compatibility constraints documented.

## Packaging

Run Composer in the module folder before packaging:

```bash
composer dump-autoload -o --no-dev
```

Include the generated `vendor/` folder in release ZIP packages so PrestaShop can autoload namespaced module classes.

## Installation

Copy the `euwithdrawalbutton` folder into `modules/`, install it in Back Office, then run the compliance self-check from the module configuration page.

