# tealium_magento2
Magento 2.X extension to deploy Tealium code.

## Introduction
Tealium's official integration for TiQ on the Magento 2 framework. In its current state it is an implementation of some minimal boiler plate code for implementing and extending universal data objects (UDOs) for various page types. It relies heavily on Magento's prescribed dependency injection system and layout systems. Included is a simple script that will scaffold out boiler plate code when creating a new UDO. It allows you to specify any UDOs that it may extend, and which pages of the site the new UDO should appear on. When finished, you're left with a scaffolded template that just needs to be filled in with any data specific logic for your particular use case.

You can get started understanding the UDO and concepts of a data layer at [https://community.tealiumiq.com/t5/Getting-Started/Getting-Started-with-The-Data-Layer/ta-p/9503](https://community.tealiumiq.com/t5/Getting-Started/Getting-Started-with-The-Data-Layer/ta-p/9503).

Documentation on Magento can be found at [http://devdocs.magento.com/](http://devdocs.magento.com/).

## Requirements
You will need the following items:
- An active Tealium IQ Account
- Your Tealium Account Id (it will likely be your company name)
- The Tealium Profile name to be associated with the app (your account may have several profiles, ideally one of them is dedicated to your iOS app)
- The Tealium environment to use:
    - prod
    - qa
    - dev
    - custom

## Installation
### Install via Magento Marketplace
You can install the Tealium Magento Extension free via the Magento Marketplace: https://marketplace.magento.com/tealium-tags.html

### Alternative (manual) Install with Ubuntu
You need to copy the Tealium folder from Github to app/code within your Magento folder.  If app/code doesn’t exist, create it.

Run the following commands:
```
sudo php bin/magento setup:upgrade

sudo php -d set_time_limit=3600 -d memory_limit=1024M bin/magento setup:di:compile
```
## Configure
In the admin panel under store configuration, you can set the  options for the extension (Stores -> Configuration -> Tealium -> Tag Management). You will need to enable it, and define your TiQ account, profile, and environment information.

- 2.4.5+ With the latest update you will also have the option to enter a [FPD (First Party domain)](https://docs.tealium.com/iq-tag-management/administration/first-party-domains/about/) The account field is ignored if the FPD field is filled in. Imporant note: This is for the Client Side Domain. The server side domain is configured in the Tealium Collect tag in the above documentation link. 
- 2.4.5+ Email Hashing is optional. If set to true, SHA256() will be applied to email addresses in "customer_email" params. 

## CSP for 2.4.5+ 
If the site is not using FPD, no updates should be needed. The site domain is not wildcard whitelisted by default. www.site.com will not accept datacollect.site.com by default. 

- To update:
  - In the extition's etc dir. public_html/integration-magento/etc Should be a csp_whitelist.xml file.
  - Update policy, "script-src", "connect-src", and "img-src" to include your FPD domain.
  - For example: ```<value id="unique id" type="host">https://data.site.com</value>``` for your Client Side domain
  - ```<value id="unique id" type="host">https://datac.site.com</value>``` for your Server side domain. 


## Change Log

- Magento 2.4.6 / PHP 8.1 Update
  - FPD (first party domain support)
  - Updated older PHP code that is not compliant with 8.1+
  - CSP (content security policy) updates for 2.4.6

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
