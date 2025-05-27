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

class ilDclDateSelectionFieldRepresentation extends ilDclSelectionFieldRepresentation
{
    protected function buildOptionsInput(): ilDclGenericMultiInputGUI
    {
        $selection_options = new ilDclGenericMultiInputGUI(
            $this->lng->txt('dcl_selection_options'),
            'prop_' . $this->field::PROP_SELECTION_OPTIONS
        );

        $date = new ilDateTimeInputGUI($this->lng->txt('dcl_selection_options'), 'selection_value');
        $selection_options->setInput($date);

        return $selection_options;
    }
}
