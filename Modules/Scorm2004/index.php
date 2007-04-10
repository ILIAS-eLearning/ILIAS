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


function get_admin()
{
	require_once('classes/ilSCORM13Package.php');
	$mod = new ilSCORM13Package();
	$mod->getAdmin();
}


/**
 * Upload a package file (zip), 
 * analyze it, and import it
 */ 
function submit_uploadAndImport() 
{
	require_once('classes/ilSCORM13Package.php');
	$path = IL_OP_SAMPLES_FOLDER . '/';
	$newfile = $_FILES['packagedata'];
	if ($newfile) 
	{
		$fn = $path . $newfile['name'];
		if ($newfile['error']) 
			return 'Upload error: ' . $newfile['error']; 
		if (is_file($fn))
			return 'File with this name already exists';
		@rename($newfile['tmp_name'], $fn);
		$oldfile = $fn;
	}
	else
	{
		$oldfile = $path . $_POST['packagefile'];
	}
	if (!is_file($oldfile)) 
	{
		return 'No file uploaded or selected';
	}
	$importer = new ilSCORM13Package();
	if (!$importer->uploadAndImport($oldfile)) 
	{
		$importer->rollback();
	};
	header('Location: ' . $_SERVER['SCRIPT_NAME']);
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
	header('Location: ' . $_SERVER['SCRIPT_NAME']);
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
}

/**
 */ 
function submit_removeCMIData() 
{
	require_once('classes/ilSCORM13Package.php');
	$importer = new ilSCORM13Package($_POST['packageId']);
	$importer->removeCMIData();
	header('Location: ' . $_SERVER['SCRIPT_NAME']);
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


$cmd = is_array($_POST['submit']) ? 'submit_' . key($_POST['submit']) : 'get_admin';
if (is_callable($cmd)) 
	die($cmd());
else 
	die($cmd);
		
?>
