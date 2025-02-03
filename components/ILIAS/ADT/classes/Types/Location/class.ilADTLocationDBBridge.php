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

class ilADTLocationDBBridge extends ilADTDBBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTLocation);
    }

    public function readRecord(array $a_row): void
    {
        $this->getADT()->setLongitude((float) $a_row[$this->getElementId() . "_long"]);
        $this->getADT()->setLatitude((float) $a_row[$this->getElementId() . "_lat"]);
        $this->getADT()->setZoom($a_row[$this->getElementId() . "_zoom"]);
    }

    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId() . "_long"] = array("float", $this->getADT()->getLongitude());
        $a_fields[$this->getElementId() . "_lat"] = array("float", $this->getADT()->getLatitude());
        $a_fields[$this->getElementId() . "_zoom"] = array("integer", $this->getADT()->getZoom());
    }

    public function supportsDefaultValueColumn(): bool
    {
        return false;
    }
}
