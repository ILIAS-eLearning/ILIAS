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
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_target_release, $a_id)
	{
		include_once("./Services/MetaData/classes/class.ilMD2XML.php");
		$id = explode(":", $a_id);
		$mdxml = new ilMD2XML($id[0], $id[1], $id[2]);
		$mdxml->setExportMode();
		$mdxml->startExport();
		
		return $mdxml->getXml();
	}
}

?>