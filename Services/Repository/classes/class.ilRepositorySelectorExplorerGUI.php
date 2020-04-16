<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Explorer for selecting repository items.
 *
 * The implementation starts as a replacement for the often (ab)used ilSearchRootSelector class.
 * Clicking items triggers a "selection" command.
 * However ajax/checkbox/radio and use in an inputgui class should be implemented in the future, too.
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesRepository
 */
class ilRepositorySelectorExplorerGUI extends ilTreeExplorerGUI
{
    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $type_grps = array();
    protected $session_materials = array();
    protected $highlighted_node = null;
    protected $clickable_types = array();

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var callable
     */
    protected $nc_modifier = null;

    /**
     * @var object
     */
    protected $selection_gui = null;

    /**
     * @var string
     */
    protected $selection_par;

    /**
     * @var string
     */
    protected $selection_cmd;

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent cmd that renders the explorer
     * @param object/string $a_selection_gui gui class that should be called for the selection command
     * @param string $a_selection_cmd selection command
     * @param string $a_selection_par selection parameter
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "selectObject",
        $a_selection_par = "sel_ref_id",
        $a_id = "rep_exp_sel"
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();

        if (is_null($a_selection_gui)) {
            $a_selection_gui = $a_parent_obj;
        }

        $this->selection_gui = is_object($a_selection_gui)
            ? strtolower(get_class($a_selection_gui))
            : strtolower($a_selection_gui);
        $this->selection_cmd = $a_selection_cmd;
        $this->selection_par = $a_selection_par;
        parent::__construct($a_id, $a_parent_obj, $a_parent_cmd, $tree);

        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("title");

        // per default: all object types, except item groups
        $white = array();
        foreach ($objDefinition->getSubObjectsRecursively("root") as $rtype) {
            if ($rtype["name"] != "itgr" && !$objDefinition->isSideBlock($rtype["name"])) {
                $white[] = $rtype["name"];
            }
        }
        $this->setTypeWhiteList($white);

        // always open the path to the current ref id
        $this->setPathOpen((int) $this->tree->readRootId());
        if ((int) $_GET["ref_id"] > 0) {
            $this->setPathOpen((int) $_GET["ref_id"]);
        }
        $this->setChildLimit((int) $ilSetting->get("rep_tree_limit_number"));
    }

    /**
     * Set node content modifier
     *
     * @param callable $a_val
     */
    public function setNodeContentModifier(callable $a_val)
    {
        $this->nc_modifier = $a_val;
    }

    /**
     * Get node content modifier
     *
     * @return callable
     */
    public function getNodeContentModifier()
    {
        return $this->nc_modifier;
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

        $c = $this->getNodeContentModifier();
        if (is_callable($c)) {
            return $c($a_node);
        }

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
     * @param array $a_node node data
     * @return string icon path
     */
    public function getNodeIcon($a_node)
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }

    /**
     * Get node icon alt text
     *
     * @param array $a_node node data
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
        if ($this->getHighlightedNode()) {
            if ($this->getHighlightedNode() == $a_node["child"]) {
                return true;
            }
            return false;
        }

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

        if ($this->select_postvar == "") {
            $ilCtrl->setParameterByClass($this->selection_gui, $this->selection_par, $a_node["child"]);
            $link = $ilCtrl->getLinkTargetByClass($this->selection_gui, $this->selection_cmd);
            $ilCtrl->setParameterByClass($this->selection_gui, $this->selection_par, "");
        } else {
            return "#";
        }

        return $link;
    }

    /**
     * Is node visible
     *
     * @param array $a_node node data
     * @return bool visible true/false
     */
    public function isNodeVisible($a_node)
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('visible', '', $a_node["child"])) {
            return false;
        }

        return true;
    }

    /**
     * Sort childs
     *
     * @param array $a_childs array of child nodes
     * @param int $a_parent_node_id parent node id
     * @return array array of childs nodes
     */
    public function sortChilds($a_childs, $a_parent_node_id)
    {
        $objDefinition = $this->obj_definition;

        $parent_obj_id = ilObject::_lookupObjId($a_parent_node_id);

        if ($parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($parent_obj_id);
        } else {
            $parent_type = "dummy";
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

        // #14587 - $objDefinition->getGroupedRepositoryObjectTypes does NOT include side blocks!
        $wl = $this->getTypeWhiteList();
        if (is_array($wl) && in_array("poll", $wl)) {
            $this->type_grps[$parent_type]["poll"] = array();
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
     * @param int $a_parent_node_id node id
     * @return array childs array
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("read", "", $a_parent_node_id)) {
            return array();
        }

        return parent::getChildsOfNode($a_parent_node_id);
    }

    /**
     * Is node clickable?
     *
     * @param array $a_node node data
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        $ilAccess = $this->access;

        if ($this->select_postvar != "") {
            // return false; #14354
        }

        if (!$ilAccess->hasUserRBACorAnyPositionAccess("read", $a_node["child"])) {
            return false;
        }

        if (is_array($this->getClickableTypes()) && count($this->getClickableTypes()) > 0) {
            return in_array($a_node["type"], $this->getClickableTypes());
        }

        return true;
    }

    /**
     * set an alternate highlighted node if $_GET["ref_id"] is not set or wrong
     *
     * @param int $a_value ref_id
     */
    public function setHighlightedNode($a_value)
    {
        $this->highlighted_node = $a_value;
    }

    /**
     * get an alternate highlighted node if $_GET["ref_id"] is not set or wrong
     * Returns null if not set
     *
     * @return mixed ref_id
     */
    public function getHighlightedNode()
    {
        return $this->highlighted_node;
    }

    /**
     * set Whitelist for clickable items
     *
     * @param array/string $a_types array type
     */
    public function setClickableTypes($a_types)
    {
        if (!is_array($a_types)) {
            $a_types = array($a_types);
        }
        $this->clickable_types = $a_types;
    }

    /**
     * get whitelist for clickable items
     *
     * @return array types
     */
    public function getClickableTypes()
    {
        return (array) $this->clickable_types;
    }

    /**
     * Get HTML
     *
     * @param
     * @return
     */
    /*	function getHTML()
        {
            global $ilCtrl, $tpl;

            $add = "";
            if ($ilCtrl->isAsynch())
            {
                $add = $this->getOnLoadCode();
            }
            else
            {
                $tpl->addOnloadCode($this->getOnLoadCode());
            }

            return parent::getHTML().$add;
        }
    */

    /**
     * set Whitelist for clickable items
     *
     * @param array/string $a_types array type
     */
    public function setSelectableTypes($a_types)
    {
        if (!is_array($a_types)) {
            $a_types = array($a_types);
        }
        $this->selectable_types = $a_types;
    }

    /**
     * get whitelist for clickable items
     *
     * @return array types
     */
    public function getSelectableTypes()
    {
        return (array) $this->selectable_types;
    }

    /**
     * Is node selectable?
     *
     * @param mixed $a_node node object/array
     * @return boolean node selectable true/false
     */
    protected function isNodeSelectable($a_node)
    {
        if (count($this->getSelectableTypes())) {
            return in_array($a_node['type'], $this->getSelectableTypes());
        }
        return true;
    }
}
