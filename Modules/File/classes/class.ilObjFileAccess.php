<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("classes/class.ilObjectAccess.php");

/**
* Access class for file objects.
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesFile
*/
class ilObjFileAccess extends ilObjectAccess
{
	// BEGIN WebDAV cache inline file extensions
	/**
	 * Contains an array of extensions separated by space.
	 * Since this array is needed for every file object displayed on a 
	 * repository page, we only create it once, and cache it here.
	 * @see function _isFileInline
	 */
	private static $_inlineFileExtensionsArray;
	// END WebDAV cache inline file extensions


	/**
	 * get commands
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands()
	{
		$commands = array();
		$commands[] = array("permission" => "read", "cmd" => "sendfile", "lang_var" => "download",
				"default" => true);
		// BEGIN ChangeEvent show info screen for file object
		$commands[] = array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "info_short", "enable_anonymous" => "false", 'level'=>2);
		// END ChangeEvent show info screen for file object
		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
		$commands[] = array("permission" => "read", "cmd" => "versions", "lang_var" => "versions");
		
		return $commands;
	}
	
	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "file" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}

	/**
	* lookup version
	*/
	function _lookupVersion($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return ilUtil::stripSlashes($row->version);
	}

	/**
	* lookup size
	*/
	function _lookupFileSize($a_id, $a_as_string = false, $long_info = false)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		// BEGIN WebDAV: getFileSize from Database
		$size = $row->file_size;
		// END PATCH WebDAV getFileSize from Database
		
		if ($a_as_string)
		{
			// BEGIN WebDAV: Use sizeToString function.
			return self::_sizeToString($size, $long_info);
			// END WebDAV: Use sizeToString function.
			
		}
		
		return $size;
	}


	// BEGIN WebDAV: sizeToString function.
	/**
	 * Returns the specified file size value in a human friendly form.
	 * The value returned by this function is the same value that Windows
	 * and Mac OS X returns for a file. The value is a GigiBig, MegiBit,
	 * KiliBit or byte value based on 1024.
	 */
	function _sizeToString($size, $long_info = false)
	{
		global $lng;
		require_once 'classes/class.ilFormat.php';

		$result;

		$formattedBytes = ilFormat::fmtFloat($size,0,$lng->txt('lang_sep_thousand'));

		if ($size > 1073741824)
		{
			$result = round($size/1073741824,1)." GB";
			if ($long_info) {
				$result .= " (".$formattedBytes." bytes)";
			}
		}
		else if ($size > 1048576)
		{
			$result = round($size/1048576,1)." MB";
			if ($long_info) {
				$result .= " (".$formattedBytes." bytes)";
			}
		}
		else if ($size > 1024)
		{
			$result = round($size/1024,1)." KB";
			if ($long_info) {
				$result .= " (".$formattedBytes." bytes)";
			}
		}
		else
		{
			$result = $formattedBytes." bytes";		
		}
		return $result;
	}
	// END WebDAV: sizeToString function.

	/**
	* lookup suffix
	*/
	function _lookupSuffix($a_id)
	{
		include_once('Modules/File/classes/class.ilFSStorageFile.php');
		
		global $ilDB;
		
		// BEGIN WebDAV: Filename suffix is determined by file title
		$q = "SELECT * FROM object_data WHERE obj_id = '".$a_id."'";
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		require_once 'Modules/File/classes/class.ilObjFile.php';
		return self::_getFileExtension($row->title);
		// END WebDAV: Filename suffix is determined by file title
	}

	//BEGIN WebDAV: Get used disk space..
	/**
	 * Returns the number of bytes used on the harddisk by the file object
	 * with the specified object id.
	 * @param int object id of a file object.
	 */
	function _getDiskSpaceUsed($a_id)
	{
		include_once('Modules/File/classes/class.ilFSStorageFile.php');
		$fileStorage = new ilFSStorageFile($a_id);
		$dir = $fileStorage->getAbsolutePath();
		return ilUtil::dirsize($dir);
	}
	
	/**
	 * Returns the number of bytes used on the harddisk by the user with
	 * the specified user id.
	 * @param int user id.
	 */
	function _getDiskSpaceUsedBy($user_id, $as_string = false)
	{
		// 
		global $ilDB, $lng;
		
		$q = "SELECT obj_id FROM object_data WHERE type = 'file' AND owner = $user_id";
		$us_set = $ilDB->query($q);
		$size = 0;
		$count = 0;
		while($us_rec = $us_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$size += ilObjFileAccess::_getDiskSpaceUsed($us_rec["obj_id"]);
			$count++;
		}
		
		return ($as_string) ? $count.' '.$lng->txt('files').', '.self::_sizeToString($size) : $size;
	}
	//END WebDAV: Get used disk space.

	// BEGIN WebDAV: Get file extension, determine if file is inline, guess file type.
	/**
	 * Returns true, if the specified file shall be displayed inline in the browser.
	 */
	public static function _isFileInline($a_file_name)
	{
		if (self::$_inlineFileExtensionsArray == null)
		{
			require_once 'Services/Administration/classes/class.ilSetting.php';
			$settings = new ilSetting('file_access');
			self::$_inlineFileExtensionsArray = preg_split('/ /', $settings->get('inline_file_extensions'), -1, PREG_SPLIT_NO_EMPTY);
		}

		$extension = self::_getFileExtension($a_file_name);

		return in_array($extension, self::$_inlineFileExtensionsArray);
	}
	/**
	 * Gets the file extension of the specified file name.
	 * The file name extension is converted to lower case before it is returned.
	 *
	 * For example, for the file name "HELLO.MP3", this function returns "mp3".
	 *
	 * A file name extension can have multiple parts. For the file name 
	 * "hello.tar.gz", this function returns "gz".
	 *
	 *
	 * @param	string	$a_file_name	The file name
	 */
	public static function _getFileExtension($a_file_name) 
	{
		if (preg_match('/\.([a-z0-9]+)\z/i',$a_file_name,$matches) == 1)
		{
			return strtolower($matches[1]);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Returns true, if a file with the specified name, is usually hidden from
	 * the user.
	 *
	 * - Filenames starting with '.' are hidden Unix files
	 * - Filenames ending with '~' are temporary Unix files
	 * - Filenames starting with '~$' are temporary Windows files
	 * - The file "Thumbs.db" is a hidden Windows file 
	 */
	public static function _isFileHidden($a_file_name) 
	{
		return substr($a_file_name,0,1) == '.' ||
			substr($a_file_name,-1,1) == '~' ||
			substr($a_file_name,0,2) == '~$' ||
			 $a_file_name == 'Thumbs.db'; 
	}
	// END WebDAV: Get file extension, determine if file is inline, guess file type.
}

?>
