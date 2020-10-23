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
In the admin panel under store configuration, you can set the  options for the extension (Stores->Configuration->Tealium->Tags). You will need to enable it, and define your TiQ account, profile, and environment information.

## How To Use
### Predefined data layer variables already available
#### Home
- site_region
- site_currency
- page_name
- page_type
#### Search
- site_region
- site_currency
- page_name
- page_type
- search_results
- search_keyword
#### Category
- site_region
- site_currency
- page_name
- page_type
- page_section_name
- page_category_name
- page_subcategory_name
#### ProductPage
- site_region
- site_currency
- page_name
- page_type
- product_id
- product_sku
- product_name
- product_brand
- product_category
- product_unit_price
- product_list_price
#### Cart
- site_region
- site_currency
- page_name
- page_type
- product_id
- product_sku
- product_name
- product_quantity
- product_list_price
- Confirmation
- site_region
- site_currency
- page_name
- page_type
- order_id
- order_discount
- order_subtotal
- order_shipping
- order_tax
- order_payment_type
- order_total
- order_currency
- customer_id
- customer_email
- product_id
- product_sku
- product_name
- product_list_price
- product_quantity
- product_discount
- product_discounts
- site_region
- site_currency
- page_name
- page_type
- customer_id
- customer_email
- customer_type
### Adding Custom Data Sources
If you need to modify the default variables or page types you can define these in an external file and place it in accessible folder for the plugin to read from.
1. In the Magento admin configuration set Enable Custom UDO to "Yes"
1. Specify the system path to the file in the configuration
1. If you need a base PHP block to start with click the "Click for UDO PHP base code" link and copy this code

## Tealium IQ Basic Set Up + Verification Test

This example is for mapping two variables to a Google Analytics account to your app through the Tealium Management Console. This example presumes you have already done the following:

- Setup a Google analytics account from www.google.com/analytics/
- Have added Tealium tracking code to your project (see instructions above)
- Have a Tealium account at www.tealium.com

Verification steps are:

1. Log into your Tealium account
1. Load the Account and Profile that matches the Account and Profile used in your Tealium($accountInit, $profileInit, $targetInit[, $pageType][, $data]) method.
1. Goto the Data Sources tab and add the following new data source:
    - screen_title
    Note: leave them as the default type: Data Layer. screen_title are your views' viewcontroller title or nibName property. Optional: copy and paste the entire set of predefined Data Sources found at: https://community.tealiumiq.com/t5/Mobile/Mobile-Autotracked-Data-Sources/m-p/1798/highlight/true#M259

1. Go to the Tags tab:
    - click on the +Add Tag button
    - select Google Analytics
    - enter any title (ie "GAN") in the title field
    - enter your Google Analytics product id into the account id field (this is the account id assigned by Google and usually starts with the letters UA...)
    - click on the Next button
    - make sure the Display All Pages option is checked in the Load Rules section
    - click on the Next button
    - in the Source Values dropdown - select screen_title(js) - click on Select Variable
    - select Page Name (Override) option in the Mapping Toolbox
    - click save
    - click save
    - click on the finish
1. Click on the Save/Publish button
    - Click on Configure Publish Options... The Publish Settings dialog box will appear. Make certain the "Enable Mobile App Support" option is checked on and click "Apply".
    - Enter any Version Notes regarding this deployment
    - Select the Publish Location that matches the environmentName, or target argument from your initSharedInstance:profile:target: method
    - Click "Save"
    NOTE: It may take up to five minutes for your newly published settings to take effect.
1. Log into your Google Analytics dash board - goto your real time tracking section
1. Launch your app and interact with it. You should see view appearances (page changes) show in your Google Analytics dashboard

## Change Log

- 2.0.0 Release
    - Updated composer.JSON
    - Updated readme
    - Releasing all previous commits

- 1.0.1 Release
    - Default UDO (utag_data JSON object in page source) deployed via Magento Extension to match Tealium iQ TMS Data Layer bundle
    - Configure account info in admin panel
    - Extend and customize UDOs

## Support
For additional help and support, please send all inquires to your Account Manager or post questions on our community site at: https://community.tealiumiq.com

## License

Use of this software is subject to the terms and conditions of the license agreement contained in the file titled "LICENSE.txt".  Please read the license before downloading or using any of the files contained in this repository. By downloading or using any of these files, you are agreeing to be bound by and comply with the license agreement.

---
Copyright (C) 2012-2020, Tealium Inc.
