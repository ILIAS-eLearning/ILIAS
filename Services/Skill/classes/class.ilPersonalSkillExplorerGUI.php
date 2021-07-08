<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Explorer for selecting a personal skill
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilPersonalSkillExplorerGUI extends ilTreeExplorerGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $selectable = array();
    protected $selectable_child_nodes = array();
    protected $has_selectable_nodes = false;

    /**
     * @var \ILIAS\Skill\Tree\SkillTreeFactory
     */
    protected $skill_tree_factory;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $tree_repo;

    protected $node = [];
    protected $all_nodes = [];
    protected $child_nodes = [];
    protected $parent = [];

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_select_gui, $a_select_cmd, $a_select_par = "obj_id")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        
        $this->select_gui = (is_object($a_select_gui))
            ? strtolower(get_class($a_select_gui))
            : $a_select_gui;
        $this->select_cmd = $a_select_cmd;
        $this->select_par = $a_select_par;

        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();

        $this->lng->loadLanguageModule("skmg");
        
        $this->tree = new ilGlobalSkillTree();
        $this->root_id = $this->tree->readRootId();
        //$this->setRootId(0);
        
        parent::__construct("pskill_sel", $a_parent_obj, $a_parent_cmd, $this->tree);
        $this->setSkipRootNode(true); // false?
        //var_dump($this->tree->getChilds(0)); exit;

        //var_dump($this->tree->getNodeData($this->root_id)); exit;
        $this->all_nodes = [];

        foreach ($this->tree->getChilds(0) as $c) {
            $tree_id = $this->tree_repo->getTreeIdForNodeId($c["child"]);
            //var_dump($tree_id);
            $tree1 = $this->skill_tree_factory->getById($tree_id);
            //var_dump($tree1->getNodeData($c["child"])); exit;
            $all_nodes = $tree1->getSubTree($tree1->getNodeData($c["child"]));
            //var_dump($all_nodes); exit;
            foreach ($all_nodes as $n) {
                $this->node[$n["child"]] = $n;
                $this->child_nodes[$n["parent"]][] = $n;
                $this->parent[$n["child"]] = $n["parent"];
                $this->all_nodes[] = $n;
                //echo "-$k-"; var_dump($n);
            }
        }
        //var_dump($this->child_nodes); exit;
        //exit;
        
        //		$this->setTypeWhiteList(array("skrt", "skll", "scat", "sktr"));
        $this->buildSelectableTree(0);
    }

    /**
     * @inheritdoc
     */
    protected function getRootId()
    {
        return 0;
    }

    /**
     * Set selectable nodes exist?
     *
     * @param bool $a_val selectable nodes exist
     */
    protected function setHasSelectableNodes($a_val)
    {
        $this->has_selectable_nodes = $a_val;
    }

    /**
     * Get selectable nodes exist?
     *
     * @return bool selectable nodes exist
     */
    public function getHasSelectableNodes()
    {
        return $this->has_selectable_nodes;
    }

    /**
     * Build selectable tree
     *
     * @param int $a_node_id tree id
     */
    public function buildSelectableTree($a_node_id)
    {
        if (in_array(ilSkillTreeNode::_lookupStatus($a_node_id), array(ilSkillTreeNode::STATUS_DRAFT, ilSkillTreeNode::STATUS_OUTDATED))) {
            if ($a_node_id != 0 && ilSkillTreeNode::_lookupType($a_node_id) !== "skrt") {
                return;
            }
        }

        if (ilSkillTreeNode::_lookupSelfEvaluation($a_node_id)) {
            $this->selectable[$a_node_id] = true;
            $cid = $a_node_id;
            //$this->selectable[$this->parent[$a_node_id]] = true;
            while (isset($this->parent[$cid])) {
                $this->selectable[$this->parent[$cid]] = true;
                $cid = $this->parent[$cid];
            }
        }
        foreach ($this->getOriginalChildsOfNode($a_node_id) as $n) {
            //echo "+".$n["child"]."+";
            $this->buildSelectableTree($n["child"]);
        }
        if ($this->selectable[$a_node_id]) {
            $this->setHasSelectableNodes(true);
            $this->selectable_child_nodes[$this->node[$a_node_id]["parent"]][] =
                $this->node[$a_node_id];
        }
    }

    public function getNodeId($a_node)
    {
        if (is_null($a_node["child"])) {
            return 0;
        }
        return parent::getNodeId($a_node);
    }

    /**
     * Get childs of node (selectable tree)
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_id)
    {
        if (is_array($this->selectable_child_nodes[$a_parent_id])) {
            $childs = $this->selectable_child_nodes[$a_parent_id];
            $childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
            return $childs;
        }
        return array();
    }

    /**
     * Get original childs of node (whole tree)
     *
     * @param int $a_parent_id parent id
     * @return array childs
     */
    public function getOriginalChildsOfNode($a_parent_id)
    {
        if (is_array($this->child_nodes[$a_parent_id])) {
            return $this->child_nodes[$a_parent_id];
        }
        return array();
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
        
        $skill_id = $a_node["child"];
        
        $ilCtrl->setParameterByClass($this->select_gui, $this->select_par, $skill_id);
        $ret = $ilCtrl->getLinkTargetByClass($this->select_gui, $this->select_cmd);
        $ilCtrl->setParameterByClass($this->select_gui, $this->select_par, "");
        
        return $ret;
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

        return $title;
    }
    
    /**
     * Is clickable
     *
     * @param
     * @return
     */
    public function isNodeClickable($a_node)
    {
        if (!ilSkillTreeNode::_lookupSelfEvaluation($a_node["child"])) {
            return false;
        }
        return true;
    }
    
    /**
     * get image path (may be overwritten by derived classes)
     */
    public function getNodeIcon($a_node)
    {
        $t = $a_node["type"];
        if (in_array($t, array("sktr"))) {
            return ilUtil::getImagePath("icon_skll.svg");
        }
        return ilUtil::getImagePath("icon_" . $t . ".svg");
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
