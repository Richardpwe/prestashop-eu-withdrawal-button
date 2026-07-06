# Release Checklist

This checklist defines the release path for EU Withdrawal Button for PrestaShop.

## Pre-Release Hardening

- Keep `euwithdrawalbutton/euwithdrawalbutton.php` at `0.1.0` until the PrestaShop 8.0/8.1/8.2 test matrix is complete.
- Use GitHub pre-releases for `0.x` builds.
- Do not advertise PrestaShop 9.x as officially supported before milestone `1.2`.
- Keep `CHANGELOG.md`, `SECURITY.md`, and both READMEs aligned before tagging.

## Required Checks Before Any Release

Run from `euwithdrawalbutton/`:

```bash
composer validate
composer install
vendor/bin/phpunit
```

Run a PHP syntax check over all module PHP files.

## Release Package Build

Run from the repository root:

```bash
cd euwithdrawalbutton
composer install --no-dev --prefer-dist
composer dump-autoload -o --no-dev
```

Then create a ZIP archive that contains the `euwithdrawalbutton/` folder at the archive root. The release ZIP must include the generated `euwithdrawalbutton/vendor/` files.

Do not use GitHub's automatic source archive as the primary PrestaShop install artifact.

## PrestaShop Test Matrix For `1.0.0`

Verify each supported PrestaShop 8 line before the stable release:

- PrestaShop `8.0`: install, public withdrawal flow, acknowledgement email, Back Office list/detail, resend/export, uninstall.
- PrestaShop `8.1`: install, public withdrawal flow, acknowledgement email, Back Office list/detail, resend/export, uninstall.
- PrestaShop `8.2`: install, public withdrawal flow, acknowledgement email, Back Office list/detail, resend/export, uninstall.

PrestaShop `9.x` may be tested experimentally, but must not be presented as an official `1.0.0` support target.

## Final `1.0.0` Steps

- Set `$this->version = '1.0.0'` in `euwithdrawalbutton/euwithdrawalbutton.php`.
- Finalize the `1.0.0` entry in `CHANGELOG.md`.
- Update `SECURITY.md` so `1.x` is the supported stable line and `< 1.0.0` is unsupported.
- Build the release ZIP with `vendor/`.
- Tag `v1.0.0`.
- Create a GitHub Release without the pre-release flag.

