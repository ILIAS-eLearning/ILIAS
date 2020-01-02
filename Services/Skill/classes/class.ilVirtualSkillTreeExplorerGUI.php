<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

/**
 * Virtual skill tree explorer
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesSkill
 */
class ilVirtualSkillTreeExplorerGUI extends ilExplorerBaseGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $show_draft_nodes = false;
    protected $show_outdated_nodes = false;
    
    /**
     * Constructor
     */
    public function __construct($a_id, $a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_id, $a_parent_obj, $a_parent_cmd);
        
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $this->vtree = new ilVirtualSkillTree();
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
    }
    
    /**
     * Set show draft nodes
     *
     * @param boolean $a_val show draft nodes
     */
    public function setShowDraftNodes($a_val)
    {
        $this->show_draft_nodes = $a_val;
        $this->vtree->setIncludeDrafts($a_val);
    }

    /**
     * Get show draft nodes
     *
     * @return boolean show draft nodes
     */
    public function getShowDraftNodes()
    {
        return $this->show_draft_nodes;
    }

    /**
     * Set show outdated nodes
     *
     * @param boolean $a_val show outdated notes
     */
    public function setShowOutdatedNodes($a_val)
    {
        $this->show_outdated_nodes = $a_val;
        $this->vtree->setIncludeOutdated($a_val);
    }

    /**
     * Get show outdated nodes
     *
     * @return boolean show outdated notes
     */
    public function getShowOutdatedNodes()
    {
        return $this->show_outdated_nodes;
    }
    
    /**
     * Get root node
     *
     * @return array root node data
     */
    public function getRootNode()
    {
        return $this->vtree->getRootNode();
    }
    
    /**
     * Get node id
     *
     * @param array $a_node node data
     * @return string node id
     */
    public function getNodeId($a_node)
    {
        return $a_node["id"];
    }

    /**
     * @inheritdoc
     */
    public function getDomNodeIdForNodeId($node_id)
    {
        return parent::getDomNodeIdForNodeId(str_replace(":", "_", $node_id));
    }

    /**
     * @inheritdoc
     */
    public function getNodeIdForDomNodeId($a_dom_node_id)
    {
        $id = parent::getNodeIdForDomNodeId($a_dom_node_id);
        return str_replace("_", ":", $id);
    }


    /**
     * Get childs of node
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_id)
    {
        return $this->vtree->getChildsOfNode($a_parent_id);
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

        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = $a_parent_id_parts[1];
        
        // title
        $title = $a_node["title"];
        
        // root?
        if ($a_node["type"] == "skrt") {
            $lng->txt("skmg_skills");
        } else {
            if ($a_node["type"] == "sktr") {
                //				include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
//				$title.= " (".ilSkillTreeNode::_lookupTitle($a_parent_skl_template_tree_id).")";
            }
        }
        
        return $title;
    }
    
    /**
     * Get node icon
     *
     * @param array
     * @return
     */
    public function getNodeIcon($a_node)
    {
        $a_id_parts = explode(":", $a_node["id"]);
        $a_skl_template_tree_id = $a_id_parts[1];

        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_scat.svg");
        } else {
            $type = $a_node["type"];
            if ($type == "sktr") {
                include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
                $type = ilSkillTreeNode::_lookupType($a_skl_template_tree_id);
            }
            if ($type == "sktp") {
                $type = "skll";
            }
            if ($type == "sctp") {
                $type = "scat";
            }
            $icon = ilUtil::getImagePath("icon_" . $type . ".svg");
        }
        
        return $icon;
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
        
        // we have a tree id like <skl_tree_id>:<skl_template_tree_id> here
        // use this, if you want a "common" skill id in format <skill_id>:<tref_id>
        $id_parts = explode(":", $a_node["id"]);
        if ($id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $a_node["id"];
        } else {
            // skill in template
            $skill_id = $id_parts[1] . ":" . $id_parts[0];
        }
        
        return "";
    }

    /**
     * Is clickable
     *
     * @param
     * @return
     */
    public function isNodeClickable($a_node)
    {
        return false;
    }
}
