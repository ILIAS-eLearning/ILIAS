<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* container xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesFolder
*/
class ilContainerImporter extends ilXmlImporter
{
	

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
		include_once './Services/Container/classes/class.ilContainerXmlParser.php';

		$GLOBALS['ilLog']->write($a_xml);
		$GLOBALS['ilLog']->write($a_id);
		
		$parser = new ilContainerXmlParser($a_mapping,trim($a_xml));
		$parser->parse();
	}
}
?>