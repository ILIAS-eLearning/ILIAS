<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Explorer for skill management
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilSurveySkillExplorer extends ilExplorer
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $root_id;
    public $slm_obj;
    public $output;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target, $a_templates = false)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->templates = $a_templates;
        
        parent::__construct($a_target);
        
        $this->setFilterMode(IL_FM_POSITIVE);
        $this->addFilter("skrt");
        $this->addFilter("skll");
        $this->addFilter("scat");
        //		$this->addFilter("sktr");
        $this->setTitleLength(999);
        
        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $this->tree = new ilSkillTree();
        $this->root_id = $this->tree->readRootId();
        
        $this->setSessionExpandVariable("skpexpand");
        $this->checkPermissions(false);
        $this->setPostSort(false);
        
        $this->setOrderColumn("order_nr");
        //		$this->textwidth = 200;

        $this->force_open_path = array();
        
        $this->all_nodes = $this->tree->getSubTree($this->tree->getNodeData($this->root_id));
        foreach ($this->all_nodes as $n) {
            $this->node[$n["child"]] = $n;
            $this->child_nodes[$n["parent"]][] = $n;
            $this->parent[$n["child"]] = $n["parent"];
            //echo "-$k-"; var_dump($n);
        }
        
        //		$this->buildSelectableTree($this->root_id);
    }
    
    /**
     * Build selectable tree
     *
     * @param
     * @return
     */
    /*
        function buildSelectableTree($a_node_id)
        {
            if (ilSkillTreeNode::_lookupSelfEvaluation($a_node_id))
            {
                $this->selectable[$a_node_id] = true;
                $this->selectable[$this->parent[$a_node_id]] = true;
            }
            foreach ($this->getOriginalChildsOfNode($a_node_id) as $n)
            {
                $this->buildSelectableTree($n["child"]);
            }
            if ($this->selectable[$a_node_id] &&
                !ilSkillTreeNode::_lookupDraft($a_node_id))
            {
                $this->selectable_child_nodes[$this->node[$a_node_id]["parent"]][] =
                    $this->node[$a_node_id];
            }
        }*/
    

    /**
    * set force open path
    */
    public function setForceOpenPath($a_path)
    {
        $this->force_open_path = $a_path;
    }

    
    /**
    * check if links for certain object type are activated
    *
    * @param	string		$a_type			object type
    *
    * @return	boolean		true if linking is activated
    */
    public function isClickable($a_type, $a_obj_id = 0)
    {
        $ilUser = $this->user;
        if ($a_type == "skll") {
            return true;
        }
        return false;
    }
    
    /**
    * build link target
    */
    public function buildLinkTarget($a_node_id, $a_type)
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilsurveyskillgui", "obj_id", $a_node_id);
        $ret = $ilCtrl->getLinkTargetByClass("ilsurveyskillgui", "selectSkillForQuestion");
        $ilCtrl->setParameterByClass("ilsurveyskillgui", "obj_id", $_GET["obj_id"]);
        
        return $ret;
    }

    /**
     * standard implementation for title, may be overwritten by derived classes
     */
    public function buildTitle($a_title, $a_id, $a_type)
    {
        $lng = $this->lng;
        
        if ($a_type == "sktr") {
            include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
            $tid = ilSkillTemplateReference::_lookupTemplateId($a_id);
            //			$a_title.= " (".ilSkillTreeNode::_lookupTitle($tid).")";
        }
        
        /*		if (ilSkillTreeNode::_lookupSelfEvaluation($a_id))
                {
                    $a_title.= " [".$lng->txt("add")."]";
                }*/

        return $a_title;
    }

    /**
    * force expansion of node
    */
    public function forceExpanded($a_obj_id)
    {
        if (in_array($a_obj_id, $this->force_open_path)) {
            return true;
        }
        return false;
    }

    /**
     * Get frame target
     */
    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        return "";
    }
    
    /**
     * Get maximum tree depth
     *
     * @param
     * @return
     */
    /*	function getMaximumTreeDepth()
        {
            $this->tree->getMaximumDepth();
        }*/

    /**
     * Get childs of node (selectable tree)
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    /*
        function getChildsOfNode($a_parent_id)
        {
            if (is_array($this->selectable_child_nodes[$a_parent_id]))
            {
                $childs =  $this->selectable_child_nodes[$a_parent_id];
                $childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
                return $childs;
            }
            return array();
        }*/

    /**
     * Get original childs of node (whole tree)
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    /*	function getOriginalChildsOfNode($a_parent_id)
        {
            if (is_array($this->child_nodes[$a_parent_id]))
            {
                return $this->child_nodes[$a_parent_id];
            }
            return array();
        }*/

    /**
     * get image path (may be overwritten by derived classes)
     */
    public function getImage($a_name, $a_type = "", $a_obj_id = "")
    {
        if (in_array($a_type, array("sktr"))) {
            return ilUtil::getImagePath("icon_skll_s.gif");
        }
        return ilUtil::getImagePath($a_name);
    }
}
