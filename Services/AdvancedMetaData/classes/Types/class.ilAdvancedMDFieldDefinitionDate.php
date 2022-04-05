<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type date
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionDate extends ilAdvancedMDFieldDefinition
{
    //
    // generic types
    //

    public function getType() : int
    {
        return self::TYPE_DATE;
    }


    //
    // ADT
    //

    protected function initADTDefinition() : ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("Date");
    }


    //
    // import/export
    //

    public function getValueForXML(ilADT $element) : string
    {
        return $element->getDate()->get(IL_CAL_DATE);
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $this->getADT()->setDate(new ilDate($a_cdata, IL_CAL_DATE));
    }

    public function importFromECS(string $a_ecs_type, $a_value, string $a_sub_id) : bool
    {
        $value = '';
        switch ($a_ecs_type) {
            case ilECSUtils::TYPE_TIMEPLACE:
                if ($a_value instanceof ilECSTimePlace) {
                    $value = new ilDate($a_value->{'getUT' . ucfirst($a_sub_id)}(), IL_CAL_UNIX);
                }
                break;
        }

        if ($a_value instanceof ilDate) {
            $this->getADT()->setDate($value);
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getLuceneSearchString($a_value) : string
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
        return "null";
    }
}
