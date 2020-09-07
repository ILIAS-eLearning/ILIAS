<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for auto link glossaries
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
 */
class ilGlossaryAutoLinkTableGUI extends ilTable2GUI
{
    /**
     * @var ilObjGlossary
     */
    protected $glossary;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct(ilObjGlossary $a_glossary, $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;
        
        $this->glossary = $a_glossary;
        $this->id = "glo_glo";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $data = array();
        foreach ($a_glossary->getAutoGlossaries() as $glo_id) {
            $data[] = array("glo_id" => $glo_id, "title" => ilObject::_lookupTitle($glo_id));
        }
        $this->setData($data);
        $this->setTitle($this->lng->txt("cont_auto_glossaries"));
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.glo_glossary_auto_link_row.html", "Modules/Glossary");

        //		$this->addMultiCommand("", $lng->txt(""));
//		$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $this->ctrl->setParameter($this->parent_obj, "glo_id", $a_set["glo_id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD_HREF", $this->ctrl->getLinkTarget($this->parent_obj, "removeGlossary"));
        $this->tpl->setVariable("CMD_TXT", $this->lng->txt("remove"));
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable("TITLE", ilObject::_lookupTitle($a_set["glo_id"]));
    }
}
