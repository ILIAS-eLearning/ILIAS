<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for question pools
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */

class ilTestQuestionPoolImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_setImportDirectory($this->getImportDirectory());

		// Container import => test object already created
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

		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		ilObjQuestionPool::_setImportDirectory($this->getImportDirectory());

		// FIXME: Copied from ilObjQuestionPoolGUI::importVerifiedFileObject
		// TODO: move all logic to ilObjQuestionPoolGUI::importVerifiedFile and call 
		// this method from ilObjQuestionPoolGUI and ilTestImporter 

		$GLOBALS['ilLog']->write(__METHOD__.': xml file: '. $xml_file . ", qti file:" . $qti_file);
		
		$newObj->setOnline(true);
		$newObj->saveToDb();
		
		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_PARSE_QTI, $newObj->getId(), null);
		$result = $qtiParser->startParsing();

		// import page data
		if (strlen($xml_file))
		{
			include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
			$contParser = new ilContObjParser($newObj, $xml_file, basename($this->getImportDirectory()));
			$contParser->setQuestionMapping($qtiParser->getImportMapping());
			$contParser->startParsing();
		}

		$a_mapping->addMapping("Modules/TestQuestionPool", "qpl", $a_id, $newObj->getId());
		ilObjQuestionPool::_setImportDirectory(null);
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
		$qti = $this->getImportDirectory().'/'.preg_replace('/qpl/', 'qti', $basename).'.xml';
		
		return array($xml,$qti);
	}
}

?>