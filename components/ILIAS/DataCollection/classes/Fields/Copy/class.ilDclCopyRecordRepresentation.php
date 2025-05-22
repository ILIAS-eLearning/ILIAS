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

class ilDclCopyRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public function parseFormInput($value)
    {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $value = [$value];
        }

        return parent::parseFormInput($value);
    }

    public function getHTML(bool $link = true, array $options = []): string
    {
        $return = parent::getHTML();
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE)) {
            $return = str_replace('; ', '<br>', $return);
        }
        return $return;
    }
}
