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
 * Class ilDclMobRecordRepresentation
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public function getHTML(bool $link = true): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $rgui = new ilRatingGUI();
        $rgui->setObject(
            $this->getRecordField()->getRecord()->getId(),
            "dcl_record",
            $this->getRecordField()->getField()->getId(),
            "dcl_field"
        );
        $ilCtrl->setParameterByClass("ilratinggui", "field_id", $this->getRecordField()->getField()->getId());
        $ilCtrl->setParameterByClass("ilratinggui", "record_id", $this->getRecordField()->getRecord()->getId());
        $html = $rgui->getHTML();

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function fillFormInput(ilPropertyFormGUI $form): void
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }
}
