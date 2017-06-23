# AssetLink API

## Introduction
The AssetLink API can be used to manage and track legal ownership of digital assets (e-books in particular).
A single deployment of the API can be used by multiple parties including different kinds of 'digital-marketplaces' (merchants) that each have their own customers.
The customers can have their assets managed by multiple merchants of their choosing.

## Requirements
- Apache 2.4 (or similar webserver e.g. NGINX)
- PHP 5.6 (PHP 7 recommended)
- MySQL 5.6 (or similar database server e.g. MariaDB)
- Ruby (for CSS compilation)
-- SASS
-- Compass
-- SassyLists

## Installation

1. If you want to use logging to file, make sure the `runtime/log/` directory is writable for the apache and/or current console user

2. Create database & auditlog database (optionally replace `assetlink-local`, `assetlink-local-auditlog`)
```
CREATE DATABASE `assetlink-local` CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
CREATE DATABASE `assetlink-local-auditlog` CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
```

3. Create database versioning user (replace `yourVersioningUserPassword` and optionally `versioninguser`, `localhost`, `assetlink-local`, `assetlink-local-auditlog`)
```
CREATE USER 'versioninguser'@'localhost' IDENTIFIED BY 'yourVersioningUserPassword';
GRANT SELECT, EXECUTE, SHOW VIEW, ALTER, ALTER ROUTINE, CREATE, CREATE ROUTINE, CREATE TEMPORARY TABLES, CREATE VIEW, DELETE, DROP, EVENT, INDEX, INSERT, REFERENCES, TRIGGER, UPDATE, LOCK TABLES  ON `assetlink-local`.* TO 'versioninguser'@'localhost' WITH GRANT OPTION;
GRANT SELECT, EXECUTE, SHOW VIEW, ALTER, ALTER ROUTINE, CREATE, CREATE ROUTINE, CREATE TEMPORARY TABLES, CREATE VIEW, DELETE, DROP, EVENT, INDEX, INSERT, REFERENCES, TRIGGER, UPDATE, LOCK TABLES  ON `assetlink-local-auditlog`.* TO 'versioninguser'@'localhost' WITH GRANT OPTION;
```

4. Create database web user (replace `yourWebUserPassword` and optionally `webuser`, `localhost`, `assetlink-local`, `assetlink-local-auditlog`)
```
CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'yourWebUserPassword';
GRANT SELECT, DELETE, INSERT, UPDATE ON `assetlink-local`.* TO 'webuser'@'localhost';
GRANT SELECT, DELETE, INSERT, UPDATE ON `assetlink-local-auditlog`.* TO 'webuser'@'localhost';
```

5. Optionally create database auditlog user (will be created automatically if config `versionIsAuditLogEnabled` is true) (replace `yourAuditLogUserPassword` and optionally `auditloguser`, `localhost`, `assetlink-local`, `assetlink-local-auditlog`)
```
CREATE USER 'auditloguser'@'localhost' IDENTIFIED BY 'yourAuditLogUserPassword';
GRANT SELECT, TRIGGER ON `assetlink-local`.* TO 'auditloguser'@'localhost'
GRANT CREATE ROUTINE, ALTER ROUTINE, SELECT, EXECUTE, INSERT ON `assetlink-local-auditlog`.* TO 'auditloguser'@'localhost'
```

6. Copy config mode file (and possibly alter it to set the correct config for the current installation)
```
cp data/assetLink/config-mode.example.php data/assetLink/config-mode.php
```

7. Copy config environment example file
```
cp data/assetLink/config-environment.example.php data/assetLink/config-environment.php
```

8. Replace the config environment example values with the correct values

9. Run database version script
```
php code/php/assetLink/execution/version/versionScript.php
```

10. Compile the CSS
```
compass compile data/assetLink/sass/
```

11. Use createSystemScript to create a system that can access the API remotely (replace `loginName`, `password`)
```
php code/php/assetLink/security/system/createSystemScript.php loginName password
```

## Usage
After installation, check the documenation on: `https://yourhost/documentation/`

## Example
A customer of a merchant (e.g. a digital marketplace) wants to sell his legally purchased e-book to the highest bidder.
The customer places his trust in this merchant to manage his digital asset for him, so he gives the merchant his original e-book download link.
The merchant is a user of the AssetLink API, so he registers the asset with the customer as a legal owner of this e-book by calling the `ebookAssetService.registerEbookDownloadLink` action.
Because the customer has placed his trust in this merchant, this action automatically grants the merchant the permission to `manage` the asset.
The API will now verify and process the download link of which the merchant can obtain the status via the `ebookAssetService.obtainEbookDownloadLinkStatus` action.
If the download link is found to be valid and hasn't been registered to any owner before, the merchant can list the e-book on its website for sale.
The merchant can then handle all bidding, negotiation and financial transactions and when the sale is complete, the merchant can transfer ownership of the e-book to the new owner with `assetService.transferAssetOwnership`.
When the new owner wants to read the e-book, the merchant can call `ebookAssetService.downloadEbook`.

If the new owner wants another merchant to manage his e-book (e.g. he wants to sell it on a different website), he can ask the merchant for a `passtoken` which the merchant can generate via `assetService.generateManagePermissionPasstoken`.
The new owner then gives this passtoken to the new merchant which calls `assetService.grantManagePermissionUsingPasstoken`.

Should the original owner keep his original download link and try to sell it again, the AssetLink API will catch the duplicate link and prevent this.