<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


/**
* Export
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @defgroup ServicesExport Services/Export
* @ingroup	ServicesExport
*/
class ilExport
{
	// this should be part of module.xml and be parsed in the future
	static $export_implementer = array("tst", "lm", "glo");
	
	// file type short (this is a workaround, for resource types,
	// that used the wrong file type string in the past
	static $file_type_str = array("tst" => "test_");

	/**
	* Get file type string
	*
	* @param	string		Object Type
	*/
	static function _getFileTypeString($a_obj_type)
	{
		if (!empty(self::$file_type_str[$a_obj_type]))
		{
			return self::$file_type_str[$a_obj_type];
		}
		else
		{
			return $a_obj_type;
		}
	}
	
	/**
	* Get a list of subitems of a repository resource, that implement
	* the export. Includes also information on last export file.
	*/
	static function _getValidExportSubItems($a_ref_id)
	{
		global $tree;
		
		$valid_items = array();
		$sub_items = $tree->getSubTree($tree->getNodeData($a_ref_id));
		foreach ($sub_items as $sub_item)
		{
			if (in_array($sub_item["type"], self::$export_implementer))
			{
				$valid_items[] = array("type" => $sub_item["type"],
					"title" => $sub_item["title"], "ref_id" => $sub_item["child"],
					"obj_id" => $sub_item["obj_id"],
					"timestamp" =>
					ilExport::_getLastExportFileDate($sub_item["obj_id"], "xml", $sub_item["type"]));
			}
		}
		return $valid_items;
	}
	
	/**
	* Get date of last export file
	*/
	static function _getLastExportFileDate($a_obj_id, $a_type = "", $a_obj_type = "")
	{
		$files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
		if (is_array($files))
		{
			$files = ilUtil::sortArray($files, "timestamp", "desc");
			return $files[0]["timestamp"];
		}
		return false;
	}
	
	/**
	* Get last export file information
	*/
	static function _getLastExportFileInformation($a_obj_id, $a_type = "", $a_obj_type = "")
	{
		$files = ilExport::_getExportFiles($a_obj_id, $a_type, $a_obj_type);
		if (is_array($files))
		{
			$files = ilUtil::sortArray($files, "timestamp", "desc");
			return $files[0];
		}
		return false;
	}

	/**
	* Get export directory
	*
	* @param	integer		Object ID
	* @param	string		Export Type ("xml", "html", ...)
	* @param	string		Object Type
	*/
	function _getExportDirectory($a_obj_id, $a_type = "xml", $a_obj_type = "")
	{
		if ($a_obj_type == "")
		{
			$a_obj_type = ilObject::_lookupType($a_obj_id);
		}

		if ($a_type !=  "xml")
		{
			$export_dir = ilUtil::getDataDir()."/".$a_obj_type."_data"."/".$a_obj_type."_".$a_obj_id."/export_".$a_type;
		}
		else
		{
			$export_dir = ilUtil::getDataDir()."/".$a_obj_type."_data"."/".$a_obj_type."_".$a_obj_id."/export";
		}

		return $export_dir;
	}

	/**
	* Get Export Files
	*/
	function _getExportFiles($a_obj_id, $a_export_types = "", $a_obj_type = "")
	{

		if ($a_obj_type == "")
		{
			$a_obj_type = ilObject::_lookupType($a_obj_id);
		}

		if ($a_export_types == "")
		{
			$a_export_types = array("xml");
		}
		if (!is_array($a_export_types))
		{
			$a_export_types = array($a_export_types);
		}

		// initialize array
		$file = array();
		
		$types = $a_export_types;

		foreach($types as $type)
		{
			$dir = ilExport::_getExportDirectory($a_obj_id, $type, $a_obj_type);
			
			// quit if import dir not available
			if (!@is_dir($dir) or
				!is_writeable($dir))
			{
				continue;
			}

			// open directory
			$h_dir = dir($dir);

			// get files and save the in the array
			while ($entry = $h_dir->read())
			{
				if ($entry != "." and
					$entry != ".." and
					substr($entry, -4) == ".zip" and
					ereg("^[0-9]{10}_{2}[0-9]+_{2}(".ilExport::_getFileTypeString($a_obj_type)."_)*[0-9]+\.zip\$", $entry))
				{
					$ts = substr($entry, 0, strpos($entry, "__"));
					$file[$entry.$type] = array("type" => $type, "file" => $entry,
						"size" => filesize($dir."/".$entry),
						"timestamp" => $ts);
				}
			}
	
			// close import directory
			$h_dir->close();
		}

		// sort files
		ksort ($file);
		reset ($file);
		return $file;
	}

	/**
	* Create export directory
	*/
	function _createExportDirectory($a_obj_id, $a_export_type = "xml", $a_obj_type = "")
	{
		global $ilErr;
		
		if ($a_obj_type == "")
		{
			$a_obj_type = ilObject::_lookupType($a_obj_id);
		}

		$data_dir = ilUtil::getDataDir()."/".$a_obj_type."_data";
		ilUtil::makeDir($data_dir);
		if(!is_writable($data_dir))
		{
			$ilErr->raiseError("Data Directory (".$data_dir
				.") not writeable.",$ilErr->FATAL);
		}
		
		// create resource data directory
		$res_dir = $data_dir."/".$a_obj_type."_".$a_obj_id;
		ilUtil::makeDir($res_dir);
		if(!@is_dir($res_dir))
		{
			$ilErr->raiseError("Creation of Glossary Directory failed.",$ilErr->FATAL);
		}

		// create Export subdirectory (data_dir/glo_data/glo_<id>/Export)
		if ($a_export_type != "xml")
		{
			$export_dir = $res_dir."/export_".$a_export_type;
		}
		else
		{
			$export_dir = $res_dir."/export";
		}

		ilUtil::makeDir($export_dir);

		if(!@is_dir($export_dir))
		{
			$ilErr->raiseError("Creation of Export Directory failed.",$ilErr->FATAL);
		}
	}

	/**
	* Generates an index.html file including links to all xml files included
	* (for container exports)
	*/
	function _generateIndexFile($a_filename, $a_obj_id, $a_files, $a_type = "")
	{
		global $lng;
		
		$lng->loadLanguageModule("export");
		
		if ($a_type == "")
		{
			$a_type = ilObject::_lookupType($a_obj_id);
		}
		$a_tpl = new ilTemplate("tpl.main.html", true, true);
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$a_tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
		$a_tpl->getStandardTemplate();
		$a_tpl->setTitle(ilObject::_lookupTitle($a_obj_id));
		$a_tpl->setDescription($lng->txt("export_export_date").": ".
			date('Y-m-d H:i:s', time())." (".date_default_timezone_get().")");
		$f_tpl = new ilTemplate("tpl.export_list.html", true, true, "Services/Export");
		foreach ($a_files as $file)
		{
			$f_tpl->setCurrentBlock("file_row");
			$f_tpl->setVariable("TITLE", $file["title"]);
			$f_tpl->setVariable("TYPE", $lng->txt("obj_".$file["type"]));
			$f_tpl->setVariable("FILE", $file["file"]);
			$f_tpl->parseCurrentBlock();
		}
		$a_tpl->setContent($f_tpl->get());
		$index_content = $a_tpl->get("DEFAULT", false, false, false, true, false, false);

		$f = fopen ($a_filename, "w");
		fwrite($f, $index_content);
		fclose($f);
	}
}
