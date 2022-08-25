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

/**
 * TableGUI class for collecting foreign terms
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryForeignTermTableGUI extends ilTable2GUI
{
    protected ilObjGlossary $glossary;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjGlossary $a_glossary
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->glossary = $a_glossary;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $terms = $this->glossary->getTermList();

        $this->setData($terms);
        $this->setTitle($this->glossary->getTitle() . ": " . $this->lng->txt("glo_select_terms"));

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("glo_term"));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.glo_foreign_term_row.html", "Modules/Glossary");

        $this->addMultiCommand("copyTerms", $this->lng->txt("glo_copy_terms"));
        $this->addMultiCommand("referenceTerms", $this->lng->txt("glo_reference_terms"));
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TERM", $a_set["term"]);
        $this->tpl->setVariable("TERM_ID", $a_set["id"]);
    }
}
