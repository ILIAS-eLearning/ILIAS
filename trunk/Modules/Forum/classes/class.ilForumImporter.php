<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for forums
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once 'Modules/Forum/classes/class.ilObjForum.php';

		// case i container
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
		}
		else	// case ii, non container
		{
			$newObj = new ilObjForum();
			$newObj->setType('frm');
			$newObj->create(true);
		}

		include_once 'Modules/Forum/classes/class.ilForumXMLParser.php';
		$parser = new ilForumXMLParser($newObj, $a_xml);
		$parser->setImportDirectory($this->getImportDirectory());
		$parser->setImportInstallId($this->getInstallId());
		$parser->startParsing();

		$a_mapping->addMapping("Modules/Forum", "frm", $a_id, $newObj->getId());
	}
}
?>