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

use ILIAS\UI\Component\Input\Container\Form\FormInput;

class ilDclBooleanFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(): FormInput
    {
        return $this->factory->input()->field()->checkbox(
            $this->getField()->getTitle(),
            $this->getField()->getDescription()
        );
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): mixed
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->getField()->getId()
        );
        $input->setOptions(
            [
                "" => $this->lng->txt("dcl_any"),
                "not_checked" => $this->lng->txt("dcl_not_checked"),
                "checked" => $this->lng->txt("dcl_checked"),
            ]
        );

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }
}
