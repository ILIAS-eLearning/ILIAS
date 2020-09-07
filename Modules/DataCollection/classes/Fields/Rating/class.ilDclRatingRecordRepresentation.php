<?php

/**
 * Class ilDclMobRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingRecordRepresentation extends ilDclBaseRecordRepresentation
{

    /**
     * Return rating html
     *
     * @return string
     */
    public function getHTML($link = true)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $rgui = new ilRatingGUI();
        $rgui->setObject($this->getRecordField()->getRecord()->getId(), "dcl_record", $this->getRecordField()->getField()->getId(), "dcl_field");
        $ilCtrl->setParameterByClass("ilratinggui", "field_id", $this->getRecordField()->getField()->getId());
        $ilCtrl->setParameterByClass("ilratinggui", "record_id", $this->getRecordField()->getRecord()->getId());
        $html = $rgui->getHTML();

        return $html;
    }


    /**
     * @inheritDoc
     */
    public function fillFormInput($form)
    {
        // explicitly do nothing. the value is handled via the model and gui of ilRating.
    }
}
