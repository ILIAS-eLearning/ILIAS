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
    private $logger = null;
    
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = $GLOBALS['DIC']->logger()->amet();
    }
    
    
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDParser.php";
        include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
        include_once "Services/Container/classes/class.ilContainer.php";
        include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
        
        $parser = new ilAdvancedMDParser($a_id, $a_mapping);
        $parser->setXMLContent($a_xml);
        $parser->startParsing();
        
        // records with imported values should be selected
        foreach ($parser->getRecordIds() as $obj_id => $sub_types) {
            ilContainer::_writeContainerSetting($obj_id, ilObjectServiceSettingsGUI::CUSTOM_METADATA, 1);
            
            foreach ($sub_types as $sub_type => $rec_ids) {
                ilAdvancedMDRecord::saveObjRecSelection($obj_id, $sub_type, array_unique($rec_ids), false);
            }
        }
    }
}
