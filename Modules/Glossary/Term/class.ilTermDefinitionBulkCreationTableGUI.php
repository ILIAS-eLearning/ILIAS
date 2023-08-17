<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilTermDefinitionBulkCreationTableGUI extends ilTable2GUI
{
    public function __construct(
        ilTermDefinitionBulkCreationGUI $a_parent_obj,
        string $a_parent_cmd,
        string $raw_data,
        ilObjGlossary $glossary
    ) {
        global $DIC;

        $this->setId("bulk_creation");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $term_manager = $DIC->glossary()
                               ->internal()
                               ->domain()
                               ->term($glossary);

        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->setMaxCount(9999);
        $this->setShowRowsSelector(false);

        $this->setTitle($lng->txt("glo_term_definition_pairs"));
        $this->setData($term_manager->getDataArrayFromInputString($raw_data));

        $this->addColumn($this->lng->txt("cont_term"));
        $this->addColumn($this->lng->txt("cont_definition"));

        $this->setFormAction($ctrl->getFormAction($a_parent_obj, "createTermDefinitionPairs"));
        $this->setRowTemplate(
            "tpl.bulk_creation_row.html",
            "Modules/Glossary"
        );
        $this->addHiddenInput("bulk_data", $raw_data);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TERM", $a_set["term"]);
        $this->tpl->setVariable("DEFINITION", $a_set["definition"]);
    }
}
