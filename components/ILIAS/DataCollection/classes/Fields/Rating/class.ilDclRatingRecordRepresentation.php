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

class ilDclRatingRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public function getHTML(bool $link = true, array $options = []): string
    {
        $rgui = new ilRatingGUI();
        $rgui->setObject(
            $this->getRecordField()->getRecord()->getId(),
            "dcl_record",
            (int)$this->getRecordField()->getField()->getId(),
            "dcl_field"
        );
        $this->ctrl->setParameterByClass(ilRatingGUI::class, "field_id", $this->getRecordField()->getField()->getId());
        $this->ctrl->setParameterByClass(ilRatingGUI::class, "record_id", $this->getRecordField()->getRecord()->getId());
        if(array_key_exists("tableview_id", $options)) {
            $this->ctrl->setParameterByClass(ilObjDataCollectionGUI::class, 'tableview_id', $options['tableview_id']);
        }
        return $rgui->getHTML();
    }

    /**
     * @inheritDoc
     */
    public function fillFormInput(ilPropertyFormGUI $form): void
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }
}
