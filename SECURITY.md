# Security Policy

## Supported Versions

This project is currently in pre-`1.0.0` release hardening. Security fixes are provided for the default branch and the latest public `0.x` pre-release.

| Version | Supported |
| ------- | --------- |
| `main` | Yes |
| `0.x` latest pre-release | Yes |
| Older `0.x` pre-releases | No |
| `< 0.1.0` | No |

After `1.0.0`, the supported stable line will be:

| Version | Supported |
| ------- | --------- |
| `1.x` latest release | Yes |
| `< 1.0.0` | No |

## Reporting a Vulnerability

Please do not report security vulnerabilities through public GitHub issues.

Use GitHub's private vulnerability reporting feature if it is enabled for this repository. If private reporting is not available, contact the maintainer privately through the contact method listed in the repository profile.

When reporting a vulnerability, please include:

- Affected module version or commit hash
- PrestaShop version
- PHP version
- A clear description of the issue
- Steps to reproduce
- Potential impact
- Any suggested fix, if available

We aim to acknowledge reports within 7 days. Accepted vulnerabilities will be handled privately until a fix is available or a coordinated disclosure plan is agreed.

## Scope

Security-relevant issues include, but are not limited to:

- Unauthorized access to withdrawal declarations
- Customer/order data exposure
- Order/customer enumeration
- CSRF bypasses
- Stored or reflected XSS
- SQL injection
- Authentication or authorization bypass in Back Office features
- Unsafe file handling, if file uploads are added in the future

This module does not provide legal advice. Legal or compliance interpretation questions should be reported as normal issues or discussions, not as security vulnerabilities.
