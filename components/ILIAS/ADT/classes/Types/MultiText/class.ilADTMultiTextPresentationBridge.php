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

class ilADTMultiTextPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            $res = array();
            foreach ($this->getADT()->getTextElements() as $item) {
                if (trim($item)) {
                    $res[] = $this->decorate($item);
                }
            }
            return implode(", ", $res);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return implode(";", $this->getADT()->getTextElements());
        }
        return '';
    }
}
