<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Importer class for adv md
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMetaDataImporter extends ilXmlImporter
{
    private ilLogger $logger;
    
    /**
     *
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->amet();
        parent::__construct();
    }
    
    
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        
        $parser = new ilAdvancedMDParser($a_id, $a_mapping);
        $parser->setXMLContent($a_xml);
        $parser->startParsing();
        
        // records with imported values should be selected
        foreach ($parser->getRecordIds() as $obj_id => $sub_types) {
            ilContainer::_writeContainerSetting($obj_id, ilObjectServiceSettingsGUI::CUSTOM_METADATA, "1");
            
            foreach ($sub_types as $sub_type => $rec_ids) {
                ilAdvancedMDRecord::saveObjRecSelection($obj_id, $sub_type, array_unique($rec_ids), false);
            }
        }
    }
}
