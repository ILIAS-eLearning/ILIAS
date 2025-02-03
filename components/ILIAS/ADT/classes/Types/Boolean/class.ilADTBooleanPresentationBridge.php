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

class ilADTBooleanPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTBoolean);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: force icon?

            $presentation_value = $this->getADT()->getStatus()
                ? $this->lng->txt("yes")
                : $this->lng->txt("no");
            return $this->decorate($presentation_value);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: cast to int ?
            return $this->getADT()->getStatus() ? 1 : 0;
        }
        return 0;
    }
}
