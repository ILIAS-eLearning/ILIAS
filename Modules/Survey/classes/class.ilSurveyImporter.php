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
	 * @var ilObjSurvey
	 */
	protected static $survey;

	/**
	 * Init
	 *
	 * @param
	 * @return
	 */
	function init()
	{
		include_once("./Modules/Survey/classes/class.ilSurveyDataSet.php");
		$this->ds = new ilSurveyDataSet();
		$this->ds->setDSPrefix("ds");
		$this->ds->setImport($this);
	}


	/**
	 * Set current survey object (being imported). This is done statically,
	 * since the survey import uses multiple input files being processed for every survey
	 * and all of these need the current survey object (ilSurveyImporter is intantiated multiple times)
	 *
	 * @param ilObjSurvey $a_val survey
	 */
	function setSurvey(ilObjSurvey $a_val)
	{
		self::$survey = $a_val;
	}

	/**
	 * Get current survey object
	 *
	 * @return ilObjSurvey survey
	 */
	function getSurvey()
	{
		return self::$survey;
	}

	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		if ($a_entity == "svy")
		{
			// Container import => test object already created
			if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id))
			{
				$newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
				#$newObj->setImportDirectory(dirname(rtrim($this->getImportDirectory(),'/')));
			} else    // case ii, non container
			{
				$new_id = $a_mapping->getMapping("Modules/Survey", "svy", 0);
				$newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
			}
			$this->setSurvey($newObj);

			include_once "./Services/Survey/classes/class.SurveyImportParser.php";

			list($xml_file) = $this->parseXmlFileNames();

			if (!@file_exists($xml_file))
			{
				$GLOBALS['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
				return false;
			}

			$import = new SurveyImportParser(-1, $xml_file, true, $a_mapping);
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
						$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
						ilObjMediaObject::_saveUsage($media_object->getId(), "svy:html", $newObj->getId());
						$newObj->setIntroduction(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getIntroduction()));
						$newObj->setOutro(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $newObj->getOutro()));
					} else
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
		}
		else
		{
			include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
			$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
					$a_xml, $this->ds, $a_mapping);
		}

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