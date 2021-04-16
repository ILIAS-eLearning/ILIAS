<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateTreeExplorerGUI extends ilTreeExplorerGUI
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
     * @var array
     */
    protected $parent;

    /**
     * @var array
     */
    protected $draft;

    /**
     * Constructor
     *
     * @param object|string[] $a_parent_obj parent gui object(s)
     * @param string          $a_parent_cmd parent command
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $tree = new ilSkillTree();
        parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd, $tree);

        $params = $this->request->getQueryParams();
        $this->requested_obj_id = (int) ($params["obj_id"] ?? 0);

        $this->setTypeWhiteList(array("skrt", "sktp", "sctp"));
        
        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("order_nr");
    }
    
    /**
     * Get root node
     *
     * @return array node data
     */
    public function getRootNode()
    {
        $path = $this->getTree()->getPathId($this->requested_obj_id);
        return $this->getTree()->getNodeData($path[1]);
    }

    /**
     * Get childs of node
     *
     * @param int $a_parent_node_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        $childs = parent::getChildsOfNode($a_parent_node_id);

        foreach ($childs as $c) {
            $this->parent[$c["child"]] = $c["parent"];
            if ($this->draft[$c["parent"]]) {
                $this->draft[$c["child"]] = true;
            } else {
                $this->draft[$c["child"]] = (ilSkillTreeNode::_lookupStatus($c["child"]) == ilSkillTreeNode::STATUS_DRAFT);
            }
        }
        return $childs;
    }

    /**
     * Get node content
     *
     * @param array
     * @return
     */
    public function getNodeContent($a_node)
    {
        $lng = $this->lng;
        
        // title
        $title = $a_node["title"];
        
        // root?
        if ($a_node["type"] == "skrt") {
            $title = $lng->txt("skmg_skill_templates");
        } else {
            if ($a_node["type"] == "sktr") {
                $tid = ilSkillTemplateReference::_lookupTemplateId($a_node["child"]);
                $title .= " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
            }
            if (ilSkillTreeNode::_lookupSelfEvaluation($a_node["child"])) {
                $title = "<u>" . $title . "</u>";
            }
        }
        
        return $title;
    }
    
    /**
     * Get node content
     *
     * @param array
     * @return
     */
    public function getNodeIcon($a_node)
    {
        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_sctp.svg");
        } elseif (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr"))) {
            $icon = ilSkillTreeNode::getIconPath(
                $a_node["child"],
                $a_node["type"],
                "",
                $this->draft[$a_node["child"]]
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
        if ($a_node["child"] == $this->requested_obj_id ||
            ($this->requested_obj_id == "" && $a_node["type"] == "skrt")) {
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

        switch ($a_node["type"]) {
            // root
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "ilskillrootgui"
                ], "listTemplates");
                $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", $this->requested_obj_id);
                return $ret;

            // template
            case "sktp":
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "ilbasicskilltemplategui"
                ], "edit");
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "obj_id", $this->requested_obj_id);
                return $ret;

            // template category
            case "sctp":
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "ilskilltemplatecategorygui"
                ], "listItems");
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "obj_id", $this->requested_obj_id);
                return $ret;

            default:
                return "";
        }
    }
    
    /**
     * Get node icon alt attribute
     *
     * @param mixed $a_node node object/array
     * @return string image alt attribute
     */
    public function getNodeIconAlt($a_node)
    {
        $lng = $this->lng;

        if ($lng->exists("skmg_" . $a_node["type"])) {
            return $lng->txt("skmg_" . $a_node["type"]);
        }

        return $lng->txt($a_node["type"]);
    }
}
