<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/LearningModule/classes/class.ilLMExplorerGUI.php");

/**
 * LM editor explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ModulesLearningModule
 */
class ilLMEditorExplorerGUI extends ilLMExplorerGUI
{

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
                include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
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
                    include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
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
        
        include_once("./Modules/LearningModule/classes/class.ilLMObject.php");

        if ($a_node["type"] == "du") {
            $a_node["type"] = "lm";
        }

        if ($a_node["type"] == "pg") {
            include_once("./Modules/LearningModule/classes/class.ilLMPage.php");
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
//				$ilCtrl->setParameterByClass("ilobjlearningmodulegui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass("ilobjlearningmodulegui", "chapters");
//				$ilCtrl->setParameterByClass("ilobjlearningmodulegui", "obj_id", $_GET["obj_id"]);
                return $ret;
                break;

            case "pg":
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "illmpageobjectgui"), "edit");
                $ilCtrl->setParameterByClass("illmpageobjectgui", "obj_id", $_GET["obj_id"]);
                return $ret;
                break;

            case "st":
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(array("ilobjlearningmodulegui", "ilstructureobjectgui"), "view");
                $ilCtrl->setParameterByClass("ilstructureobjectgui", "obj_id", $_GET["obj_id"]);
                return $ret;
                break;
        }
    }
}
