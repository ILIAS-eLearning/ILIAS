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
			$newObj->setImportDirectory(dirname(rtrim($this->getImportDirectory(),'/')));
		}
		else	// case ii, non container
		{
			// Shouldn't happen
			$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
			return false;
		}
		
		$mess = $newObj->importFromDirectory($this->getImportDirectory(),true);
		$GLOBALS['ilLog']->write(__METHOD__.': Import message is: '.$mess);

		$a_mapping->addMapping("Modules/LearningModule", "lm", $a_id, $newObj->getId());
	}
}

?>