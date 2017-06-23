<?php
// @usage php createSystemScript.php loginName password
$di = require_once(__DIR__ . '/../../initScript.php');

$loginName	= isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'test';
$password	= isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 'test';

// create system
$di->db->insert('system', 'id', [
	'role_id'			=> $di->db->insert('role', 'id', [
		'roletype_id' => $di->db->select(['id', 'FROM', 'roletype', 'WHERE', ['key' => 'system']])->fetchField('id'),
	]),
	'loginname'			=> $loginName,
	'passwordhash'		=> password_hash($password, PASSWORD_DEFAULT),
	'sharedsecret'		=> 'shared',
	'hasremoteaccess'	=> 1,
	'registrationdate'	=> $di->db->expression('UTC_TIMESTAMP()'),
	'lastlogindate'		=> $di->db->expression('UTC_TIMESTAMP()'),
	'approvaldate'		=> $di->db->expression('UTC_TIMESTAMP()'),
]);

