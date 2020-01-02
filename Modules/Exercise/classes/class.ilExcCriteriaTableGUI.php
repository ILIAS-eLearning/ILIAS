<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Table/classes/class.ilTable2GUI.php";

/**
 * Class ilExcCriteriaTableGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCriteriaTableGUI extends ilTable2GUI
{
    protected $cat_id; // [int]

    public function __construct($a_parent_obj, $a_parent_cmd, $a_cat_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->cat_id = $a_cat_id;
        $this->setId("exccrit" . $this->cat_id);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setLimit(9999); // because of manual order
    
        $this->setTitle($lng->txt("exc_criterias"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("position"), "pos", "10%");
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("type"), "type");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setDefaultOrderField("pos");
        $this->setDefaultOrderDirection("asc");
    
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_crit_row.html", "Modules/Exercise");
        $this->setSelectAllCheckbox("id");

        $this->addMultiCommand("confirmDeletion", $lng->txt("delete"));
        
        if ($this->getItems()) {
            $this->addCommandButton("saveOrder", $lng->txt("exc_save_order"));
        }
    }
    
    protected function getItems()
    {
        $data = array();
        
        $pos = 0;
        foreach (ilExcCriteria::getInstancesByParentId($this->cat_id) as $item) {
            $pos += 10;
            
            $data[] = array(
                "id" => $item->getId()
                ,"type" => $item->getTranslatedType()
                ,"pos" => $pos
                ,"title" => $item->getTitle()
            );
        }
        
        $this->setData($data);
        
        return (bool) sizeof($data);
    }
    
    public function numericOrdering($a_field)
    {
        return in_array($a_field, array("pos"));
    }
    
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("POS", $a_set["pos"]);
        $this->tpl->setVariable("TYPE", $a_set["type"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        
        $ilCtrl->setParameter($this->getParentObject(), "crit_id", $a_set["id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), "edit");
        $ilCtrl->setParameter($this->getParentObject(), "crit_id", "");
                
        $this->tpl->setCurrentBlock("action_bl");
        $this->tpl->setVariable("ACTION_URL", $url);
        $this->tpl->setVariable("ACTION_TXT", $lng->txt("edit"));
        $this->tpl->parseCurrentBlock();
    }
}
