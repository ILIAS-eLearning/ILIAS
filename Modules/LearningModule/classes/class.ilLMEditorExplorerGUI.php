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
 * LM editor explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMEditorExplorerGUI extends ilLMExplorerGUI
{
    /**
     * ilLMEditorExplorerGUI constructor.
     * @param object|string $a_parent_obj
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilObjContentObject $a_lm,
        string $a_id = ""
    ) {
        global $DIC;
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_lm, $a_id);

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
    }


    /**
     * @param object|array $a_node
     */
    public function getNodeIcon($a_node) : string
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
     * @param object|array $a_node
     */
    public function getNodeIconAlt($a_node) : string
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
     * @param object|array $a_node
     */
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;

        if ($a_node["child"] == "") {
            $a_node["child"] = null;
        }
        $obj_id = ($this->obj_id == "")
            ? null
            : $this->obj_id;

        switch ($a_node["type"]) {
            case "du":
                $ret = $ilCtrl->getLinkTargetByClass("ilobjlearningmodulegui", "chapters");
                return $ret;

            case "pg":
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $obj_id);
                return $ret;

            case "st":
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "ilstructureobjectgui"), "view");
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $obj_id);
                return $ret;
        }
        return "";
    }
}
