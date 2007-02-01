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
		$commands = array
		(
			array("permission" => "read", "cmd" => "sendfile", "lang_var" => "download",
				"default" => true),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
			array("permission" => "read", "cmd" => "versions", "lang_var" => "versions")
		);
		
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
	function _lookupFileSize($a_id, $a_as_string = false)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		include_once('Modules/File/classes/class.ilFSStorageFile.php');
		$fss = new ilFSStorageFile($a_id);
		$file = $fss->getAbsolutePath().'/'.$row->file_name;
		#$file = ilUtil::getDataDir()."/files/file_".$a_id."/".$row->file_name;

		if (@!is_file($file))
		{
			$version_subdir = "/".sprintf("%03d", ilObjFileAccess::_lookupVersion($a_id));
			#$file = ilUtil::getDataDir()."/files/file_".$a_id.$version_subdir."/".$row->file_name;
			$file = $fss->getAbsolutePath().'/'.$version_subdir.'/'.$row->file_name;
			
		}

		if (is_file($file))
		{
			$size = filesize($file);
		}
		else
		{
			$size = 0;
		}
		
		if ($a_as_string)
		{
			if ($size > 1000000)
			{
				return round($size/1000000,1)." MB";
			}
			else if ($size > 1000)
			{
				return round($size/1000,1)." KB";
			}
			else
			{
				return $size." Bytes";
			}
			
		}
		
		return $size;
	}

	/**
	* lookup suffix
	*/
	function _lookupSuffix($a_id)
	{
		include_once('Modules/File/classes/class.ilFSStorageFile.php');
		
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		#$file = ilUtil::getDataDir()."/files/file_".$a_id."/".$row->file_name;
		$fss = new ilFSStorageFile($a_id);
		$file = $fss->getAbsolutePath().'/'.$row->file_name;
		if (@!is_file($file))
		{
			$version_subdir = "/".sprintf("%03d", ilObjFileAccess::_lookupVersion($a_id));
			#$file = ilUtil::getDataDir()."/files/file_".$a_id.$version_subdir."/".$row->file_name;
			$file = $fss->getAbsolutePath().'/'.$version_subdir.'/'.$row->file_name;
		}

		if (is_file($file))
		{
			$pi = pathinfo($file);
			return ".".$pi["extension"];
		}
		
		return "";
	}

}

?>
