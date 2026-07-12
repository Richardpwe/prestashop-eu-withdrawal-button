# Changelog

All notable changes to this project will be documented in this file.

The format follows the spirit of Keep a Changelog, and this project uses semantic versioning once the first stable release is published.

## [Unreleased]

### Added

- Release-hardening documentation for the path to `1.0.0`.
- GitHub community templates for issues and pull requests.
- Dependabot configuration for Composer and GitHub Actions.

### Changed

- Composer package metadata now uses the GitHub owner namespace.
- Maintainer metadata is consistent across Composer and the PrestaShop module class.
- Alpha test builds no longer register Back Office tabs or Symfony admin routes during installation.
- Hook registration is no longer a hard installation blocker in alpha test builds.

### Fixed

- Module installation now rolls back module data when hook or SQL setup fails.
- Uninstall cleanup removes legacy `AdminEuWithdrawalButton*` tab entries left by earlier alpha builds.
- Install SQL now uses a more conservative `utf8` charset and prefixed email index for broader MySQL/MariaDB compatibility.
- Install failures now write module-specific messages to the PrestaShop log.

## [1.0.0] - Pending

This release is not tagged yet. It will only be published after the release gates below are complete.

### Release Gates

- PrestaShop `8.0`, `8.1`, and `8.2` install/uninstall tests pass.
- Public withdrawal flow works for guest and logged-in customers.
- Acknowledgement emails include declaration content, submission date/time, and public reference.
- Back Office list/detail/resend/export workflows are verified.
- No automatic refund, cancellation, payment-state change, or return-label behavior exists by default.
- Release ZIP contains the `euwithdrawalbutton/` folder and generated production autoloader files under `vendor/`.

## [0.1.0-alpha.1] - Pending

### Added

- Initial open-source module scaffold.
- Public two-step withdrawal declaration flow.
- Guest-safe manual submission and optional verification-link flow.
- Database schema for withdrawal declarations, items, audit log, and rate limiting.
- Customer acknowledgement and admin notification mail templates in German and English.
- Back Office configuration, compliance, withdrawal list, and detail screens.
- Basic PHPUnit unit-test scaffold.
