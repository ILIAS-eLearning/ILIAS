<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesSurvey
 */
class ilSurveyImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		// Container import => test object already created
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
			#$newObj->setImportDirectory(dirname(rtrim($this->getImportDirectory(),'/')));
		}
		else	// case ii, non container
		{
			// Shouldn't happen
			$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
			return false;
		}
		
		
		include_once "./Services/Survey/classes/class.SurveyImportParser.php";
		
		list($xml_file) = $this->parseXmlFileNames();

		if(!@file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $xml_file);
			return false;
		}

		$import = new SurveyImportParser(-1, $xml_file, TRUE);
		$import->setSurveyObject($newObj);
		$import->startParsing();

		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				$importfile = dirname($xml_file) . "/" . $mob["uri"];
				if (file_exists($importfile))
				{
					$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
					ilObjMediaObject::_saveUsage($media_object->getId(), "svy:html", $newObj->getId());
					$newObj->setIntroduction(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getIntroduction()));
					$newObj->setOutro(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getOutro()));
				}
				else
				{
					global $ilLog;
					$ilLog->write("Error: Could not open XHTML mob file for test introduction during test import. File $importfile does not exist!");
				}
			}
			$newObj->setIntroduction(ilRTE::_replaceMediaObjectImageSrc($newObj->getIntroduction(), 1));
			$newObj->setOutro(ilRTE::_replaceMediaObjectImageSrc($newObj->getOutro(), 1));
			$newObj->saveToDb();
		}
		
		$a_mapping->addMapping("Modules/Survey", "svy", $a_id, $newObj->getId());

		return true;

	}
	
	
	/**
	 * Create qti and xml file name
	 * @return array 
	 */
	protected function parseXmlFileNames()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': '.$this->getImportDirectory());
		
		$basename = basename($this->getImportDirectory());
		$xml = $this->getImportDirectory().'/'.$basename.'.xml';
		
		return array($xml);
	}
}

?>