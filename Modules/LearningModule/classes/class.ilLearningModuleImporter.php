<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesLearningModule
 */
class ilLearningModuleImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once './Modules/File/classes/class.ilObjFile.php';

		// case i container
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
			$newObj->createLMTree();
			$newObj->createImportDirectory();
			
		}
		else	// case ii, non container
		{
			// Shouldn't happen
			$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
			return false;
		}
		
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser(
			$newObj, 
			$this->fetchXmlFile(),
			$this->fetchSubdir(),
			NULL
		);
		$contParser->setQuestionMapping(array());
		$contParser->startParsing();
		ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());
		$newObj->MDUpdateListener('General');
		
		
		$GLOBALS['ilLog']->write(__METHOD__.': Import dir is '.$this->getImportDirectory());
		
		$a_mapping->addMapping("Modules/LearningModule", "lm", $a_id, $newObj->getId());
	}
	
	/**
	 * Read xmlfile from import directory
	 * @return string 
	 */
	protected function fetchXmlFile()
	{
		return $this->getImportDirectory().DIRECTORY_SEPARATOR.$this->fetchSubdir().'.xml';
	}
	
	/**
	 * Read subdir name
	 * @return 
	 */
	protected function fetchSubdir()
	{
		return basename(rtrim($this->getImportDirectory(),'/'));
	}
}

?>