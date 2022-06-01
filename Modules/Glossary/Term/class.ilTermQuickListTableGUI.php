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
 * Term list table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTermQuickListTableGUI extends ilTable2GUI
{
    protected ilObjGlossary $glossary;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $request;
    protected ilAccessHandler $access;

    public function __construct(
        ilGlossaryTermGUI $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        
        $this->glossary = $a_parent_obj->glossary;
        $this->setId("gloqtl" . $this->glossary->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("cont_terms"));
        $this->addColumn("", "");
        $this->setEnableHeader(false);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.term_quick_list_row.html", "Modules/Glossary");
        $this->setEnableTitle(false);
        $this->setData($this->glossary->getTermList("", "", "", 0, false, false, null, true));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;

        $defs = ilGlossaryDefinition::getDefinitionList($a_set["id"]);
        $ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $a_set["id"]);
        
        $sep = ": ";
        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
            $def = $defs[$j];

            $this->tpl->setCurrentBlock("definition");
            $this->tpl->setVariable("SEP", $sep);
            $ilCtrl->setParameterByClass("ilglossarydefpagegui", "def", $def["id"]);
            $this->tpl->setVariable(
                "LINK_EDIT_DEF",
                $ilCtrl->getLinkTargetByClass(array("ilglossarytermgui",
                "iltermdefinitioneditorgui",
                "ilglossarydefpagegui"), "edit")
            );
            $this->tpl->setVariable("TEXT_DEF", $this->lng->txt("glo_definition_abbr") . ($j + 1));
            $this->tpl->parseCurrentBlock();
            $sep = ", ";
        }
        $ilCtrl->setParameterByClass(
            "ilglossarydefpagegui",
            "def",
            $this->request->getDefinitionId()
        );

        if ($a_set["id"] == $this->request->getTermId()) {
            $this->tpl->touchBlock("hl");
        }
        
        $this->tpl->setVariable("TEXT_TERM", $a_set["term"]);
        $this->tpl->setVariable(
            "LINK_EDIT_TERM",
            $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "editTerm")
        );
        
        $ilCtrl->setParameterByClass(
            "ilglossarytermgui",
            "term_id",
            $this->request->getTermId()
        );
    }
}
