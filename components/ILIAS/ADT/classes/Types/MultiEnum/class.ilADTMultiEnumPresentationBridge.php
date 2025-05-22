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

class ilADTMultiEnumPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            $res = array();

            $options = $this->getADT()->getCopyOfDefinition()->getOptions();
            foreach ((array) $this->getADT()->getSelections() as $value) {
                if (array_key_exists($value, $options)) {
                    $res[] = $this->decorate($options[$value]);
                }
            }

            return implode(", ", $res);
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return implode(";", (array) $this->getADT()->getSelections());
        }
        return '';
    }
}
