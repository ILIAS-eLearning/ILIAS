<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * TableGUI class for user administration
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilUserTableGUI: ilFormPropertyDispatchGUI
 */
class ilUserTableGUI extends ilTable2GUI
{
    public const MODE_USER_FOLDER = 1;
    public const MODE_LOCAL_USER = 2;
    
    private ?int $mode = null;
    private int $user_folder_id = 0;
    protected \ILIAS\User\StandardGUIRequest $user_request;
    protected array $udf_fields = array(); // Missing array type.
    protected array $filter = array(); // Missing array type.

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_mode = self::MODE_USER_FOLDER,
        bool $a_load_items = true
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->user_folder_id = $a_parent_obj->getObject()->getRefId();

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

        $this->user_request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

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
    
    protected function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }
    
    protected function getMode() : int
    {
        return $this->mode;
    }
    
    protected function getUserFolderId() : int
    {
        return $this->user_folder_id;
    }

    /**
     * Read user defined fields definitions
     */
    public function readUserDefinedFieldsDefinitions() : void
    {
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
     */
    public function getUserDefinedField(string $a_key) : array // Missing array type.
    {
        return $this->udf_fields[$a_key] ?? array();
    }

    public function isUdfColumn(string $a_key) : bool
    {
        if (strpos($a_key, "udf_") === 0) {
            return true;
        }
        return false;
    }

    public function getSelectableColumns() : array // Missing array type.
    {
        global $DIC;

        $lng = $DIC['lng'];
        
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
            if (!isset($cols[$f]) && (!isset($fd["lists_hide"]) || !$fd["lists_hide"])) {
                // #18795
                $caption = $fd["lang_var"] ?? $f;
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
    
    public function getItems() : void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->determineOffsetAndOrder();
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            $user_filter = ilLocalUser::_getFolderIds(true);
        } else {
            if ($this->filter['time_limit_owner']) {
                $user_filter = array($this->filter['time_limit_owner']);
            } else {
                // All accessible users
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }



        //#13221 don't show all users if user filter is empty!
        if (!count($user_filter)) {
            $this->setMaxCount(0);
            $this->setData([]);
            return;
        }

        if (isset($this->filter['user_ids']) && is_array($this->filter['user_ids']) && !count($this->filter['user_ids'])) {
            $this->setMaxCount(0);
            $this->setData([]);
            return;
        }

        $additional_fields = $this->getSelectedColumns();
        unset(
            $additional_fields["firstname"],
            $additional_fields["lastname"],
            $additional_fields["email"],
            $additional_fields["second_email"],
            $additional_fields["last_login"],
            $additional_fields["access_until"],
            $additional_fields['org_units']
        );

        $udf_filter = array();
        foreach ($this->filter as $k => $v) {
            if (strpos($k, "udf_") === 0) {
                $udf_filter[$k] = $v;
            }
        }

        $query = new ilUserQuery();
        $order_field = $this->getOrderField();
        if (strpos($order_field, "udf_") !== 0 || isset($additional_fields[$order_field])) {
            $query->setOrderField($order_field);
            $query->setOrderDirection($this->getOrderDirection());
        }
        $query->setOffset($this->getOffset());
        $query->setLimit($this->getLimit());
        $query->setTextFilter($this->filter['query']);
        $query->setActionFilter($this->filter['activation']);
        $query->setLastLogin($this->filter['last_login']);
        $query->setLimitedAccessFilter($this->filter['limited_access']);
        $query->setNoCourseFilter($this->filter['no_courses']);
        $query->setNoGroupFilter($this->filter['no_groups']);
        $query->setCourseGroupFilter($this->filter['course_group']);
        $query->setRoleFilter((int) $this->filter['global_role']);
        $query->setAdditionalFields($additional_fields);
        $query->setUserFolder($user_filter);
        $query->setUserFilter($this->filter['user_ids'] ?? []);
        $query->setUdfFilter($udf_filter);
        $query->setFirstLetterLastname($this->user_request->getLetter());
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

    public function addFilterItemValue($filter, $value) : void // Missing parameter types.
    {
        $this->filter[$filter] = $value;
    }
        
    public function getUserIdsForFilter() : array // Missing array type.
    {
        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // All accessible users
            $user_filter = ilLocalUser::_getFolderIds(true);
        } else {
            if ($this->filter['time_limit_owner']) {
                $user_filter = array($this->filter['time_limit_owner']);
            } else {
                // All accessible users
                $user_filter = ilLocalUser::_getFolderIds();
            }
        }

        if (!isset($this->filter['user_ids'])) {
            $this->filter['user_ids'] = null;
        }
        
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
        $query->setUserFilter($this->filter['user_ids']);
        $query->setFirstLetterLastname($this->user_request->getLetter());
        
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
    
    public function initFilter() : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        
        
        // Show context filter
        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $parent_ids = ilLocalUser::_getFolderIds();

            if (count($parent_ids) > 1) {
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


        // activation
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
        $cb = new ilCheckboxInputGUI($this->lng->txt("user_limited_access"), "limited_access");
        $this->addFilterItem($cb);
        $cb->readFromSession();
        $this->filter["limited_access"] = $cb->getChecked();
        
        // last login
        $di = new ilDateTimeInputGUI($this->lng->txt("user_last_login_before"), "last_login");
        $default_date = new ilDateTime(time(), IL_CAL_UNIX);
        $default_date->increment(IL_CAL_DAY, 1);
        $di->setDate($default_date);
        $this->addFilterItem($di);
        $di->readFromSession();
        $this->filter["last_login"] = $di->getDate();

        if ($this->getMode() == self::MODE_USER_FOLDER) {
            // no assigned courses
            $cb = new ilCheckboxInputGUI($this->lng->txt("user_no_courses"), "no_courses");
            $this->addFilterItem($cb);
            $cb->readFromSession();
            $this->filter["no_courses"] = $cb->getChecked();
            
            // no assigned groups
            $ng = new ilCheckboxInputGUI($this->lng->txt("user_no_groups"), "no_groups");
            $this->addFilterItem($ng);
            $ng->readFromSession();
            $this->filter['no_groups'] = $ng->getChecked();

            // course/group members
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
     */
    public function addFilterItemByUdfType(
        string $id,
        int $type,
        bool $a_optional = false,
        ?string $caption = null,
        array $a_options = array()
    ) : ?ilFormPropertyGUI {
        global $DIC;

        $lng = $DIC['lng'];

        if (!$caption) {
            $caption = $lng->txt($id);
        }

        switch ($type) {
            case UDF_TYPE_SELECT:
                $item = new ilSelectInputGUI($caption, $id);
                $sel_options = array("" => $this->lng->txt("user_all"));
                foreach ($a_options as $o) {
                    $sel_options[$o] = $o;
                }
                $item->setOptions($sel_options);
                break;

            case UDF_TYPE_TEXT:
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

    protected function fillRow(array $a_set) : void // Missing array type.
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameterByClass("ilobjusergui", "letter", $this->user_request->getLetter());

        foreach ($this->getSelectedColumns() as $c) {
            if ($c == "access_until") {
                $this->tpl->setCurrentBlock("access_until");
                $this->tpl->setVariable("VAL_ACCESS_UNTIL", $a_set["access_until"]);
                $this->tpl->setVariable("CLASS_ACCESS_UNTIL", $a_set["access_class"]);
            } elseif ($c == "last_login") {
                $this->tpl->setCurrentBlock("last_login");
                $this->tpl->setVariable(
                    "VAL_LAST_LOGIN",
                    ilDatePresentation::formatDate(new ilDateTime($a_set['last_login'], IL_CAL_DATETIME))
                );
            } elseif (in_array($c, array("firstname", "lastname"))) {
                $this->tpl->setCurrentBlock($c);
                $this->tpl->setVariable("VAL_" . strtoupper($c), (string) $a_set[$c]);
            } elseif ($c == 'auth_mode') {
                $this->tpl->setCurrentBlock('user_field');
                $this->tpl->setVariable('VAL_UF', ilAuthUtils::getAuthModeTranslation(ilAuthUtils::_getAuthMode($a_set['auth_mode'])));
                $this->tpl->parseCurrentBlock();
            } else {	// all other fields
                $this->tpl->setCurrentBlock("user_field");
                $val = (trim($a_set[$c]) == "")
                    ? " "
                    : $a_set[$c];
                if ($a_set[$c] != "") {
                    switch ($c) {
                        case "birthday":
                            $val = ilDatePresentation::formatDate(new ilDate($val, IL_CAL_DATE));
                            break;
                        
                        case "gender":
                            $val = $lng->txt("gender_" . $a_set[$c]);
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

        if ($a_set["usr_id"] != 6) {
            if ($this->getMode() == self::MODE_USER_FOLDER or $a_set['time_limit_owner'] == $this->getUserFolderId()) {
                $this->tpl->setCurrentBlock("checkb");
                $this->tpl->setVariable("ID", $a_set["usr_id"]);
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->getMode() == self::MODE_USER_FOLDER or $a_set['time_limit_owner'] == $this->getUserFolderId()) {
            $this->tpl->setVariable("VAL_LOGIN", $a_set["login"]);
            $ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $a_set["usr_id"]);
            $this->tpl->setVariable(
                "HREF_LOGIN",
                $ilCtrl->getLinkTargetByClass("ilobjusergui", "view")
            );
            $ilCtrl->setParameterByClass("ilobjusergui", "obj_id", "");
        } else {
            $this->tpl->setVariable('VAL_LOGIN_PLAIN', $a_set['login']);
        }

        if ($this->getMode() == self::MODE_LOCAL_USER) {
            $this->tpl->setCurrentBlock('context');
            $this->tpl->setVariable(
                'VAL_CONTEXT',
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_set['time_limit_owner']))
            );
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setCurrentBlock('roles');
            $ilCtrl->setParameter($this->getParentObject(), 'obj_id', $a_set['usr_id']);
            $this->tpl->setVariable('ROLE_LINK', $ilCtrl->getLinkTarget($this->getParentObject(), 'assignRoles'));
            $this->tpl->setVariable('TXT_ROLES', $this->lng->txt('edit'));
            $ilCtrl->clearParameters($this->getParentObject());
            $this->tpl->parseCurrentBlock();
        }
    }
}
