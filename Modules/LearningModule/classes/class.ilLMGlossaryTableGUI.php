<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for glossary tables
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMGlossaryTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct($a_lm, $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $this->lm = $a_lm;
        $this->id = "lm_glo";
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $data = array();
        foreach ($a_lm->getAutoGlossaries() as $glo_id) {
            $data[] = array("glo_id" => $glo_id, "title" => ilObject::_lookupTitle($glo_id));
        }
        $this->setData($data);
        $this->setTitle($lng->txt("cont_auto_glossaries"));
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lm_glossary_row.html", "Modules/LearningModule");

        //		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "glo_id", $a_set["glo_id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "removeLMGlossary"));
        $this->tpl->setVariable("CMD_TXT", $lng->txt("remove"));
        $this->tpl->parseCurrentBlock();
        
        $this->tpl->setVariable("TITLE", ilObject::_lookupTitle($a_set["glo_id"]));
    }
}
