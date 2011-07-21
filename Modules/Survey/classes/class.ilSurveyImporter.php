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
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		
		list($xml_file) = $this->parseXmlFileNames();

		if(!@file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Cannot find xml definition: '. $xml_file);
			return false;
		}

		$pool_ref = $a_mapping->getMapping('Services/Container','spl',$newObj->getId());
		$pool_obj = ilObject::_lookupObjId($pool_ref);
		
		$spl = new ilObjSurveyQuestionPool($pool_obj, FALSE);	
		$import = new SurveyImportParser($spl, $xml_file, TRUE);
		$import->setSurveyObject($newObj);
		$import->startParsing();

		// Finally delete tmp question pool
		if($spl->getType() == 'spl')
		{
			$spl->delete();
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