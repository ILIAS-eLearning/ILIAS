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
 * TableGUI class for all pages of a learning module
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPagesTableGUI extends ilTable2GUI
{
    protected ilSetting $lm_set;
    protected ilObjLearningModule $lm;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->lm = $a_lm;
        $this->lm_set = new ilSetting("lm");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData(ilLMPageObject::getPageList($this->lm->getId()));
        $this->setTitle($lng->txt("cont_pages"));
        
        $this->addColumn($this->lng->txt(""), "", "1");
        $this->addColumn($this->lng->txt("type"), "", "1");
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("cont_usage"));
        
        $this->setSelectAllCheckbox("id[]");
        
        if ($this->lm->getLayoutPerPage()) {
            $this->addColumn($this->lng->txt("cont_layout"));
        }
        
        $this->setLimit(9999);
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.page_list_row.html", "Modules/LearningModule");
        
        if (ilEditClipboard::getContentObjectType() == "pg" &&
            ilEditClipboard::getAction() == "copy") {
            $this->addMultiCommand("pastePage", $lng->txt("pastePage"));
        }

        if ($this->lm->getLayoutPerPage()) {
            $this->addMultiCommand("setPageLayout", $lng->txt("cont_set_layout"));
        }
        
        $this->addMultiCommand("activatePages", $lng->txt("cont_de_activate"));
        $this->addMultiCommand("movePage", $lng->txt("movePage"));
        $this->addMultiCommand("copyPage", $lng->txt("copyPage"));
        $this->addMultiCommand("delete", $lng->txt("delete"));
        $this->addMultiCommand("selectHeader", $lng->txt("selectHeader"));
        $this->addMultiCommand("selectFooter", $lng->txt("selectFooter"));
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // icon...
        
        // check activation
        $active = ilLMPage::_lookupActive(
            $a_set["obj_id"],
            $this->lm->getType(),
            $this->lm_set->get("time_scheduled_page_activation")
        );
            
        // is page scheduled?
        $img_sc = ($this->lm_set->get("time_scheduled_page_activation") &&
            ilLMPage::_isScheduledActivation($a_set["obj_id"], $this->lm->getType()))
            ? "_sc"
            : "";

        if (!$active) {
            $img = "icon_pg_d" . $img_sc . ".svg";
            $alt = $lng->txt("cont_page_deactivated");
        } else {
            if (ilLMPage::_lookupContainsDeactivatedElements(
                $a_set["obj_id"],
                $this->lm->getType()
            )) {
                $img = "icon_pg_del" . $img_sc . ".svg";
                $alt = $lng->txt("cont_page_deactivated_elements");
            } else {
                $img = "icon_pg" . $img_sc . ".svg";
                $alt = $this->lng->txt("pg");
            }
        }
        $this->tpl->setVariable("ICON", ilUtil::img(ilUtil::getImagePath($img), $alt));

        // title/link
        $ilCtrl->setParameter($this, "backcmd", null);
        $ilCtrl->setParameterByClass("ilLMPageObjectGUI", "obj_id", $a_set["obj_id"]);
        $this->tpl->setVariable(
            "HREF_TITLE",
            $ilCtrl->getLinkTargetByClass("ilLMPageObjectGUI", "edit")
        );
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("ID", $a_set["obj_id"]);
        
        // context
        if ($this->lm->lm_tree->isInTree($a_set["obj_id"])) {
            $path_str = $this->parent_obj->getContextPath($a_set["obj_id"]);
        } else {
            $path_str = "---";
        }

        // check whether page is header or footer
        $add_str = "";
        if ($a_set["obj_id"] == $this->lm->getHeaderPage()) {
            $add_str = " <b>(" . $lng->txt("cont_header") . ")</b>";
        }
        if ($a_set["obj_id"] == $this->lm->getFooterPage()) {
            $add_str .= " <b>(" . $lng->txt("cont_footer") . ")</b>";
        }
    
        $this->tpl->setVariable("USAGE", $path_str . $add_str);

        // layout
        if ($this->lm->getLayoutPerPage()) {
            if (($l = ilLMObject::lookupLayout($a_set["obj_id"])) != "") {
                $this->tpl->setVariable(
                    "LAYOUT",
                    $lng->txt("cont_layout_" . $l)
                );
            }
        }
    }
}
