<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Administration explorer GUI class
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @todo: isClickable, top node id
 *
 * @ingroup ServicesAdministration
 */
class ilAdministrationExplorerGUI extends ilTreeExplorerGUI
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $type_grps = array();
    protected $session_materials = array();
    
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        $this->cur_ref_id = (int) $_GET["ref_id"];
        
        $this->top_node_id = 0;
        parent::__construct("adm_exp", $a_parent_obj, $a_parent_cmd, $tree);

        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("title");

        $white = array();
        foreach ($objDefinition->getSubObjectsRecursively("root") as $rtype) {
            if ($rtype["name"] != "itgr" && !$objDefinition->isSideBlock($rtype["name"])) {
                $white[] = $rtype["name"];
            }
        }
        $this->setTypeWhiteList($white);

        if ((int) $_GET["ref_id"] > 0) {
            $this->setPathOpen((int) $_GET["ref_id"]);
        }
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
        
        $title = $a_node["title"];
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            if ($title == "ILIAS") {
                $title = $lng->txt("repository");
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
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }

    /**
     * Get node icon alt text
     *
     * @param array node array
     * @return string alt text
     */
    public function getNodeIconAlt($a_node)
    {
        $lng = $this->lng;

        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $title = $a_node["title"];
            if ($title == "ILIAS") {
                $title = $lng->txt("repository");
            }
            return $lng->txt("icon") . " " . $title;
        }

        
        return parent::getNodeIconAlt($a_node);
    }
    
    /**
     * Is node highlighted?
     *
     * @param mixed $a_node node object/array
     * @return boolean node visible true/false
     */
    public function isNodeHighlighted($a_node)
    {
        if ($a_node["child"] == $_GET["ref_id"] ||
            ($_GET["ref_id"] == "" && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
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
        $objDefinition = $this->obj_definition;
        
        $class_name = $objDefinition->getClassName($a_node["type"]);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $ilCtrl->setParameterByClass($class, "ref_id", $a_node["child"]);
        $link = $ilCtrl->getLinkTargetByClass($class, "view");
        $ilCtrl->setParameterByClass($class, "ref_id", $_GET["ref_id"]);
        
        return $link;
    }

    /**
     * Is node visible
     *
     * @param
     * @return
     */
    public function isNodeVisible($a_node)
    {
        $rbacsystem = $this->rbacsystem;

        $visible = $rbacsystem->checkAccess('visible', $a_node["child"]);
        if ($a_node["type"] == "rolf" && $a_node["child"] != ROLE_FOLDER_ID) {
            return false;
        }
        return $visible;
    }
    
    /**
     * Sort childs
     *
     * @param array $a_childs array of child nodes
     * @param mixed $a_parent_node parent node
     *
     * @return array array of childs nodes
     */
    public function sortChilds($a_childs, $a_parent_node_id)
    {
        $objDefinition = $this->obj_definition;

        $parent_obj_id = ilObject::_lookupObjId($a_parent_node_id);
        
        if ($parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($parent_obj_id);
        } else {
            $parent_type  = "dummy";
            $this->type_grps["dummy"] = array("root" => "dummy");
        }

        if (empty($this->type_grps[$parent_type])) {
            $this->type_grps[$parent_type] =
                $objDefinition->getGroupedRepositoryObjectTypes($parent_type);
        }
        $group = array();
        
        foreach ($a_childs as $child) {
            $g = $objDefinition->getGroupOfObj($child["type"]);
            if ($g == "") {
                $g = $child["type"];
            }
            $group[$g][] = $child;
        }

        $childs = array();
        foreach ($this->type_grps[$parent_type] as $t => $g) {
            if (is_array($group[$t])) {
                // do we have to sort this group??
                include_once("./Services/Container/classes/class.ilContainer.php");
                include_once("./Services/Container/classes/class.ilContainerSorting.php");
                $sort = ilContainerSorting::_getInstance($parent_obj_id);
                $group = $sort->sortItems($group);
                
                // need extra session sorting here
                if ($t == "sess") {
                }
                
                foreach ($group[$t] as $k => $item) {
                    $childs[] = $item;
                }
            }
        }
        
        return $childs;
    }

    /**
     * Get childs of node
     *
     * @param
     * @return
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        $rbacsystem = $this->rbacsystem;
        
        if (!$rbacsystem->checkAccess("read", $a_parent_node_id)) {
            return array();
        }

        return parent::getChildsOfNode($a_parent_node_id);
    }
    
    /**
     * Is node clickable?
     *
     * @param mixed $a_node node object/array
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $ilDB = $this->db;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        return $rbacsystem->checkAccess('read', $a_node["child"]);
    }
}
