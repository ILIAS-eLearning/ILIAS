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

/*if($_SERVER['argc'] < 4)
{
	die("Usage: " . __FILE__ . " username password client\n");
}

chdir(dirname(__FILE__));

require_once 'Services/Authentication/classes/class.ilAuthFactory.php';
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


if(!$ilDB->tableExists('org_unit_personal_units'))
{
	$fields = array (
    'orgunit_id'    => array(
    		'type' => 'integer',
    		'length'  => 11,
    		'notnull' => true,
    		'default' => 0),

  'usr_id'    => array(
    		'type' => 'integer',
    		'length'  => 11,
    		'notnull' => true,
    		'default' => 0),

  
  );
  $ilDB->createTable('org_unit_personal_units', $fields);
  $ilDB->addPrimaryKey('org_unit_personal_units', array('orgunit_id'));
// -----------------------------------------------------------------------------
// End
}

echo "[DONE INSTALLING / UPDATING Database Tables]\r\n";