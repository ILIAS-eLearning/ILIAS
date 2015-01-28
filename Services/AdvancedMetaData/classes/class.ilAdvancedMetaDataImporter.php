<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for adv md
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMetaDataImporter extends ilXmlImporter
{
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{				
		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDParser.php";
		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
		
		$parser = new ilAdvancedMDParser($a_id, $a_mapping);
		$parser->setXMLContent($a_xml);
		$parser->startParsing();
								
		// select records for object
		foreach($parser->getFieldIds() as $obj_id => $items)
		{			
			foreach($items as $sub_type => $field_ids)
			{
				// find records for fields
				$rec_ids = array();
				foreach($field_ids as $field_id)
				{
					$def = new ilAdvancedMDFieldDefinition($field_id);
					$rec_ids[] = $def->getRecordId();
				}			
				$rec_ids = array_unique($rec_ids);

				ilAdvancedMDRecord::saveObjRecSelection($obj_id, $sub_type, $rec_ids);
			}
		}
	}
}

?>