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
 * Module extending PHP core 
 * for it is missing some basic functionality 
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id: $
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *  
 */ 
 

	// some handy constants
define('NEWLINE', "\n");

	// there are similar functions for all other literal types 
	// but not for boolean (poor unsystematic PHP)
function boolval($mixed) 
{
	if (is_numeric($mixed)) return (bool) $mixed;
	elseif (is_string($mixed) && preg_match('/^true|yes|on|ok|correct/i', $mixed)) return true;
	else return false;
}

	// there is not copy folder method in PHP core (poor PHP) 
	// so we to do it ourselves (does not retain all file attributes!)
function dir_copy($source, $dest, $overwrite = false)
{
	if (!is_dir($dest)) 
	{
		if (!@mkdir($dest)) 
		{
			return false;
		}
	}
	foreach (scandir($source) as $file) 
	{
		if ($file === '.' || $file === '..') 
		{
			continue;
		}
		$s = $source . DIRECTORY_SEPARATOR . $file;
		$d = $dest . DIRECTORY_SEPARATOR . $file;
		if (is_file($s)) 
		{
			if (!@copy($s, $d)) 
			{
				return false;
			}
		} 
		elseif (is_dir($s)) 
		{
			dir_copy($s, $d, $overwrite);
		}
	}
	return true;
} // end of dir_copy()


	// there is not delete folder method in PHP core (poor PHP) 
	// so we to do it ourselves
function dir_delete($d)
{
	foreach (@scandir($d) as $n)
	{
		if($n!=='.' && $n!=='..') 
		{
			$fn = $d . DIRECTORY_SEPARATOR . $n;
			if (is_file($fn)) 
			{
				@unlink($fn);
			}
			else 
			{
				dir_delete($fn);
			}
		}
	}
	@rmdir($d);
} // end of dir_delete()



	// Win32 related stuff
	// -------------------

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
	// "memory_get_usage()" not defined in win32 (poor PHP)
	// we do not want to require php_win32ps.dll extension
	if (!function_exists('memory_get_usage')) 
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

		// expects XStandard ActiveX component registered on your box
		// see http://www.xstandard.com/ (not GNU but FREEWARE)
	function unzip($sArchive, $sFolderPath) 
	{
		try {
			$com = new COM('XStandard.Zip');
			$com->UnPack($sArchive, $sFolderPath);
			$return = $com->ErrorCode ?  $com->ErrorDescription : 0;
			unset($com);
		} catch (Exception $e) {
			$return = $e->getMessage();
		};
		return $return;
	}
	
	
		// expects XStandard ActiveX component registered on your box
		// see http://www.xstandard.com/ (not GNU but FREEWARE)
	function zip($sArchive, $sFilespec) 
	{
		try {
			$com = new COM('XStandard.Zip');
			$com->Pack($sFilespec, $sArchive);
			$return = $com->ErrorCode ? $com->ErrorDescription : 0;
			unset($com);
		} catch (Exception $e) {
			$return = $e->getMessage();
		};
		return $return;
	}

}


	// Non Windows related stuff
	// expecting standard linux system
	// -------------------------------

if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
{

	/**
	 * expects unzip installed in you box and being in your environment path
	 * see http://www.info-zip.org/	 	
	 * @return {variant} integer 0 on success, msg string on failure
	 */
	function unzip($sArchive, $sFolderPath) 
	{
		$unzippath = 'unzip'; 
		try {
			$cmd = $unzippath . ' -o "' . $sArchive . '" -d "' . $sFolderPath . '"';
			exec($cmd, $output, $return);
			$return = $return ? (string) $return : 0;  
		} catch (Exception $e) {
			$return = $e->getMessage();
		};
		return $return;
	}
	
	/**
	 * expects zip installed in you box and being in your environment path
	 * see http://www.info-zip.org/	 	
	 * @return {variant} integer 0 on success, msg string on failure
	 */
	function zip($sArchive, $sFilespec) 
	{
		$zippath = 'zip'; 
		$curpath = getcwd();
		chdir(dirname($sFilespec));
		try {
			$cmd = $zippath . ' -ur "' . $sArchive . '" "' . basename($sFilespec) . '"';
			exec($cmd, $output, $return);
			$return = $return ? (string) $return : 0;  
		} catch (Exception $e) {
			$return = $e->getMessage();
		};
		chdir($curpath);
		return $return;
	}	
}

?>