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

class ilDclRatingFieldRepresentation extends ilDclBaseFieldRepresentation
{
    public function getInputField(): null
    {
        return null;
    }

    public function addFilterInputFieldToTable(ilTable2GUI $table): mixed
    {
        $input = $table->addFilterItemByMetaType(
            "filter_" . $this->getField()->getId(),
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->getField()->getId()
        );
        $options = ["" => $this->lng->txt("dcl_any"), 1 => ">1", 2 => ">2", 3 => ">3", 4 => ">4", 5 => "5"];
        $input->setOptions($options);

        $this->setupFilterInputField($input);

        return $this->getFilterInputFieldValue($input);
    }
}
