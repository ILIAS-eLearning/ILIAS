<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/


/**
 * AMD field type date time
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionDateTime extends ilAdvancedMDFieldDefinition
{
    //
    // generic types
    //

    public function getType(): int
    {
        return self::TYPE_DATETIME;
    }


    //
    // ADT
    //

    protected function initADTDefinition(): ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("DateTime");
    }


    //
    // import/export
    //

    public function getValueForXML(ilADT $element): string
    {
        return $element->getDate()->get(IL_CAL_DATETIME);
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $this->getADT()->setDate(new ilDate($a_cdata, IL_CAL_DATETIME));
    }

    public function importFromECS(string $a_ecs_type, $a_value, string $a_sub_id): bool
    {
        $value = '';
        switch ($a_ecs_type) {
            case ilECSUtils::TYPE_TIMEPLACE:
                if ($a_value instanceof ilECSTimePlace) {
                    $value = new ilDateTime($a_value->{'getUT' . ucfirst($a_sub_id)}(), IL_CAL_UNIX);
                }
                break;
        }

        if ($value instanceof ilDateTime) {
            $this->getADT()->setDate($value);
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getLuceneSearchString($a_value): string
    {
        $start = new ilDateTime('1970-01-01 00:00:01', IL_CAL_DATETIME, ilTimeZone::UTC);
        $end = new ilDateTime('2038-01-19 00:00:01', IL_CAL_DATETIME, ilTimeZone::UTC);

        if (!($a_value['lower'] ?? false) || !($a_value['upper'])) {
            return '';
        }
        if ($a_value['lower'] ?? false) {
            $start = ilCalendarUtil::parseIncomingDate($a_value['lower'], true);
        }
        if ($a_value['upper'] ?? false) {
            $end = ilCalendarUtil::parseIncomingDate($a_value['upper'], true);
        }
        return '[' . $start->get(IL_CAL_FKT_DATE, 'Y-m-d\Th:m') . ' TO ' . $end->get(IL_CAL_FKT_DATE, 'Y-m-d\Th:m') . ']';
    }
}
