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

	public function getXmlRepresentation($a_entity, $a_target_release, $a_ids)
	{
		$xml = $this->getExportStartTag($a_entity, $a_target_release);
		
		include_once("./Modules/File/classes/class.ilObjFile.php");
		include_once("./Modules/File/classes/class.ilFileXMLWriter.php");
		$dir_cnt = 1;
		foreach ($a_ids as $id)
		{
			if (ilObject::_lookupType($id) == "file")
			{
				$file = new ilObjFile($id, false);
				$writer = new ilFileXMLWriter();
				$writer->setFile($file);
				$writer->setOmitHeader(true);
				$writer->setAttachFileContents(ilFileXMLWriter::$CONTENT_ATTACH_COPY);
				$writer->setFileTargetDirectories($this->getRelativeExportDirectory()."/dir_".$dir_cnt,
					$this->getAbsoluteExportDirectory()."/dir_".$dir_cnt);
				ilUtil::makeDirParents($this->getAbsoluteExportDirectory()."/dir_".$dir_cnt);
				$xml.= $this->getExportRecordStartTag($id);
				$writer->start();
				$xml.= $writer->getXml();
				$xml.= $this->getExportRecordEndTag();
			}
			$dir_cnt++;
		}
		$xml.= $this->getExportEndTag();

		return $xml;
	}
}

?>