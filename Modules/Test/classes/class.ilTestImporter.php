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
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		// Container import => test object already created
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		ilObjTest::_setImportDirectory($this->getImportDirectoryBase());

		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
		}
		else	// case ii, non container
		{
			// Shouldn't happen
			$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
			return false;
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
		
		// Important: The container question pool is the test object implicitly. If we do not pass a valid object id here, there will be problems concerning
		// filesystem path determinations
		$qpl_id = $newObj->getId();

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, $qpl_id , array());
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();
		$newObj->saveToDb();

		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $xml_file, basename($this->getImportDirectory()));
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		$a_mapping->addMapping("Modules/Test", "tst", $a_id, $newObj->getId());

		ilObjTest::_setImportDirectory();
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

	private function getImportDirectoryBase()
	{
		$dir = $this->getImportDirectory();
		$dir = dirname($dir);
		return $dir;
	}
}

?>