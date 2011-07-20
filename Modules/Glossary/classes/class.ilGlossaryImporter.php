<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesGlossary
 */
class ilGlossaryImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		// case i container
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);

			$xml_file = $this->getImportDirectory().'/'.basename($this->getImportDirectory()).'.xml';
			$GLOBALS['ilLog']->write(__METHOD__.': Using XML file '.$xml_file);

		}
		else	// case ii, non container
		{
			// Shouldn't happen
			$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
			$GLOBALS['ilLog']->logStack();
			return false;
		}

		if(!file_exists($xml_file))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': ERROR Cannot find '.$xml_file);
			return false;
		}

		include_once './Modules/LearningModule/classes/class.ilContObjParser.php';
		$contParser = new ilContObjParser(
			$newObj, 
			$xml_file,
			dirname($this->getImportDirectory())
		);
		
		$contParser->startParsing();
		ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());

		$a_mapping->addMapping("Modules/Glossary", "glo", $a_id, $newObj->getId());
	}
}
?>