<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilVirtualSkillTreeExplorerGUI.php");

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
class ilSkillTreeExplorerGUI extends ilVirtualSkillTreeExplorerGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd);
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setShowDraftNodes(true);
        $this->setShowOutdatedNodes(true);
    }
    
    
    /**
     * Get node content
     *
     * @param array $a_node node data
     * @return string content
     */
    public function getNodeContent($a_node)
    {
        $lng = $this->lng;
        
        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = $a_parent_id_parts[1];
        
        // title
        $title = $a_node["title"];
        
        // root?
        if ($a_node["type"] == "skrt") {
            $title = $lng->txt("skmg_skills");
        } else {
            if ($a_node["type"] == "sktr") {
                include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
                $tid = ilSkillTemplateReference::_lookupTemplateId($a_parent_skl_tree_id);
                $title.= " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
            }
            
            // @todo: fix this if possible for skill/tref_id combination
            if (ilSkillTreeNode::_lookupSelfEvaluation($a_parent_skl_tree_id)) {
                if ($a_parent_skl_template_tree_id == 0 || $a_node["type"] == "sktr") {
                    $title = "<u>" . $title . "</u>";
                }
            }
        }

        if ($this->vtree->isOutdated($a_node["id"])) {
            $title = "<span class='light'>" . $title . "</span>";
        }

        return $title;
    }
    
    /**
     * Get node content
     *
     * @param array $a_node node data
     * @return string icon path
     */
    public function getNodeIcon($a_node)
    {
        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = $a_parent_id_parts[1];

        
        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_scat.svg");
        } else {
            if (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr", "sctp", "sktp"))) {
                $icon = ilSkillTreeNode::getIconPath(
                    $a_parent_skl_tree_id,
                    $a_node["type"],
                    "",
                    ($this->vtree->isDraft($a_node["id"]) || $this->vtree->isOutdated($a_node["id"]))
                );
            } else {
                $icon = ilUtil::getImagePath("icon_" . $a_node["type"] . ".svg");
            }
        }
        
        return $icon;
    }

    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        $id_parts = explode(":", $a_node["id"]);
        if ($id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $a_node["id"];
            $tref_id = 0;
        } else {
            // skill in template
            $tref_id = $id_parts[0];
            $skill_id = $id_parts[1];
        }

        if ($_GET["obj_id"] == "" && $a_node["type"] == "skrt") {
            return true;
        }
        
        if ($skill_id == $_GET["obj_id"] &&
            ($_GET["tref_id"] == $tref_id)) {
            return true;
        }
        return false;
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

        $id_parts = explode(":", $a_node["id"]);
        if ($id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $a_node["id"];
            $tref_id = 0;
        } else {
            // skill in template
            $tref_id = $id_parts[0];
            $skill_id = $id_parts[1];
        }

        $gui_class = array(
            "skrt" => "ilskillrootgui",
            "scat" => "ilskillcategorygui",
            "sktr" => "ilskilltemplatereferencegui",
            "skll" => "ilbasicskillgui",
            "sktp" => "ilbasicskilltemplategui",
            "sctp" => "ilskilltemplatecategorygui"
        );
        
        $cmd = array(
            "skrt" => "listSkills",
            "scat" => "listItems",
            "sktr" => "listItems",
            "skll" => "edit",
            "sktp" => "edit",
            "sctp" => "listItems"
        );
        
        $gui_class = $gui_class[$a_node["type"]];
        $cmd = $cmd[$a_node["type"]];
        
        $ilCtrl->setParameterByClass($gui_class, "tref_id", $tref_id);
        $ilCtrl->setParameterByClass($gui_class, "obj_id", $skill_id);
        $ret = $ilCtrl->getLinkTargetByClass($gui_class, $cmd);
        $ilCtrl->setParameterByClass($gui_class, "obj_id", $_GET["obj_id"]);
        $ilCtrl->setParameterByClass($gui_class, "tref_id", $_GET["tref_id"]);

        return $ret;
    }

    /**
     * Is clickable
     *
     * @param array $a_node node data
     * @return bool clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        return true;
    }
}
