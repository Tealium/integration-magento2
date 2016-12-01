# integration-magento2
Tealium Magento 2 Integration

## Introduction
This is a pre-release of what is to become Tealium's official integration for TiQ on the Magento 2 framework. In its current state it is simply an implementation of some minimal boiler plate code for implementing and extending UDOs for various page types. It relies heavily on Magento's prescribed dependency injection system and layout systems. Included is a simple script that will scaffold out boiler plate code when creating a new UDO. It allows you to specify any UDOs that it may extend, and which pages of the site the new UDO should appear on. When finished, you're left with a scaffolded template that just needs to be filled in with any data specific logic for your particular use case.

## Configure
In the "Utag.php" file in the "Block" folder, lines 14, 15, and 16 need to be changed to match your account, profile, and environment. As of now these values are simply hardcoded in this file. A better means of configuring these variables is planned before the official release of the module.

## Create a UDO
Included is a script for scaffolding new UDOs, which is creatively named "scaffold.php". You run it from the command line passing info such as the name of the new UDO, any UDOs it extends, and site pages that the UDO should be present on as arguments. The arguments form a simple declarative domain specific language, which should be fairly simple and easy to pick up and understand by looking at a few examples.

The first example is a very basic use case, where we are creating a new UDO named "NewUdo" extending an exiting UDO named "ExistingUdo", to be placed on any page with the handle "layout_handle". After running the command, there will be a new PHP file in the "Block" folder named "NewUdo.php", which implements a "NewUdo" class. The contents of the file will already be scaffolded out using the arguments provided to the scaffolding script. Within the file it will be necessary to add any other custom data. The area to do this is clearly commented with some example data.
```shell
php scaffold.php create NewUdo from ExistingUdo on layout_handle
```
The following example shows how to create a new UDO extending 2 existing UDOs, which will go on two pages of the site (specified with 2 layout handles).
```shell
php scaffold.php create NewUdo from ExistingUdo1 ExistingUdo2 on layout_handle_1 layout_handle_2
```
The next example does exactly the same thing as the previous example, however it includes some syntactic sugar, the "and" word which has no effect on the result. It just reads better.
```shell
php scaffold.php create NewUdo from ExistingUdo1 and ExistingUdo2 on layout_handle_1 and layout_handle_2
```
You can even add commas to make long lists easier to read. Again they are just syntactic sugar with no effect on the result.
```shell
php scaffold.php create NewUdo from ExistingUdo1, ExistingUdo2, and ExistingUdo3 on layout_handle
```
To create a default UDO that does not extend any other UDO, use the "default" layout handle.
```shell
php scaffold.php create DefaultUdo on default
```
However most likely you will want to have some basic UDO that it extends
```shell
php scaffold.php create DefaultUdo from BasicUdo on default
```
Since the basic UDO is probably only used by other UDOs and won't actually go on a page, you'd create it without specifying any layout handles. Since it doesn't extend any UDOs you don't define those either.
```shell
php scaffold.php create BasicUdo
```
