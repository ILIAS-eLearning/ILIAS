<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * LM editor explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMEditorExplorerGUI extends ilLMExplorerGUI
{
    protected ilObjLearningModule $lm;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, ilObjContentObject $a_lm, $a_id = "")
    {
        global $DIC;
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_lm, $a_id);

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }


    /**
     * Get node icon
     *
     * @param array $a_node node array
     * @return string icon path
     */
    public function getNodeIcon($a_node)
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $icon = ilUtil::getImagePath("icon_lm.svg");
        } else {
            $a_name = "icon_" . $a_node["type"] . ".svg";
            if ($a_node["type"] == "pg") {
                $lm_set = new ilSetting("lm");
                $active = ilLMPage::_lookupActive(
                    $a_node["child"],
                    $this->lm->getType(),
                    $lm_set->get("time_scheduled_page_activation")
                );
                
                // is page scheduled?
                $img_sc = ($lm_set->get("time_scheduled_page_activation") &&
                    ilLMPage::_isScheduledActivation($a_node["child"], $this->lm->getType()))
                    ? "_sc"
                    : "";
                    
                $a_name = "icon_pg" . $img_sc . ".svg";
    
                if (!$active) {
                    $a_name = "icon_pg_d" . $img_sc . ".svg";
                } else {
                    $contains_dis = ilLMPage::_lookupContainsDeactivatedElements(
                        $a_node["child"],
                        $this->lm->getType()
                    );
                    if ($contains_dis) {
                        $a_name = "icon_pg_del" . $img_sc . ".svg";
                    }
                }
            }
            $icon = ilUtil::getImagePath($a_name);
        }
        
        return $icon;
    }

    /**
     * Get node icon alt text
     *
     * @param array $a_node node array
     * @return string alt text
     */
    public function getNodeIconAlt($a_node)
    {
        $lng = $this->lng;
        
        if ($a_node["type"] == "du") {
            $a_node["type"] = "lm";
        }

        if ($a_node["type"] == "pg") {
            $lm_set = new ilSetting("lm");
            $active = ilLMPage::_lookupActive(
                $a_node["child"],
                $this->lm->getType(),
                $lm_set->get("time_scheduled_page_activation")
            );

            if (!$active) {
                return $lng->txt("cont_page_deactivated");
            } else {
                $contains_dis = ilLMPage::_lookupContainsDeactivatedElements(
                    $a_node["child"],
                    $this->lm->getType()
                );
                if ($contains_dis) {
                    return $lng->txt("cont_page_deactivated_elements");
                }
            }
        }
        return parent::getNodeIconAlt($a_node);
    }
    
    /**
     * Get href for node
     *
     * @param mixed $a_node node object/array
     * @return string href attribute
     */
    public function getNodeHref($a_node)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_node["type"]) {
            case "du":
                $ret = $ilCtrl->getLinkTargetByClass("ilobjlearningmodulegui", "chapters");
                return $ret;

            case "pg":
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $this->obj_id);
                return $ret;

            case "st":
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "ilstructureobjectgui"), "view");
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $this->obj_id);
                return $ret;
        }
    }
}
