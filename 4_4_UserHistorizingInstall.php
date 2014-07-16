<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Custom update script for feature branches and patches.
 * 
 * This commandline tool is used to install the necessary tables for the 
 * associated product during development and/or manual install.
 * 
 * If you do not exactly know why you want to run exactly this script, DO NOT RUN IT.
 * 
 * Usage:
 * ------
 * 
 * (from Ilias root directory)
 * <filename>.php username password client 
 * (Use credentials of a user with elevated permissions, preferrably root.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * 
 * @version $Id$
 */
/*
if($_SERVER['argc'] < 4)
{
	die("Usage: " . __FILE__ . " username password client\n");
}

chdir(dirname(__FILE__));

require_once './Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_POST['username'] 		= $_SERVER['argv'][1];
$_POST['password'] 		= $_SERVER['argv'][2];
$_COOKIE["ilClientId"] 	= $_SERVER['argv'][3];
*/
require_once './include/inc.header.php';

/** @var ilDB $ilDB */
global $ilDB;

echo "[INSTALLING / UPDATING Database Tables]\r\n";

// The following code can easily be ported to a dbupdate-file. Please
// note that the code only executes, if the given table does not exist.
// This script however does not deal with step numberings.
// -----------------------------------------------------------------------------

if(!$ilDB->tableExists('hist_user'))
{
	$fields = array (
		'row_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'hist_version' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1),
		'hist_historic' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'creator_user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'created_ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true),
		'firstname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'lastname' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'gender' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'birthday' => array(
			'type' => 'date'),
		'org_unit' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'position_key' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'entry_date' => array(
			'type' => 'date'),
		'exit_date' => array(
			'type' => 'date'),
		'bwv_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'okz' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true),
		'begin_of_certification' => array(
			'type' => 'date'),
		'deleted' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true)
	);
	$ilDB->createTable('hist_user', $fields);
	$ilDB->addPrimaryKey('hist_user', array('row_id'));
	$ilDB->createSequence('hist_user');
}

// -----------------------------------------------------------------------------
// End

echo "[DONE INSTALLING / UPDATING Database Tables]\r\n";