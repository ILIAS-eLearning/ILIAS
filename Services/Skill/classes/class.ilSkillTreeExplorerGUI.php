<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
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
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_obj_id;

    /**
     * @var int
     */
    protected $requested_tref_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd);

        $params = $this->request->getQueryParams();
        $this->requested_obj_id = (int) ($params["obj_id"] ?? 0);
        $this->requested_tref_id = (int) ($params["tref_id"] ?? 0);

        // node should be hidden #26849 (not not hidden, see discussion in #26813 and JF 6 Jan 2020)
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
                $tid = ilSkillTemplateReference::_lookupTemplateId($a_parent_skl_tree_id);
                $title .= " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
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
        } elseif (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr", "sctp", "sktp"))) {
            $icon = ilSkillTreeNode::getIconPath(
                $a_parent_skl_tree_id,
                $a_node["type"],
                "",
                ($this->vtree->isDraft($a_node["id"]) || $this->vtree->isOutdated($a_node["id"]))
            );
        } else {
            $icon = ilUtil::getImagePath("icon_" . $a_node["type"] . ".svg");
        }
        
        return $icon;
    }

    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return bool node visible true/false
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

        if ($this->requested_obj_id == "" && $a_node["type"] == "skrt") {
            return true;
        }
        
        if ($skill_id == $this->requested_obj_id &&
            ($this->requested_tref_id == $tref_id)) {
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
        $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI", "ilObjSkillManagementGUI", $gui_class], $cmd);
        $ilCtrl->setParameterByClass($gui_class, "obj_id", $this->requested_obj_id);
        $ilCtrl->setParameterByClass($gui_class, "tref_id", $this->requested_tref_id);

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
