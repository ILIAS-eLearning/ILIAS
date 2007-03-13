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
 

class ilSCORM13Utils
{

	static public $userId; // user_id
	static public $packageId; // sahs_id

	/**
	 * this is a static class, so hide the constructor as private
	 */	 
	private function __construct() {}
	
	/**
	 * doing some tasks that are part of ILIAS header.php
	 */ 
	function init() 
	{
		/**
		 * Special database module is normally used in static mode. 
		 * So there ist only one database and is accessable from everywhere. 
		 * You could also use instances of ilSCORM13DB for binding to additional databases.  
		 */
		ilSCORM13DB::init(IL_OP_DB_DSN, IL_OP_DB_TYPE);
		
		/**
		 * (Pseudo-) login: Select user by basic auth 
		 * or default to "root"		 
		 */ 
		$name = $_SERVER['PHP_AUTH_USER'];
		$name = $name ? $name : 'root';
		$data = ilSCORM13DB::query('SELECT usr_id FROM usr_data WHERE login=?', array($name));
		if (!$data) 
		{
			die('not logged in');
		}
		else
		{
			self::$userId = $data[0]['usr_id'];
		} 
		$name = IL_OP_SAHS_ID;
		$data = ilSCORM13DB::query('SELECT id FROM sahs_lm WHERE id=?', array($name));
		if (!$data) 
		{
			die('course not found');
		}
		else
		{
			self::$packageId = $data[0]['id'];
		} 
	}

	/**
	 * there is not copy folder method in PHP core (poor PHP)
	 * so we to do it ourselves (does not retain all file attributes!)
	 **/	 
	function dir_copy($source, $dest, $overwrite = false)
	{
		if (!is_dir($source)) 
		{
			return false;
		} 
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
				self::dir_copy($s, $d, $overwrite);
			}
		}
		return true;
	} // end of dir_copy()
	
	
		// there is not delete folder method in PHP core (poor PHP) 
		// so we to do it ourselves
	function dir_delete($d)
	{
		if (!is_dir($d)) 
		{
			return false;
		} 
		foreach (scandir($d) as $n)
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
					self::dir_delete($fn);
				}
			}
		}
		@rmdir($d);
	} // end of dir_delete()
	
	
	
	/**
	 * expects unzip installed in you box and being in your environment path
	 * see http://www.info-zip.org/	 	
	 * @return {variant} integer 0 on success, msg string on failure
	 */
	function unzip($sArchive, $sFolderPath) 
	{
		$cmd = IL_OP_UNZIP_EXE . ' -o -n -P "" "' . $sArchive . '" -d "' . $sFolderPath . '"';
		try 
		{
			@exec($cmd, $output, $return);
			$return = $return ? (string) $return : 0;  
		} 
		catch (Exception $e) 
		{
			$return = $e->getMessage();
		}
		return $return;
	}
	
	/**
	 * expects zip installed in you box and being in your environment path
	 * see http://www.info-zip.org/	 	
	 * @return {variant} integer 0 on success, msg string on failure
	 */
	function zip($sArchive, $sFilespec) 
	{
		$curpath = getcwd();
		$cmd = IL_OP_ZIP_EXE . ' -ur "' . $sArchive . '" "' . basename($sFilespec) . '"';
		@chdir(dirname($sFilespec));
		try 
		{
			@exec($cmd, $output, $return);
			$return = $return ? (string) $return : 0;  
		} 
		catch (Exception $e) 
		{
			$return = $e->getMessage();
		}
		@chdir($curpath);
		return $return;
	}
	
	
}

?>
