<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for meta data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesCOPage
 */
class ilCOPageExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
	}


	/**
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{

		// get all media objects and files of the page
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$mob_ids = array();
		$file_ids = array();
		foreach ($a_ids as $pg_id)
		{
			$pg_id = explode(":", $pg_id);

			// get media objects
			$mids = ilObjMediaObject::_getMobsOfObject($pg_id[0].":pg", $pg_id[1]);
			foreach ($mids as $mid)
			{
				if (ilObject::_lookupType($mid) == "mob")
				{
					$mob_ids[] = $mid;
				}
			}

			// get files
			$files = ilObjFile::_getFilesOfObject($pg_id[0].":pg", $pg_id[1]);
			foreach ($files as $file)
			{
				if (ilObject::_lookupType($file) == "file")
				{
					$file_ids[] = $file;
				}
			}
		}

		return array (
			array(
				"component" => "Services/MediaObjects",
				"entity" => "mob",
				"ids" => $mob_ids),
			array(
				"component" => "Modules/File",
				"entity" => "file",
				"ids" => $file_ids)
			);
	}

	/**
	 * Get xml representation
	 *
	 * @param string	entity
	 * @param string	target release
	 * @param array		ids
	 * @return string	xml
	 */
	public function getXmlRepresentation($a_entity, $a_target_release, $a_id)
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		
		$id = explode(":", $a_id);

		$page_object = new ilPageObject($id[0], $id[1]);
		$page_object->buildDom();
		$page_object->insertInstIntoIDs($a_inst);
		$pxml = $page_object->getXMLFromDom(false, false, false, "", true);
		$pxml = str_replace("&","&amp;", $pxml);
		$xml = "<PageObject>";
		$xml.= $pxml;
		$xml.= "</PageObject>";
		$page_object->freeDom();

		return $xml;
	}
}

?>