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
class ilTermListTableGUI extends ilTable2GUI
{
    protected array $selected_cols;
    protected array $adv_cols_order;
    protected array $selectable_cols;
    protected array $adv_fields;
    protected int $tax_node;
    protected ilObjGlossary $glossary;
    protected ilGlossaryTermPermission $term_perm;
    protected array $filter;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_tax_node
    ) {
        global $DIC;

        $this->glossary = $a_parent_obj->getObject();
        $this->setId("glotl" . $this->glossary->getId());
        $this->tax_node = $a_tax_node;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->selectable_cols = array();

        $this->term_perm = ilGlossaryTermPermission::getInstance();

        $adv_ad = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_fields = $adv_ad->getAllFields();
        foreach ($this->adv_fields as $f) {
            $this->selectable_cols["md_" . $f["id"]] = array(
                "txt" => $f["title"],
                "default" => false
                );
        }

        // selectable columns
        $this->selectable_cols["language"] = array(
            "txt" => $this->lng->txt("language"),
            "default" => true);
        $this->selectable_cols["usage"] = array(
            "txt" => $this->lng->txt("cont_usage"),
            "default" => true);

        // selectable columns of advanced metadata

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("cont_terms"));

        $this->addColumn("", "", "1", true);
        //$this->addColumn($this->lng->txt("cont_term"));

        $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_cols_order = $adv_ap->getColumnOrder();
        $this->selected_cols = $this->getSelectedColumns();
        foreach ($this->adv_cols_order as $c) {
            if ($c["id"] == 0) {
                $this->addColumn($this->lng->txt("cont_term"), "term");
            } elseif (in_array("md_" . $c["id"], $this->selected_cols)) {
                $this->addColumn($c["text"], "md_" . $c["id"]);
            }
        }

        foreach (array("language", "usage") as $c) {
            if (in_array($c, $this->selected_cols)) {
                $this->addColumn($this->selectable_cols[$c]["txt"]);
            }
        }

        $this->setDefaultOrderDirection("asc");
        $this->setDefaultOrderField("term");
        $this->addColumn($this->lng->txt("cont_definitions"));

        if ($this->showGlossaryColumn()) {
            $this->addColumn($this->lng->txt("obj_glo"));
        }

        $this->addColumn("", "", "1");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.term_tbl_row.html", "Modules/Glossary");
        $this->setEnableTitle(true);

        $this->addMultiCommand("copyTerms", $this->lng->txt("copy"));
        $this->addMultiCommand("referenceTerms", $this->lng->txt("glo_reference"));
        $this->addMultiCommand("confirmTermDeletion", $this->lng->txt("delete"));
        //$this->addMultiCommand("addDefinition", $this->lng->txt("cont_add_definition"));

        $this->setShowRowsSelector(true);

        $this->initFilter();
        $this->setData($this->glossary->getTermList(
            $this->filter["term"],
            "",
            $this->filter["definition"],
            $this->tax_node,
            true,
            true,
            null,
            false,
            true
        ));
    }

    public function showGlossaryColumn(): bool
    {
        return (in_array(
            $this->glossary->getVirtualMode(),
            array("level", "subtree")
        ) || ilGlossaryTermReferences::hasReferences($this->glossary->getId()));
    }


    public function getSelectableColumns(): array
    {
        return $this->selectable_cols;
    }

    public function numericOrdering(string $a_field): bool
    {
        if (strpos($a_field, "md_") === 0) {
            $md_id = (int) substr($a_field, 3);
            if ($this->adv_fields[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }

    public function initFilter(): void
    {
        // term
        $ti = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["term"] = $ti->getValue();

        // definition
        if ($this->glossary->supportsLongTextQuery()) {
            $ti = new ilTextInputGUI($this->lng->txt("cont_definition"), "defintion");
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $ti->setSubmitFormOnEnter(true);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["definition"] = $ti->getValue();
        }
    }

    protected function fillRow(array $a_set): void
    {
        $defs = ilGlossaryDefinition::getDefinitionList($a_set["id"]);
        $this->ctrl->setParameterByClass("ilobjglossarygui", "term_id", $a_set["id"]);
        $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $a_set["id"]);
        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $a_set["id"]);

        // actions drop down
        //if ($this->glossary->getId() == $term["glo_id"])

        if ($this->term_perm->checkPermission("write", $a_set["id"]) ||
            $this->term_perm->checkPermission("edit_content", $a_set["id"])) {
            if (ilGlossaryTerm::_lookGlossaryID($a_set["id"]) == $this->glossary->getId() ||
                ilGlossaryTermReferences::isReferenced([$this->glossary->getId()], $a_set["id"])) {
                $list = new ilAdvancedSelectionListGUI();
                $list->addItem($this->lng->txt("cont_edit_term"), "", $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "editTerm"));
                if (count($defs) > 1) {
                    $list->addItem($this->lng->txt("cont_edit_definitions"), "", $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listDefinitions"));
                } elseif (count($defs) == 1) {
                    $this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", $defs[0]["id"]);
                    $list->addItem($this->lng->txt("cont_edit_definition"), "", $this->ctrl->getLinkTargetByClass(array("ilglossarytermgui",
                        "iltermdefinitioneditorgui",
                        "ilglossarydefpagegui"), "edit"));
                }
                $list->addItem($this->lng->txt("cont_add_definition"), "", $this->ctrl->getLinkTargetByClass("ilobjglossarygui", "addDefinition"));
                $this->ctrl->setParameterByClass("ilglossarydefpagegui", "def", "");

                $list->setId("act_term_" . $a_set["id"]);
                $list->setListTitle($this->lng->txt("actions"));
                $this->tpl->setVariable("ACTIONS", $list->getHTML());
            }
        }


        for ($j = 0, $jMax = count($defs); $j < $jMax; $j++) {
            $def = $defs[$j];


            // text
            $this->tpl->setCurrentBlock("definition");
            $short_str = $def["short_text"];

            if ($def["short_text_dirty"]) {
                // #18022
                $def_obj = new ilGlossaryDefinition($def["id"]);
                $def_obj->updateShortText();
                $short_str = $def_obj->getShortText();
            }

            // replace tex
            // if a tex end tag is missing a tex end tag
            $ltexs = strrpos($short_str, "[tex]");
            $ltexe = strrpos($short_str, "[/tex]");
            if ($ltexs > $ltexe) {
                $page = new ilGlossaryDefPage($def["id"]);
                $page->buildDom();
                $short_str = $page->getFirstParagraphText();
                $short_str = strip_tags($short_str, "<br>");
                $ltexe = strpos($short_str, "[/tex]", $ltexs);
                $short_str = ilStr::shortenTextExtended($short_str, $ltexe + 6, true);
            }

            $short_str = ilMathJax::getInstance()->insertLatexImages($short_str);

            $short_str = ilPCParagraph::xml2output($short_str);
            $this->tpl->setVariable("DEF_SHORT", $short_str);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("definition_row");
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("check_col");
        $this->tpl->setVariable("CHECKBOX_ID", $a_set["id"]);
        $this->tpl->parseCurrentBlock();

        $this->ctrl->setParameter($this->parent_obj, "term_id", $a_set["id"]);


        // usage
        if (in_array("usage", $this->getSelectedColumns())) {
            $nr_usage = ilGlossaryTerm::getNumberOfUsages($a_set["id"]);
            if ($nr_usage > 0 && $this->glossary->getId() == $a_set["glo_id"]) {
                $this->tpl->setCurrentBlock("link_usage");
                $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $a_set["id"]);
                $this->tpl->setVariable("LUSAGE", ilGlossaryTerm::getNumberOfUsages($a_set["id"]));
                $this->tpl->setVariable(
                    "LINK_USAGE",
                    $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages")
                );
                $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", "");
            } else {
                $this->tpl->setCurrentBlock("usage");
                $this->tpl->setVariable("USAGE", ilGlossaryTerm::getNumberOfUsages($a_set["id"]));
            }
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("td_usage");
            $this->tpl->parseCurrentBlock();
        }

        // glossary title
        if ($this->showGlossaryColumn()) {
            $this->tpl->setCurrentBlock("glossary");
            $this->tpl->setVariable("GLO_TITLE", ilObject::_lookupTitle($a_set["glo_id"]));
            $this->tpl->parseCurrentBlock();
        }

        // output language
        if (in_array("language", $this->getSelectedColumns())) {
            $this->tpl->setCurrentBlock("td_lang");
            $this->tpl->setVariable("TEXT_LANGUAGE", $this->lng->txt("meta_l_" . $a_set["language"]));
            $this->tpl->parseCurrentBlock();
        }


        foreach ($this->adv_cols_order as $c) {
            if ($c["id"] == 0) {
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TD_VAL", $a_set["term"]);
                $this->tpl->parseCurrentBlock();
            } elseif (in_array("md_" . $c["id"], $this->selected_cols)) {
                $id = (int) $c["id"];

                $val = " ";
                if (isset($a_set["md_" . $id . "_presentation"])) {
                    $pb = $a_set["md_" . $id . "_presentation"]->getHTML();
                    if ($pb) {
                        $val = $pb;
                    }
                }

                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TD_VAL", $val);
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
