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
* @ingroup ModulesFolder
*/
class ilFolderImporter extends ilXmlImporter
{
	private $folder = null;
	

	public function init()
	{
		include_once './Modules/Folder/classes/class.ilObjFolder.php';

		$this->folder = new ilObjFolder();
		$this->folder->setTitle('XML Import');
		$this->folder->create(true);
	}
	
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once './Modules/Folder/classes/class.ilFolderXmlParser.php';

		$GLOBALS['ilLog']->write($a_xml);

		try 
		{
			$parser = new ilFolderXmlParser($this->folder,$a_xml);
			$parser->start();
			$a_mapping->addMapping('Modules/Folder','fold',$a_rec['Id'],$this->folder->getId());
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