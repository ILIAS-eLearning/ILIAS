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
		include_once("./Services/COPage/classes/class.ilCOPageDataSet.php");
		$this->ds = new ilCOPageDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
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
		if ($a_entity == "pg")
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
		
		return array();
	}
	
	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		if ($a_entity == "pgtp")
		{
			$pg_ids = array();
			foreach ($a_ids as $id)
			{
				$pg_ids[] = "stys:".$id;
			}
	
			return array(
				array(
					"component" => "Services/COPage",
					"entity" => "pg",
					"ids" => $pg_ids)
				);
		}
		
		return array();
	}


	/**
	 * Get xml representation
	 *
	 * @param string	entity
	 * @param string	schema version
	 * @param array		ids
	 * @return string	xml
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		if ($a_entity == "pg")
		{
			include_once("./Services/COPage/classes/class.ilPageObject.php");
			
			$id = explode(":", $a_id);
	
			include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
			$page_object = ilPageObjectFactory::getInstance($id[0], $id[1]);
			$page_object->buildDom();
			$page_object->insertInstIntoIDs(IL_INST_ID);
			$pxml = $page_object->getXMLFromDom(false, false, false, "", true);
			$pxml = str_replace("&","&amp;", $pxml);
			$xml = "<PageObject>";
			$xml.= $pxml;
			$xml.= "</PageObject>";
			$page_object->freeDom();
	
			return $xml;
		}
		if ($a_entity == "pgtp")
		{
			return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
		}
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	function getValidSchemaVersions($a_entity)
	{
		if ($a_entity == "pg")
		{
			return array (
				"4.2.0" => array(
					"namespace" => "http://www.ilias.de/Services/COPage/pg/4_2",
					"xsd_file" => "ilias_pg_4_2.xsd",
					"min" => "4.2.0",
					"max" => ""),
				"4.1.0" => array(
					"namespace" => "http://www.ilias.de/Services/COPage/pg/4_1",
					"xsd_file" => "ilias_pg_4_1.xsd",
					"min" => "4.1.0",
					"max" => "4.1.99")
			);
		}
		if ($a_entity == "pgtp")
		{
			return array (
				"4.2.0" => array(
					"namespace" => "http://www.ilias.de/Services/COPage/pgtp/4_1",
					"xsd_file" => "ilias_pgtp_4_1.xsd",
					"uses_dataset" => true,
					"min" => "4.2.0",
					"max" => "")
			);
		}
	}

}

?>