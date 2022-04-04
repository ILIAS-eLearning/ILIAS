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
 * Class ilExcCriteriaTableGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCriteriaTableGUI extends ilTable2GUI
{
    protected int $cat_id;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_cat_id
    ) {
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
    
    protected function getItems() : bool
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
        
        return (bool) count($data);
    }
    
    public function numericOrdering(string $a_field) : bool
    {
        return $a_field === "pos";
    }
    
    protected function fillRow(array $a_set) : void
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
