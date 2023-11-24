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

const IL_EXTRACT_ROLES = 1;
const IL_USER_IMPORT = 2;
const IL_VERIFY = 3;

const IL_FAIL_ON_CONFLICT = 1;
const IL_UPDATE_ON_CONFLICT = 2;
const IL_IGNORE_ON_CONFLICT = 3;

const IL_IMPORT_SUCCESS = 1;
const IL_IMPORT_WARNING = 2;
const IL_IMPORT_FAILURE = 3;

const IL_USER_MAPPING_LOGIN = 1;
const IL_USER_MAPPING_ID = 2;


/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * User Import Parser
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilUserImportParser extends ilSaxParser
{
    protected ?string $tmp_udf_name = null;
    protected ?string $tmp_udf_id = null;
    protected array $multi_values; // Missing array type.
    protected array $udf_data; // Missing array type.
    protected bool $auth_mode_set;
    protected ?string $currentPrefKey = null;
    protected array $prefs; // Missing array type.
    protected string $current_role_action;
    protected string $current_role_type;
    protected string $current_role_id = "";
    protected string $cdata;
    protected array $role_assign; // Missing array type.
    protected string $req_send_mail;
    protected ilAccountMail $acc_mail;
    protected int $mode;
    public bool $approve_date_set = false;
    public bool $time_limit_set = false;
    public bool $time_limit_owner_set = false;

    public bool $updateLookAndSkin = false;
    public int $folder_id;
    public array $roles; // Missing array type.
    public string $action;      // "Insert","Update","Delete"
    protected array $required_fields = []; // Missing array type.
    protected array $containedTags = [];

    /**
     * The variable holds the protocol of the import.
     * This variable is an associative array.
     * - Keys are login names of users or "missing login", if the login name is
     *   missing.
     * - Values are an array of error messages associated with the login.
     *   If the value array is empty, then the user was imported successfully.
     */
    public array $protocol;

    /**
     * This variable is used to collect each login that we encounter in the
     * import data.
     * This variable is needed to detect duplicate logins in the import data.
     * The variable is an associative array. (I would prefer using a set, but PHP
     * does not appear to support sets.)
     * Keys are logins.
     * Values are logins.
     */
    public array $logins;

    /**
     * Conflict handling rule.
     *
     * Values:  IL_FAIL_ON_CONFLICT
     *          IL_UPDATE_ON_CONFLICT
     *          IL_IGNORE_ON_CONFLICT
     */
    public int $conflict_rule;

    public bool $send_mail;

    /**
     * This variable is used to report the error level of the validation process
     * or the importing process.
     *
     * Values:  IL_IMPORT_SUCCESS
     *          IL_IMPORT_WARNING
     *          IL_IMPORT_FAILURE
     *
     * Meaning of the values when in validation mode:
     *          IL_IMPORT_WARNING
     *					Some of the entity actions can not be processed
     *                  as specified in the XML file. One or more of the
     *                  following conflicts have occurred:
     *                  -	An "Insert" action has been specified for a user
     *						who is already in the database.
     *                  -	An "Update" action has been specified for a user
     *						who is not in the database.
     *                  -	A "Delete" action has been specified for a user
     *					   who is not in the database.
     *          IL_IMPORT_FAILURE
     *					Some of the XML elements are invalid.
     *
     * Meaning of the values when in import mode:
     *          IL_IMPORT_WARNING
     *					Some of the entity actions have not beeen processed
     *					as specified in the XML file.
     *
     *                  In IL_UPDATE_ON_CONFLICT mode, the following
     *					 may have occured:
     *                  -	An "Insert" action has been replaced by a
     *						"Update" action for a user who is already in the
     *						database.
     *                   -	An "Update" action has been replaced by a
     *						"Insert" action for a user who is not in the
     *						database.
     *                  -	A "Delete" action has been replaced by a "Ignore"
     *						action for a user who is not in the database.
     *
     *                 In IL_IGNORE_ON_CONFLICT mode, the following
     *					 may have occured:
     *                 -	An "Insert" action has been replaced by a
     *						"Ignore" action for a user who is already in the
     *						database.
     *                 -	An "Update" action has been replaced by a
     *						"Ignore" action for a user who is not in the
     *						database.
     *                  -	A "Delete" action has been replaced by a "Ignore"
     *						action for a user who is not in the database.
     *
     *          IL_IMPORT_FAILURE
     *					The import could not be completed.
     *
     *                       In IL_FAIL_ON_CONFLICT mode, the following
     *						 may have occured:
     *                       -	An "Insert" action has failed for a user who is
     *							already in the database.
     *                       -	An "Update" action has failed for a user who is
     *							not in the database.
     *                       -	A "Delete" action has failed for a user who is
     *							not in the database.
     */
    public int $error_level;

    public ?string $currPasswordType;
    public ?string $currPassword;
    public ?string $currActive = null;
    public int $userCount;
    public array $user_mapping = []; // Missing array type.
    public int $mapping_mode;

    /**
     * Cached local roles.
     * This is used to speed up access to local roles.
     * This is an associative array.
     * The key is either a role_id  or  a role_id with the string "_courseMembersObject" appended.
     * The value is a role object or  the course members object for which the role is defined
     */
    public array $localRoleCache;

    /**
     * Cached personal picture of the actual user
     * This is used because the ilObjUser object has no field for the personal picture
     */
    public ?array $personalPicture = null; // Missing array type.

    /**
     * Cached parent roles.
     * This is used to speed up assignment to local roles with parents.
     * This is an associative array.
     * The key is a role_id .
     * The value is an array of role_ids containing all parent roles.
     */
    public array $parentRolesCache;

    public string $skin;
    public string $style;

    /**
     * User assigned styles
     */
    public array $userStyles; // Missing array type.

    /**
     * Indicates if the skins are hidden
     */
    public bool $hideSkin;

    /**
     * Indicates if the skins are enabled
     */
    public bool $disableSkin;

    public int $user_id;

    private ilObjUser $userObj;
    private string $current_messenger_type;
    protected ilRecommendedContentManager $recommended_content_manager;
    protected ilUserSettingsConfig $user_settings_config;

    /**
     * @param string $a_xml_file
     * @param int    $a_mode IL_EXTRACT_ROLES | IL_USER_IMPORT | IL_VERIFY
     * @param int    $a_conflict_rule IL_FAIL_ON_CONFLICT | IL_UPDATE_ON_CONFLICT | IL_IGNORE_ON_CONFLICT
     * @throws ilSystemStyleException
     */
    public function __construct(
        string $a_xml_file = '',
        int $a_mode = IL_USER_IMPORT,
        int $a_conflict_rule = IL_FAIL_ON_CONFLICT
    ) {
        global $DIC;

        $DIC->settings();

        $this->roles = array();
        $this->mode = $a_mode;
        $this->conflict_rule = $a_conflict_rule;
        $this->error_level = IL_IMPORT_SUCCESS;
        $this->protocol = array();
        $this->logins = array();
        $this->userCount = 0;
        $this->localRoleCache = array();
        $this->parentRolesCache = array();
        $this->send_mail = false;
        $this->mapping_mode = IL_USER_MAPPING_LOGIN;

        $this->user_settings_config = new ilUserSettingsConfig();

        // get all active style  instead of only assigned ones -> cannot transfer all to another otherwise
        $this->userStyles = array();
        $skins = ilStyleDefinition::getAllSkins();

        if (is_array($skins)) {
            foreach ($skins as $skin) {
                foreach ($skin->getStyles() as $style) {
                    if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId())) {
                        continue;
                    }
                    $this->userStyles [] = $skin->getId() . ":" . $style->getId();
                }
            }
        }

        $this->hideSkin = (!$this->user_settings_config->isVisible("skin_style"));
        $this->disableSkin = (!$this->user_settings_config->isChangeable("skin_style"));

        $this->acc_mail = new ilAccountMail();
        $this->acc_mail->setAttachConfiguredFiles(true);
        $this->acc_mail->useLangVariablesAsFallback(true);

        $this->recommended_content_manager = new ilRecommendedContentManager();

        $request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
        $this->req_send_mail = $request->getSendMail();

        parent::__construct($a_xml_file);
    }

    /**
     * assign users to this folder (normally the usr_folder)
     * But if called from local admin => the ref_id of the category
     */
    public function setFolderId(int $a_folder_id): void
    {
        $this->folder_id = $a_folder_id;
    }

    public function getFolderId(): int
    {
        return $this->folder_id;
    }

    /**
    * set event handler
    * should be overwritten by inherited class
    * @access	private
    *
    * @param resource|\XMLParser $a_xml_parser
    */
    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * set import to local role assignemt
    *
    * @param	array		role assignment (key: import id; value: local role id)
    */
    public function setRoleAssignment(array $a_assign): void
    {
        $this->role_assign = $a_assign;
    }

    /**
     * generate a tag with given name and attributes
     */
    public function buildTag(string $type, string $name, array $attr = null): string // Missing array type.
    {
        $tag = "<";

        if ($type === "end") {
            $tag .= "/";
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        $tag .= ">";

        return $tag;
    }

    public function handlerBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        switch ($this->mode) {
            case IL_EXTRACT_ROLES:
                $this->extractRolesBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case IL_USER_IMPORT:
                $this->importBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case IL_VERIFY:
                $this->verifyBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
        }

        $this->cdata = "";
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function extractRolesBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        switch ($a_name) {
            case "Role":
                // detect numeric, ilias id (then extract role id) or alphanumeric
                $this->current_role_id = $a_attribs["Id"];
                if (($internal_id = ilUtil::__extractId($this->current_role_id, IL_INST_ID)) > 0) {
                    $this->current_role_id = $internal_id;
                }
                $this->current_role_type = $a_attribs["Type"];
                break;
        }
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function importBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        global $DIC;

        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];

        switch ($a_name) {
            case "Role":
                $this->current_role_id = $a_attribs["Id"];
                if (($internal_id = ilUtil::__extractId($this->current_role_id, IL_INST_ID)) > 0) {
                    $this->current_role_id = $internal_id;
                }
                $this->current_role_type = $a_attribs["Type"];
                $this->current_role_action = (!isset($a_attribs["Action"])) ? "Assign" : $a_attribs["Action"];
                break;

            case "PersonalPicture":
                $this->personalPicture = array(
                    "encoding" => $a_attribs["encoding"],
                    "imagetype" => $a_attribs["imagetype"],
                    "content" => ""
                );
                break;

            case "Look":
                $this->skin = $a_attribs["Skin"];
                $this->style = $a_attribs["Style"];
                break;

            case "User":
                $this->acc_mail->reset();
                $this->prefs = array();
                $this->currentPrefKey = null;
                $this->auth_mode_set = false;
                $this->approve_date_set = false;
                $this->time_limit_set = false;
                $this->time_limit_owner_set = false;
                $this->updateLookAndSkin = false;
                $this->skin = "";
                $this->style = "";
                $this->personalPicture = null;
                $this->userCount++;
                $this->userObj = new ilObjUser();

                // user defined fields
                $this->udf_data = array();

                // if we have an object id, store it
                $this->user_id = -1;
                if (isset($a_attribs["Id"]) && $this->getUserMappingMode() == IL_USER_MAPPING_ID) {
                    if (is_numeric($a_attribs["Id"])) {
                        $this->user_id = $a_attribs["Id"];
                    } elseif ($id = ilUtil::__extractId($a_attribs["Id"], IL_INST_ID) > 0) {
                        $this->user_id = $id;
                    }
                }

                $this->userObj->setPref(
                    "skin",
                    $ilias->ini->readVariable("layout", "skin")
                );
                $this->userObj->setPref(
                    "style",
                    $ilias->ini->readVariable("layout", "style")
                );

                if (isset($a_attribs["Language"])) {
                    $this->containedTags[] = "Language";
                }
                $this->userObj->setLanguage($a_attribs["Language"] ?? '');
                $this->userObj->setImportId($a_attribs["Id"] ?? '');
                $this->action = (is_null($a_attribs["Action"])) ? "Insert" : $a_attribs["Action"];
                $this->currPassword = null;
                $this->currPasswordType = null;
                $this->currActive = null;
                $this->multi_values = array();
                break;

            case 'Password':
                $this->currPasswordType = $a_attribs['Type'];
                break;
            case "AuthMode":
                if (array_key_exists("type", $a_attribs)) {
                    switch ($a_attribs["type"]) {
                        case "saml":
                        case "ldap":
                            if (strcmp('saml', $a_attribs['type']) === 0) {
                                $list = ilSamlIdp::getActiveIdpList();
                                if (count($list) === 1) {
                                    $this->auth_mode_set = true;
                                    $idp = current($list);
                                    $this->userObj->setAuthMode('saml_' . $idp->getIdpId());
                                }
                                break;
                            }
                            if (strcmp('ldap', $a_attribs['type']) === 0) {
                                // no server id provided => use default server
                                $list = ilLDAPServer::_getActiveServerList();
                                if (count($list) == 1) {
                                    $this->auth_mode_set = true;
                                    $ldap_id = current($list);
                                    $this->userObj->setAuthMode('ldap_' . $ldap_id);
                                }
                            }
                            break;

                        case "default":
                        case "local":
                        case "shibboleth":
                        case "script":
                        case "cas":
                        case "soap":
                        case "openid":
                            // begin-patch auth_plugin
                        default:
                            $this->auth_mode_set = true;
                            $this->userObj->setAuthMode($a_attribs["type"]);
                            break;
                    }
                } else {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_inapplicable"), "AuthMode", $a_attribs["type"])
                    );
                }
                break;

            case 'UserDefinedField':
                $this->tmp_udf_id = $a_attribs['Id'];
                $this->tmp_udf_name = $a_attribs['Name'];
                break;

            case 'AccountInfo':
                $this->current_messenger_type = strtolower($a_attribs["Type"]);
                break;
            case 'GMapInfo':
                $this->userObj->setLatitude($a_attribs["latitude"]);
                $this->userObj->setLongitude($a_attribs["longitude"]);
                $this->userObj->setLocationZoom($a_attribs["zoom"]);
                break;
            case 'Pref':
                $this->currentPrefKey = $a_attribs["key"];
                break;
        }
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function verifyBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        global $DIC;

        $lng = $DIC['lng'];

        switch ($a_name) {
            case "Role":
                if ($a_attribs['Id'] == "") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_missing"), "Role", "Id"));
                }
                $this->current_role_id = $a_attribs["Id"];
                $this->current_role_type = $a_attribs["Type"];
                if ($this->current_role_type !== 'Global'
                && $this->current_role_type !== 'Local') {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_missing"), "Role", "Type"));
                }
                $this->current_role_action = (!isset($a_attribs["Action"])) ? "Assign" : $a_attribs["Action"];
                if ($this->current_role_action !== "Assign"
                && $this->current_role_action !== "AssignWithParents"
                && $this->current_role_action !== "Detach") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "Role", "Action", $a_attribs["Action"]));
                }
                if ($this->action === "Insert"
                && $this->current_role_action === "Detach") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_inapplicable"), "Role", "Action", $this->current_role_action, $this->action));
                }
                if ($this->action === "Delete") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_inapplicable"), "Role", "Delete"));
                }
                break;

            case "User":
                $this->userCount++;
                $this->containedTags = [];
                $this->userObj = new ilObjUser();
                $this->userObj->setLanguage($a_attribs["Language"] ?? '');
                $this->userObj->setImportId($a_attribs["Id"]);
                $this->currentPrefKey = null;
                // if we have an object id, store it
                $this->user_id = -1;

                if (!is_null($a_attribs["Id"]) && $this->getUserMappingMode() == IL_USER_MAPPING_ID) {
                    if (is_numeric($a_attribs["Id"])) {
                        $this->user_id = $a_attribs["Id"];
                    } elseif ($id = ilUtil::__extractId($a_attribs["Id"], IL_INST_ID) > 0) {
                        $this->user_id = $id;
                    }
                }

                $this->action = (is_null($a_attribs["Action"])) ? "Insert" : $a_attribs["Action"];
                if ($this->action !== "Insert"
                && $this->action !== "Update"
                && $this->action !== "Delete") {
                    $this->logFailure($this->userObj->getImportId(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "User", "Action", $a_attribs["Action"]));
                }
                $this->currPassword = null;
                $this->currPasswordType = null;
                break;

            case 'Password':
                $this->currPasswordType = $a_attribs['Type'];
                break;
            case "AuthMode":
                if (array_key_exists("type", $a_attribs)) {
                    switch ($a_attribs["type"]) {
                        case "saml":
                        case "ldap":
                            if (strcmp('saml', $a_attribs['type']) === 0) {
                                $list = ilSamlIdp::getActiveIdpList();
                                if (count($list) !== 1) {
                                    $this->logFailure(
                                        $this->userObj->getImportId(),
                                        sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "AuthMode", "type", $a_attribs['type'])
                                    );
                                }
                                break;
                            }
                            if (strcmp('ldap', $a_attribs['type']) === 0) {
                                // no server id provided
                                $list = ilLDAPServer::_getActiveServerList();
                                if (count($list) != 1) {
                                    $this->logFailure(
                                        $this->userObj->getImportId(),
                                        sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "AuthMode", "type", $a_attribs['type'])
                                    );
                                }
                            }
                            break;

                        case "default":
                        case "local":
                        case "shibboleth":
                        case "script":
                        case "cas":
                        case "soap":
                        case "openid":
                            // begin-patch auth_plugin
                        default:
                            $this->userObj->setAuthMode($a_attribs["type"]);
                            break;
                    }
                } else {
                    $this->logFailure($this->userObj->getImportId(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "AuthMode", "type", ""));
                }
                break;
            case 'Pref':
                $this->currentPrefKey = $a_attribs["key"];
                break;

        }
    }

    public function handlerEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        switch ($this->mode) {
            case IL_EXTRACT_ROLES:
                $this->extractRolesEndTag($a_xml_parser, $a_name);
                break;
            case IL_USER_IMPORT:
                $this->importEndTag($a_xml_parser, $a_name);
                break;
            case IL_VERIFY:
                $this->verifyEndTag($a_xml_parser, $a_name);
                break;
        }
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function extractRolesEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        switch ($a_name) {
            case "Role":
                $this->roles[$this->current_role_id]["name"] = $this->cdata;
                $this->roles[$this->current_role_id]["type"] =
                    $this->current_role_type;
                break;
        }
    }

    /**
     * Returns the parent object of the role folder object which contains the specified role.
     */
    public function getRoleObject(int $a_role_id): ilObjRole
    {
        if (array_key_exists($a_role_id, $this->localRoleCache)) {
            return $this->localRoleCache[$a_role_id];
        } else {
            $role_obj = new ilObjRole($a_role_id, false);
            $role_obj->read();
            $this->localRoleCache[$a_role_id] = $role_obj;
            return $role_obj;
        }
    }

    /**
     * Returns the parent object of the role folder object which contains the specified role.
     */
    public function getCourseMembersObjectForRole(int $a_role_id): ilCourseParticipants
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (array_key_exists($a_role_id . '_courseMembersObject', $this->localRoleCache)) {
            return $this->localRoleCache[$a_role_id . '_courseMembersObject'];
        } else {
            $course_refs = $rbacreview->getFoldersAssignedToRole($a_role_id, true);
            $course_ref = $course_refs[0];
            $course_obj = new ilObjCourse($course_ref, true);
            $crsmembers_obj = ilCourseParticipants::_getInstanceByObjId($course_obj->getId());
            $this->localRoleCache[$a_role_id . '_courseMembersObject'] = $crsmembers_obj;
            return $crsmembers_obj;
        }
    }

    /**
     * Assigns a user to a role.
     */
    public function assignToRole(ilObjUser $a_user_obj, int $a_role_id): void
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];

        // Do nothing, if the user is already assigned to the role.
        // Specifically, we do not want to put a course object or
        // group object on the personal desktop again, if a user
        // has removed it from the personal desktop.
        if ($rbacreview->isAssigned($a_user_obj->getId(), $a_role_id)) {
            return;
        }

        // If it is a course role, use the ilCourseMember object to assign
        // the user to the role

        $rbacadmin->assignUser($a_role_id, $a_user_obj->getId(), true);
        $obj_id = $rbacreview->getObjectOfRole($a_role_id);
        switch (ilObject::_lookupType($obj_id)) {
            case 'grp':
            case 'crs':
                $ref_ids = ilObject::_getAllReferences($obj_id);
                $ref_id = current((array) $ref_ids);
                if ($ref_id) {
                    // deactivated for now, see discussion at
                    // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
                    //$this->recommended_content_manager->addObjectRecommendation($a_user_obj->getId(), $ref_id);
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get array of parent role ids from cache.
     * If necessary, create a new cache entry.
     * @return array[]
     */
    public function getParentRoleIds(int $a_role_id): array
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if (!array_key_exists($a_role_id, $this->parentRolesCache)) {
            $parent_role_ids = array();

            $role_obj = $this->getRoleObject($a_role_id);
            $short_role_title = substr($role_obj->getTitle(), 0, 12);
            $folders = $rbacreview->getFoldersAssignedToRole($a_role_id, true);
            if (count($folders) > 0) {
                $all_parent_role_ids = $rbacreview->getParentRoleIds($folders[0]);
                foreach ($all_parent_role_ids as $parent_role_id => $parent_role_data) {
                    if ($parent_role_id != $a_role_id) {
                        switch (substr($parent_role_data['title'], 0, 12)) {
                            case 'il_crs_admin':
                            case 'il_grp_admin':
                                if ($short_role_title === 'il_crs_admin' || $short_role_title === 'il_grp_admin') {
                                    $parent_role_ids[] = $parent_role_id;
                                }
                                break;
                            case 'il_crs_tutor':
                            case 'il_grp_tutor':
                                if ($short_role_title === 'il_crs_tutor' || $short_role_title === 'il_grp_tutor') {
                                    $parent_role_ids[] = $parent_role_id;
                                }
                                break;
                            case 'il_crs_membe':
                            case 'il_grp_membe':
                                if ($short_role_title === 'il_crs_membe' || $short_role_title === 'il_grp_membe') {
                                    $parent_role_ids[] = $parent_role_id;
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
            $this->parentRolesCache[$a_role_id] = $parent_role_ids;
        }
        return $this->parentRolesCache[$a_role_id];
    }

    /**
     * Assigns a user to a role and to all parent roles.
     */
    public function assignToRoleWithParents(
        ilObjUser $a_user_obj,
        int $a_role_id
    ): void {
        $this->assignToRole($a_user_obj, $a_role_id);

        $parent_role_ids = $this->getParentRoleIds($a_role_id);
        foreach ($parent_role_ids as $parent_role_id) {
            $this->assignToRole($a_user_obj, $parent_role_id);
        }
    }

    /**
     * Detaches a user from a role.
     */
    public function detachFromRole(
        ilObjUser $a_user_obj,
        int $a_role_id
    ): void {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];

        $rbacadmin->deassignUser($a_role_id, $a_user_obj->getId());

        if (substr(ilObject::_lookupTitle($a_role_id), 0, 6) === 'il_crs' ||
            substr(ilObject::_lookupTitle($a_role_id), 0, 6) === 'il_grp') {
            $obj = $rbacreview->getObjectOfRole($a_role_id);
            $ref = ilObject::_getAllReferences($obj);
            $ref_id = end($ref);
            $this->recommended_content_manager->removeObjectRecommendation($a_user_obj->getId(), $ref_id);
        }
    }

    protected function tagContained(string $tagname): bool
    {
        return in_array($tagname, $this->containedTags, true);
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function importEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];

        $this->containedTags[] = $a_name;

        switch ($a_name) {
            case "Role":
                $this->roles[$this->current_role_id]["name"] = $this->cdata;
                $this->roles[$this->current_role_id]["type"] = $this->current_role_type;
                $this->roles[$this->current_role_id]["action"] = $this->current_role_action;
                break;

            case "PersonalPicture":
                switch ($this->personalPicture["encoding"]) {
                    case "Base64":
                        $this->personalPicture["content"] = base64_decode($this->cdata);
                        break;
                    case "UUEncode":
                        $this->personalPicture["content"] = convert_uudecode($this->cdata);
                        break;
                }
                break;

            case "User":
                $this->userObj->setFullname();
                // Fetch the user_id from the database, if we didn't have it in xml file
                // fetch as well, if we are trying to insert -> recognize duplicates!
                if ($this->user_id == -1 || $this->action === "Insert") {
                    $user_id = ilObjUser::getUserIdByLogin($this->userObj->getLogin());
                } else {
                    $user_id = $this->user_id;
                }

                if ($user_id === (int) ANONYMOUS_USER_ID || $user_id === (int) SYSTEM_USER_ID) {
                    return;
                }

                // Handle conflicts
                switch ($this->conflict_rule) {
                    case IL_FAIL_ON_CONFLICT:
                        // do not change action
                        break;
                    case IL_UPDATE_ON_CONFLICT:
                        switch ($this->action) {
                            case "Insert":
                                if ($user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_replaced"), "Insert", "Update"));
                                    $this->action = "Update";
                                }
                                break;
                            case "Update":
                                if (!$user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_replaced"), "Update", "Insert"));
                                    $this->action = "Insert";
                                }
                                break;
                            case "Delete":
                                if (!$user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_ignored"), "Delete"));
                                    $this->action = "Ignore";
                                }
                                break;
                        }
                        break;
                    case IL_IGNORE_ON_CONFLICT:
                        switch ($this->action) {
                            case "Insert":
                                if ($user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_ignored"), "Insert"));
                                    $this->action = "Ignore";
                                }
                                break;
                            case "Update":
                                if (!$user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_ignored"), "Update"));
                                    $this->action = "Ignore";
                                }
                                break;
                            case "Delete":
                                if (!$user_id) {
                                    $this->logWarning($this->userObj->getLogin(), sprintf($lng->txt("usrimport_action_ignored"), "Delete"));
                                    $this->action = "Ignore";
                                }
                                break;
                        }
                        break;
                }

                // check external account conflict (if external account is already used)
                // note: we cannot apply conflict rules in the same manner as to logins here
                // so we ignore records with already existing external accounts.
                //echo $this->userObj->getAuthMode().'h';
                $am = ($this->userObj->getAuthMode() === "default" || $this->userObj->getAuthMode() == "")
                    ? ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode'))
                    : $this->userObj->getAuthMode();
                $loginForExternalAccount = ($this->userObj->getExternalAccount() == "")
                    ? ""
                    : ilObjUser::_checkExternalAuthAccount($am, $this->userObj->getExternalAccount());
                switch ($this->action) {
                    case "Insert":
                        if ($loginForExternalAccount != "") {
                            $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_insert_ext_account_exists") . " (" . $this->userObj->getExternalAccount() . ")");
                            $this->action = "Ignore";
                        }
                        break;

                    case "Update":
                        // this variable describes the ILIAS login which belongs to the given external account!!!
                        // it is NOT nescessarily the ILIAS login of the current user record !!
                        // so if we found an ILIAS login according to the authentication method
                        // check if the ILIAS login belongs to the current user record, otherwise somebody else is using it!
                        if ($loginForExternalAccount != "") {
                            // check if we changed the value!
                            $externalAccountHasChanged = $this->userObj->getExternalAccount() != ilObjUser::_lookupExternalAccount($this->user_id);
                            // if it has changed and the external login
                            if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->userObj->getLogin())) {
                                $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_update_ext_account_exists") . " (" . $this->userObj->getExternalAccount() . ")");
                                $this->action = "Ignore";
                            }
                        }
                        break;
                }

                if (count($this->multi_values)) {
                    if (isset($this->multi_values["GeneralInterest"])) {
                        $this->userObj->setGeneralInterests($this->multi_values["GeneralInterest"]);
                    }
                    if (isset($this->multi_values["OfferingHelp"])) {
                        $this->userObj->setOfferingHelp($this->multi_values["OfferingHelp"]);
                    }
                    if (isset($this->multi_values["LookingForHelp"])) {
                        $this->userObj->setLookingForHelp($this->multi_values["LookingForHelp"]);
                    }
                }

                // Perform the action
                switch ($this->action) {
                    case "Insert":
                        if ($user_id) {
                            $this->logFailure($this->userObj->getLogin(), $lng->txt("usrimport_cant_insert"));
                        } else {
                            if (!strlen($this->currPassword) == 0) {
                                switch (strtoupper($this->currPasswordType)) {
                                    case "BCRYPT":
                                        $this->userObj->setPasswd($this->currPassword, ilObjUser::PASSWD_CRYPTED);
                                        $this->userObj->setPasswordEncodingType('bcryptphp');
                                        $this->userObj->setPasswordSalt(null);
                                        break;

                                    case "PLAIN":
                                        $this->userObj->setPasswd($this->currPassword, ilObjUser::PASSWD_PLAIN);
                                        $this->acc_mail->setUserPassword((string) $this->currPassword);
                                        break;

                                    default:
                                        $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "Type", "Password", $this->currPasswordType));
                                        break;

                                }
                            } else {
                                // this does the trick for empty passwords
                                // since a MD5 string has always 32 characters,
                                // no hashed password combination will ever equal to
                                // an empty string
                                $this->userObj->setPasswd("", ilObjUser::PASSWD_CRYPTED);
                            }

                            $this->userObj->setTitle($this->userObj->getFullname());
                            $this->userObj->setDescription($this->userObj->getEmail());

                            if (!$this->time_limit_owner_set) {
                                $this->userObj->setTimeLimitOwner($this->getFolderId());
                            }

                            // default time limit settings
                            if (!$this->time_limit_set) {
                                $this->userObj->setTimeLimitUnlimited(1);
                                $this->userObj->setTimeLimitMessage(0);

                                if (!$this->approve_date_set) {
                                    $this->userObj->setApproveDate(date("Y-m-d H:i:s"));
                                }
                            }


                            $this->userObj->setActive($this->currActive === 'true' || is_null($this->currActive));

                            // Finally before saving new user.
                            // Check if profile is incomplete

                            // #8759
                            if (count($this->udf_data)) {
                                $this->userObj->setUserDefinedData($this->udf_data);
                            }

                            if (!$this->userObj->getLanguage()) {
                                $this->userObj->setLanguage($this->lng->getDefaultLanguage());
                            }

                            $this->userObj->setProfileIncomplete($this->checkProfileIncomplete($this->userObj));
                            $this->userObj->create();

                            //insert user data in table user_data
                            $this->userObj->saveAsNew();

                            // Set default prefs
                            $this->userObj->setPref('hits_per_page', $ilSetting->get('hits_per_page', 30));
                            //$this->userObj->setPref('show_users_online',$ilSetting->get('show_users_online','y'));

                            if (count($this->prefs)) {
                                foreach ($this->prefs as $key => $value) {
                                    if ($key !== "mail_incoming_type" &&
                                        $key !== "mail_signature" &&
                                        $key !== "mail_linebreak"
                                    ) {
                                        $this->userObj->setPref($key, $value);
                                    }
                                }
                            }

                            if (!is_array($this->prefs) || !in_array('chat_osc_accept_msg', $this->prefs)) {
                                $this->userObj->setPref('chat_osc_accept_msg', $ilSetting->get('chat_osc_accept_msg', 'n'));
                            }
                            if (!is_array($this->prefs) || !in_array('chat_broadcast_typing', $this->prefs)) {
                                $this->userObj->setPref('chat_broadcast_typing', $ilSetting->get('chat_broadcast_typing', 'n'));
                            }
                            if (!is_array($this->prefs) || !in_array('bs_allow_to_contact_me', $this->prefs)) {
                                $this->userObj->setPref('bs_allow_to_contact_me', $ilSetting->get('bs_allow_to_contact_me', 'n'));
                            }

                            $this->userObj->writePrefs();

                            // update mail preferences, to be extended
                            $this->updateMailPreferences($this->userObj->getId());

                            if (is_array($this->personalPicture)) {
                                if (strlen($this->personalPicture["content"])) {
                                    $extension = "jpg";
                                    if (preg_match("/.*(png|jpg|gif|jpeg)$/", $this->personalPicture["imagetype"], $matches)) {
                                        $extension = $matches[1];
                                    }
                                    $tmp_name = $this->saveTempImage($this->personalPicture["content"], ".$extension");
                                    if (strlen($tmp_name)) {
                                        ilObjUser::_uploadPersonalPicture($tmp_name, $this->userObj->getId());
                                        unlink($tmp_name);
                                    }
                                }
                            }

                            //set role entries
                            foreach ($this->roles as $role_id => $role) {
                                if (isset($this->role_assign[$role_id]) && $this->role_assign[$role_id]) {
                                    $this->assignToRole($this->userObj, $this->role_assign[$role_id]);
                                }
                            }

                            if (count($this->udf_data)) {
                                $udd = new ilUserDefinedData($this->userObj->getId());
                                foreach ($this->udf_data as $field => $value) {
                                    $udd->set("f_" . $field, $value);
                                }
                                $udd->update();
                            }

                            $this->sendAccountMail();
                            $this->logSuccess($this->userObj->getLogin(), $this->userObj->getId(), "Insert");
                            // reset account mail object
                            $this->acc_mail->reset();
                        }
                        break;

                    case "Update":
                        if (!$user_id) {
                            $this->logFailure($this->userObj->getLogin(), $lng->txt("usrimport_cant_update"));
                        } else {
                            $updateUser = new ilObjUser($user_id);
                            $updateUser->read();
                            $updateUser->readPrefs();
                            if ($this->currPassword != null) {
                                switch (strtoupper($this->currPasswordType)) {
                                    case "BCRYPT":
                                        $updateUser->setPasswd($this->currPassword, ilObjUser::PASSWD_CRYPTED);
                                        $updateUser->setPasswordEncodingType('bcryptphp');
                                        $updateUser->setPasswordSalt(null);
                                        break;

                                    case "PLAIN":
                                        $updateUser->setPasswd($this->currPassword, ilObjUser::PASSWD_PLAIN);
                                        $this->acc_mail->setUserPassword((string) $this->currPassword);
                                        break;

                                    default:
                                        $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "Type", "Password", $this->currPasswordType));
                                        break;
                                }
                            }
                            if ($this->tagContained("Firstname")) {
                                $updateUser->setFirstname($this->userObj->getFirstname());
                            }
                            if ($this->tagContained("Lastname")) {
                                $updateUser->setLastname($this->userObj->getLastname());
                            }
                            if ($this->tagContained("Title")) {
                                $updateUser->setUTitle($this->userObj->getUTitle());
                            }
                            if ($this->tagContained("Gender")) {
                                $updateUser->setGender($this->userObj->getGender());
                            }
                            if ($this->tagContained("Email")) {
                                $updateUser->setEmail($this->userObj->getEmail());
                            }
                            if ($this->tagContained("SecondEmail")) {
                                $updateUser->setSecondEmail($this->userObj->getSecondEmail());
                            }
                            if ($this->tagContained("Birthday")) {
                                $updateUser->setBirthday($this->userObj->getBirthday());
                            }
                            if ($this->tagContained("Institution")) {
                                $updateUser->setInstitution($this->userObj->getInstitution());
                            }
                            if ($this->tagContained("Street")) {
                                $updateUser->setStreet($this->userObj->getStreet());
                            }
                            if ($this->tagContained("City")) {
                                $updateUser->setCity($this->userObj->getCity());
                            }
                            if ($this->tagContained("PostalCode")) {
                                $updateUser->setZipcode($this->userObj->getZipcode());
                            }
                            if ($this->tagContained("Country")) {
                                $updateUser->setCountry($this->userObj->getCountry());
                            }
                            if ($this->tagContained("SelCountry")) {
                                $updateUser->setSelectedCountry($this->userObj->getSelectedCountry());
                            }
                            if ($this->tagContained("PhoneOffice")) {
                                $updateUser->setPhoneOffice($this->userObj->getPhoneOffice());
                            }
                            if ($this->tagContained("PhoneHome")) {
                                $updateUser->setPhoneHome($this->userObj->getPhoneHome());
                            }
                            if ($this->tagContained("PhoneMobile")) {
                                $updateUser->setPhoneMobile($this->userObj->getPhoneMobile());
                            }
                            if ($this->tagContained("Fax")) {
                                $updateUser->setFax($this->userObj->getFax());
                            }
                            if ($this->tagContained("Hobby")) {
                                $updateUser->setHobby($this->userObj->getHobby());
                            }
                            if ($this->tagContained("GeneralInterest")) {
                                $updateUser->setGeneralInterests($this->userObj->getGeneralInterests());
                            }
                            if ($this->tagContained("OfferingHelp")) {
                                $updateUser->setOfferingHelp($this->userObj->getOfferingHelp());
                            }
                            if ($this->tagContained("LookingForHelp")) {
                                $updateUser->setLookingForHelp($this->userObj->getLookingForHelp());
                            }
                            if ($this->tagContained("Comment")) {
                                $updateUser->setComment($this->userObj->getComment());
                            }
                            if ($this->tagContained("Department")) {
                                $updateUser->setDepartment($this->userObj->getDepartment());
                            }
                            if ($this->tagContained("Matriculation")) {
                                $updateUser->setMatriculation($this->userObj->getMatriculation());
                            }
                            if (!is_null($this->currActive)) {
                                $updateUser->setActive($this->currActive === "true", is_object($ilUser) ? $ilUser->getId() : 0);
                            }
                            if ($this->tagContained("ClientIP")) {
                                $updateUser->setClientIP($this->userObj->getClientIP());
                            }
                            if ($this->time_limit_set) {
                                $updateUser->setTimeLimitUnlimited($this->userObj->getTimeLimitUnlimited());
                            }
                            if ($this->tagContained("TimeLimitFrom")) {
                                $updateUser->setTimeLimitFrom($this->userObj->getTimeLimitFrom());
                            }
                            if ($this->tagContained("TimeLimitUntil")) {
                                $updateUser->setTimeLimitUntil($this->userObj->getTimeLimitUntil());
                            }
                            if ($this->tagContained("TimeLimitMessage")) {
                                $updateUser->setTimeLimitMessage($this->userObj->getTimeLimitMessage());
                            }
                            if ($this->tagContained("ApproveDate")) {
                                $updateUser->setApproveDate($this->userObj->getApproveDate());
                            }
                            if ($this->tagContained("AgreeDate")) {
                                $updateUser->setAgreeDate($this->userObj->getAgreeDate());
                            }
                            if ($this->tagContained("Language")) {
                                $updateUser->setLanguage($this->userObj->getLanguage());
                            }
                            if ($this->tagContained("ExternalAccount")) {
                                $updateUser->setExternalAccount($this->userObj->getExternalAccount());
                            }

                            // Fixed: if auth_mode is not set, it was always overwritten with auth_default
                            #if (! is_null($this->userObj->getAuthMode())) $updateUser->setAuthMode($this->userObj->getAuthMode());
                            if ($this->auth_mode_set) {
                                $updateUser->setAuthMode($this->userObj->getAuthMode());
                            }

                            // Special handlin since it defaults to 7 (USER_FOLDER_ID)
                            if ($this->time_limit_owner_set) {
                                $updateUser->setTimeLimitOwner($this->userObj->getTimeLimitOwner());
                            }

                            if (count($this->prefs)) {
                                foreach ($this->prefs as $key => $value) {
                                    if ($key !== "mail_incoming_type" &&
                                        $key !== "mail_signature" &&
                                        $key !== "mail_linebreak"
                                    ) {
                                        $updateUser->setPref($key, $value);
                                    }
                                }
                            }

                            // save user preferences (skin and style)
                            if ($this->updateLookAndSkin) {
                                $updateUser->setPref("skin", $this->userObj->getPref("skin"));
                                $updateUser->setPref("style", $this->userObj->getPref("style"));
                            }


                            $updateUser->writePrefs();

                            // update mail preferences, to be extended
                            $this->updateMailPreferences($updateUser->getId());

                            // #8759
                            if (count($this->udf_data)) {
                                $updateUser->setUserDefinedData($this->udf_data);
                            }

                            $updateUser->setProfileIncomplete($this->checkProfileIncomplete($updateUser));
                            $updateUser->setFullname();
                            $updateUser->setTitle($updateUser->getFullname());
                            $updateUser->setDescription($updateUser->getEmail());
                            $updateUser->update();

                            if (count($this->udf_data)) {
                                $udd = new ilUserDefinedData($updateUser->getId());
                                foreach ($this->udf_data as $field => $value) {
                                    $udd->set("f_" . $field, $value);
                                }
                                $udd->update();
                            }

                            // update login
                            if ($this->tagContained("Login") && $this->user_id != -1) {
                                try {
                                    $updateUser->updateLogin($this->userObj->getLogin());
                                } catch (ilUserException $e) {
                                }
                            }


                            // if language has changed

                            if (is_array($this->personalPicture)) {
                                if (strlen($this->personalPicture["content"])) {
                                    $extension = "jpg";
                                    if (preg_match("/.*(png|jpg|gif|jpeg)$/", $this->personalPicture["imagetype"], $matches)) {
                                        $extension = $matches[1];
                                    }
                                    $tmp_name = $this->saveTempImage($this->personalPicture["content"], ".$extension");
                                    if (strlen($tmp_name)) {
                                        ilObjUser::_uploadPersonalPicture($tmp_name, $updateUser->getId());
                                        unlink($tmp_name);
                                    }
                                }
                            }


                            //update role entries
                            //-------------------
                            foreach ($this->roles as $role_id => $role) {
                                if (array_key_exists($role_id, $this->role_assign)) {
                                    switch ($role["action"]) {
                                        case "Assign":
                                            $this->assignToRole($updateUser, $this->role_assign[$role_id]);
                                            break;
                                        case "AssignWithParents":
                                            $this->assignToRoleWithParents($updateUser, $this->role_assign[$role_id]);
                                            break;
                                        case "Detach":
                                            $this->detachFromRole($updateUser, $this->role_assign[$role_id]);
                                            break;
                                    }
                                }
                            }
                            $this->logSuccess($updateUser->getLogin(), $user_id, "Update");
                        }
                        break;
                    case "Delete":
                        if (!$user_id) {
                            $this->logFailure($this->userObj->getLogin(), $lng->txt("usrimport_cant_delete"));
                        } else {
                            $deleteUser = new ilObjUser($user_id);
                            $deleteUser->delete();

                            $this->logSuccess($this->userObj->getLogin(), $user_id, "Delete");
                        }
                        break;
                }

                // init role array for next user
                $this->roles = array();
                break;

            case "Login":
                $this->userObj->setLogin($this->cdata);
                break;

            case "Password":
                $this->currPassword = $this->cdata;
                break;

            case "Firstname":
                $this->userObj->setFirstname($this->cdata);
                break;

            case "Lastname":
                $this->userObj->setLastname($this->cdata);
                break;

            case "Title":
                $this->userObj->setUTitle($this->cdata);
                break;

            case "Gender":
                $this->userObj->setGender($this->cdata);
                break;

            case "Email":
                $this->userObj->setEmail($this->cdata);
                break;
            case "SecondEmail":
                $this->userObj->setSecondEmail($this->cdata);
                break;
            case "Birthday":
                $timestamp = strtotime($this->cdata);
                if ($timestamp !== false) {
                    $this->userObj->setBirthday($this->cdata);
                }
                break;
            case "Institution":
                $this->userObj->setInstitution($this->cdata);
                break;

            case "Street":
                $this->userObj->setStreet($this->cdata);
                break;

            case "City":
                $this->userObj->setCity($this->cdata);
                break;

            case "PostalCode":
                $this->userObj->setZipcode($this->cdata);
                break;

            case "Country":
                $this->userObj->setCountry($this->cdata);
                break;

            case "SelCountry":
                $this->userObj->setSelectedCountry($this->cdata);
                break;

            case "PhoneOffice":
                $this->userObj->setPhoneOffice($this->cdata);
                break;

            case "PhoneHome":
                $this->userObj->setPhoneHome($this->cdata);
                break;

            case "PhoneMobile":
                $this->userObj->setPhoneMobile($this->cdata);
                break;

            case "Fax":
                $this->userObj->setFax($this->cdata);
                break;

            case "Hobby":
                $this->userObj->setHobby($this->cdata);
                break;

            case "GeneralInterest":
            case "OfferingHelp":
            case "LookingForHelp":
                $this->multi_values[$a_name][] = $this->cdata;
                break;

            case "Comment":
                $this->userObj->setComment($this->cdata);
                break;

            case "Department":
                $this->userObj->setDepartment($this->cdata);
                break;

            case "Matriculation":
                $this->userObj->setMatriculation($this->cdata);
                break;

            case "Active":
                $this->currActive = $this->cdata;
                break;

            case "ClientIP":
                $this->userObj->setClientIP($this->cdata);
                break;

            case "TimeLimitOwner":
                $this->time_limit_owner_set = true;
                $this->userObj->setTimeLimitOwner($this->cdata);
                break;

            case "TimeLimitUnlimited":
                $this->time_limit_set = true;
                $this->userObj->setTimeLimitUnlimited($this->cdata);
                break;

            case "TimeLimitFrom":
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $this->userObj->setTimeLimitFrom($this->cdata);
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== "0000-00-00 00:00:00") {
                        $this->userObj->setTimeLimitFrom($timestamp);
                    } elseif ($this->cdata === "0000-00-00 00:00:00") {
                        $this->userObj->setTimeLimitFrom(null);
                    }
                }
                break;

            case "TimeLimitUntil":
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $this->userObj->setTimeLimitUntil($this->cdata);
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== "0000-00-00 00:00:00") {
                        $this->userObj->setTimeLimitUntil($timestamp);
                    } elseif ($this->cdata === "0000-00-00 00:00:00") {
                        $this->userObj->setTimeLimitUntil(null);
                    }
                }
                break;

            case "TimeLimitMessage":
                $this->userObj->setTimeLimitMessage($this->cdata);
                break;

            case "ApproveDate":
                $this->approve_date_set = true;
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $tmp_date = new ilDateTime($this->cdata, IL_CAL_UNIX);
                    $this->userObj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== "0000-00-00 00:00:00") {
                        $tmp_date = new ilDateTime($timestamp, IL_CAL_UNIX);
                        $this->userObj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
                    } elseif ($this->cdata === "0000-00-00 00:00:00") {
                        $this->userObj->setApproveDate(null);
                    }
                }
                break;

            case "AgreeDate":
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $tmp_date = new ilDateTime($this->cdata, IL_CAL_UNIX);
                    $this->userObj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== "0000-00-00 00:00:00") {
                        $tmp_date = new ilDateTime($timestamp, IL_CAL_UNIX);
                        $this->userObj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
                    } elseif ($this->cdata === "0000-00-00 00:00:00") {
                        $this->userObj->setAgreeDate(null);
                    }
                }
                break;

            case "ExternalAccount":
                $this->userObj->setExternalAccount($this->cdata);
                break;

            case "Look":
                $this->updateLookAndSkin = false;
                if (!$this->hideSkin) {
                    // TODO: what to do with disabled skins? is it possible to change the skin via import?
                    if ((strlen($this->skin) > 0) && (strlen($this->style) > 0)) {
                        if (is_array($this->userStyles)) {
                            if (in_array($this->skin . ":" . $this->style, $this->userStyles)) {
                                $this->userObj->setPref("skin", $this->skin);
                                $this->userObj->setPref("style", $this->style);
                                $this->updateLookAndSkin = true;
                            }
                        }
                    }
                }
                break;

            case 'UserDefinedField':
                $udf = ilUserDefinedFields::_getInstance();
                if ($field_id = $udf->fetchFieldIdFromImportId($this->tmp_udf_id)) {
                    $this->udf_data[$field_id] = $this->cdata;
                } elseif ($field_id = $udf->fetchFieldIdFromName($this->tmp_udf_name)) {
                    $this->udf_data[$field_id] = $this->cdata;
                }
                break;
            case 'AccountInfo':
                if ($this->current_messenger_type === "external") {
                    $this->userObj->setExternalAccount($this->cdata);
                }
                break;
            case 'Pref':
                if ($this->currentPrefKey != null && strlen(trim($this->cdata)) > 0
                    && ilUserXMLWriter::isPrefExportable($this->currentPrefKey)) {
                    $this->prefs[$this->currentPrefKey] = trim($this->cdata);
                }
                $this->currentPrefKey = null;
                break;
        }
    }

    /**
     * Saves binary image data to a temporary image file and returns
     * the name of the image file on success.
     */
    public function saveTempImage(
        string $image_data,
        string $filename
    ): string {
        $tempname = ilFileUtils::ilTempnam() . $filename;
        $fh = fopen($tempname, "wb");
        if ($fh == false) {
            return "";
        }
        fwrite($fh, $image_data);
        fclose($fh);
        return $tempname;
    }

    /**
     * handler for end of element when in verify mode.
     */
    public function verifyEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilSetting = $DIC['ilSetting'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $externalAccountHasChanged = false;

        switch ($a_name) {
            case "Role":
                $this->roles[$this->current_role_id]["name"] = $this->cdata;
                $this->roles[$this->current_role_id]["type"] = $this->current_role_type;
                $this->roles[$this->current_role_id]["action"] = $this->current_role_action;
                break;

            case "User":
                $this->userObj->setFullname();
                if ($this->user_id != -1 && ($this->action === "Update" || $this->action === "Delete")) {
                    $user_id = $this->user_id;
                    $user_exists = !is_null(ilObjUser::_lookupLogin($user_id));
                } else {
                    $user_id = ilObjUser::getUserIdByLogin($this->userObj->getLogin());
                    $user_exists = $user_id != 0;
                }
                if (is_null($this->userObj->getLogin())) {
                    $this->logFailure("---", sprintf($lng->txt("usrimport_xml_element_for_action_required"), "Login", "Insert"));
                }

                if ($user_id === (int) ANONYMOUS_USER_ID || $user_id === (int) SYSTEM_USER_ID) {
                    $this->logWarning($this->userObj->getLogin(), $lng->txt('usrimport_xml_anonymous_or_root_not_allowed'));
                    break;
                }

                switch ($this->action) {
                    case "Insert":
                        if ($user_exists and $this->conflict_rule == IL_FAIL_ON_CONFLICT) {
                            $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_cant_insert"));
                        }
                        if (is_null($this->userObj->getGender()) && $this->isFieldRequired("gender")) {
                            $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_for_action_required"), "Gender", "Insert"));
                        }
                        if (is_null($this->userObj->getFirstname())) {
                            $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_for_action_required"), "Firstname", "Insert"));
                        }
                        if (is_null($this->userObj->getLastname())) {
                            $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_for_action_required"), "Lastname", "Insert"));
                        }
                        if (count($this->roles) == 0) {
                            $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_for_action_required"), "Role", "Insert"));
                        } else {
                            $has_global_role = false;
                            foreach ($this->roles as $role) {
                                if ($role['type'] === 'Global') {
                                    $has_global_role = true;
                                    break;
                                }
                            }
                            if (!$has_global_role) {
                                $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_global_role_for_action_required"), "Insert"));
                            }
                        }
                        break;
                    case "Update":
                        if (!$user_exists) {
                            $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_cant_update"));
                        } elseif ($this->user_id != -1 && $this->tagContained("Login")) {
                            // check if someone owns the new login name!
                            $someonesId = ilObjUser::_lookupId($this->userObj->getLogin());

                            if (is_numeric($someonesId) && $someonesId != $this->user_id) {
                                $this->logFailure($this->userObj->getLogin(), $lng->txt("usrimport_login_is_not_unique"));
                            }
                        }
                        break;
                    case "Delete":
                        if (!$user_exists) {
                            $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_cant_delete"));
                        }
                        break;
                }

                // init role array for next user
                $this->roles = array();
                break;

            case "Login":
                if (array_key_exists($this->cdata, $this->logins)) {
                    $this->logWarning($this->cdata, $lng->txt("usrimport_login_is_not_unique"));
                } else {
                    $this->logins[$this->cdata] = $this->cdata;
                }
                $this->userObj->setLogin($this->cdata);
                break;

            case "Password":
                switch ($this->currPasswordType) {
                    case "BCRYPT":
                        $this->userObj->setPasswd($this->cdata, ilObjUser::PASSWD_CRYPTED);
                        $this->userObj->setPasswordEncodingType('bcryptphp');
                        $this->userObj->setPasswordSalt(null);
                        break;

                    case "PLAIN":
                        $this->userObj->setPasswd($this->cdata, ilObjUser::PASSWD_PLAIN);
                        $this->acc_mail->setUserPassword((string) $this->currPassword);
                        break;

                    default:
                        $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"), "Type", "Password", $this->currPasswordType));
                        break;
                }
                break;

            case "Firstname":
                $this->userObj->setFirstname($this->cdata);
                break;

            case "Lastname":
                $this->userObj->setLastname($this->cdata);
                break;

            case "Title":
                $this->userObj->setUTitle($this->cdata);
                break;

            case "Gender":
                if (!in_array(strtolower($this->cdata), ['n', 'm', 'f'])) {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_content_illegal"), "Gender", $this->cdata)
                    );
                }
                $this->userObj->setGender($this->cdata);
                break;

            case "Email":
                $this->userObj->setEmail($this->cdata);
                break;
            case "SecondEmail":
                $this->userObj->setSecondEmail($this->cdata);
                break;
            case "Institution":
                $this->userObj->setInstitution($this->cdata);
                break;

            case "Street":
                $this->userObj->setStreet($this->cdata);
                break;

            case "City":
                $this->userObj->setCity($this->cdata);
                break;

            case "PostalCode":
                $this->userObj->setZipcode($this->cdata);
                break;

            case "Country":
                $this->userObj->setCountry($this->cdata);
                break;

            case "SelCountry":
                $this->userObj->setSelectedCountry($this->cdata);
                break;

            case "PhoneOffice":
                $this->userObj->setPhoneOffice($this->cdata);
                break;

            case "PhoneHome":
                $this->userObj->setPhoneHome($this->cdata);
                break;

            case "PhoneMobile":
                $this->userObj->setPhoneMobile($this->cdata);
                break;

            case "Fax":
                $this->userObj->setFax($this->cdata);
                break;

            case "Hobby":
                $this->userObj->setHobby($this->cdata);
                break;

            case "GeneralInterest":
            case "OfferingHelp":
            case "LookingForHelp":
                $this->multi_values[$a_name][] = $this->cdata;
                break;

            case "Comment":
                $this->userObj->setComment($this->cdata);
                break;

            case "Department":
                $this->userObj->setDepartment($this->cdata);
                break;

            case "Matriculation":
                $this->userObj->setMatriculation($this->cdata);
                break;

            case "ExternalAccount":
                //echo "-".$this->userObj->getAuthMode()."-".$this->userObj->getLogin()."-";
                $am = ($this->userObj->getAuthMode() === "default" || $this->userObj->getAuthMode() == "")
                    ? ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode'))
                    : $this->userObj->getAuthMode();
                $loginForExternalAccount = (trim($this->cdata) == "")
                    ? ""
                    : ilObjUser::_checkExternalAuthAccount($am, trim($this->cdata));
                switch ($this->action) {
                    case "Insert":
                        if ($loginForExternalAccount != "") {
                            $this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_insert_ext_account_exists") . " (" . $this->cdata . ")");
                        }
                        break;

                    case "Update":
                        if ($loginForExternalAccount != "") {
                            $externalAccountHasChanged = trim($this->cdata) != ilObjUser::_lookupExternalAccount($this->user_id);
                            if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->userObj->getLogin())) {
                                $this->logWarning(
                                    $this->userObj->getLogin(),
                                    $lng->txt("usrimport_no_update_ext_account_exists") . " (" . $this->cdata . " for " . $loginForExternalAccount . ")"
                                );
                            }
                        }
                        break;

                }
                if ($externalAccountHasChanged) {
                    $this->userObj->setExternalAccount(trim($this->cdata));
                }
                break;

            case "Active":
                if ($this->cdata !== "true"
                && $this->cdata !== "false") {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_content_illegal"), "Active", $this->cdata)
                    );
                }
                $this->currActive = $this->cdata;
                break;
            case "TimeLimitOwner":
                if (!preg_match("/\d+/", $this->cdata)) {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitOwner", $this->cdata)
                    );
                } elseif (!$ilAccess->checkAccess('cat_administrate_users', '', $this->cdata)) {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitOwner", $this->cdata)
                    );
                } elseif ($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId((int) $this->cdata)) !== 'cat' && !(int) $this->cdata == USER_FOLDER_ID) {
                    $this->logFailure(
                        $this->userObj->getLogin(),
                        sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitOwner", $this->cdata)
                    );
                }
                $this->userObj->setTimeLimitOwner($this->cdata);
                break;
            case "TimeLimitUnlimited":
                switch (strtolower($this->cdata)) {
                    case "true":
                    case "1":
                        $this->userObj->setTimeLimitUnlimited(1);
                        break;
                    case "false":
                    case "0":
                        $this->userObj->setTimeLimitUnlimited(0);
                        break;
                    default:
                        $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitUnlimited", $this->cdata));
                        break;
                }
                break;
            case "TimeLimitFrom":
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata)) {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitFrom", $this->cdata));
                }
                $this->userObj->setTimeLimitFrom((int) $this->cdata);
                break;
            case "TimeLimitUntil":
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata)) {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitUntil", $this->cdata));
                }
                $this->userObj->setTimeLimitUntil((int) $this->cdata);
                break;
            case "TimeLimitMessage":
                switch (strtolower($this->cdata)) {
                    case "1":
                        $this->userObj->setTimeLimitMessage(1);
                        break;
                    case "0":
                        $this->userObj->setTimeLimitMessage(0);
                        break;
                    default:
                        $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "TimeLimitMessage", $this->cdata));
                        break;
                }
                break;
            case "ApproveDate":
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata) && !$this->cdata === "0000-00-00 00:00:00") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "ApproveDate", $this->cdata));
                }
                break;
            case "AgreeDate":
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata) && !$this->cdata === "0000-00-00 00:00:00") {
                    $this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"), "AgreeDate", $this->cdata));
                }
                break;
            case "Pref":
                if ($this->currentPrefKey != null) {
                    $this->verifyPref($this->currentPrefKey, $this->cdata);
                }
        }
    }

    /**
     * handler for character data
     * @param \XMLParser|resource $a_xml_parser
     */
    public function handlerCharacterData(
        $a_xml_parser,
        string $a_data
    ): void {
        if ($a_data !== "\n") {
            $a_data = preg_replace("/\t+/", " ", $a_data);
        }

        if (strlen($a_data) > 0) {
            $this->cdata .= $a_data;
        }
    }

    /**
     * @return array[]
     */
    public function getCollectedRoles(): array
    {
        return $this->roles;
    }

    public function getUserCount(): int
    {
        return $this->userCount;
    }

    /**
     * Writes a warning log message to the protocol.
     */
    public function logWarning(
        string $aLogin,
        string $aMessage
    ): void {
        if (!array_key_exists($aLogin, $this->protocol)) {
            $this->protocol[$aLogin] = array();
        }
        if ($aMessage) {
            $this->protocol[$aLogin][] = $aMessage;
        }
        if ($this->error_level == IL_IMPORT_SUCCESS) {
            $this->error_level = IL_IMPORT_WARNING;
        }
    }

    /**
     * Writes a failure log message to the protocol.
     */
    public function logFailure(
        string $aLogin,
        string $aMessage
    ): void {
        if (!array_key_exists($aLogin, $this->protocol)) {
            $this->protocol[$aLogin] = array();
        }
        if ($aMessage) {
            $this->protocol[$aLogin][] = $aMessage;
        }
        $this->error_level = IL_IMPORT_FAILURE;
    }

    /**
     * Writes a success log message to the protocol.
     */
    public function logSuccess(
        string $aLogin,
        string $userid,
        string $action
    ): void {
        $this->user_mapping[$userid] = array("login" => $aLogin, "action" => $action, "message" => "successful");
    }


    /**
     * The protocol is an associative array.
     * Keys are login names.
     * Values are non-associative arrays. Each array element contains an error
     * message.
     * @return array[]
     */
    public function getProtocol(): array
    {
        return $this->protocol;
    }

    /**
     * Returns the protocol as a HTML table.
     */
    public function getProtocolAsHTML(string $a_log_title): string
    {
        global $DIC;

        $lng = $DIC['lng'];

        $block = new ilTemplate("tpl.usr_import_log_block.html", true, true, "Services/User");
        $block->setVariable("TXT_LOG_TITLE", $a_log_title);
        $block->setVariable("TXT_MESSAGE_ID", $lng->txt("login"));
        $block->setVariable("TXT_MESSAGE_TEXT", $lng->txt("message"));
        foreach ($this->getProtocol() as $login => $messages) {
            $block->setCurrentBlock("log_row");
            $reason = "";
            foreach ($messages as $message) {
                if ($reason == "") {
                    $reason = $message;
                } else {
                    $reason .= "<br>" . $message;
                }
            }
            $block->setVariable("MESSAGE_ID", $login);
            $block->setVariable("MESSAGE_TEXT", $reason);
            $block->parseCurrentBlock();
        }
        return $block->get();
    }

    /**
     * Returns true, if the import was successful.
     */
    public function isSuccess(): bool
    {
        return $this->error_level == IL_IMPORT_SUCCESS;
    }

    /**
     * Returns the error level.
     * @return int IL_IMPORT_SUCCESS | IL_IMPORT_WARNING | IL_IMPORT_FAILURE
     */
    public function getErrorLevel(): int
    {
        return $this->error_level;
    }

    /**
     * returns a map user_id <=> login
     * @return array with user_id as key and login as value
     */
    public function getUserMapping(): array
    {
        return $this->user_mapping;
    }

    /**
     * send account mail
     */
    public function sendAccountMail(): void
    {
        if ($this->req_send_mail != "" ||
            ($this->isSendMail() && $this->userObj->getEmail() != "")) {
            $this->acc_mail->setUser($this->userObj);
            $this->acc_mail->send();
        }
    }

    public function setSendMail(bool $value): void
    {
        $this->send_mail = $value;
    }

    public function isSendMail(): bool
    {
        return $this->send_mail;
    }

    /**
     * write access to user mapping mode
     *
     * @param int $value must be one of IL_USER_MAPPING_ID or IL_USER_MAPPING_LOGIN, die otherwise
     */
    public function setUserMappingMode(int $value): void
    {
        if ($value == IL_USER_MAPPING_ID || $value == IL_USER_MAPPING_LOGIN) {
            $this->mapping_mode = $value;
        } else {
            die("wrong argument using methode setUserMappingMethod in " . __FILE__);
        }
    }

    /**
     * read access to user mapping mode
     * @return int one of IL_USER_MAPPING_ID or IL_USER_MAPPING_LOGIN
     */
    public function getUserMappingMode(): int
    {
        return $this->mapping_mode;
    }

    /**
     * @return array[]
     */
    private function readRequiredFields(): array
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (is_array($this->required_fields)) {
            return $this->required_fields;
        }
        foreach ($ilSetting->getAll() as $field => $value) {
            if (strpos($field, 'require_') === 0 && $value == 1) {
                $value = substr($field, 8);
                $this->required_fields[$value] = $value;
            }
        }
        return $this->required_fields ?: array();
    }

    /**
     * Check if profile is incomplete
     * Will set the usr_data field profile_incomplete if any required field is missing
     */
    private function checkProfileIncomplete(ilObjUser $user_obj): bool
    {
        return ilUserProfile::isProfileIncomplete($user_obj);
    }

    /**
     * determine if a field $fieldname is to a required field (global setting)
     *
     * @param	$fieldname	string value of fieldname, e.g. gender
     * @return true, if field of required fields contains fieldname as key, false otherwise.
     */
    protected function isFieldRequired(string $fieldname): bool
    {
        $requiredFields = $this->readRequiredFields();
        $fieldname = strtolower(trim($fieldname));
        return array_key_exists($fieldname, $requiredFields);
    }

    private function verifyPref(string $key, string $value): void
    {
        switch ($key) {
            case 'mail_linebreak':
            case 'hits_per_page':
                if (!is_numeric($value) || $value < 0) {
                    $this->logFailure("---", "Wrong value '$value': Positiv numeric value expected for preference $key.");
                }
                break;
            case 'language':
            case 'skin':
            case 'style':
            case 'ilPageEditor_HTMLMode':
            case 'ilPageEditor_JavaScript':
            case 'ilPageEditor_MediaMode':
            case 'tst_javascript':
            case 'tst_lastquestiontype':
            case 'tst_multiline_answers':
            case 'tst_use_previous_answers':
            case 'graphicalAnswerSetting':
            case 'priv_feed_pass':
                $this->logFailure("---", "Preference $key is not supported.");
                break;
            case 'public_city':
            case 'public_country':
            case 'public_department':
            case 'public_email':
            case 'public_second_email':
            case 'public_fax':
            case 'public_hobby':
            case 'public_institution':
            case 'public_matriculation':
            case 'public_phone':
            case 'public_phone_home':
            case 'public_phone_mobile':
            case 'public_phone_office':
            case 'public_street':
            case 'public_upload':
            case 'public_zip':
            case 'public_interests_general':
            case 'public_interests_help_offered':
            case 'public_interests_help_looking':
            case 'send_info_mails':
            case 'bs_allow_to_contact_me':
            case 'chat_osc_accept_msg':
            case 'chat_broadcast_typing':
            case 'hide_own_online_status':
                if (!in_array($value, array('y', 'n'))) {
                    $this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' expected for preference $key.");
                }
                break;
            case 'public_profile':
                if (!in_array($value, array('y', 'n', 'g'))) {
                    $this->logFailure("---", "Wrong value '$value': Value 'y', 'g' or 'n' expected for preference $key.");
                }
                break;
            case 'show_users_online':
                if (!in_array($value, array('y', 'n', 'associated'))) {
                    $this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' or 'associated' expected for preference $key.");
                }
                break;
            case 'mail_incoming_type':
                if (!in_array((int) $value, array("0","1","2"))) {
                    $this->logFailure("---", "Wrong value '$value': Value \"0\" (LOCAL),\"1\" (EMAIL) or \"2\" (BOTH) expected for preference $key.");
                }
                break;
            case 'weekstart':
                if (!in_array($value, array("0","1"))) {
                    $this->logFailure("---", "Wrong value '$value': Value \"0\" (Sunday) or \"1\" (Monday) expected for preference $key.");
                }
                break;

            case 'mail_signature':
                break;
            case 'user_tz':
                try {
                    ilTimeZone::_getInstance($value);
                    return;
                } catch (ilTimeZoneException $tze) {
                    $this->logFailure("---", "Wrong value '$value': Invalid timezone $value detected for preference $key.");
                }
                break;
            default:
                if (!ilUserXMLWriter::isPrefExportable($key)) {
                    $this->logFailure("---", "Preference $key is not supported.");
                }
                break;
        }
    }

    private function updateMailPreferences(int $usr_id): void
    {
        if (array_key_exists("mail_incoming_type", $this->prefs) ||
            array_key_exists("mail_signature", $this->prefs) ||
            array_key_exists("mail_linebreak", $this->prefs)
        ) {
            $mailOptions = new ilMailOptions($usr_id);

            $mailOptions->setLinebreak(array_key_exists("mail_linebreak", $this->prefs) ? $this->prefs["mail_linebreak"] : $mailOptions->getLinebreak());
            $mailOptions->setSignature(array_key_exists("mail_signature", $this->prefs) ? $this->prefs["mail_signature"] : $mailOptions->getSignature());
            $mailOptions->setIncomingType(array_key_exists("mail_incoming_type", $this->prefs) ? $this->prefs["mail_incoming_type"] : $mailOptions->getIncomingType());
            $mailOptions->updateOptions();
        }
    }
}
