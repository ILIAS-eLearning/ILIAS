<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for meta data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesMetaData
 */
class ilMetaDataExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		schema version
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		include_once("./Services/MetaData/classes/class.ilMD2XML.php");
		$id = explode(":", $a_id);
		$mdxml = new ilMD2XML($id[0], $id[1], $id[2]);
		$mdxml->setExportMode();
		$mdxml->startExport();
		
		return $mdxml->getXml();
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
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
				"xsd_file" => "ilias_md_4_1.xsd",
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>