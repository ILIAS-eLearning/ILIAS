<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");

/**
 * TableGUI class for new item groups
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesRepository
 */
class ilNewItemGroupTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $has_write; // [bool]
    
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->has_write = (bool) $a_has_write;
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setId("repnwitgrptbl");
        
        $this->setTitle($lng->txt("rep_new_item_groups"));

        if ($this->has_write) {
            $this->addColumn("", "", 1);
        }
        $this->addColumn($lng->txt("cmps_add_new_rank"), "");
        $this->addColumn($lng->txt("title"), "");
        $this->addColumn($lng->txt("rep_new_item_group_nr_subitems"), "");
        
        if ($this->has_write) {
            $this->addColumn($lng->txt("action"), "");
        }

        if ($this->has_write) {
            $this->addCommandButton("saveNewItemGroupOrder", $lng->txt("cmps_save_options"));
            $this->addMultiCommand("confirmDeleteNewItemGroup", $lng->txt("delete"));
        }
    
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_row_new_item_group.html", "Services/Repository");
        $this->setLimit(10000);
        
        $this->setExternalSorting(true);
        $this->getGroups();
    }
    
    /**
    * Get pages for list.
    */
    public function getGroups()
    {
        $lng = $this->lng;
        
        $data = array();
                
        $subitems = ilObjRepositorySettings::getNewItemGroupSubItems();
        
        if ($subitems[0]) {
            ilUtil::sendInfo(sprintf(
                $lng->txt("rep_new_item_group_unassigned_subitems"),
                is_array($subitems[0]) ? sizeof($subitems[0]) : 0
            ));
            unset($subitems[0]);
        }
        
        foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
            $data[] = array(
                "id" => $item["id"],
                "pos" => $item["pos"],
                "title" => $item["title"],
                "type" => $item["type"],
                "subitems" => is_array($subitems[$item["id"]]) ? sizeof($subitems[$item["id"]]) : 0
            );
        }
        
        $data = ilUtil::sortArray($data, "pos", "asc", true);
        
        $this->setData($data);
    }

    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->has_write) {
            $this->tpl->setVariable("VAR_MULTI", "grp_id[]");
            $this->tpl->setVariable("VAL_MULTI", $a_set["id"]);
        }
        
        $this->tpl->setVariable("VAR_POS", "grp_order[" . $a_set["id"] . "]");
        $this->tpl->setVariable("VAL_POS", $a_set["pos"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        
        if ($a_set["type"] == ilObjRepositorySettings::NEW_ITEM_GROUP_TYPE_GROUP) {
            $this->tpl->setVariable("VAL_ITEMS", $a_set["subitems"]);

            if ($this->has_write) {
                $ilCtrl->setParameter($this->parent_obj, "grp_id", $a_set["id"]);
                $url = $ilCtrl->getLinkTarget($this->parent_obj, "editNewItemGroup");
                $ilCtrl->setParameter($this->parent_obj, "grp_id", "");

                $this->tpl->setVariable("URL_EDIT", $url);
                $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
            }
        }
    }
}
