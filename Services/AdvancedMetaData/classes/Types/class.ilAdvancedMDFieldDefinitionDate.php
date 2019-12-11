<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php";

/**
 * AMD field type date
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionDate extends ilAdvancedMDFieldDefinition
{
    //
    // generic types
    //
    
    public function getType()
    {
        return self::TYPE_DATE;
    }
    
    
    //
    // ADT
    //
    
    protected function initADTDefinition()
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("Date");
    }
    
    
    //
    // import/export
    //
    
    public function getValueForXML(ilADT $element)
    {
        return $element->getDate()->get(IL_CAL_DATE);
    }
    
    public function importValueFromXML($a_cdata)
    {
        $this->getADT()->setDate(new ilDate($a_cdata, IL_CAL_DATE));
    }
    
    public function importFromECS($a_ecs_type, $a_value, $a_sub_id)
    {
        switch ($a_ecs_type) {
            case ilECSUtils::TYPE_TIMEPLACE:
                if ($a_value instanceof ilECSTimePlace) {
                    $value = new ilDate($a_value->{'getUT' . ucfirst($a_sub_id)}(), IL_CAL_UNIX);
                }
                break;
        }
        
        if ($value instanceof ilDate) {
            $this->getADT()->setDate($value);
            return true;
        }
        return false;
    }
    
    
    //
    // search
    //
    
    public function getLuceneSearchString($a_value)
    {
        // see ilADTDateSearchBridgeRange::importFromPost();
        
        if ($a_value["tgl"]) {
            $start = $end = null;
            
            if ($a_value["lower"]["date"]) {
                $start = mktime(
                    12,
                    0,
                    0,
                    $a_value["lower"]["date"]["m"],
                    $a_value["lower"]["date"]["d"],
                    $a_value["lower"]["date"]["y"]
                );
            }
            if ($a_value["upper"]["date"]) {
                $end = mktime(
                    12,
                    0,
                    0,
                    $a_value["upper"]["date"]["m"],
                    $a_value["upper"]["date"]["d"],
                    $a_value["upper"]["date"]["y"]
                );
            }
            
            if ($start && $end && $start > $end) {
                $tmp = $start;
                $start = $end;
                $end = $tmp;
            }
            
            $start = new ilDate($start, IL_CAL_UNIX);
            $end = new ilDate($end, IL_CAL_UNIX);
            
            return "{" . $start->get(IL_CAL_DATE) . " TO " . $end->get(IL_CAL_DATE) . "}";
        }
    }
}
