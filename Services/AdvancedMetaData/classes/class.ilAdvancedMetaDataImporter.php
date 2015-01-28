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
		foreach($parser->getRecordIds() as $obj_id => $sub_types)
		{					
			// currently only supported for wikis and glossary
			if(!in_array(ilObject::_lookupType($obj_id), array("glo", "wiki")))
			{
				continue;
			}
			
			foreach($sub_types as $sub_type => $rec_ids)
			{
				ilAdvancedMDRecord::saveObjRecSelection($obj_id, $sub_type, array_unique($rec_ids), false);			
			}
		}
	}
}

?>