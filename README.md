# SPPassport

phpVMS v7 module for a personal Passport feature

* Module supports **only** php8.2+ and laravel 11
* Minimum required phpVMS v7 version is `7.0.4` / 13. March 2025

## What you get

The SPPassport module tracks and visualizes a pilot’s visited countries, continents, and airports. It displays progress, statistics, maps, recommended destinations, and global leaderboards. Users can compare profiles, analyze similarities, flight times, and distances. The module supports multiple languages and offers a complete, interactive travel record system. There is also an additional widget that you can use anywhere you want.

## Compatibility

This module is fully compatible with phpVMS v7 and will work with any other module you have installed.

## Installation and Updates

_Make sure the name of the folder you upload is **SPPassport**._
* Manual Install : Upload contents of the package to your phpVMS root `/modules` folder via ftp or your control panel's file manager
* GitHub Clone : Clone/pull repository to your phpVMS root `/modules/SPPassport` folder
* phpVMS Module Installer : Go to admin > addons/modules , click Add New , select downloaded file then click Add Module

* Go to admin > addons/modules enable the module
* Go to admin > maintenance and clean `application` and `view` cache

## Link removal

* SPPassport: Add ``//`` in front of row 51 in your ``Providers/AppServiceProvider.php`` file.

## User Widget

You have the opportunity to add a widget to basically any place in your phpvms v7.

* Please use the following code ``@widget('Modules\\SPPassport\\Widgets\\PassportStamps', ['user_id' => $user->id])``

## License Compatibility & Attribution Link

Do **not** distribute the module elsewhere. However, you may share a link to it on your public pages if you wish.

## Do you have any suggestions or need help?
Please use the GitHub [issue](https://github.com/PaintSplasher/phpvms7_SPPassport/issues) tracker.

## Release / Update Notes

18.OCTOBER.25
* Initial Release