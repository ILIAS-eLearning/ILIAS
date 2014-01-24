<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id: $
 * @ingroup ModulesSurvey
 */
class ilSurveyQuestionPoolImporter extends ilXmlImporter
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
		
		
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		
		list($xml_file) = $this->parseXmlFileNames();

		if(!@file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $xml_file);
			return false;
		}

		// import qti data
		$qtiresult = $newObj->importObject($xml_file);

		$a_mapping->addMapping("Modules/SurveyQuestionPool", "spl", $a_id, $newObj->getId());

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