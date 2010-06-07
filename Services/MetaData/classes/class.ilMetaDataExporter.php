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

	public function getXmlRepresentation($a_entity, $a_target_release, $a_ids)
	{
		$xml = $this->getExportStartTag($a_entity, $a_target_release);
		include_once("./Services/MetaData/classes/class.ilMD2XML.php");
		foreach ($a_ids as $id)
		{
			$id = explode(":", $id);
			$mdxml = new ilMD2XML($id[0], $id[1], $id[2]);
			$mdxml->setExportMode();
			$mdxml->startExport();
			$xml.= $this->getExportRecordStartTag($id[0].":".$id[1].":".$id[2]);
			$xml.= $mdxml->getXml();
			$xml.= $this->getExportRecordEndTag();
		}
		$xml.= $this->getExportEndTag();

		return $xml;
	}
}

?>