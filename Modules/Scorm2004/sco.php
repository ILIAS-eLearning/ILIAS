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
 */ 
 

/**
 * estimate content type for a filename by extension
 * first do it for common static web files from external list
 * if not found peek into file by slow php function mime_content_type()
 * @param $filename required
 * @return string mimetype name e.g. image/jpeg
 */
function getMimetype($filename) 
{
	$mimetypes = array();
	require_once('classes/mimemap.php');
	$info = pathinfo($filename);
	$ext = $mimetypes[$info['extension']];
	return $ext ? $ext : mime_content_type($filename);
}

/**
 * getting and setting Scorm2004 cookie
 * Cookie contains enrypted associative array of sahs_lm.id and permission value
 * you may enforce stronger symmetrical encryption by adding RC4 via mcrypt()
 **/
function getScorm2004Cookie() 
{
	return unserialize(base64_decode($_COOKIE['Scorm2004']));
}
function setScorm2004Cookie($cook) 
{
	if (headers_sent()) 
	{
		die('Error');
	}
	setCookie('Scorm2004', base64_encode(serialize($cook)));
}

/**
 * Try to find file, identify content type, write it to buffer, and stop immediatly
 * If no file given, read file from PATH_INFO, check permission by cookie, and write out and stop.	 
 * @param $path optional filename
 * @return void	 
 */	 	
function printResource($path = null) {
	global $config;
	$SAHS_LM_POSITION = 1;
	
	if (!is_string($path))
	{
		$path = $_SERVER['PATH_INFO'];
		if (!is_string($path))
		{
			die('Insufficient parameters.');
		}
	}
	
	$comp = explode('/', $path);
	$sahs = $comp[$SAHS_LM_POSITION];
	$cook = getScorm2004Cookie();
	$perm = $cook[$sahs];
	
	if (!$perm) 
	{
		// check login an package access
		// TODO add rbac check function here
		$perm = 1;
		if (!$perm) 
		{
			header('HTTP/1.0 401 Unauthorized');
			die('/* Unauthorized */');
		}
		// write cookie
		$cook[$sahs] = $perm;
		setScorm2004Cookie($cook);
	}
	
	$path = '.' . $path;
	
	if (!is_file($path))
	{
		header('HTTP/1.0 404 Not Found');
		die('/* Not Found ' . $path . '*/');
	} 
	
	// send mimetype to client
	header('Content-Type: ' . getMimetype($path));

	// let page be cached in browser for session duration
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + session_cache_expire()*60) . ' GMT');
	header('Cache-Control: private');

	// now show it to the user and be fine
	readfile($path);
	die();

} 


// let's run it
printResource();


?>