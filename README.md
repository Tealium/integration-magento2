![image](https://github.com/efrazier/integration-magento-osb/assets/3696386/0a2e3179-498c-487a-9c83-0e8376b87e28)


# Tealium Magento 2 Extension


## Introduction

Tealium's official integration for TiQ on the Magento 2 framework. This extension provides a robust implementation of minimal boilerplate code for implementing and extending universal data objects (UDOs) across various page types. Leveraging Magento's prescribed dependency injection and layout systems, the module simplifies the process of creating and extending UDOs.

Included is a script that scaffolds out boilerplate code for new UDOs. It allows you to specify UDO extensions and the pages where the new UDO should appear. Once generated, you're left with a template ready to be filled in with data-specific logic for your particular use case.

Get started understanding UDOs and data layer concepts at [Tealium Community](https://community.tealiumiq.com/t5/Getting-Started/Getting-Started-with-The-Data-Layer/ta-p/9503).

For Magento documentation, refer to [Magento DevDocs](http://devdocs.magento.com/).

## Requirements

Ensure you have the following:

- Active Tealium IQ Account
- Tealium Account ID (usually your company name)
- Tealium Profile name associated with the app
- Tealium environment (prod, qa, dev, custom)

## Installation

### Manual Install with Ubuntu

1. **Enable Maintenance Mode:** Enable maintenance mode before installing the extension to avoid any user issues. To enable maintenance mode, run the following command:

    ```bash
    php bin/magento maintenance:enable
    ```

2. **Copy the Tealium Folder:**
   Copy the Tealium folder from GitHub to `app/code/Tealium/Tags` within your Magento folder. If `app/code/Tealium/Tags` doesn’t exist, create it.

3. **Run the Following Commands to Update Changes:**
   
    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy -f
    php bin/magento cache:flush
    ```

4. **Disable Maintenance Mode:** After the installation is complete, disable maintenance mode to allow users to access the site. To disable maintenance mode, run:

    ```bash
    php bin/magento maintenance:disable
    ```

## Configuration

In the admin panel under store configuration (`Stores -> Configuration -> Tealium -> Tag Management`), set the extension options. Enable the extension and define your TiQ account, profile, and environment information.

### Version 2.4.5+

- Optional FPD (First Party Domain) configuration.
- Email Hashing: If set to true, SHA256() will be applied to email addresses in "customer_email" params.

### CSP for Version 2.4.5+

- Update the `csp_whitelist.xml` file in the extension's `etc` directory (`public_html/integration-magento/etc`).
- Update policies ("script-src," "connect-src," and "img-src") to include your FPD domain.

Example:

```xml
<value id="unique id" type="host">https://data.site.com</value> <!-- Client Side domain -->
<value id="unique id" type="host">https://datac.site.com</value> <!-- Server side domain -->
```

## Change Log

### Magento 2.4.6 / PHP 8.1 Update

- 3.2.0 Release
    - FPD (First Party Domain) support
    - Update older PHP code for compliance with PHP 8.1+
    - CSP (Content Security Policy) updates for Magento 2.4.6
    - Newsletter signup Event
    - SHA256 encryption option on customer_email UDO variable
    - Bug Fixes

- 3.1.0 Release
    - Update for support of Magento 2.4
    - Add tracking for user-submitted product reviews
    - Bug fixes

- 3.0.2 Release
    - Add support for Magento 2.3.3

- 3.0.1 Release
    - Add support for Magento 2.2.8, 2.3.0, and 2.3.1-2

- 2.0.0 Release
    - Updated composer.JSON
    - Updated readme
    - Releasing all previous commits

- 1.0.1 Release
    - Default UDO (utag_data JSON object in page source) deployed via Magento Extension to match Tealium iQ TMS Data Layer bundle
    - Configure account info in admin panel
    - Extend and customize UDOs

## Sharing Feedback
If you should experience any issues with this plugin, please report them as issues directly to the repository. In your submitted issue, please include which version of Magento you reference, e.g. 2.4.1. Tealium also accepts enhancement requests, if you find there are features you wish to see supported in future releases. Please use the _enhancement_ label on your submitted issue.

## License
Use of this software is subject to the terms and conditions of the license agreement contained in the file titled "LICENSE.txt".  Please read the license before downloading or using any of the files contained in this repository. By downloading or using any of these files, you are agreeing to be bound by and comply with the license agreement.

---
Copyright (C) 2012-2020, Tealium Inc.

