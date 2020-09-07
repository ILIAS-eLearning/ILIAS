<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Item group items table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesItemGroup
 */
class ilItemGroupItemsTableGUI extends ilTable2GUI
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_def;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tree = $tree;
        $this->obj_def = $objDefinition;
        
        include_once 'Modules/ItemGroup/classes/class.ilItemGroupItems.php';
        $this->item_group_items = new ilItemGroupItems($a_parent_obj->object->getRefId());
        $this->items = $this->item_group_items->getItems();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        
        $this->getMaterials();
        $this->setTitle($lng->txt("itgr_assigned_materials"));
        
        $this->addColumn("", "", "1px", true);
        $this->addColumn($this->lng->txt("itgr_item"));
        $this->addColumn($this->lng->txt("itgr_assignment"));
        $this->setSelectAllCheckbox("items[]");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.item_group_items_row.html", "Modules/ItemGroup");

        $this->addCommandButton("saveItemAssignment", $lng->txt("save"));
    }
    
    /**
     * Get materials
     *
     * @param
     * @return
     */
    public function getMaterials()
    {
        $materials = array();
        $items = $this->item_group_items->getAssignableItems();
        
        foreach ($items as $item) {
            $item["sorthash"] = (int) (!in_array($item['ref_id'], $this->items)) . $item["title"];
            $materials[] = $item;
        }
        
        $materials = ilUtil::sortArray($materials, "sorthash", "asc");
        $this->setData($materials);
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;

        $this->tpl->setVariable("ITEM_REF_ID", $a_set["child"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("IMG", ilUtil::img(
            ilObject::_getIcon($a_set["obj_id"], "tiny"),
            "",
            "",
            "",
            "",
            "",
            "ilIcon"
        ));
        
        if (in_array($a_set["child"], $this->items)) {
            $this->tpl->setVariable("IMG_ASSIGNED", ilUtil::img(
                ilUtil::getImagePath("icon_ok.svg")
            ));
            $this->tpl->setVariable("CHECKED", "checked='checked'");
        } else {
            $this->tpl->setVariable("IMG_ASSIGNED", ilUtil::img(
                ilUtil::getImagePath("icon_not_ok.svg")
            ));
        }
    }
}
