<?php
$data['dataDirectory']				= __DIR__ . '/../../data/';
$data['displayErrors']				= false;
if ($mode === 'preview' || $mode === 'local') $data['displayErrors'] = true;

$data['errorLogFilePath']			= __DIR__ . '/../../runtime/log/error-{date}.log';
$data['profileFilePath']			= __DIR__ . '/../../runtime/log/profile.log';
$data['profile']					= false;

$data['logEmailMessageSubject']		= 'AssetLink log';
$data['logEmailErrorSubject']		= 'AssetLink error';
$data['logEmailSender']				= 'error@assetlink.tld';
$data['logEmailPreventDuplicatesFilePath']	= __DIR__ . '/../../runtime/log/prevent-duplicate_{date}_{hash}.log';

$data['versionIsAuditLogEnabled'] = true;
//if ($mode === 'preview' || $mode === 'local') $data['versionIsAuditLogEnabled'] = false;
// @todo SET disableAuditLog = true

$data['useSite']		= false; // @temp
$data['useSeoUrls']		= false; // @temp
$data['useUuid']		= true;

if ($mode === 'preview' || $mode === 'local') $data['ignoreForceHttps'] = true;
if ($mode === 'preview' || $mode === 'local') $data['overrideDomainUrlVariable'] = 'customDomain';
if ($mode === 'preview' || $mode === 'local') $data['overrideDomain'] = 'www.assetlink.tld';

$data['versionDirectories']	= [
	__DIR__ . '/../../code/version/',
	__DIR__ . '/../../lib/bite/code/version/',
];

$data['maxTextFieldLenth']		= 191; // 191 for utf8mb4 (when indexed as primary or unique key)
// @todo max number of bytes in text fields etc

$data['routes'] = 'assetLink/routes';
$data['entityDatabaseConfig']	= 'assetLink/entityDatabaseConfig';
$data['entityForgerDirectory']	= 'assetLink/forging/entity/';


$data['defaultLanguageCode']	= 'nld';
$data['fallbackLanguageCode']	= 'eng';

// @note environment config is a fallback for development or for servers without $_ENV values
if (is_file(__DIR__ . '/config-environment.php')) require(__DIR__ . '/config-environment.php');