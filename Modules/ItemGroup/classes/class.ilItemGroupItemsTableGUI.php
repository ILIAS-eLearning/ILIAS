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
 * Item group items table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupItemsTableGUI extends ilTable2GUI
{
    protected array $items;
    protected ilItemGroupItems $item_group_items;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_def;

    public function __construct(
        ilObjItemGroupGUI $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tree = $tree;
        $this->obj_def = $objDefinition;

        $this->item_group_items = new ilItemGroupItems($a_parent_obj->getObject()->getRefId());
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
    
    public function getMaterials() : void
    {
        $materials = array();
        $items = $this->item_group_items->getAssignableItems();
        
        foreach ($items as $item) {
            $item["sorthash"] = (int) (!in_array($item['ref_id'], $this->items)) . $item["title"];
            $materials[] = $item;
        }
        
        $materials = ilArrayUtil::sortArray($materials, "sorthash", "asc");
        $this->setData($materials);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("ITEM_REF_ID", $a_set["child"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("IMG", ilUtil::img(
            ilObject::_getIcon((int) $a_set["obj_id"], "tiny"),
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
