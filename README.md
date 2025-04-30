## Security Patches

Provides security patches for out-of-date Craft CMS installs.

> [!WARNING]
> This extension only attempts to mitigate high-severity vulnerabilities, and is not a substitute for keeping
> Craft CMS up-to-date. Maintaining a regular update cadence to ensure Craft CMS is kept up-to-date on a
> [supported version](https://craftcms.com/knowledge-base/supported-versions) is highly recommended.

## Compatibility

This extension is compatible with Craft CMS 3.6.0+, 4.0.0+, and 5.0.0+.

## Installation

To install, run the following command within a Craft 3/4/5 project:

```sh
composer require craftcms/security-patches:dev-main
```

> **Note**
> If you get the following prompt, make sure to answer `y`:
>
> ```sh
> yiisoft/yii2-composer contains a Composer plugin which is currently not in your allow-plugins config. See https://getcomposer.org/allow-plugins
> Do you trust "yiisoft/yii2-composer" to execute code and wish to enable it now? (writes "allow-plugins" to composer.json)
> ```

## Mitigated Security Advisories

- `CVE-2025-32432` (RCE)
  - Affects Craft CMS 3.0.0 – 3.9.14, 4.0.0 – 4.14.14, and 5.0.0 – 5.6.16
  - Fixed in Craft CMS 3.9.15, 4.14.15, and 5.6.17
- `CVE-2024-56145`
  - Affects Craft CMS 3.0.0 – 3.9.13, 4.0.0 – 4.13.1, and 5.0.0 – 5.5.1
  - Fixed in Craft CMS 3.9.14, 4.13.2, and 5.5.2
