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

This project is not affiliated with, endorsed by, or sponsored by PrestaShop SA.

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

The GitHub source archive is not the preferred PrestaShop install artifact. Build a release ZIP that contains the `euwithdrawalbutton/` folder at the archive root.

Run Composer in the module folder before packaging:

```bash
cd euwithdrawalbutton
composer install --no-dev --prefer-dist
composer dump-autoload -o --no-dev
```

Include the generated `vendor/` folder in release ZIP packages so PrestaShop can autoload namespaced module classes.

## Release status

The stable `1.0.0` release target is official support for PrestaShop `8.0`, `8.1`, and `8.2`. PrestaShop `9.x` compatibility remains experimental until milestone `1.2`.

Do not tag `1.0.0` until install, public flow, acknowledgement email, Back Office list/detail, CSV export, resend, and uninstall checks pass on PrestaShop `8.0`, `8.1`, and `8.2`.

See `docs/RELEASE.md` in the repository root for the release checklist and packaging steps.

## Installation

Copy the `euwithdrawalbutton` folder into `modules/`, install it in Back Office, then run the compliance self-check from the module configuration page.
