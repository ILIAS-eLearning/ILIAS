<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
 * AMD field type location
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionLocation extends ilAdvancedMDFieldDefinition
{
    //
    // generic types
    //
    
    public function getType()
    {
        return self::TYPE_LOCATION;
    }
    
    public function isFilterSupported()
    {
        return false;
    }
    
    
    //
    // ADT
    //
    
    protected function initADTDefinition()
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("Location");
    }
    
    
    //
    // import/export
    //
    
    public function getValueForXML(ilADT $element)
    {
        return $element->getLatitude() . "#" . $element->getLongitude() . "#" . $element->getZoom();
    }
    
    public function importValueFromXML($a_cdata)
    {
        $parts = explode("#", $a_cdata);
        if (sizeof($parts) == 3) {
            $adt = $this->getADT();
            $adt->setLatitude($parts[0]);
            $adt->setLongitude($parts[1]);
            $adt->setZoom($parts[2]);
        }
    }
        
    
    //
    // search
    //
    
    public function getLuceneSearchString($a_value)
    {
        // #14777 - currently not supported
        return;
        
        if ($a_value["tgl"]) {
        }
    }
}
