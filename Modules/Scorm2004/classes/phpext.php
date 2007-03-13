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
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
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
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *  
 * 
 */ 
 

	// some handy constants
define('NEWLINE', "\n");

	// there are similar functions for all other literal types 
	// but not for boolean (poor unsystematic PHP)
function boolval($mixed) 
{
	if (is_numeric($mixed)) 
	{
		return (bool) $mixed;
	}
	elseif (is_string($mixed) && preg_match('/^true|yes|on|ok|correct/i', $mixed))
	{
		return true;
	}
	else 
	{
		return false;
	}
}


/**
* "memory_get_usage()" not defined in win32 (poor PHP)
* we do not want to require php_win32ps.dll extension
* memory_get_usage helps in performance checks
**/	 
if (!function_exists('memory_get_usage')) 
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		function memory_get_usage()
		{
			$return = '';
			try 
			{
				$output = array();
				exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
				$return = substr($output[5], strpos($output[5], ':') + 1);
			} catch (Exception $e) {}
			return $return;
		}
	}
}

/**
 * Checking for json and load script emulation for older php versions.
 * It is strongly recommended to use php_json extension or PHP 5.2 or higher.
 */ 
if (!function_exists('json_encode')) 
{
	require_once('JSON.php'); // you may read this from PEAR
	function json_encode($data) 
	{
		$value = new Services_JSON(); 
		return $value->encode($data);
	}
	function json_decode($data) 
	{
		$value = new Services_JSON(); 
		return $value->decode($data); 
	}
}



?>
