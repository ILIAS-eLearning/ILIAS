<?php

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

declare(strict_types=1);

class ilADTMultiTextDBBridge extends ilADTMultiDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }

    protected function readMultiRecord(ilDBStatement $a_set): void
    {
        $elements = array();

        while ($row = $this->db->fetchAssoc($a_set)) {
            $elements[] = $row[$this->getElementId()];
        }

        $this->getADT()->setTextElements($elements);
    }

    protected function prepareMultiInsert(): array
    {
        $res = [];
        foreach ((array) $this->getADT()->getTextElements() as $element) {
            $res[] = array($this->getElementId() => array("text", $element));
        }

        return $res;
    }
}
