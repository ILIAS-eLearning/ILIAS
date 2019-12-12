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
class ilTermQuickListTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->glossary = $a_parent_obj->glossary;
        $this->setId("gloqtl" . $this->glossary->getId());
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("cont_terms"));
        
        $this->addColumn("", "");
        $this->setEnableHeader(false);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.term_quick_list_row.html", "Modules/Glossary");
        $this->setEnableTitle(false);

        //$this->setData($this->glossary->getTermList($this->filter["term"], "",
        //	$this->filter["definition"]));
        $this->setData($this->glossary->getTermList("", "", "", 0, false, false, null, true));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($term)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
        $ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $term["id"]);
        
        $sep = ": ";
        for ($j=0; $j < count($defs); $j++) {
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
            $this->tpl->setVariable("TEXT_DEF", $this->lng->txt("glo_definition_abbr") . ($j+1));
            $this->tpl->parseCurrentBlock();
            $sep = ", ";
        }
        $ilCtrl->setParameterByClass("ilglossarydefpagegui", "def", $_GET["def"]);

        if ($term["id"] == $_GET["term_id"]) {
            $this->tpl->touchBlock("hl");
        }
        
        $this->tpl->setVariable("TEXT_TERM", $term["term"]);
        $this->tpl->setVariable(
            "LINK_EDIT_TERM",
            $ilCtrl->getLinkTargetByClass("ilglossarytermgui", "editTerm")
        );
        
        $ilCtrl->setParameterByClass("ilglossarytermgui", "term_id", $_GET["term_id"]);
    }
}
