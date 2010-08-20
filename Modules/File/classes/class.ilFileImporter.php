<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilFileImporter extends ilXmlImporter
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
		}
		else	// case ii, non container
		{
			$newObj = new ilObjFile();
			$newObj->create(true);
		}

		include_once("./Modules/File/classes/class.ilFileXMLParser.php");
		$parser = new ilFileXMLParser($newObj, $a_xml);
		$parser->setImportDirectory($this->getImportDirectory());
		$parser->startParsing();

		$newObj->createProperties(false,false);
		
		$parser->setFileContents();
		$this->current_obj = $newObj;

		$newObj->update();		// this is necessary for case ii (e.g. wiki import)

		$a_mapping->addMapping("Modules/File", "file", $a_id, $newObj->getId());
		$a_mapping->addMapping("Services/MetaData", "md", $a_id.":0:file",
			$newObj->getId().":0:file");

	}
	
}

?>