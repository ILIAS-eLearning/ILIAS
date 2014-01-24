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
include_once('Services/Migration/DBUpdate_904/classes/class.ilUpdateUtils.php');
class ilObjFileAccess
{

	/**
	* lookup version
	*/
	function _lookupVersion($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return $row->version;
	}

	/**
	* lookup size
	*/
	function _lookupFileSize($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM file_data WHERE file_id = ".$ilDB->quote($a_id);
		$r = $ilDB->query($q);
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		include_once('Services/Migration/DBUpdate_904/classes/class.ilFSStorageFile.php');
		$fss = new ilFSStorageFile($a_id);
		$file = $fss->getAbsolutePath().'/'.$row->file_name;

		if (@!is_file($file))
		{
			$version_subdir = "/".sprintf("%03d", ilObjFileAccess::_lookupVersion($a_id));
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
		
		return $size;
	}
}

?>
