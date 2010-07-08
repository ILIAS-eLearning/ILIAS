<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* Webresource xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilWebResourceImporter extends ilXmlImporter
{
	private $webl = null;
	

	public function init()
	{
		include_once './Modules/WebResource/classes/class.ilObjLinkResource.php';

		$this->webl = new ilObjLinkResource();
		$this->webl->setTitle('XML Import');
		$this->webl->create(true);
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once './Modules/WebResource/classes/class.ilWebLinkXmlParser.php';

		$GLOBALS['ilLog']->write($a_xml);

		try 
		{
			$parser = new ilWebLinkXmlParser($this->webl,$a_xml);
			$parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
			$parser->start();
			$a_mapping->addMapping('Modules/WebResource','webr',$a_rec['Id'],$this->webl->getId());
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
