<?php

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
    public function getHTML(bool $link = true): string
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
