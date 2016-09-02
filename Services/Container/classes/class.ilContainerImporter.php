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

		/**
		 * @var ilLogger
		 */
		$log = ilLoggerFactory::getLogger('exp');
		$log->debug('Import xml: '. $a_xml);
		$log->debug('Using id: ' . $a_id);
		
		$parser = new ilContainerXmlParser($a_mapping,trim($a_xml));
		$parser->parse($a_id);		
	}
	
	function finalProcessing($a_mapping)
	{				
		// pages
		include_once('./Services/COPage/classes/class.ilPageObject.php');
		$page_map = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
		foreach ($page_map as $old_pg_id => $new_pg_id)
		{		
			$parts = explode(':', $old_pg_id);
			$pg_type = $parts[0];
			$old_obj_id = $parts[1];
			$new_pg_id = array_pop(explode(':', $new_pg_id));
			$new_obj_id = $a_mapping->getMapping('Services/Container', 'objs', $old_obj_id);
			ilPageObject::_writeParentId($pg_type, $new_pg_id, $new_obj_id);
		}
		
		// style
		include_once('./Services/Style/classes/class.ilObjStyleSheet.php');
		$sty_map = $a_mapping->getMappingsOfEntity('Services/Style', 'sty');
		foreach ($sty_map as $old_sty_id => $new_sty_id)
		{			
			if(is_array(ilContainerXmlParser::$style_map[$old_sty_id]))
			{
				foreach(ilContainerXmlParser::$style_map[$old_sty_id] as $obj_id)
				{
					ilObjStyleSheet::writeStyleUsage($obj_id, $new_sty_id);	
				}
			}				
		}
	}
}
?>