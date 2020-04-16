<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/*
 * Repository Explorer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup	ServicesRepository
 */
class ilRepositoryExplorer extends ilExplorer
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

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


    /**
     * id of root folder
     * @var int root folder id
     * @access private
     */
    public $root_id;
    public $output;
    public $ctrl;
    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_target, $a_top_node = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $tree = $DIC->repositoryTree();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilSetting = $DIC->settings();
        $objDefinition = $DIC["objDefinition"];

        $this->ctrl = $ilCtrl;


        $this->force_open_path = array();


        parent::__construct($a_target);
        $this->tree = $tree;
        $this->root_id = $this->tree->readRootId();
        $this->order_column = "title";
        $this->setSessionExpandVariable("repexpand");
        $this->setTitle($lng->txt("overview"));

        // please do not uncomment this
        if ($ilSetting->get("repository_tree_pres") == "" ||
            ($ilSetting->get("rep_tree_limit_grp_crs") && $a_top_node == 0)) {
            foreach ($objDefinition->getExplorerContainerTypes() as $type) {
                $this->addFilter($type);
            }
            $this->setFiltered(true);
            $this->setFilterMode(IL_FM_POSITIVE);
        } elseif ($ilSetting->get("repository_tree_pres") == "all_types") {
            foreach ($objDefinition->getAllRBACObjects() as $rtype) {
                $this->addFilter($rtype);
            }
            $this->setFiltered(true);
            $this->setFilterMode(IL_FM_POSITIVE);
        }
    }

    /**
     * set force open path
     */
    public function setForceOpenPath($a_path)
    {
        $this->force_open_path = $a_path;
    }

    /**
    * note: most of this stuff is used by ilCourseContentInterface too
    */
    public function buildLinkTarget($a_node_id, $a_type)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_type) {
            case "cat":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $link;

            case "catr":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $link;

            case "grp":
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjgroupgui"), "");
                $ilCtrl->setParameterByClass("ilobjgroupgui", "ref_id", $_GET["ref_id"]);
                return $link;
            case "grpr":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $link;

            case "crs":
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjcoursegui"), "view");
                $ilCtrl->setParameterByClass("ilobjcoursegui", "ref_id", $_GET["ref_id"]);
                return $link;
                
            case "crsr":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "redirect");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $link;

            case 'rcrs':
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "infoScreen");
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                return $link;

            case 'prg':
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $a_node_id);
                $link = $ilCtrl->getLinkTargetByClass("ilobjstudyprogrammegui", "view");
                $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $_GET["ref_id"]);
                return $link;

            default:
                include_once('./Services/Link/classes/class.ilLink.php');
                return ilLink::_getStaticLink($a_node_id, $a_type, true);

        }
    }

    /**
    *
    * STATIC, do not use $this inside!
    *
    * Note: this is used by course interface !?
    */
    public function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
    {
        switch ($a_type) {
            case "cat":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "cat");
                return $t_frame;

            case "catr":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "catr");
                return $t_frame;

            case "grp":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "grp");
                return $t_frame;

            case "grpr":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "grpr");
                return $t_frame;

            case "crs":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "crs");
                return $t_frame;
                
            case "crsr":
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "crsr");
                return $t_frame;

            case 'rcrs':
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", 'rcrs');
                return $t_frame;

            case 'prg':
                $t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", 'prg');
                return $t_frame;

            default:
                return "_top";
        }
    }
    
    /**
    * get image path
    */
    public function getImage($a_name, $a_type = "", $a_obj_id = "")
    {
        if ($a_type != "") {
            return ilObject::_getIcon($a_obj_id, "tiny", $a_type);
        }
        
        return parent::getImage($a_name);
    }

    public function isClickable($a_type, $a_ref_id = 0, $a_obj_id = 0)
    {
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $ilDB = $this->db;
        $ilUser = $this->user;
        $ilAccess = $this->access;

        if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id)) {
            return false;
        }

        switch ($a_type) {
            case 'tst':
                if (!$rbacsystem->checkAccess("read", $a_ref_id)) {
                    return false;
                }

                $query = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s", $a_obj_id);
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    return (bool) $row->complete;
                }
                return false;

            case 'svy':
                if (!$rbacsystem->checkAccess("read", $a_ref_id)) {
                    return false;
                }

                $query = sprintf("SELECT * FROM svy_svy WHERE obj_fi=%s", $a_obj_id);
                $res = $ilDB->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    return (bool) $row->complete;
                }
                return false;

            // media pools can only be edited
            case "mep":
                if ($rbacsystem->checkAccess("read", $a_ref_id)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'grpr':
            case 'crsr':
            case 'catr':
                include_once('./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php');
                return ilContainerReferenceAccess::_isAccessible($a_ref_id);
            case 'prg':
                    return $rbacsystem->checkAccess("visible", $a_ref_id);

                

            // all other types are only clickable, if read permission is given
            default:
                if ($rbacsystem->checkAccess("read", $a_ref_id)) {
                    // check if lm is online
                    if ($a_type == "lm") {
                        include_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                        $lm_obj = new ilObjLearningModule($a_ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_ref_id))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($a_type == "htlm") {
                        include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
                        $lm_obj = new ilObjFileBasedLM($a_ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_ref_id))) {
                            return false;
                        }
                    }
                    // check if fblm is online
                    if ($a_type == "sahs") {
                        include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
                        $lm_obj = new ilObjSAHSLearningModule($a_ref_id);
                        if (($lm_obj->getOfflineStatus()) && (!$rbacsystem->checkAccess('write', $a_ref_id))) {
                            return false;
                        }
                    }
                    // check if glossary is online
                    if ($a_type == "glo") {
                        $obj_id = ilObject::_lookupObjectId($a_ref_id);
                        include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
                        if ((!ilObjGlossary::_lookupOnline($obj_id)) &&
                            (!$rbacsystem->checkAccess('write', $a_ref_id))) {
                            return false;
                        }
                    }

                    return true;
                } else {
                    return false;
                }
                break;
        }
    }

    public function showChilds($a_ref_id, $a_obj_id = 0)
    {
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        //vd($a_ref_id);

        if ($a_ref_id == 0) {
            return true;
        }
        if (!ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $a_obj_id)) {
            return false;
        }
        if ($rbacsystem->checkAccess("read", $a_ref_id)) {
            return true;
        } else {
            return false;
        }
    }

    public function isVisible($a_ref_id, $a_type)
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
        $ilSetting = $this->settings;

        if (!$ilAccess->checkAccess('visible', '', $a_ref_id)) {
            return false;
        }
        
        $is_course = false;
        $container_parent_id = $tree->checkForParentType($a_ref_id, 'grp');
        if (!$container_parent_id) {
            $is_course = true;
            $container_parent_id = $tree->checkForParentType($a_ref_id, 'crs');
        }
        if ($container_parent_id) {
            // do not display session materials for container course/group
            if ($ilSetting->get("repository_tree_pres") == "all_types" && $container_parent_id != $a_ref_id) {
                // get container event items only once
                if (!isset($this->session_materials[$container_parent_id])) {
                    include_once './Modules/Session/classes/class.ilEventItems.php';
                    $this->session_materials[$container_parent_id] = ilEventItems::_getItemsOfContainer($container_parent_id);
                }
                // get item group items only once
                if (!isset($this->item_group_items[$container_parent_id])) {
                    include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';
                    $this->item_group_items[$container_parent_id] = ilItemGroupItems::_getItemsOfContainer($container_parent_id);
                }
                if (in_array($a_ref_id, $this->session_materials[$container_parent_id])) {
                    return false;
                }
                if (in_array($a_ref_id, $this->item_group_items[$container_parent_id])) {
                    return false;
                }
            }
        }
        
        return true;
    }



    /**
    * overwritten method from base class
    * @access	public
    * @param	integer obj_id
    * @param	integer array options
    * @return	string
    */
    public function formatHeader($tpl, $a_obj_id, $a_option)
    {
        $lng = $this->lng;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        // custom icons
        $path = ilObject::_getIcon($a_obj_id, "tiny", "root");

        $tpl->setCurrentBlock("icon");
        $nd = $tree->getNodeData(ROOT_FOLDER_ID);
        $title = $nd["title"];
        if ($title == "ILIAS") {
            $title = $lng->txt("repository");
        }

        $tpl->setVariable("ICON_IMAGE", $path);
        $tpl->setVariable("TXT_ALT_IMG", $lng->txt("icon") . " " . $title);
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("link");
        $tpl->setVariable("TITLE", $title);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", "1");
        $tpl->setVariable(
            "LINK_TARGET",
            $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset")
        );
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
        $tpl->setVariable("TARGET", " target=\"_top\"");
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("element");
        $tpl->parseCurrentBlock();
    }
    
    /**
     * sort nodes
     *
     * @access public
     * @param
     * @return
     */
    public function sortNodes($a_nodes, $a_parent_obj_id)
    {
        $objDefinition = $this->obj_definition;

        if ($a_parent_obj_id > 0) {
            $parent_type = ilObject::_lookupType($a_parent_obj_id);
        } else {
            $parent_type = "dummy";
            $this->type_grps["dummy"] = array("root" => "dummy");
        }

        if (empty($this->type_grps[$parent_type])) {
            $this->type_grps[$parent_type] =
                $objDefinition->getGroupedRepositoryObjectTypes($parent_type);
        }
        $group = array();
        
        foreach ($a_nodes as $node) {
            $g = $objDefinition->getGroupOfObj($node["type"]);
            if ($g == "") {
                $g = $node["type"];
            }
            $group[$g][] = $node;
        }

        $nodes = array();
        foreach ($this->type_grps[$parent_type] as $t => $g) {
            if (is_array($group[$t])) {
                // do we have to sort this group??
                include_once("./Services/Container/classes/class.ilContainer.php");
                include_once("./Services/Container/classes/class.ilContainerSorting.php");
                $sort = ilContainerSorting::_getInstance($a_parent_obj_id);
                $group = $sort->sortItems($group);
                
                // need extra session sorting here
                if ($t == "sess") {
                }
                
                foreach ($group[$t] as $k => $item) {
                    $nodes[] = $item;
                }
            }
        }
        
        return $nodes;
        //return parent::sortNodes($a_nodes,$a_parent_obj_id);
    }

    /**
     * Force expansion of node
     *
     * @param
     * @return
     */
    public function forceExpanded($a_node)
    {
        if (in_array($a_node, $this->force_open_path)) {
            return true;
        }
        return false;
    }
} // END class ilRepositoryExplorer
