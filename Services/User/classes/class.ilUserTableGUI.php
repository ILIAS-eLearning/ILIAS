<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for user administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilUserTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesUser
*/
class ilUserTableGUI extends ilTable2GUI
{
    const MODE_USER_FOLDER = 1;
    const MODE_LOCAL_USER = 2;
    
    private $mode = null;
    private $user_folder_id = 0;

    /**
     * @var array
     */
    protected $udf_fields = array();
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_mode = self::MODE_USER_FOLDER, $a_load_items = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        
        $this->user_folder_id = $a_parent_obj->object->getRefId();

        $this->setMode($a_mode);
        $this->setId("user" . $this->getUserFolderId());
        $this->readUserDefinedFieldsDefinitions();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        //		$this->setTitle($this->lng->txt("users"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("login"), "login");
        
        foreach ($this->getSelectedColumns() as $c) {
            if ($this->isUdfColumn($c)) {
                $f = $this->getUserDefinedField($c);
                $this->addColumn($f["txt"], $f["sortable"] ? $c : "");
            } else {	// usual column
                $this->addColumn($this->lng->txt($c), $c);
            }
        }
                
        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $this->addColumn($this->lng->txt('context'), 'time_limit_owner');
            $this->addColumn($this->lng->txt('role_assignment'));
        }

        $this->setShowRowsSelector(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);

        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "applyFilter"));
        $this->setRowTemplate("tpl.user_list_row.html", "Services/User");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->initFilter();
        $this->setFilterCommand("applyFilter");
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");

        $this->setSelectAllCheckbox("id[]");
        $this->setTopCommands(true);

        
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            $this->setEnableAllCommand(true);
            
            $cmds = $a_parent_obj->getUserMultiCommands();
            foreach ($cmds as $cmd => $caption) {
                $this->addMultiCommand($cmd, $caption);
            }
        } else {
            $this->addMultiCommand("deleteUsers", $lng->txt("delete"));
        }
        
        if ($a_load_items) {
            $this->getItems();
        }
    }
    
    protected function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }
    
    protected function getMode()
    {
        return $this->mode;
    }
    
    protected function getUserFolderId()
    {
        return $this->user_folder_id;
    }

    /**
     * Read user defined fields definitions
     */
    public function readUserDefinedFieldsDefinitions()
    {
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        foreach ($user_defined_fields->getDefinitions() as $field => $definition) {
            $this->udf_fields["udf_" . $field] = array(
                "txt" => $definition["field_name"],
                "default" => false,
                "options" => $definition["field_values"],
                "type" => $definition["field_type"],
                "sortable" => in_array($definition["field_type"], array(UDF_TYPE_TEXT, UDF_TYPE_SELECT))
            );
        }
    }

    /**
     * Get user defined field
     * @param string $a_key field key
     * @return array
     */
    public function getUserDefinedField($a_key)
    {
        if (isset($this->udf_fields[$a_key])) {
            return $this->udf_fields[$a_key];
        }
        return array();
    }

    /**
     * Field key
     * @param string $a_key field key
     * @return bool
     */
    public function isUdfColumn($a_key)
    {
        if (substr($a_key, 0, 4) == "udf_") {
            return true;
        }
        return false;
    }


    /**
     * Get selectable columns
     *
     * @param
     * @return
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipGroup("preferences");
        $up->skipGroup("interests");
        $up->skipGroup("settings");
        
        // default fields
        $cols = array();

        // first and last name cannot be hidden
        $cols["firstname"] = array(
            "txt" => $lng->txt("firstname"),
            "default" => true);
        $cols["lastname"] = array(
            "txt" => $lng->txt("lastname"),
            "default" => true);
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            $ufs = $up->getStandardFields();
        
            $cols["access_until"] = array(
                "txt" => $lng->txt("access_until"),
                "default" => true);
            $cols["last_login"] = array(
                "txt" => $lng->txt("last_login"),
                "default" => true);
            
            // #13967
            $cols["create_date"] = array(
                "txt" => $lng->txt("create_date"));
            $cols["approve_date"] = array(
                "txt" => $lng->txt("approve_date"));
            $cols["agree_date"] = array(
                "txt" => $lng->txt("agree_date"));
        } else {
            $ufs = $up->getLocalUserAdministrationFields();
        }
        
        // email should be the 1st "optional" field (can be hidden)
        if (isset($ufs["email"])) {
            $cols["email"] = array(
                "txt" => $lng->txt("email"),
                "default" => true);
        }
        if (isset($ufs["second_email"])) {
            $cols["second_email"] = array(
                "txt" => $lng->txt("second_email"),
                "default" => true);
        }
        // other user profile fields
        foreach ($ufs as $f => $fd) {
            if (!isset($cols[$f]) && !$fd["lists_hide"]) {
                // #18795
                $caption = $fd["lang_var"]
                    ? $fd["lang_var"]
                    : $f;
                $cols[$f] = array(
                    "txt" => $lng->txt($caption),
                    "default" => false);
            }
        }


        /**
         * LTI, showing depending by mode user?
         */
        $cols["auth_mode"] = array(
            "txt" => $lng->txt("auth_mode"),
            "default" => false);

        
        // custom user fields
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            foreach ($this->udf_fields as $k => $field) {
                $cols[$k] = $field;
            }
        }

        // fields that are always shown
        unset($cols["username"]);
        
        return $cols;
    }
    
    /**
    * Get user items
    */
    public function getItems()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->determineOffsetAndOrder();
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            include_once './Services/User/classes/class.ilLocalUser.php';
            $user_filter = ilLocalUser::_getFolderIds();
        } else {
            if ($this->filter['time_limit_owner']) {
                $user_filter = array($this->filter['time_limit_owner']);
            } else {
                // All accessible users
                include_once './Services/User/classes/class.ilLocalUser.php';
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }

        //#13221 don't show all users if user filter is empty!
        if (!count($user_filter)) {
            $this->setMaxCount(0);
            $this->setData(array());
            return;
        }

        include_once("./Services/User/classes/class.ilUserQuery.php");
        
        $additional_fields = $this->getSelectedColumns();
        unset($additional_fields["firstname"]);
        unset($additional_fields["lastname"]);
        unset($additional_fields["email"]);
        unset($additional_fields["second_email"]);
        unset($additional_fields["last_login"]);
        unset($additional_fields["access_until"]);
        unset($additional_fields['org_units']);

        $udf_filter = array();
        foreach ($this->filter as $k => $v) {
            if (substr($k, 0, 4) == "udf_") {
                $udf_filter[$k] = $v;
            }
        }

        $query = new ilUserQuery();
        $query->setOrderField($this->getOrderField());
        $query->setOrderDirection($this->getOrderDirection());
        $query->setOffset($this->getOffset());
        $query->setLimit($this->getLimit());
        $query->setTextFilter($this->filter['query']);
        $query->setActionFilter($this->filter['activation']);
        $query->setLastLogin($this->filter['last_login']);
        $query->setLimitedAccessFilter($this->filter['limited_access']);
        $query->setNoCourseFilter($this->filter['no_courses']);
        $query->setNoGroupFilter($this->filter['no_groups']);
        $query->setCourseGroupFilter($this->filter['course_group']);
        $query->setRoleFilter($this->filter['global_role']);
        $query->setAdditionalFields($additional_fields);
        $query->setUserFolder($user_filter);
        $query->setUdfFilter($udf_filter);
        $query->setFirstLetterLastname(ilUtil::stripSlashes($_GET['letter']));
        $query->setAuthenticationFilter($this->filter['authentication']);
        $usr_data = $query->query();

        if (count($usr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $query->setOffset($this->getOffset());
            $usr_data = $query->query();
        }

        foreach ($usr_data["set"] as $k => $user) {
            if (in_array('org_units', $this->getSelectedColumns())) {
                $usr_data['set'][$k]['org_units'] = ilObjUser::lookupOrgUnitsRepresentation($user['usr_id']);
            }

            
            $current_time = time();
            if ($user['active']) {
                if ($user["time_limit_unlimited"]) {
                    $txt_access = $lng->txt("access_unlimited");
                    $usr_data["set"][$k]["access_class"] = "smallgreen";
                } elseif ($user["time_limit_until"] < $current_time) {
                    $txt_access = $lng->txt("access_expired");
                    $usr_data["set"][$k]["access_class"] = "smallred";
                } else {
                    $txt_access = ilDatePresentation::formatDate(new ilDateTime($user["time_limit_until"], IL_CAL_UNIX));
                    $usr_data["set"][$k]["access_class"] = "small";
                }
            } else {
                $txt_access = $lng->txt("inactive");
                $usr_data["set"][$k]["access_class"] = "smallred";
            }
            $usr_data["set"][$k]["access_until"] = $txt_access;
        }

        $this->setMaxCount($usr_data["cnt"]);
        $this->setData($usr_data["set"]);
    }
        
    public function getUserIdsForFilter()
    {
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            include_once './Services/User/classes/class.ilLocalUser.php';
            $user_filter = ilLocalUser::_getFolderIds();
        } else {
            if ($this->filter['time_limit_owner']) {
                $user_filter = array($this->filter['time_limit_owner']);
            } else {
                // All accessible users
                include_once './Services/User/classes/class.ilLocalUser.php';
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }
        
        include_once("./Services/User/classes/class.ilUserQuery.php");
        $query = new ilUserQuery();
        $query->setOffset($this->getOffset());
        $query->setLimit($this->getLimit());

        $query->setTextFilter($this->filter['query']);
        $query->setActionFilter($this->filter['activation']);
        $query->setAuthenticationFilter($this->filter['authentication']);
        $query->setLastLogin($this->filter['last_login']);
        $query->setLimitedAccessFilter($this->filter['limited_access']);
        $query->setNoCourseFilter($this->filter['no_courses']);
        $query->setNoGroupFilter($this->filter['no_groups']);
        $query->setCourseGroupFilter($this->filter['course_group']);
        $query->setRoleFilter($this->filter['global_role']);
        $query->setUserFolder($user_filter);
        $query->setFirstLetterLastname(ilUtil::stripSlashes($_GET['letter']));
        
        if ($this->getOrderField()) {
            $query->setOrderField(ilUtil::stripSlashes($this->getOrderField()));
            $query->setOrderDirection(ilUtil::stripSlashes($this->getOrderDirection()));
        }
        
        $usr_data = $query->query();
        $user_ids = array();

        foreach ($usr_data["set"] as $item) {
            // #11632
            if ($item["usr_id"] != SYSTEM_USER_ID) {
                $user_ids[] = $item["usr_id"];
            }
        }
        return $user_ids;
    }
    
    
    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        
        
        // Show context filter
        if ($this->getMode() == self::MODE_LOCAL_USER) {
            include_once './Services/User/classes/class.ilLocalUser.php';
            $parent_ids = ilLocalUser::_getFolderIds();

            if (count($parent_ids) > 1) {
                include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                $co = new ilSelectInputGUI($lng->txt('context'), 'time_limit_owner');
        
                $ref_id = $this->getUserFolderId();
        
                $opt[0] = $this->lng->txt('all_users');
                $opt[$this->getUserFolderId()] = $lng->txt('users') . ' (' . ilObject::_lookupTitle(ilObject::_lookupObjId($this->getUserFolderId())) . ')';

                foreach ($parent_ids as $parent_id) {
                    if ($parent_id == $this->getUserFolderId()) {
                        continue;
                    }
                    switch ($parent_id) {
                        case USER_FOLDER_ID:
                            $opt[USER_FOLDER_ID] = $lng->txt('global_user');
                            break;
                        
                        default:
                            $opt[$parent_id] = $lng->txt('users') . ' (' . ilObject::_lookupTitle(ilObject::_lookupObjId($parent_id)) . ')';
                            break;
                    }
                }
                $co->setOptions($opt);
                $this->addFilterItem($co);
                $co->readFromSession();
                $this->filter['time_limit_owner'] = $co->getValue();
            }
        }
        
        // User name, login, email filter
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ul = new ilTextInputGUI($lng->txt("login") . "/" . $lng->txt("email") . "/" .
            $lng->txt("name"), "query");
        $ul->setDataSource($ilCtrl->getLinkTarget(
            $this->getParentObject(),
            "addUserAutoComplete",
            "",
            true
        ));
        $ul->setSize(20);
        $ul->setSubmitFormOnEnter(true);
        $this->addFilterItem($ul);
        $ul->readFromSession();
        $this->filter["query"] = $ul->getValue();

        /*
        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new ilTextInputGUI($lng->txt("login")."/".$lng->txt("email")."/".$lng->txt("name"), "query");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setSubmitFormOnEnter(true);
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["query"] = $ti->getValue();
        */
        
        // activation
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $options = array(
            "" => $lng->txt("user_all"),
            "active" => $lng->txt("active"),
            "inactive" => $lng->txt("inactive"),
            );
        $si = new ilSelectInputGUI($this->lng->txt("user_activation"), "activation");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["activation"] = $si->getValue();
        
        // limited access
        include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        $cb = new ilCheckboxInputGUI($this->lng->txt("user_limited_access"), "limited_access");
        $this->addFilterItem($cb);
        $cb->readFromSession();
        $this->filter["limited_access"] = $cb->getChecked();
        
        // last login
        include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
        $di = new ilDateTimeInputGUI($this->lng->txt("user_last_login_before"), "last_login");
        $default_date = new ilDateTime(time(), IL_CAL_UNIX);
        $default_date->increment(IL_CAL_DAY, 1);
        $di->setDate($default_date);
        $this->addFilterItem($di);
        $di->readFromSession();
        $this->filter["last_login"] = $di->getDate();

        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // no assigned courses
            include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
            $cb = new ilCheckboxInputGUI($this->lng->txt("user_no_courses"), "no_courses");
            $this->addFilterItem($cb);
            $cb->readFromSession();
            $this->filter["no_courses"] = $cb->getChecked();
            
            // no assigned groups
            include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
            $ng = new ilCheckboxInputGUI($this->lng->txt("user_no_groups"), "no_groups");
            $this->addFilterItem($ng);
            $ng->readFromSession();
            $this->filter['no_groups'] = $ng->getChecked();

            // course/group members
            include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
            $rs = new ilRepositorySelectorInputGUI($lng->txt("user_member_of_course_group"), "course_group");
            $rs->setSelectText($lng->txt("user_select_course_group"));
            $rs->setHeaderMessage($lng->txt("user_please_select_course_group"));
            $rs->setClickableTypes(array("crs", "grp"));
            $this->addFilterItem($rs);
            $rs->readFromSession();
            $this->filter["course_group"] = $rs->getValue();
        }
        
        // global roles
        $options = array(
            "" => $lng->txt("user_any"),
            );
        $roles = $rbacreview->getRolesByFilter(2, $ilUser->getId());
        foreach ($roles as $role) {
            $options[$role["rol_id"]] = $role["title"];
        }
        $si = new ilSelectInputGUI($this->lng->txt("user_global_role"), "global_role");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["global_role"] = $si->getValue();

        // authentication mode
        $auth_methods = ilAuthUtils::_getActiveAuthModes();
        $options = array(
            "" => $lng->txt("user_any"),
        );
        foreach ($auth_methods as $method => $value) {
            if ($method == 'default') {
                $options[$method] = $this->lng->txt('auth_' . $method) . " (" . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName($value)) . ")";
            } else {
                $options[$method] = ilAuthUtils::getAuthModeTranslation($value);
            }
        }
        $si = new ilSelectInputGUI($this->lng->txt("auth_mode"), "authentication_method");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["authentication"] = $si->getValue();
        
        // udf fields
        foreach ($this->udf_fields as $id => $f) {
            $this->addFilterItemByUdfType($id, $f["type"], true, $f["txt"], $f["options"]);
        }
    }

    /**
     * Add filter by standard type
     *
     * @param	string	$id
     * @param	int		$type
     * @param	bool	$a_optional
     * @param	string	$caption
     * @return	object
     */
    public function addFilterItemByUdfType($id, $type, $a_optional = false, $caption = null, $a_options = array())
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (!$caption) {
            $caption = $lng->txt($id);
        }

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        switch ($type) {
            case UDF_TYPE_SELECT:
                include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                $item = new ilSelectInputGUI($caption, $id);
                $sel_options = array("" => $this->lng->txt("user_all"));
                foreach ($a_options as $o) {
                    $sel_options[$o] = $o;
                }
                $item->setOptions($sel_options);
                break;

            case UDF_TYPE_TEXT:
                include_once("./Services/Form/classes/class.ilTextInputGUI.php");
                $item = new ilTextInputGUI($caption, $id);
                $item->setMaxLength(64);
                $item->setSize(20);
                // $item->setSubmitFormOnEnter(true);
                break;

            default:
                return null;
        }

        if ($item) {
            $this->addFilterItem($item, $a_optional);
            $item->readFromSession();
            $this->filter[$id] = $item->getValue();
        }
        return $item;
    }

    /**
     * Fill table row
     */
    protected function fillRow($user)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameterByClass("ilobjusergui", "letter", $_GET["letter"]);
        
        foreach ($this->getSelectedColumns() as $c) {
            if ($c == "access_until") {
                $this->tpl->setCurrentBlock("access_until");
                $this->tpl->setVariable("VAL_ACCESS_UNTIL", $user["access_until"]);
                $this->tpl->setVariable("CLASS_ACCESS_UNTIL", $user["access_class"]);
            } elseif ($c == "last_login") {
                $this->tpl->setCurrentBlock("last_login");
                $this->tpl->setVariable(
                    "VAL_LAST_LOGIN",
                    ilDatePresentation::formatDate(new ilDateTime($user['last_login'], IL_CAL_DATETIME))
                );
            } elseif (in_array($c, array("firstname", "lastname"))) {
                $this->tpl->setCurrentBlock($c);
                $this->tpl->setVariable("VAL_" . strtoupper($c), (string) $user[$c]);
            } elseif ($c == 'auth_mode') {
                $this->tpl->setCurrentBlock('user_field');
                $this->tpl->setVariable('VAL_UF', ilAuthUtils::getAuthModeTranslation(ilAuthUtils::_getAuthMode($user['auth_mode'])));
                $this->tpl->parseCurrentBlock();
            } else {	// all other fields
                $this->tpl->setCurrentBlock("user_field");
                $val = (trim($user[$c]) == "")
                    ? " "
                    : $user[$c];
                if ($user[$c] != "") {
                    switch ($c) {
                        case "birthday":
                            $val = ilDatePresentation::formatDate(new ilDate($val, IL_CAL_DATE));
                            break;
                        
                        case "gender":
                            $val = $lng->txt("gender_" . $user[$c]);
                            break;
                        
                        case "create_date":
                        case "agree_date":
                        case "approve_date":
                            // $val = ilDatePresentation::formatDate(new ilDateTime($val,IL_CAL_DATETIME));
                            $val = ilDatePresentation::formatDate(new ilDate($val, IL_CAL_DATE));
                            break;
                    }
                }
                $this->tpl->setVariable("VAL_UF", $val);
            }
            
            $this->tpl->parseCurrentBlock();
        }

        if ($user["usr_id"] != 6) {
            if ($this->getMode() == self::MODE_USER_FOLDER or $user['time_limit_owner'] == $this->getUserFolderId()) {
                $this->tpl->setCurrentBlock("checkb");
                $this->tpl->setVariable("ID", $user["usr_id"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->getMode() == self::MODE_USER_FOLDER or $user['time_limit_owner'] == $this->getUserFolderId()) {
            $this->tpl->setVariable("VAL_LOGIN", $user["login"]);
            $ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $user["usr_id"]);
            $this->tpl->setVariable(
                "HREF_LOGIN",
                $ilCtrl->getLinkTargetByClass("ilobjusergui", "view")
            );
            $ilCtrl->setParameterByClass("ilobjusergui", "obj_id", "");
        } else {
            $this->tpl->setVariable('VAL_LOGIN_PLAIN', $user['login']);
        }

        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $this->tpl->setCurrentBlock('context');
            $this->tpl->setVariable('VAL_CONTEXT', (string) ilObject::_lookupTitle(ilObject::_lookupObjId($user['time_limit_owner'])));
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setCurrentBlock('roles');
            $ilCtrl->setParameter($this->getParentObject(), 'obj_id', $user['usr_id']);
            $this->tpl->setVariable('ROLE_LINK', $ilCtrl->getLinkTarget($this->getParentObject(), 'assignRoles'));
            $this->tpl->setVariable('TXT_ROLES', $this->lng->txt('edit'));
            $ilCtrl->clearParameters($this->getParentObject());
            $this->tpl->parseCurrentBlock();
        }
    }
}
