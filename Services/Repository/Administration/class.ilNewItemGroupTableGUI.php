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
 * TableGUI class for new item groups
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilNewItemGroupTableGUI extends ilTable2GUI
{
    protected bool $has_write;
    protected ilGlobalTemplateInterface $main_tpl;
    
    public function __construct(
        ilObjRepositorySettingsGUI $a_parent_obj,
        string $a_parent_cmd = "",
        bool $a_has_write = false
    ) {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->has_write = $a_has_write;
                
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setId("repnwitgrptbl");
        
        $this->setTitle($lng->txt("rep_new_item_groups"));

        if ($this->has_write) {
            $this->addColumn("", "", '1');
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
        $this->setRowTemplate("tpl.table_row_new_item_group.html", "Services/Repository/Administration");
        $this->setLimit(10000);
        
        $this->setExternalSorting(true);
        $this->getGroups();
    }
    
    public function getGroups() : void
    {
        $lng = $this->lng;
        
        $data = [];
                
        $subitems = ilObjRepositorySettings::getNewItemGroupSubItems();
        
        if ($subitems[0]) {
            $this->main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt("rep_new_item_group_unassigned_subitems"),
                is_array($subitems[0]) ? count($subitems[0]) : 0
            ));
            unset($subitems[0]);
        }
        
        foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
            $data[] = [
                "id" => $item["id"],
                "pos" => $item["pos"],
                "title" => $item["title"],
                "type" => $item["type"],
                "subitems" => is_array($subitems[$item["id"]]) ? count($subitems[$item["id"]]) : 0
            ];
        }
        
        $data = ilArrayUtil::sortArray($data, "pos", "asc", true);
        
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->has_write) {
            $this->tpl->setVariable("VAR_MULTI", "grp_ids[]");
            $this->tpl->setVariable("VAL_MULTI", $a_set["id"]);
        }
        
        $this->tpl->setVariable("VAR_POS", "grp_order[" . $a_set["id"] . "]");
        $this->tpl->setVariable("VAL_POS", $a_set["pos"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        
        if ((int) $a_set["type"] === ilObjRepositorySettings::NEW_ITEM_GROUP_TYPE_GROUP) {
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
