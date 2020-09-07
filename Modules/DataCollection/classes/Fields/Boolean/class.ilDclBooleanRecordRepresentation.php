<?php

/**
 * Class ilDclBooleanRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBooleanRecordRepresentation extends ilDclBaseRecordRepresentation
{

    /**
     * Outputs html of a certain field
     *
     * @param mixed     $value
     * @param bool|true $link
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        $value = $this->getRecordField()->getValue();
        switch ($value) {
            case 0:
                $im = ilUtil::getImagePath('icon_not_ok.svg');
                break;
            case 1:
                $im = ilUtil::getImagePath('icon_ok.svg');
                break;
        }

        return "<img src='" . $im . "'>";
    }
}
