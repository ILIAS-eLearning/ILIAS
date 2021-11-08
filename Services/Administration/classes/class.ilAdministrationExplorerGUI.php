<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Administration\AdminGUIRequest;

/**
 * Administration explorer GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAdministrationExplorerGUI extends ilTreeExplorerGUI
{
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected ilRbacSystem $rbacsystem;
    protected ilDBInterface $db;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected array $type_grps = array();
    protected array $session_materials = array();
    protected AdminGUIRequest $request;
    protected int $cur_ref_id;
    protected int $top_node_id;
    
    public function __construct(
        string $a_parent_obj,
        string $a_parent_cmd
    ) {
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
        $objDefinition = $DIC["objDefinition"];
        $this->request = new AdminGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->cur_ref_id = $this->request->getRefId();
        
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

        if ($this->cur_ref_id > 0) {
            $this->setPathOpen($this->cur_ref_id);
        }
    }

    public function getNodeContent($a_node) : string
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
    
    public function getNodeIcon($a_node) : string
    {
        $obj_id = ilObject::_lookupObjId($a_node["child"]);
        return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
    }

    public function getNodeIconAlt($a_node) : string
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
    
    public function isNodeHighlighted($a_node) : bool
    {
        if ($a_node["child"] == $this->cur_ref_id ||
            ($this->cur_ref_id == 0 && $a_node["child"] == $this->getNodeId($this->getRootNode()))) {
            return true;
        }
        return false;
    }
    
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        
        $class_name = $objDefinition->getClassName($a_node["type"]);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $ilCtrl->setParameterByClass($class, "ref_id", $a_node["child"]);
        $link = $ilCtrl->getLinkTargetByClass($class, "view");
        $ilCtrl->setParameterByClass($class, "ref_id", $this->cur_ref_id);
        
        return $link;
    }

    public function isNodeVisible($a_node) : bool
    {
        $rbacsystem = $this->rbacsystem;

        $visible = $rbacsystem->checkAccess('visible', $a_node["child"]);
        if ($a_node["type"] == "rolf" && $a_node["child"] != ROLE_FOLDER_ID) {
            return false;
        }
        return $visible;
    }
    
    public function sortChilds(array $a_childs, $a_parent_node_id) : array
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

        $childs = array();
        foreach ($this->type_grps[$parent_type] as $t => $g) {
            if (is_array($group[$t])) {
                // do we have to sort this group??
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

    public function getChildsOfNode($a_parent_node_id) : array
    {
        $rbacsystem = $this->rbacsystem;
        
        if (!$rbacsystem->checkAccess("read", $a_parent_node_id)) {
            return array();
        }

        return parent::getChildsOfNode($a_parent_node_id);
    }
    
    public function isNodeClickable($a_node) : bool
    {
        $rbacsystem = $this->rbacsystem;

        return $rbacsystem->checkAccess('read', $a_node["child"]);
    }
}
