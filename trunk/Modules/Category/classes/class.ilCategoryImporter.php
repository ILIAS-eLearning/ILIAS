<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesCategory
*/
class ilCategoryImporter extends ilXmlImporter
{
	private $category = null;
	

	public function init()
	{
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once './Modules/Category/classes/class.ilObjCategory.php';
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$refs = ilObject::_getAllReferences($new_id);
			$this->category = ilObjectFactory::getInstanceByRefId(end($refs),false);
		}
		// Mapping for containers without subitems
		elseif($new_id = $a_mapping->getMapping('Services/Container','refs',0))
		{
			$this->category = ilObjectFactory::getInstanceByRefId($new_id,false);
		}
		elseif(!$this->category instanceof ilObjCategory)
		{
			$this->category = new ilObjCategory();
			$this->category->create(true);
		}

		include_once './Modules/Category/classes/class.ilCategoryXmlParser.php';

		try 
		{
			$parser = new ilCategoryXmlParser($a_xml,0);
			$parser->setCategory($this->category);
			$parser->setMode(ilCategoryXmlParser::MODE_UPDATE);
			$parser->startParsing();
			$a_mapping->addMapping('Modules/Category','cat',$a_id,$this->category->getId());
		}
		catch(ilSaxParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
		catch(Excpetion $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
	}
}
?>