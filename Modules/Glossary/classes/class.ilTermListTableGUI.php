<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Term list table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTermListTableGUI extends ilTable2GUI
{
    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * @var ilGlossaryTermPermission
     */
    protected $term_perm;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_tax_node)
    {
        global $DIC;
        
        $this->glossary = $a_parent_obj->object;
        $this->setId("glotl" . $this->glossary->getId());
        $this->tax_node = $a_tax_node;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->selectable_cols = array();

        include_once("./Modules/Glossary/classes/class.ilGlossaryTermPermission.php");
        $this->term_perm = ilGlossaryTermPermission::getInstance();

        include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
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

        include_once("./Modules/Glossary/classes/class.ilGlossaryAdvMetaDataAdapter.php");
        $adv_ap = new ilGlossaryAdvMetaDataAdapter($this->glossary->getRefId());
        $this->adv_cols_order = $adv_ap->getColumnOrder();
        $this->selected_cols = $this->getSelectedColumns();
        foreach ($this->adv_cols_order as $c) {
            if ($c["id"] == 0) {
                $this->addColumn($this->lng->txt("cont_term"), "term");
            } else {
                if (in_array("md_" . $c["id"], $this->selected_cols)) {
                    $this->addColumn($c["text"], "md_" . $c["id"]);
                }
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

    /**
     * Show glossary column
     *
     * @param
     * @return
     */
    public function showGlossaryColumn()
    {
        include_once("./Modules/Glossary/classes/class.ilGlossaryTermReferences.php");
        return (in_array(
            $this->glossary->getVirtualMode(),
            array("level", "subtree")
        ) || ilGlossaryTermReferences::hasReferences($this->glossary->getId()));
    }


    /**
     * Get selectable columns
     *
     * @param
     * @return
     */
    public function getSelectableColumns()
    {
        return $this->selectable_cols;
    }

    /**
     * Should this field be sorted numeric?
     *
     * @return	boolean		numeric ordering; default is false
     */
    public function numericOrdering($a_field)
    {
        if (substr($a_field, 0, 3) == "md_") {
            $md_id = (int) substr($a_field, 3);
            if ($this->adv_fields[$md_id]["type"] == ilAdvancedMDFieldDefinition::TYPE_DATE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        // term
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($this->lng->txt("cont_term"), "term");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["term"] = $ti->getValue();
        
        // definition
        if ($this->glossary->supportsLongTextQuery()) {
            include_once("./Services/Form/classes/class.ilTextInputGUI.php");
            $ti = new ilTextInputGUI($this->lng->txt("cont_definition"), "defintion");
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $ti->setSubmitFormOnEnter(true);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["definition"] = $ti->getValue();
        }
    }

    /**
     * Fill table row
     */
    protected function fillRow($term)
    {
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

        $defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
        $this->ctrl->setParameterByClass("ilobjglossarygui", "term_id", $term["id"]);
        $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $term["id"]);
        $this->ctrl->setParameterByClass("ilglossarydefpagegui", "term_id", $term["id"]);

        // actions drop down
        //if ($this->glossary->getId() == $term["glo_id"])

        if ($this->term_perm->checkPermission("write", $term["id"]) ||
            $this->term_perm->checkPermission("edit_content", $term["id"])) {
            include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
            include_once("./Modules/Glossary/classes/class.ilGlossaryTermReferences.php");
            if (ilGlossaryTerm::_lookGlossaryID($term["id"]) == $this->glossary->getId() ||
                ilGlossaryTermReferences::isReferenced($this->glossary->getId(), $term["id"])) {
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

                $list->setId("act_term_" . $term["id"]);
                $list->setListTitle($this->lng->txt("actions"));
                $this->tpl->setVariable("ACTIONS", $list->getHTML());
            }
        }


        for ($j=0; $j<count($defs); $j++) {
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
                $short_str = ilUtil::shortenText($short_str, $ltexe+6, true);
            }

            include_once './Services/MathJax/classes/class.ilMathJax.php';
            $short_str = ilMathJax::getInstance()->insertLatexImages($short_str);

            $short_str = ilPCParagraph::xml2output($short_str);
            $this->tpl->setVariable("DEF_SHORT", $short_str);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("definition_row");
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("check_col");
        $this->tpl->setVariable("CHECKBOX_ID", $term["id"]);
        $this->tpl->parseCurrentBlock();

        $this->ctrl->setParameter($this->parent_obj, "term_id", $term["id"]);


        // usage
        if (in_array("usage", $this->getSelectedColumns())) {
            $nr_usage = ilGlossaryTerm::getNumberOfUsages($term["id"]);
            if ($nr_usage > 0 && $this->glossary->getId() == $term["glo_id"]) {
                $this->tpl->setCurrentBlock("link_usage");
                $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", $term["id"]);
                $this->tpl->setVariable("LUSAGE", ilGlossaryTerm::getNumberOfUsages($term["id"]));
                $this->tpl->setVariable(
                    "LINK_USAGE",
                    $this->ctrl->getLinkTargetByClass("ilglossarytermgui", "listUsages")
                );
                $this->ctrl->setParameterByClass("ilglossarytermgui", "term_id", "");
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("usage");
                $this->tpl->setVariable("USAGE", ilGlossaryTerm::getNumberOfUsages($term["id"]));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("td_usage");
            $this->tpl->parseCurrentBlock();
        }
        
        // glossary title
        if ($this->showGlossaryColumn()) {
            $this->tpl->setCurrentBlock("glossary");
            $this->tpl->setVariable("GLO_TITLE", ilObject::_lookupTitle($term["glo_id"]));
            $this->tpl->parseCurrentBlock();
        }

        // output language
        if (in_array("language", $this->getSelectedColumns())) {
            $this->tpl->setCurrentBlock("td_lang");
            $this->tpl->setVariable("TEXT_LANGUAGE", $this->lng->txt("meta_l_" . $term["language"]));
            $this->tpl->parseCurrentBlock();
        }


        foreach ($this->adv_cols_order as $c) {
            if ($c["id"] == 0) {
                $this->tpl->setCurrentBlock("td");
                $this->tpl->setVariable("TD_VAL", $term["term"]);
                $this->tpl->parseCurrentBlock();
            } else {
                if (in_array("md_" . $c["id"], $this->selected_cols)) {
                    $id = (int) $c["id"];
                    
                    $val = " ";
                    if (isset($term["md_" . $id . "_presentation"])) {
                        $pb = $term["md_" . $id . "_presentation"]->getHTML();
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
}
