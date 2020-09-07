<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* This class displays the permission status of a user concerning a specific object.
* ("Permissions" -> "Permission of User")
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjectPermissionStatusGUI: ilRepositorySearchGUI
*
* @ingroup	ServicesAccessControl
*/
class ilObjectPermissionStatusGUI
{
    /** @var ilObjUser */
    public $user;
    /** @var ilLanguage */
    public $lng;
    /** @var ilCtrl */
    public $ilCtrl;
    /** @var ilTemplate */
    public $tpl;
    /** @var ilPermission */
    public $object;
    /** @var ilRbacReview */
    public $rbacreview;
    public $user_roles;
    public $global_roles;
    public $valid_roles;
    public $assigned_valid_roles;

    const IMG_OK = 0;
    const IMG_NOT_OK = 1;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_obj)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $tpl = $DIC['tpl'];

        $this->lng = $lng;
        $this->ilCtrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->object = $a_obj;
        $this->rbacreview = $rbacreview;

        $this->user = $this->getUser();
        $this->user_roles = $rbacreview->assignedRoles($this->user->getId());
        $this->global_roles = $rbacreview->getGlobalRoles();
        $this->valid_roles = $rbacreview->getParentRoleIds($this->object->getRefId());
        $this->assigned_valid_roles = $this->getAssignedValidRoles();
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        // determine next class in the call structure
        $next_class = $this->ilCtrl->getNextClass($this);

        switch ($next_class) {
            case 'ilrepositorysearchgui':
                include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
                $rep_search = new ilRepositorySearchGUI();
                $this->ilCtrl->setReturn($this, 'perminfo');
                $this->ilCtrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $this->ilCtrl->getCmd();
                $this->$cmd();
                break;
        }
    }

    /**
     * cmd function
     */
    public function perminfo()
    {
        $tpl = new ilTemplate("tpl.info_layout.html", false, false, "Services/AccessControl");

        $tpl->setVariable("INFO_SUMMARY", $this->accessStatusInfo());
        $tpl->setVariable("INFO_PERMISSIONS", $this->accessPermissionsTable());
        $tpl->setVariable("INFO_ROLES", $this->availableRolesTable());
        $tpl->setVariable("INFO_REMARK_INTERRUPTED", $this->lng->txt('info_remark_interrupted'));

        $this->tpl->setVariable("ADM_CONTENT", $tpl->get());

        $this->addToolbar();
    }

    /**
     * Creates Toolbar entries
     */
    public function addToolbar()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];

        $ilToolbar->setFormAction($this->ilCtrl->getFormAction($this, "perminfo"));

        $ilToolbar->addText($this->lng->txt('user'));

        include_once("./Services/Form/classes/class.ilTextInputGUI.php");

        $login = new ilTextInputGUI($this->lng->txt("username"), "user_login");
        $login->setDataSource($this->ilCtrl->getLinkTargetByClass(array(get_class($this),
            'ilRepositorySearchGUI'), 'doUserAutoComplete', '', true));
        $login->setSize(15);
        $login->setValue($this->user->getLogin());

        $ilToolbar->addInputItem($login);

        $ilToolbar->addFormButton($this->lng->txt("info_change_user_view"), "perminfo");

        //$ilToolbar->addText($lng->txt("info_enter_login_or_id"));
    }

    /**
     * Access- and Statusinformation Info
     * @return string HTML
     */
    public function accessStatusInfo()
    {
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

        $info = new ilInfoScreenGUI(new stdClass());
        $info->setFormAction($this->ilCtrl->getFormAction($this));

        $info->addSection($this->lng->txt("info_access_and_status_info"));

        foreach ($this->getAccessStatusInfoData() as $data) {
            $info->addProperty($data[0], $data[1]);
        }

        return $info->getHTML();
    }

    /**
     * Access Permissions Table
     * @return string HTML
     */
    public function accessPermissionsTable()
    {
        include_once("./Services/AccessControl/classes/class.ilAccessPermissionsStatusTableGUI.php");

        $table = new ilAccessPermissionsStatusTableGUI($this, "perminfo");

        $table->setData($this->getAccessPermissionTableData());
        $table->setTitle($this->lng->txt("info_access_permissions"));

        return $table->getHTML();
    }

    /**
     * Available Roles Table
     * @return string HTML
     */
    public function availableRolesTable()
    {
        include_once("./Services/AccessControl/classes/class.ilAvailableRolesStatusTableGUI.php");

        $table = new ilAvailableRolesStatusTableGUI($this, "perminfo");

        $table->setData($this->getAvailableRolesTableData());
        $table->setTitle($this->lng->txt("info_available_roles"));

        return $table->getHTML();
    }

    /**
     * get Assigned Valid Roles
     * @return array
     */
    public function getAssignedValidRoles()
    {
        include_once('./Services/AccessControl/classes/class.ilObjRole.php');
        $assigned_valid_roles = array();

        foreach ($this->valid_roles as $role) {
            if (in_array($role['obj_id'], $this->user_roles)) {
                if ($role["obj_id"] == SYSTEM_ROLE_ID) {
                    // get all possible operation of current object
                    $ops_list = ilRbacReview::_getOperationList($this->object->getType());
                    
                    foreach ($ops_list as $ops_data) {
                        $ops[] = (int) $ops_data['ops_id'];
                    }
                    
                    $role['ops'] = $ops;
                } else {
                    $role['ops'] = $this->rbacreview->getRoleOperationsOnObject($role["obj_id"], $this->object->getRefId());
                }
                
                include_once('./Services/AccessControl/classes/class.ilObjRole.php');
                $role['translation'] = str_replace(" ", "&nbsp;", ilObjRole::_getTranslation($role["title"]));
                $assigned_valid_roles[] = $role;
            }
        }

        $this->assigned_valid_roles = $assigned_valid_roles;

        return $assigned_valid_roles;
    }

    /**
     * get Commands
     * @param $a_type string
     * @return array
     */
    public function getCommands($a_type)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
                
        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "Access";
        include_once($location . "/class." . $full_class . ".php");
        
        $cmds = call_user_func(array($full_class, "_getCommands"));
        
        array_push($cmds, array('permission' => 'visible','cmd' => 'info'));
        
        return $cmds;
    }

    /**
     * ilUser
     * @return ilObjUser|object
     */
    public function getUser()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!isset($_POST['user_login'])) {
            $user = &$ilUser;
        } else {
            include_once('Services/User/classes/class.ilObjUser.php');
            $user_id = ilObjUser::_lookupId($_POST['user_login']);

            $factory = new ilObjectFactory();
            $user = $factory->getInstanceByObjId($user_id, false);


            if ($user === false or $user->getType() != 'usr') {
                $user = &$ilUser;
                ilUtil::sendFailure($this->lng->txt('info_err_user_not_exist'));
            } else {
                ilUtil::sendInfo($this->lng->txt('info_user_view_changed'));
            }
        }

        return $user;
    }

    /**
     * Access Status Info Data
     * @return array
     */
    public function getAccessStatusInfoData()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $infos = array();

        $result_set[0][] = $this->lng->txt("info_view_of_user");
        $result_set[0][] = $this->user->getFullname() . " (#" . $this->user->getId() . ")";

        $assigned_valid_roles = array();

        foreach ($this->getAssignedValidRoles() as $role) {
            $assigned_valid_roles[] = $role["translation"];
        }

        $roles_str = implode(", ", $assigned_valid_roles);

        $result_set[1][] = $this->lng->txt("roles");
        $result_set[1][] = $roles_str;

        $result_set[2][] = $this->lng->txt("status");

        $ilAccess->clear();
        $ilAccess->checkAccessOfUser(
            $this->user->getId(),
            'read',
            '',
            $this->object->getRefId(),
            $this->object->getType(),
            $this->object->getId()
        );

        $infos = array_merge($infos, $ilAccess->getInfo());

        $cmds = $this->getCommands($this->object->getType());

        foreach ($cmds as $cmd) {
            $ilAccess->clear();
            $ilAccess->doStatusCheck($cmd['permission'], $cmd['cmd'], $this->object->getRefId(), $this->user->getId(), $this->object->getId(), $this->object->getType());
            $infos = array_merge($infos, $ilAccess->getInfo());
        }

        $alert = "il_ItemAlertProperty";
        $okay = "il_ItemOkayProperty";
        $text = "";

        if (!$infos) {
            $text = "<span class=\"" . $okay . "\">" . $this->lng->txt("access") . "</span><br/> ";
        } else {
            foreach ($infos as $info) {
                switch ($info['type']) {
                    case IL_STATUS_MESSAGE:
                        $text .= "<span class=\"" . $okay . "\">" . $info['text'] . "</span><br/> ";
                        break;

                    case IL_NO_PARENT_ACCESS:
                        $factory = new ilObjectFactory();
                        $obj = $factory->getInstanceByRefId($info['data']);
                        $text .= "<span class=\"" . $alert . "\">" . $info['text'] . " (" . $this->lng->txt("obj_" . $obj->getType()) . " #" . $obj->getId() . ": " . $obj->getTitle() . ")</span><br/> ";
                        break;

                    default:
                        $text .= "<span class=\"" . $alert . "\">" . $info['text'] . "</span><br/> ";
                        break;

                }
            }
        }

        $result_set[2][] = $text;

        return $result_set;
    }

    /**
     * Access Permissions Table Data
     * @return array
     */
    public function getAccessPermissionTableData()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $objDefinition = $DIC['objDefinition'];

        // get all possible operation of current object
        $ops_list = ilRbacReview::_getOperationList($this->object->getType());

        $counter = 0;
        $result_set = array();

        // check permissions of user
        foreach ($ops_list as $ops) {
            $access = $ilAccess->doRBACCheck($ops['operation'], "info", $this->object->getRefId(), $this->user->getId(), $this->object->getType());

            $result_set[$counter]["img"] = $access ? self::IMG_OK : self::IMG_NOT_OK;

            if (substr($ops['operation'], 0, 7) == "create_" &&
                $objDefinition->isPlugin(substr($ops['operation'], 7))) {
                $result_set[$counter]["operation"] = ilObjectPlugin::lookupTxtById(
                    substr($ops['operation'], 7),
                    'rbac_' . $ops['operation']
                );
            } elseif ($objDefinition->isPlugin($this->object->getType())) {
                $result_set[$counter]["operation"] = ilObjectPlugin::lookupTxtById(
                    $this->object->getType(),
                    $this->object->getType() . "_" . $ops['operation']
                );
            } elseif (substr($ops['operation'], 0, 7) == 'create_') {
                $result_set[$counter]["operation"] = $this->lng->txt('rbac_' . $ops['operation']);
            } else {
                $result_set[$counter]["operation"] = $this->lng->txt($this->object->getType() . "_" . $ops['operation']);
            }

            $list_role = [];

            // Check ownership
            if ($this->user->getId() == $ilObjDataCache->lookupOwner($this->object->getId())) {
                if (
                    (substr($ops['operation'], 0, 7) != 'create_') and
                    ($ops['operation'] != 'edit_permission') and
                    ($ops['operation'] != 'edit_leanring_progress')
                ) {
                    $list_role[] = $this->lng->txt('info_owner_of_object');
                }
            }
            // get operations on object for each assigned role to user
            foreach ($this->getAssignedValidRoles() as $role) {
                if (in_array($ops['ops_id'], $role['ops'])) {
                    $list_role[] = $role['translation'];
                }
            }

            if (empty($list_role)) {
                $list_role[] = $this->lng->txt('none');
            }


            $result_set[$counter]["role_ownership"] = $list_role;

            ++$counter;
        }

        return $result_set;
    }

    /**
     * Available Roles Table Data
     * @return array
     */
    public function getAvailableRolesTableData()
    {
        global $DIC;

        $tree = $DIC['tree'];

        include_once('./Services/AccessControl/classes/class.ilObjRole.php');

        $path = array_reverse($tree->getPathId($this->object->getRefId()));

        include_once('./Services/AccessControl/classes/class.ilObjRole.php');
        $counter = 0;

        foreach ($this->valid_roles as $role) {
            $result_set[$counter]["img"] = in_array($role['obj_id'], $this->user_roles) ? self::IMG_OK : self::IMG_NOT_OK;

            if (is_subclass_of($this->object, ilObjectPlugin::class) && $role["parent"] == $this->object->getRefId()) {
                $result_set[$counter][] = ilObjectPlugin::lookupTxtById($this->object->getType(), ilObjRole::_removeObjectId($role["title"]));
            } else {
                $result_set[$counter][] = str_replace(" ", "&nbsp;", ilObjRole::_getTranslation($role["title"]));
            }

            $result_set[$counter]["role"] = str_replace(" ", "&nbsp;", ilObjRole::_getTranslation($role["title"]));

            if ($role['role_type'] != "linked") {
                $result_set[$counter]["effective_from"] = "";
            } else {
                $rolfs = $this->rbacreview->getFoldersAssignedToRole($role["obj_id"]);

                // ok, try to match the next rolf in path
                foreach ($path as $node) {
                    if ($node == 1) {
                        break;
                    }


                    if (in_array($node, $rolfs)) {
                        $nodedata = $tree->getNodeData($node);
                        $result_set[$counter]["effective_from"] = $nodedata["title"];
                        $result_set[$counter]["effective_from_ref_id"] = $node;
                        break;
                    }
                }
            }

            if (in_array($role['obj_id'], $this->global_roles)) {
                $result_set[$counter]["original_position"] = $this->lng->txt("global");
                $result_set[$counter]["original_position_ref_id"] = false;
            } else {
                $rolf = $this->rbacreview->getFoldersAssignedToRole($role["obj_id"], true);
                $parent_node = $tree->getNodeData($rolf[0]);
                $result_set[$counter]["original_position"] = $parent_node["title"];
                $result_set[$counter]["original_position_ref_id"] = $parent_node["ref_id"];
            }

            ++$counter;
        }

        return $result_set;
    }
}
