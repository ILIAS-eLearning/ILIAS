<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 *  
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 */ 
 
	// common constants classes, initalization, php core extension, etc. 
require_once('common.php');


/**
 * We force to UTF-8 and do not care about doctype at the moment.
 */ 
header('Content-Type: text/html; charset=UTF-8');
header("Pragma: no-cache");


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