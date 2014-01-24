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
* @ingroup ModulesGroup
*/
class ilGroupImporter extends ilXmlImporter
{
	private $group = null;
	

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
		include_once './Modules/Group/classes/class.ilObjGroup.php';
		if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
		{
			$refs = ilObject::_getAllReferences($new_id);
			$this->group = ilObjectFactory::getInstanceByRefId(end($refs),false);
			#$this->group = ilObjectFactory::getInstanceByObjId($new_id,false);
		}
		// Mapping for containers without subitems
		elseif($new_id = $a_mapping->getMapping('Services/Container','refs',0))
		{
			$this->group = ilObjectFactory::getInstanceByRefId($new_id,false);
		}
		elseif(!$this->group instanceof ilObjGroup)
		{
			$this->group = new ilObjGroup();
			$this->group->create(true);
		}

		include_once './Modules/Group/classes/class.ilGroupXMLParser.php';
		#$GLOBALS['ilLog']->write($a_xml);

		try 
		{
			$parser = new ilGroupXMLParser($a_xml,0);
			$parser->setGroup($this->group);
			$parser->setMode(ilGroupXMLParser::$UPDATE);
			$parser->startParsing();
			$a_mapping->addMapping('Modules/Group','grp',$a_id,$this->group->getId());
		}
		catch(ilSaxParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
		catch(ilWebLinkXMLParserException $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Parsing failed with message, "'.$e->getMessage().'".');
		}
	}
}
?>