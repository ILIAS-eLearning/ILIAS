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
 ********************************************************************
 */

/**
 * Class ilDclBooleanRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBooleanRecordRepresentation extends ilDclBaseRecordRepresentation
{

    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true) : string
    {
        $value = $this->getRecordField()->getValue();
        switch ($value) {
            case 0:
                $im = ilUtil::getImagePath('icon_not_ok_monochrome.svg', "/Modules/DataCollection");
                break;
            case 1:
                $im = ilUtil::getImagePath('icon_ok_monochrome.svg', "/Modules/DataCollection");
                break;
        }

        return "<img src='" . $im . "'>";
    }
}
