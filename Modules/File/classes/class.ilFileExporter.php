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
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_target_release, $a_id)
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
}

?>