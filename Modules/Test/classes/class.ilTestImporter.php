<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilTestImporter extends ilXmlImporter
{
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		/* @var ilObjTest $newObj */
		
		// Container import => test object already created
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		ilObjTest::_setImportDirectory($this->getImportDirectoryContainer());

		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			// container content
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
			$_SESSION['tst_import_subdir'] = $this->getImportPackageName();
			$questionParentObjId = $newObj->getId();
		}
		else
		{
			// single object
			$new_id = $a_mapping->getMapping('Modules/Test', 'tst', 'new_id');
			$newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
			
			if( isset($_SESSION['tst_import_qst_parent']) )
			{
				$questionParentObjId = $_SESSION['tst_import_qst_parent'];
			}
			else
			{
				$questionParentObjId = $newObj->getId();
			}
		}

		list($xml_file,$qti_file) = $this->parseXmlFileNames();

		if(!@file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $xml_file);
			return false;
		}
		if(!@file_exists($qti_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $qti_file);
			return false;
		}

		// FIXME: Copied from ilObjTestGUI::importVerifiedFileObject
		// TODO: move all logic to ilObjTest::importVerifiedFile and call 
		// this method from ilObjTestGUI and ilTestImporter 
		$newObj->mark_schema->flush();
		

		if( isset($_SESSION['tst_import_idents']) )
		{
			$idents = $_SESSION['tst_import_idents'];
		}
		else
		{
			$idents = null;
		}

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, $questionParentObjId , $idents);
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();

		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $xml_file, basename($this->getImportDirectory()));
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		// import test results
		if(@file_exists($_SESSION["tst_import_results_file"]))
		{
			include_once("./Modules/Test/classes/class.ilTestResultsImportParser.php");
			$results = new ilTestResultsImportParser($_SESSION["tst_import_results_file"], $newObj);
			$results->startParsing();
		}
		
		if( $newObj->isRandomTest() )
		{
			$newObj->questions = array();
			$this->importRandomQuestionSetConfig($xml_file);
		}

		foreach ($qtiParser->getImportMapping() as $k => $v)
		{
			$oldQuestionId = substr($k, strpos($k, 'qst_')+strlen('qst_'));
			$newQuestionId = $v['test']; // yes, this is the new question id ^^

			$a_mapping->addMapping(
				"Services/Taxonomy", "tax_item", "tst:quest:$oldQuestionId", $newQuestionId
			);

			$a_mapping->addMapping(
				"Services/Taxonomy", "tax_item_obj_id", "tst:quest:$oldQuestionId", $newObj->getId()
			);

			$a_mapping->addMapping(
				"Modules/Test", "quest", $oldQuestionId, $newQuestionId
			);
		}
		
		$a_mapping->addMapping("Modules/Test", "tst", $a_id, $newObj->getId());

		$newObj->saveToDb();

		ilObjTest::_setImportDirectory();
		
		$this->testOBJ = $newObj;
	}

	/**
	 * Final processing
	 *
	 * @param ilImportMapping $a_mapping
	 * @return
	 */
	function finalProcessing($a_mapping)
	{
		//echo "<pre>".print_r($a_mapping, true)."</pre>"; exit;
		// get all glossaries of the import
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$maps = $a_mapping->getMappingsOfEntity("Modules/Test", "tst");
		foreach ($maps as $old => $new)
		{
			if ($old != "new_id" && (int) $old > 0)
			{
				// get all new taxonomys of this object
				$new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $old);
				if($new_tax_ids !== false)
				{
					$tax_ids = explode(":", $new_tax_ids);
					foreach($tax_ids as $tid)
					{
						ilObjTaxonomy::saveUsage($tid, $new);
					}
				}

				$taxMappings = $a_mapping->getMappingsOfEntity('Services/Taxonomy', 'tax');
				foreach($taxMappings as $oldTaxId => $newTaxId)
				{
				}
			}
		}
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
		$qti = $this->getImportDirectory().'/'.preg_replace('/test|tst/', 'qti', $basename).'.xml';
		
		return array($xml,$qti);
	}

	private function getImportDirectoryContainer()
	{
		$dir = $this->getImportDirectory();
		$dir = dirname($dir);
		return $dir;
	}

	private function getImportPackageName()
	{
		$dir = $this->getImportDirectory();
		$name = basename($dir);
		return $name;
	}
}

?>