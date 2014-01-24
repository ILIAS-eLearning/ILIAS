<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for files
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesFile
 */
class ilFileExporter extends ilXmlExporter
{

	/**
	 * Initialisation
	 */
	function init()
	{
	}

	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		$md_ids = array();
		foreach ($a_ids as $file_id)
		{
			$md_ids[] = $file_id.":0:file";
		}

		return array (
			array(
				"component" => "Services/MetaData",
				"entity" => "md",
				"ids" => $md_ids)
			);
	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		
		include_once("./Modules/File/classes/class.ilObjFile.php");
		include_once("./Modules/File/classes/class.ilFileXMLWriter.php");
		if (ilObject::_lookupType($a_id) == "file")
		{
			$file = new ilObjFile($a_id, false);
			$writer = new ilFileXMLWriter();
			$writer->setFile($file);
			$writer->setOmitHeader(true);
			$writer->setAttachFileContents(ilFileXMLWriter::$CONTENT_ATTACH_COPY);
			ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
			$writer->setFileTargetDirectories($this->getRelativeExportDirectory(),
				$this->getAbsoluteExportDirectory());
			$writer->start();
			$xml.= $writer->getXml();
		}

		return $xml;
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
				"namespace" => "http://www.ilias.de/Modules/File/file/4_1",
				"xsd_file" => "ilias_file_4_1.xsd",
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>