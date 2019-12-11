<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclTextSelectionFieldRepresentation
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTextSelectionFieldRepresentation extends ilDclSelectionFieldRepresentation
{
    const PROP_SELECTION_TYPE = 'text_selection_type';
    const PROP_SELECTION_OPTIONS = 'text_selection_options';


    /**
     * @return ilDclGenericMultiInputGUI
     */
    protected function buildOptionsInput()
    {
        $selection_options = new ilDclGenericMultiInputGUI($this->lng->txt('dcl_selection_options'), 'prop_' . static::PROP_SELECTION_OPTIONS);
        $selection_options->setMulti(true, true);

        $text = new ilTextInputGUI($this->lng->txt('dcl_selection_options'), 'selection_value');
        $selection_options->addInput($text);

        return $selection_options;
    }
}
