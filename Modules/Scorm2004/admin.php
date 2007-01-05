<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * Copyright (c) 2005-2007 Alfred Kohnert.
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 * For security reasons this is not connected to ILIAS database
 * but uses a small fake database in slite2 format.
 * Waits on finishing other sub tasks before being connected to ILIAS.
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id: $
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *
 * Frontend for demonstration of current state of ILIAS SCORM 2004 
 *  
 */ 
 
/**
 * We force to UTF-8 and do not care about doctype at the moment.
 */ 
header('Content-Type: text/html; charset=UTF-8');
header("Pragma: no-cache");

/**
 * some config constants
 * that will later be mapped to ILIAS ini strings 
 */
define('ilSCORM13_FOLDER', dirname(__FILE__) . '/packages');


/**
 * We will include some global functions extending poor PHP (this time a module 
 * and not a class). 
 * We also load a special database module running for sqlite. Will later be
 * mapped to ilDB (even if it is more rdbs independent and injection secure the
 * ILIAS default database code). 
 */
require_once('classes/phpext.php');
require_once('classes/ilSCORM13DB.php');

/**
 * Special database module is normally used in static mode. 
 * So there ist only one database and is accessable from everywhere. 
 * You could also use instances of ilSCORM13DB for binding to additional databases.  
 */
ilSCORM13DB::init(
	'sqlite2:data/slite2.db',
	'sqlite'
);	


/**
 * Upload a package file (zip), 
 * analyze it, and import it
 */ 
function submit_uploadAndImport() 
{
	require_once('classes/ilSCORM13Package.php');
	$fn = $_FILES['packagedata']['tmp_name'];
	if (!is_file($fn)) 
	{
		return 'No file uploaded';
	}
	$importer = new ilSCORM13Package();
	if (!$importer->uploadAndImport($fn)) 
	{
		$importer->rollback();
	};
	return $importer->diagnostic;
}


/**
 * Get a SCORM 2004 Manifest XML as string from database.
 * Will be useful after manifest manipulation in database through an editor etc.  
 */ 
function submit_removePackage() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$importer->rollback();
	return $importer->diagnostic;
}

/**
 * Get a SCORM 2004 Manifest XML as string from database.
 * Will be useful after manifest manipulation in database through an editor etc.  
 */ 
function submit_exportManifest() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$xml = $importer->exportManifest();
	return "<textarea cols=60 rows=10>$xml</textarea>";
}


/**
 * Get a SCORM 2004 Manifest XML as string from database.
 * Will be useful after manifest manipulation in database through an editor etc.  
 */ 
function submit_exportPackage() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$importer->exportPackage();
}

/**
 * Get a SCORM 2004 Manifest XML as string from database.
 * Will be useful after manifest manipulation in database through an editor etc.  
 */ 
function submit_exportXML() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$xml = $importer->exportXML();
	return "<textarea cols=60 rows=10>$xml</textarea>";
}

/**
 * Get a SCORM 2004 Manifest XML as string from database.
 * Will be useful after manifest manipulation in database through an editor etc.  
 */ 
function submit_exportZIP() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$importer->exportZIP();
}


/**
 * Demo simply uses global action (command) handling functions for all views 
 * and behavior. So this starts request processing if there is some handler. 
 */ 
$cmd = is_array($_POST['submit']) 
	? 'submit_' . key($_POST['submit']) 
	: null;
if (is_callable($cmd)) 
{
	$msg = $cmd();
} 

/**
 * There is no non-HTML output in demo frontend.
 * Styles are intended to show that we currently are not part of real ILIAS. 
 */ 
$packages = ilSCORM13DB::getRecords('cp_package');
include('templates/tpl/admin.tpl');

?>