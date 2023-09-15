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
 * AMD field type date
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionDate extends ilAdvancedMDFieldDefinition
{
    //
    // generic types
    //

    public function getType(): int
    {
        return self::TYPE_DATE;
    }


    //
    // ADT
    //

    protected function initADTDefinition(): ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("Date");
    }


    //
    // import/export
    //

    public function getValueForXML(ilADT $element): string
    {
        return $element->getDate()->get(IL_CAL_DATE);
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $this->getADT()->setDate(new ilDate($a_cdata, IL_CAL_DATE));
    }

    public function importFromECS(string $a_ecs_type, $a_value, string $a_sub_id): bool
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
    public function getLuceneSearchString($a_value): string
    {
        $start = new ilDate('1970-01-01', IL_CAL_DATE);
        $end = new ilDate('2038-01-19', IL_CAL_DATE);

        if (!($a_value['lower'] ?? false) || !($a_value['upper'])) {
            return '';
        }
        if ($a_value['lower'] ?? false) {
            $start = ilCalendarUtil::parseIncomingDate($a_value['lower']);
        }
        if ($a_value['upper'] ?? false) {
            $end = ilCalendarUtil::parseIncomingDate($a_value['upper']);
        }
        return '[' . $start->get(IL_CAL_DATE) . ' TO ' . $end->get(IL_CAL_DATE) . ']';
    }
}
