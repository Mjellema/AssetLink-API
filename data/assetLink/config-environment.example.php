<?php
$data['db.driver']					= 'mysql';
$data['db.host']					= 'localhost';
$data['db.user']					= 'webuser'; // has SELECT, DELETE, INSERT, UPDATE permissions
$data['db.password']				= 'pass';
$data['db.database']				= 'assetlink-local';
$data['db.auditLogDatabase']		= 'assetlink-local-auditlog';
$data['db.versioningUser']			= 'versioninguser'; // has CREATE, ALTER, etc. permissions for db versioning
$data['db.versioningPassword']		= 'versioningpass';
$data['db.auditLogTriggerUser']		= 'auditloguser'; // will be created by the versioning script (if it doesn't already exist)
$data['db.auditLogTriggerPassword']	= 'auditloguserpass';
$data['db.auditLogTriggerHost']		= '%';
if ($mode === 'live' || $mode === 'live-cron' || $mode === 'preview')
{
	$data['db.host']				= 'live.com';
	$data['db.password']			= 'livepass';
	$data['db.database']			= 'assetlink';
	$data['db.auditLogDatabase']	= 'assetlink-auditlog';
	
	$data['db.versioningPassword']	= 'liveversioningpass';
	$data['db.auditLogTriggerPassword']	= 'liveauditloguserpass';
}
if ($mode === 'preview') $data['db.database'] = 'assetlink-preview';
if ($mode === 'preview') $data['db.auditLogDatabase'] = 'assetlink-preview-auditlog';

//if ($mode === 'live') $data['logEmail'] = 'dev@example.com';
//if ($mode === 'live') $data['logEmailNoDuplicates'] = 'otherdev@example.com';

$data['s3.accessKey']	= 'accesskey';
$data['s3.secretKey']	= 'secretkey';
$data['s3.bucket']		= 'assetLink';
$data['s3.directory']	= 'assetLink/preview/';
if ($mode === 'live' || $mode === 'live-cron') $data['s3.directory'] = 'assetLink/live/';

$data['inboundApi.allowedIps'] = ['127.0.0.1', '192.168.2.254'];
if ($mode === 'local' || $mode === 'preview') $data['inboundApi.allowedIps'] = null; // allow all

$data['epubWatermarkService.password'] = 'longpass';