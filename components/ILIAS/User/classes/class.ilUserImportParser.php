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

declare(strict_types=1);

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\UserGUIRequest;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Data as ProfileData;
use ILIAS\Refinery\Factory as Refinery;

class ilUserImportParser extends ilSaxParser
{
    public const IL_EXTRACT_ROLES = 1;
    public const IL_USER_IMPORT = 2;
    public const IL_VERIFY = 3;

    public const IL_FAIL_ON_CONFLICT = 1;
    public const IL_UPDATE_ON_CONFLICT = 2;
    public const IL_IGNORE_ON_CONFLICT = 3;

    public const IL_IMPORT_SUCCESS = 1;
    public const IL_IMPORT_WARNING = 2;
    public const IL_IMPORT_FAILURE = 3;

    private const IL_USER_MAPPING_LOGIN = 1;
    public const IL_USER_MAPPING_ID = 2;

    private ILIAS $ilias;
    private ilSetting $settings;
    private ilObjectDataCache $object_data_cache;
    private ilRbacReview $rbac_review;
    private ilRbacAdmin $rbac_admin;
    private ilAccess $access;
    private ilObjUser $user;

    private Profile $user_profile;

    private ?string $tmp_udf_name = null;
    private ?string $tmp_udf_id = null;
    private array $multi_values; // Missing array type.
    private array $udf_data; // Missing array type.
    private bool $auth_mode_set;
    private ?string $current_pref_key = null;
    private array $prefs; // Missing array type.
    private string $current_role_action;
    private string $current_role_type;
    private string $current_role_id = '0';
    private string $cdata;
    private array $role_assign; // Missing array type.
    private string $req_send_mail;
    private ilAccountMail $acc_mail;
    private int $mode;
    private bool $approve_date_set = false;
    private bool $time_limit_set = false;
    private bool $time_limit_owner_set = false;

    private bool $update_look_and_skin = false;
    private int $folder_id;
    private array $roles; // Missing array type.
    private string $action;      // "Insert","Update","Delete"
    private array $required_fields = []; // Missing array type.
    private array $contained_tags = [];
    private array $protocol;
    private array $logins;
    private int $conflict_rule;
    private bool $send_mail;

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
    private int $error_level;
    private ?string $current_user_password_type;
    private ?string $current_user_password;
    private ?string $currActive = null;
    private int $user_count;
    /**
     *
     * @var array<int, array>
     */
    private array $user_mapping = [];
    private int $mapping_mode;
    private array $local_role_cache;
    /**
     * @var array<string>
     */
    private ?array $personal_picture = null;
    private array $parent_roles_cache;
    private string $skin = '';
    private string $style = '';
    /**
     * @var array<string>
     */
    private array $user_styles;

    private int $user_id;
    private ilObjUser $user_obj;
    private string $current_messenger_type;
    private ilRecommendedContentManager $recommended_content_manager;
    private Refinery $refinery;

    /**
     * @param int    $a_mode IL_EXTRACT_ROLES | IL_USER_IMPORT | IL_VERIFY
     * @param int    $a_conflict_rule IL_FAIL_ON_CONFLICT | IL_UPDATE_ON_CONFLICT | IL_IGNORE_ON_CONFLICT
     * @throws ilSystemStyleException
     */
    public function __construct(
        string $a_xml_file = '',
        int $a_mode = self::IL_USER_IMPORT,
        int $a_conflict_rule = self::IL_FAIL_ON_CONFLICT
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->ilias = $DIC['ilias'];
        $this->settings = $DIC['ilSetting'];
        $this->object_data_cache = $DIC['ilObjDataCache'];
        $this->rbac_review = $DIC['rbacreview'];
        $this->rbac_admin = $DIC['rbacadmin'];
        $this->access = $DIC['ilAccess'];
        $this->user = $DIC['ilUser'];
        $this->refinery = $DIC['refinery'];
        $this->req_send_mail = (new UserGUIRequest(
            $DIC['http'],
            $this->refinery
        ))->getSendMail();

        $this->user_profile = LocalDIC::dic()[Profile::class];

        $this->roles = [];
        $this->mode = $a_mode;
        $this->conflict_rule = $a_conflict_rule;
        $this->error_level = self::IL_IMPORT_SUCCESS;
        $this->protocol = [];
        $this->logins = [];
        $this->user_count = 0;
        $this->local_role_cache = [];
        $this->parent_roles_cache = [];
        $this->send_mail = false;
        $this->mapping_mode = self::IL_USER_MAPPING_LOGIN;

        // get all active style  instead of only assigned ones -> cannot transfer all to another otherwise
        $this->user_styles = [];
        $skins = ilStyleDefinition::getAllSkins();

        if (is_array($skins)) {
            foreach ($skins as $skin) {
                foreach ($skin->getStyles() as $style) {
                    if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId())) {
                        continue;
                    }
                    $this->user_styles [] = $skin->getId() . ':' . $style->getId();
                }
            }
        }

        $this->acc_mail = new ilAccountMail();
        $this->acc_mail->useLangVariablesAsFallback(true);

        $this->recommended_content_manager = new ilRecommendedContentManager();

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
        xml_set_element_handler($a_xml_parser, $this->handlerBeginTag(...), $this->handlerEndTag(...));
        xml_set_character_data_handler($a_xml_parser, $this->handlerCharacterData(...));
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
    public function buildTag(string $type, string $name, ?array $attr = null): string // Missing array type.
    {
        $tag = '<';

        if ($type === 'end') {
            $tag .= '/';
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " {$k}='{$v}'";
            }
        }

        $tag .= '>';

        return $tag;
    }

    public function handlerBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ): void {
        switch ($this->mode) {
            case self::IL_EXTRACT_ROLES:
                $this->extractRolesBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case self::IL_USER_IMPORT:
                $this->importBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
            case self::IL_VERIFY:
                $this->verifyBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;
        }

        $this->cdata = '';
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
            case 'Role':
                // detect numeric, ilias id (then extract role id) or alphanumeric
                $current_role_id = $a_attribs['Id'];
                if (($internal_id = ilUtil::__extractId($current_role_id, (int) IL_INST_ID)) > 0) {
                    $current_role_id = $internal_id;
                }
                $this->current_role_id = $this->refinery->kindlyTo()->string()->transform($current_role_id);
                $this->current_role_type = $a_attribs['Type'];
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
        switch ($a_name) {
            case 'Role':
                $current_role_id = $a_attribs['Id'];
                if (($internal_id = ilUtil::__extractId($current_role_id, (int) IL_INST_ID)) > 0) {
                    $current_role_id = $internal_id;
                }
                $this->current_role_id = (string) $current_role_id;
                $this->current_role_type = $a_attribs['Type'];
                $this->current_role_action = (!isset($a_attribs['Action'])) ? 'Assign' : $a_attribs['Action'];
                break;

            case 'PersonalPicture':
                $this->personal_picture = [
                    'encoding' => $a_attribs['encoding'],
                    'imagetype' => $a_attribs['imagetype'],
                    'content' => ''
                ];
                break;

            case 'Look':
                $this->skin = $a_attribs['Skin'];
                $this->style = $a_attribs['Style'];
                break;

            case 'User':
                $this->contained_tags = [];

                $this->acc_mail->reset();
                $this->prefs = [];
                $this->current_pref_key = null;
                $this->auth_mode_set = false;
                $this->approve_date_set = false;
                $this->time_limit_set = false;
                $this->time_limit_owner_set = false;
                $this->update_look_and_skin = false;
                $this->skin = '';
                $this->style = '';
                $this->personal_picture = null;
                $this->user_count++;
                $this->user_obj = new ilObjUser();

                // user defined fields
                $this->udf_data = [];

                // if we have an object id, store it
                $this->user_id = -1;
                if (isset($a_attribs['Id']) && $this->getUserMappingMode() === self::IL_USER_MAPPING_ID) {
                    if (is_numeric($a_attribs['Id'])) {
                        $this->user_id = (int) $a_attribs['Id'];
                    } elseif (($id = (int) ilUtil::__extractId($a_attribs['Id'], (int) IL_INST_ID)) > 0) {
                        $this->user_id = $id;
                    }
                }

                $this->user_obj->setPref(
                    'skin',
                    $this->ilias->ini->readVariable('layout', 'skin')
                );
                $this->user_obj->setPref(
                    'style',
                    $this->ilias->ini->readVariable('layout', 'style')
                );

                if (isset($a_attribs['Language'])) {
                    $this->contained_tags[] = 'Language';
                }
                $this->user_obj->setLanguage($a_attribs['Language'] ?? '');
                $this->user_obj->setImportId($a_attribs['Id'] ?? '');
                $this->action = isset($a_attribs['Action'])
                    ? $a_attribs['Action']
                    : 'Insert';
                $this->current_user_password = null;
                $this->current_user_password_type = null;
                $this->currActive = null;
                $this->multi_values = [];
                break;

            case 'Password':
                $this->current_user_password_type = $a_attribs['Type'];
                break;
            case 'AuthMode':
                if (array_key_exists('type', $a_attribs)) {
                    switch ($a_attribs['type']) {
                        case 'saml':
                        case 'ldap':
                            if (strcmp('saml', $a_attribs['type']) === 0) {
                                $list = ilSamlIdp::getActiveIdpList();
                                if (count($list) === 1) {
                                    $this->auth_mode_set = true;
                                    $idp = current($list);
                                    $this->user_obj->setAuthMode('saml_' . $idp->getIdpId());
                                }
                                break;
                            }
                            if (strcmp('ldap', $a_attribs['type']) === 0) {
                                // no server id provided => use default server
                                $list = ilLDAPServer::_getActiveServerList();
                                if (count($list) == 1) {
                                    $this->auth_mode_set = true;
                                    $ldap_id = current($list);
                                    $this->user_obj->setAuthMode('ldap_' . $ldap_id);
                                }
                            }
                            break;

                        case 'default':
                        case 'local':
                        case 'shibboleth':
                        case 'script':
                        case 'soap':
                        case 'openid':
                            // begin-patch auth_plugin
                        default:
                            $this->auth_mode_set = true;
                            $this->user_obj->setAuthMode($a_attribs['type']);
                            break;
                    }
                } else {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_inapplicable'), 'AuthMode', $this->stripTags($a_attribs['type']))
                    );
                }
                break;

            case 'UserDefinedField':
                $this->tmp_udf_id = $a_attribs['Id'];
                $this->tmp_udf_name = $a_attribs['Name'];
                break;

            case 'AccountInfo':
                $this->current_messenger_type = strtolower($a_attribs['Type']);
                break;
            case 'GMapInfo':
                $this->user_obj->setLatitude($a_attribs['latitude']);
                $this->user_obj->setLongitude($a_attribs['longitude']);
                $this->user_obj->setLocationZoom($a_attribs['zoom']);
                break;
            case 'Pref':
                $this->current_pref_key = $a_attribs['key'];
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
        switch ($a_name) {
            case 'Role':
                if ($a_attribs['Id'] == '') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_missing'), 'Role', 'Id'));
                }
                $this->current_role_id = $a_attribs['Id'];
                $this->current_role_type = $a_attribs['Type'];
                if ($this->current_role_type !== 'Global'
                && $this->current_role_type !== 'Local') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_missing'), 'Role', 'Type'));
                }
                $this->current_role_action = (!isset($a_attribs['Action'])) ? 'Assign' : $a_attribs['Action'];
                if ($this->current_role_action !== 'Assign'
                && $this->current_role_action !== 'AssignWithParents'
                && $this->current_role_action !== 'Detach') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'Role', 'Action', $this->stripTags($a_attribs['Action'])));
                }
                if ($this->action === 'Insert'
                && $this->current_role_action === 'Detach') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_value_inapplicable'), 'Role', 'Action', $this->stripTags($this->current_role_action), $this->stripTags($this->action)));
                }
                if ($this->action === 'Delete') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_inapplicable'), 'Role', 'Delete'));
                }
                break;

            case 'User':
                $this->user_count++;
                $this->contained_tags = [];
                $this->user_obj = new ilObjUser();
                $this->user_obj->setLanguage($a_attribs['Language'] ?? '');
                $this->user_obj->setImportId($a_attribs['Id'] ?? '');
                $this->current_pref_key = null;
                // if we have an object id, store it
                $this->user_id = -1;

                if (isset($a_attribs['Id']) && $this->getUserMappingMode() === self::IL_USER_MAPPING_ID) {
                    if (is_numeric($a_attribs['Id'])) {
                        $this->user_id = (int) $a_attribs['Id'];
                    } elseif (($id = (int) ilUtil::__extractId($a_attribs['Id'], (int) IL_INST_ID)) > 0) {
                        $this->user_id = $id;
                    }
                }

                $this->action = !isset($a_attribs['Action']) ? 'Insert' : $a_attribs['Action'];
                if ($this->action !== 'Insert'
                && $this->action !== 'Update'
                && $this->action !== 'Delete') {
                    $this->logFailure($this->user_obj->getImportId(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'User', 'Action', $this->stripTags($a_attribs['Action'])));
                }
                $this->current_user_password = null;
                $this->current_user_password_type = null;
                break;

            case 'Password':
                $this->current_user_password_type = $a_attribs['Type'];
                break;
            case 'AuthMode':
                if (array_key_exists('type', $a_attribs)) {
                    switch ($a_attribs['type']) {
                        case 'saml':
                        case 'ldap':
                            if (strcmp('saml', $a_attribs['type']) === 0) {
                                $list = ilSamlIdp::getActiveIdpList();
                                if (count($list) !== 1) {
                                    $this->logFailure(
                                        $this->user_obj->getImportId(),
                                        sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'AuthMode', 'type', $this->stripTags($a_attribs['type']))
                                    );
                                }
                                break;
                            }
                            if (strcmp('ldap', $a_attribs['type']) === 0) {
                                // no server id provided
                                $list = ilLDAPServer::_getActiveServerList();
                                if (count($list) != 1) {
                                    $this->logFailure(
                                        $this->user_obj->getImportId(),
                                        sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'AuthMode', 'type', $this->stripTags($a_attribs['type']))
                                    );
                                }
                            }
                            break;

                        case 'default':
                        case 'local':
                        case 'shibboleth':
                        case 'script':
                        case 'soap':
                        case 'openid':
                            // begin-patch auth_plugin
                        default:
                            $this->user_obj->setAuthMode($a_attribs['type']);
                            break;
                    }
                } else {
                    $this->logFailure($this->user_obj->getImportId(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'AuthMode', 'type', ''));
                }
                break;
            case 'Pref':
                $this->current_pref_key = $a_attribs['key'];
                break;
        }
    }

    public function handlerEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        switch ($this->mode) {
            case self::IL_EXTRACT_ROLES:
                $this->extractRolesEndTag($a_xml_parser, $a_name);
                break;
            case self::IL_USER_IMPORT:
                $this->importEndTag($a_xml_parser, $a_name);
                break;
            case self::IL_VERIFY:
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
            case 'Role':
                $this->roles[$this->current_role_id]['name'] = $this->cdata;
                $this->roles[$this->current_role_id]['type'] =
                    $this->current_role_type;
                break;
        }
    }

    /**
     * Returns the parent object of the role folder object which contains the specified role.
     */
    public function getRoleObject(int $a_role_id): ilObjRole
    {
        if (array_key_exists($a_role_id, $this->local_role_cache)) {
            return $this->local_role_cache[$a_role_id];
        } else {
            $role_obj = new ilObjRole($a_role_id, false);
            $role_obj->read();
            $this->local_role_cache[$a_role_id] = $role_obj;
            return $role_obj;
        }
    }

    /**
     * Returns the parent object of the role folder object which contains the specified role.
     */
    public function getCourseMembersObjectForRole(int $a_role_id): ilCourseParticipants
    {
        if (array_key_exists($a_role_id . '_courseMembersObject', $this->local_role_cache)) {
            return $this->local_role_cache[$a_role_id . '_courseMembersObject'];
        } else {
            $course_refs = $this->rbac_review->getFoldersAssignedToRole($a_role_id, true);
            $course_ref = $course_refs[0];
            $course_obj = new ilObjCourse($course_ref, true);
            $crsmembers_obj = ilCourseParticipants::_getInstanceByObjId($course_obj->getId());
            $this->local_role_cache[$a_role_id . '_courseMembersObject'] = $crsmembers_obj;
            return $crsmembers_obj;
        }
    }

    /**
     * Assigns a user to a role.
     */
    public function assignToRole(ilObjUser $a_user_obj, int $a_role_id): void
    {
        // Do nothing, if the user is already assigned to the role.
        // Specifically, we do not want to put a course object or
        // group object on the personal desktop again, if a user
        // has removed it from the personal desktop.
        if ($this->rbac_review->isAssigned($a_user_obj->getId(), $a_role_id)) {
            return;
        }

        // If it is a course role, use the ilCourseMember object to assign
        // the user to the role

        $this->rbac_admin->assignUser($a_role_id, $a_user_obj->getId(), true);
        $obj_id = $this->rbac_review->getObjectOfRole($a_role_id);
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
        if (!array_key_exists($a_role_id, $this->parent_roles_cache)) {
            $parent_role_ids = [];

            $role_obj = $this->getRoleObject($a_role_id);
            $short_role_title = substr($role_obj->getTitle(), 0, 12);
            $folders = $this->rbac_review->getFoldersAssignedToRole($a_role_id, true);
            if (count($folders) > 0) {
                $all_parent_role_ids = $this->rbac_review->getParentRoleIds($folders[0]);
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
            $this->parent_roles_cache[$a_role_id] = $parent_role_ids;
        }
        return $this->parent_roles_cache[$a_role_id];
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
        $this->rbac_admin->deassignUser($a_role_id, $a_user_obj->getId());

        if (substr(ilObject::_lookupTitle($a_role_id), 0, 6) !== 'il_crs'
            && substr(ilObject::_lookupTitle($a_role_id), 0, 6) !== 'il_grp') {
            return;
        }

        $ref = ilObject::_getAllReferences(
            $this->rbac_review->getObjectOfRole($a_role_id)
        );
        $ref_id = end($ref);
        if (!$ref_id) {
            return;
        }
        $this->recommended_content_manager->removeObjectRecommendation($a_user_obj->getId(), $ref_id);
    }

    private function tagContained(string $tagname): bool
    {
        return in_array($tagname, $this->contained_tags, true);
    }

    /**
     * @param \XMLParser|resource $a_xml_parser
     */
    public function importEndTag(
        $a_xml_parser,
        string $a_name
    ): void {
        $this->contained_tags[] = $a_name;

        switch ($a_name) {
            case 'Role':
                $this->roles[$this->current_role_id]['name'] = $this->cdata;
                $this->roles[$this->current_role_id]['type'] = $this->current_role_type;
                $this->roles[$this->current_role_id]['action'] = $this->current_role_action;
                break;

            case 'PersonalPicture':
                switch ($this->personal_picture['encoding']) {
                    case 'Base64':
                        $this->personal_picture['content'] = base64_decode($this->cdata);
                        break;
                    case 'UUEncode':
                        $this->personal_picture['content'] = convert_uudecode($this->cdata);
                        break;
                }
                break;

            case 'User':
                $this->user_obj->setFullname();
                // Fetch the user_id from the database, if we didn't have it in xml file
                // fetch as well, if we are trying to insert -> recognize duplicates!
                if ($this->user_id == -1 || $this->action === 'Insert') {
                    $user_id = ilObjUser::getUserIdByLogin($this->user_obj->getLogin());
                } else {
                    $user_id = $this->user_id;
                }

                if ($user_id === (int) ANONYMOUS_USER_ID || $user_id === (int) SYSTEM_USER_ID) {
                    return;
                }

                // Handle conflicts
                switch ($this->conflict_rule) {
                    case self::IL_FAIL_ON_CONFLICT:
                        // do not change action
                        break;
                    case self::IL_UPDATE_ON_CONFLICT:
                        switch ($this->action) {
                            case 'Insert':
                                if ($user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_replaced'), 'Insert', 'Update'));
                                    $this->action = 'Update';
                                }
                                break;
                            case 'Update':
                                if (!$user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_replaced'), 'Update', 'Insert'));
                                    $this->action = 'Insert';
                                }
                                break;
                            case 'Delete':
                                if (!$user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_ignored'), 'Delete'));
                                    $this->action = 'Ignore';
                                }
                                break;
                        }
                        break;
                    case self::IL_IGNORE_ON_CONFLICT:
                        switch ($this->action) {
                            case 'Insert':
                                if ($user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_ignored'), 'Insert'));
                                    $this->action = 'Ignore';
                                }
                                break;
                            case 'Update':
                                if (!$user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_ignored'), 'Update'));
                                    $this->action = 'Ignore';
                                }
                                break;
                            case 'Delete':
                                if (!$user_id) {
                                    $this->logWarning($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_action_ignored'), 'Delete'));
                                    $this->action = 'Ignore';
                                }
                                break;
                        }
                        break;
                }

                // check external account conflict (if external account is already used)
                // note: we cannot apply conflict rules in the same manner as to logins here
                // so we ignore records with already existing external accounts.
                //echo $this->userObj->getAuthMode().'h';
                $am = ($this->user_obj->getAuthMode() === 'default' || $this->user_obj->getAuthMode() == '')
                    ? ilAuthUtils::_getAuthModeName($this->settings->get('auth_mode'))
                    : $this->user_obj->getAuthMode();
                $loginForExternalAccount = ($this->user_obj->getExternalAccount() == '')
                    ? ''
                    : ilObjUser::_checkExternalAuthAccount($am, $this->user_obj->getExternalAccount());
                switch ($this->action) {
                    case 'Insert':
                        if ($loginForExternalAccount != '') {
                            $this->logWarning(
                                $this->user_obj->getLogin(),
                                $this->lng->txt('usrimport_no_insert_ext_account_exists')
                                    . ' (' . $this->stripTags($this->user_obj->getExternalAccount()) . ')'
                            );
                            $this->action = 'Ignore';
                        }
                        break;

                    case 'Update':
                        // this variable describes the ILIAS login which belongs to the given external account!!!
                        // it is NOT nescessarily the ILIAS login of the current user record !!
                        // so if we found an ILIAS login according to the authentication method
                        // check if the ILIAS login belongs to the current user record, otherwise somebody else is using it!
                        if ($loginForExternalAccount != '') {
                            // check if we changed the value!
                            $externalAccountHasChanged = $this->user_obj->getExternalAccount() != ilObjUser::_lookupExternalAccount($this->user_id);
                            // if it has changed and the external login
                            if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->user_obj->getLogin())) {
                                $this->logWarning(
                                    $this->user_obj->getLogin(),
                                    $this->lng->txt('usrimport_no_update_ext_account_exists')
                                        . ' (' . $this->stripTags($this->user_obj->getExternalAccount()) . ')'
                                );
                                $this->action = 'Ignore';
                            }
                        }
                        break;
                }

                if (count($this->multi_values)) {
                    if (isset($this->multi_values['GeneralInterest'])) {
                        $this->user_obj->setGeneralInterests($this->multi_values['GeneralInterest']);
                    }
                    if (isset($this->multi_values['OfferingHelp'])) {
                        $this->user_obj->setOfferingHelp($this->multi_values['OfferingHelp']);
                    }
                    if (isset($this->multi_values['LookingForHelp'])) {
                        $this->user_obj->setLookingForHelp($this->multi_values['LookingForHelp']);
                    }
                }

                // Perform the action
                switch ($this->action) {
                    case 'Insert':
                        if ($user_id) {
                            $this->logFailure($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_insert'));
                        } else {
                            if ($this->current_user_password !== null) {
                                switch (strtoupper($this->current_user_password_type)) {
                                    case 'BCRYPT':
                                        $this->user_obj->setPasswd($this->current_user_password, ilObjUser::PASSWD_CRYPTED);
                                        $this->user_obj->setPasswordEncodingType('bcryptphp');
                                        $this->user_obj->setPasswordSalt(null);
                                        break;

                                    case 'PLAIN':
                                        $this->user_obj->setPasswd($this->current_user_password, ilObjUser::PASSWD_PLAIN);
                                        $this->acc_mail->setUserPassword((string) $this->current_user_password);
                                        break;

                                    default:
                                        $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'Type', 'Password', $this->stripTags($this->current_user_password_type)));
                                        break;
                                }
                            } else {
                                // this does the trick for empty passwords
                                // since a MD5 string has always 32 characters,
                                // no hashed password combination will ever equal to
                                // an empty string
                                $this->user_obj->setPasswd('', ilObjUser::PASSWD_CRYPTED);
                            }

                            $this->user_obj->setTitle($this->user_obj->getFullname());
                            $this->user_obj->setDescription($this->user_obj->getEmail());

                            if (!$this->time_limit_owner_set) {
                                $this->user_obj->setTimeLimitOwner($this->getFolderId());
                            }

                            // default time limit settings
                            if (!$this->time_limit_set) {
                                $this->user_obj->setTimeLimitUnlimited(true);

                                if (!$this->approve_date_set) {
                                    $this->user_obj->setApproveDate(date('Y-m-d H:i:s'));
                                }
                            }


                            $this->user_obj->setActive($this->currActive === 'true' || is_null($this->currActive));

                            // Finally before saving new user.
                            // Check if profile is incomplete

                            // #8759
                            if ($this->udf_data !== []) {
                                $this->user_obj = $this->addUDFDataToUser($this->user_obj);
                            }

                            if (!$this->user_obj->getLanguage()) {
                                $this->user_obj->setLanguage($this->lng->getDefaultLanguage());
                            }

                            $this->user_obj->setProfileIncomplete($this->checkProfileIncomplete($this->user_obj));
                            $this->user_obj->create();

                            //insert user data in table user_data
                            $this->user_obj->saveAsNew();

                            if (count($this->prefs)) {
                                foreach ($this->prefs as $key => $value) {
                                    if ($key !== 'mail_incoming_type' &&
                                        $key !== 'mail_signature' &&
                                        $key !== 'mail_linebreak'
                                    ) {
                                        $this->user_obj->setPref($key, $value);
                                    }
                                }
                            }

                            if (!is_array($this->prefs) || !array_key_exists('chat_osc_accept_msg', $this->prefs)) {
                                $this->user_obj->setPref('chat_osc_accept_msg', $this->settings->get('chat_osc_accept_msg', 'n'));
                            }
                            if (!is_array($this->prefs) || !array_key_exists('chat_broadcast_typing', $this->prefs)) {
                                $this->user_obj->setPref('chat_broadcast_typing', $this->settings->get('chat_broadcast_typing', 'n'));
                            }
                            if (!is_array($this->prefs) || !array_key_exists('bs_allow_to_contact_me', $this->prefs)) {
                                $this->user_obj->setPref('bs_allow_to_contact_me', $this->settings->get('bs_allow_to_contact_me', 'n'));
                            }

                            $this->user_obj->update();

                            // update mail preferences, to be extended
                            $this->updateMailPreferences($this->user_obj->getId());

                            if (is_array($this->personal_picture)) {
                                if (strlen($this->personal_picture['content'])) {
                                    $extension = 'jpg';
                                    if (preg_match('/.*(png|jpg|gif|jpeg)$/', $this->personal_picture['imagetype'], $matches)) {
                                        $extension = $matches[1];
                                    }
                                    $tmp_name = $this->saveTempImage($this->personal_picture['content'], ".{$extension}");
                                    if (strlen($tmp_name)) {
                                        $this->user_obj->uploadPersonalPicture($tmp_name);
                                        unlink($tmp_name);
                                    }
                                }
                            }

                            //set role entries
                            foreach ($this->roles as $role_id => $role) {
                                if (isset($this->role_assign[$role_id]) && $this->role_assign[$role_id]) {
                                    $this->assignToRole($this->user_obj, (int) $this->role_assign[$role_id]);
                                }
                            }

                            $this->sendAccountMail();
                            $this->logSuccess($this->user_obj->getLogin(), $this->user_obj->getId(), 'Insert');
                            // reset account mail object
                            $this->acc_mail->reset();
                        }
                        break;

                    case 'Update':
                        if (!$user_id) {
                            $this->logFailure($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_update'));
                        } else {
                            $update_user = new ilObjUser($user_id);
                            $update_user->read();
                            if ($this->current_user_password != null) {
                                switch (strtoupper($this->current_user_password_type)) {
                                    case 'BCRYPT':
                                        $update_user->setPasswd($this->current_user_password, ilObjUser::PASSWD_CRYPTED);
                                        $update_user->setPasswordEncodingType('bcryptphp');
                                        $update_user->setPasswordSalt(null);
                                        break;

                                    case 'PLAIN':
                                        $update_user->setPasswd($this->current_user_password, ilObjUser::PASSWD_PLAIN);
                                        $this->acc_mail->setUserPassword((string) $this->current_user_password);
                                        break;

                                    default:
                                        $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'Type', 'Password', $this->stripTags($this->current_user_password_type)));
                                        break;
                                }
                            }
                            if ($this->tagContained('Firstname')) {
                                $update_user->setFirstname($this->user_obj->getFirstname());
                            }
                            if ($this->tagContained('Lastname')) {
                                $update_user->setLastname($this->user_obj->getLastname());
                            }
                            if ($this->tagContained('Title')) {
                                $update_user->setUTitle($this->user_obj->getUTitle());
                            }
                            if ($this->tagContained('Gender')) {
                                $update_user->setGender($this->user_obj->getGender());
                            }
                            if ($this->tagContained('Email')) {
                                $update_user->setEmail($this->user_obj->getEmail());
                            }
                            if ($this->tagContained('SecondEmail')) {
                                $update_user->setSecondEmail($this->user_obj->getSecondEmail());
                            }
                            if ($this->tagContained('Birthday')) {
                                $update_user->setBirthday($this->user_obj->getBirthday());
                            }
                            if ($this->tagContained('Institution')) {
                                $update_user->setInstitution($this->user_obj->getInstitution());
                            }
                            if ($this->tagContained('Street')) {
                                $update_user->setStreet($this->user_obj->getStreet());
                            }
                            if ($this->tagContained('City')) {
                                $update_user->setCity($this->user_obj->getCity());
                            }
                            if ($this->tagContained('PostalCode')) {
                                $update_user->setZipcode($this->user_obj->getZipcode());
                            }
                            if ($this->tagContained('SelCountry') && mb_strlen($this->cdata) === 2) {
                                $update_user->setCountry($this->user_obj->getCountry());
                            }
                            if ($this->tagContained('PhoneOffice')) {
                                $update_user->setPhoneOffice($this->user_obj->getPhoneOffice());
                            }
                            if ($this->tagContained('PhoneHome')) {
                                $update_user->setPhoneHome($this->user_obj->getPhoneHome());
                            }
                            if ($this->tagContained('PhoneMobile')) {
                                $update_user->setPhoneMobile($this->user_obj->getPhoneMobile());
                            }
                            if ($this->tagContained('Fax')) {
                                $update_user->setFax($this->user_obj->getFax());
                            }
                            if ($this->tagContained('Hobby')) {
                                $update_user->setHobby($this->user_obj->getHobby());
                            }
                            if ($this->tagContained('GeneralInterest')) {
                                $update_user->setGeneralInterests($this->user_obj->getGeneralInterests());
                            }
                            if ($this->tagContained('OfferingHelp')) {
                                $update_user->setOfferingHelp($this->user_obj->getOfferingHelp());
                            }
                            if ($this->tagContained('LookingForHelp')) {
                                $update_user->setLookingForHelp($this->user_obj->getLookingForHelp());
                            }
                            if ($this->tagContained('Comment')) {
                                $update_user->setComment($this->user_obj->getComment());
                            }
                            if ($this->tagContained('Department')) {
                                $update_user->setDepartment($this->user_obj->getDepartment());
                            }
                            if ($this->tagContained('Matriculation')) {
                                $update_user->setMatriculation($this->user_obj->getMatriculation());
                            }
                            if (!is_null($this->currActive)) {
                                $update_user->setActive($this->currActive === 'true', is_object($this->user) ? $this->user->getId() : 0);
                            }
                            if ($this->tagContained('ClientIP')) {
                                $update_user->setClientIP($this->user_obj->getClientIP());
                            }
                            if ($this->time_limit_set) {
                                $update_user->setTimeLimitUnlimited($this->user_obj->getTimeLimitUnlimited());
                            }
                            if ($this->tagContained('TimeLimitFrom')) {
                                $update_user->setTimeLimitFrom($this->user_obj->getTimeLimitFrom());
                            }
                            if ($this->tagContained('TimeLimitUntil')) {
                                $update_user->setTimeLimitUntil($this->user_obj->getTimeLimitUntil());
                            }
                            if ($this->tagContained('ApproveDate')) {
                                $update_user->setApproveDate($this->user_obj->getApproveDate());
                            }
                            if ($this->tagContained('AgreeDate')) {
                                $update_user->setAgreeDate($this->user_obj->getAgreeDate());
                            }
                            if ($this->tagContained('Language')) {
                                $update_user->setLanguage($this->user_obj->getLanguage());
                            }
                            if ($this->tagContained('ExternalAccount')) {
                                $update_user->setExternalAccount($this->user_obj->getExternalAccount());
                            }

                            // Fixed: if auth_mode is not set, it was always overwritten with auth_default
                            #if (! is_null($this->userObj->getAuthMode())) $updateUser->setAuthMode($this->userObj->getAuthMode());
                            if ($this->auth_mode_set) {
                                $update_user->setAuthMode($this->user_obj->getAuthMode());
                            }

                            // Special handlin since it defaults to 7 (USER_FOLDER_ID)
                            if ($this->time_limit_owner_set) {
                                $update_user->setTimeLimitOwner($this->user_obj->getTimeLimitOwner());
                            }

                            if (count($this->prefs)) {
                                foreach ($this->prefs as $key => $value) {
                                    if ($key !== 'mail_incoming_type' &&
                                        $key !== 'mail_signature' &&
                                        $key !== 'mail_linebreak'
                                    ) {
                                        $update_user->setPref($key, $value);
                                    }
                                }
                            }

                            // save user preferences (skin and style)
                            if ($this->update_look_and_skin) {
                                $update_user->setPref('skin', $this->user_obj->getPref('skin'));
                                $update_user->setPref('style', $this->user_obj->getPref('style'));
                            }

                            // update mail preferences, to be extended
                            $this->updateMailPreferences($update_user->getId());

                            // #8759
                            if ($this->udf_data !== []) {
                                $update_user = $this->addUDFDataToUser($update_user);
                            }

                            $update_user->setProfileIncomplete($this->checkProfileIncomplete($update_user));
                            $update_user->setFullname();
                            $update_user->setTitle($update_user->getFullname());
                            $update_user->setDescription($update_user->getEmail());
                            $update_user->update();

                            // update login
                            if ($this->tagContained('Login') && $this->user_id != -1) {
                                try {
                                    $update_user->updateLogin($this->user_obj->getLogin(), Context::UserAdministration);
                                } catch (ilUserException $e) {
                                }
                            }


                            // if language has changed

                            if (is_array($this->personal_picture)) {
                                if (strlen($this->personal_picture['content'])) {
                                    $extension = 'jpg';
                                    if (preg_match('/.*(png|jpg|gif|jpeg)$/', $this->personal_picture['imagetype'], $matches)) {
                                        $extension = $matches[1];
                                    }
                                    $tmp_name = $this->saveTempImage($this->personal_picture['content'], ".{$extension}");
                                    if (strlen($tmp_name)) {
                                        $update_user->uploadPersonalPicture($tmp_name);
                                        unlink($tmp_name);
                                    }
                                }
                            }


                            //update role entries
                            //-------------------
                            foreach ($this->roles as $role_id => $role) {
                                if (array_key_exists($role_id, $this->role_assign)) {
                                    switch ($role['action']) {
                                        case 'Assign':
                                            $this->assignToRole($update_user, (int) $this->role_assign[$role_id]);
                                            break;
                                        case 'AssignWithParents':
                                            $this->assignToRoleWithParents($update_user, (int) $this->role_assign[$role_id]);
                                            break;
                                        case 'Detach':
                                            $this->detachFromRole($update_user, (int) $this->role_assign[$role_id]);
                                            break;
                                    }
                                }
                            }
                            $this->logSuccess($update_user->getLogin(), $user_id, 'Update');
                        }
                        break;
                    case 'Delete':
                        if (!$user_id) {
                            $this->logFailure($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_delete'));
                        } else {
                            $deleteUser = new ilObjUser($user_id);
                            $deleteUser->delete();

                            $this->logSuccess($this->user_obj->getLogin(), $user_id, 'Delete');
                        }
                        break;
                }

                // init role array for next user
                $this->roles = [];
                break;

            case 'Login':
                $this->user_obj->setLogin($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Password':
                $this->current_user_password = $this->cdata;
                break;

            case 'Firstname':
                $this->user_obj->setFirstname($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Lastname':
                $this->user_obj->setLastname($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Title':
                $this->user_obj->setUTitle($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Gender':
                $this->user_obj->setGender($this->cdata);
                break;

            case 'Email':
                $this->user_obj->setEmail($this->getCDataWithoutTags($this->cdata));
                break;
            case 'SecondEmail':
                $this->user_obj->setSecondEmail($this->getCDataWithoutTags($this->cdata));
                break;
            case 'Birthday':
                $birthday = $this->getCDataWithoutTags($this->cdata);
                if (strtotime($birthday) !== false) {
                    $this->user_obj->setBirthday($birthday);
                }
                break;
            case 'Institution':
                $this->user_obj->setInstitution($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Street':
                $this->user_obj->setStreet($this->getCDataWithoutTags($this->cdata));
                break;

            case 'City':
                $this->user_obj->setCity($this->getCDataWithoutTags($this->cdata));
                break;

            case 'PostalCode':
                $this->user_obj->setZipcode($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Country':
            case 'SelCountry':
                if (mb_strlen($this->cdata) !== 2) {
                    break;
                }
                $this->user_obj->setCountry($this->getCDataWithoutTags($this->cdata));
                break;

            case 'PhoneOffice':
                $this->user_obj->setPhoneOffice($this->getCDataWithoutTags($this->cdata));
                break;

            case 'PhoneHome':
                $this->user_obj->setPhoneHome($this->getCDataWithoutTags($this->cdata));
                break;

            case 'PhoneMobile':
                $this->user_obj->setPhoneMobile($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Fax':
                $this->user_obj->setFax($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Hobby':
                $this->user_obj->setHobby($this->getCDataWithoutTags($this->cdata));
                break;

            case 'GeneralInterest':
            case 'OfferingHelp':
            case 'LookingForHelp':
                $this->multi_values[$a_name][] = $this->getCDataWithoutTags($this->cdata);
                break;

            case 'Comment':
                $this->user_obj->setComment($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Department':
                $this->user_obj->setDepartment($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Matriculation':
                $this->user_obj->setMatriculation($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Active':
                $this->currActive = $this->cdata;
                break;

            case 'ClientIP':
                $this->user_obj->setClientIP($this->getCDataWithoutTags($this->cdata));
                break;

            case 'TimeLimitOwner':
                $this->time_limit_owner_set = true;
                $this->user_obj->setTimeLimitOwner((int) $this->cdata);
                break;

            case 'TimeLimitUnlimited':
                $this->time_limit_set = true;
                $this->user_obj->setTimeLimitUnlimited((bool) $this->cdata);
                break;

            case 'TimeLimitFrom':
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $this->user_obj->setTimeLimitFrom((int) $this->cdata);
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== '0000-00-00 00:00:00') {
                        $this->user_obj->setTimeLimitFrom($timestamp);
                    } elseif ($this->cdata === '0000-00-00 00:00:00') {
                        $this->user_obj->setTimeLimitFrom(null);
                    }
                }
                break;

            case 'TimeLimitUntil':
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $this->user_obj->setTimeLimitUntil((int) $this->cdata);
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== '0000-00-00 00:00:00') {
                        $this->user_obj->setTimeLimitUntil($timestamp);
                    } elseif ($this->cdata === '0000-00-00 00:00:00') {
                        $this->user_obj->setTimeLimitUntil(null);
                    }
                }
                break;

            case 'ApproveDate':
                $this->approve_date_set = true;
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $tmp_date = new ilDateTime($this->cdata, IL_CAL_UNIX);
                    $this->user_obj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== '0000-00-00 00:00:00') {
                        $tmp_date = new ilDateTime($timestamp, IL_CAL_UNIX);
                        $this->user_obj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
                    } elseif ($this->cdata === '0000-00-00 00:00:00') {
                        $this->user_obj->setApproveDate(null);
                    }
                }
                break;

            case 'AgreeDate':
                if (is_numeric($this->cdata)) {
                    // Treat cdata as a unix timestamp
                    $tmp_date = new ilDateTime($this->cdata, IL_CAL_UNIX);
                    $this->user_obj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
                } else {
                    // Try to convert cdata into unix timestamp, or ignore it
                    $timestamp = strtotime($this->cdata);
                    if ($timestamp !== false && trim($this->cdata) !== '0000-00-00 00:00:00') {
                        $tmp_date = new ilDateTime($timestamp, IL_CAL_UNIX);
                        $this->user_obj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
                    } elseif ($this->cdata === '0000-00-00 00:00:00') {
                        $this->user_obj->setAgreeDate(null);
                    }
                }
                break;

            case 'ExternalAccount':
                $this->user_obj->setExternalAccount($this->getCDataWithoutTags($this->cdata));
                break;

            case 'Look':
                $this->update_look_and_skin = false;
                if ($this->skin !== '' && $this->style !== '') {
                    if (is_array($this->user_styles)) {
                        if (in_array($this->skin . ':' . $this->style, $this->user_styles)) {
                            $this->user_obj->setPref('skin', $this->skin);
                            $this->user_obj->setPref('style', $this->style);
                            $this->update_look_and_skin = true;
                        }
                    }
                }
                break;

            case 'UserDefinedField':
                $field_id = null;
                if ($this->user_profile->getFieldByIdentifier(
                    $this->tmp_udf_id
                ) !== null) {
                    $field_id = $this->tmp_udf_id;
                }

                if ($field_id === null) {
                    $field_id = $this->fetchFieldIdFromName($this->tmp_udf_name);
                }

                if ($field_id === null) {
                    break;
                }

                $data = json_decode(
                    strip_tags($this->cdata),
                    true
                ) ?? $this->cdata;
                if ($data === '') {
                    break;
                }

                if (!is_array($data)) {
                    $data = [$data];
                }

                $this->udf_data[$field_id] = $data;

                break;
            case 'AccountInfo':
                if ($this->current_messenger_type === 'external') {
                    $this->user_obj->setExternalAccount($this->cdata);
                }
                break;
            case 'Pref':
                if ($this->current_pref_key != null && strlen(trim($this->cdata)) > 0
                    && ilUserXMLWriter::isPrefExportable($this->current_pref_key)) {
                    $this->prefs[$this->current_pref_key] = trim($this->cdata);
                }
                $this->current_pref_key = null;
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
        $fh = fopen($tempname, 'wb');
        if ($fh == false) {
            return '';
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
        $externalAccountHasChanged = false;

        switch ($a_name) {
            case 'Role':
                $this->roles[$this->current_role_id]['name'] = $this->cdata;
                $this->roles[$this->current_role_id]['type'] = $this->current_role_type;
                $this->roles[$this->current_role_id]['action'] = $this->current_role_action;
                break;

            case 'User':
                $this->user_obj->setFullname();
                if ($this->user_id != -1 && ($this->action === 'Update' || $this->action === 'Delete')) {
                    $user_id = $this->user_id;
                    $user_exists = !is_null(ilObjUser::_lookupLogin($user_id));
                } else {
                    $user_id = ilObjUser::getUserIdByLogin($this->user_obj->getLogin());
                    $user_exists = $user_id != 0;
                }
                if (is_null($this->user_obj->getLogin())) {
                    $this->logFailure('---', sprintf($this->lng->txt('usrimport_xml_element_for_action_required'), 'Login', 'Insert'));
                }

                if ($user_id === (int) ANONYMOUS_USER_ID || $user_id === (int) SYSTEM_USER_ID) {
                    $this->logWarning($this->user_obj->getLogin(), $this->lng->txt('usrimport_xml_anonymous_or_root_not_allowed'));
                    break;
                }

                switch ($this->action) {
                    case 'Insert':
                        if ($user_exists and $this->conflict_rule === self::IL_FAIL_ON_CONFLICT) {
                            $this->logWarning($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_insert'));
                        }
                        if (is_null($this->user_obj->getGender()) && $this->isFieldRequired('gender')) {
                            $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_for_action_required'), 'Gender', 'Insert'));
                        }
                        if (is_null($this->user_obj->getFirstname())) {
                            $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_for_action_required'), 'Firstname', 'Insert'));
                        }
                        if (is_null($this->user_obj->getLastname())) {
                            $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_for_action_required'), 'Lastname', 'Insert'));
                        }
                        if (count($this->roles) == 0) {
                            $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_for_action_required'), 'Role', 'Insert'));
                        } else {
                            $has_global_role = false;
                            foreach ($this->roles as $role) {
                                if ($role['type'] === 'Global') {
                                    $has_global_role = true;
                                    break;
                                }
                            }
                            if (!$has_global_role) {
                                $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_global_role_for_action_required'), 'Insert'));
                            }
                        }
                        break;
                    case 'Update':
                        if (!$user_exists) {
                            $this->logWarning($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_update'));
                        } elseif ($this->user_id != -1 && $this->tagContained('Login')) {
                            // check if someone owns the new login name!
                            $someonesId = ilObjUser::_lookupId($this->user_obj->getLogin());

                            if (is_numeric($someonesId) && $someonesId != $this->user_id) {
                                $this->logFailure($this->user_obj->getLogin(), $this->lng->txt('usrimport_login_is_not_unique'));
                            }
                        }
                        break;
                    case 'Delete':
                        if (!$user_exists) {
                            $this->logWarning($this->user_obj->getLogin(), $this->lng->txt('usrimport_cant_delete'));
                        }
                        break;
                }

                // init role array for next user
                $this->roles = [];
                break;

            case 'Login':
                if (array_key_exists($this->cdata, $this->logins)) {
                    $this->logWarning($this->cdata, $this->lng->txt('usrimport_login_is_not_unique'));
                } else {
                    $this->logins[$this->cdata] = $this->cdata;
                }
                $this->user_obj->setLogin($this->stripTags($this->cdata));
                break;

            case 'Password':
                switch ($this->current_user_password_type) {
                    case 'BCRYPT':
                        $this->user_obj->setPasswd($this->cdata, ilObjUser::PASSWD_CRYPTED);
                        $this->user_obj->setPasswordEncodingType('bcryptphp');
                        $this->user_obj->setPasswordSalt(null);
                        break;

                    case 'PLAIN':
                        $this->user_obj->setPasswd($this->cdata, ilObjUser::PASSWD_PLAIN);
                        $this->acc_mail->setUserPassword((string) $this->current_user_password);
                        break;

                    default:
                        $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_attribute_value_illegal'), 'Type', 'Password', $this->stripTags($this->current_user_password_type)));
                        break;
                }
                break;

            case 'Firstname':
                $this->user_obj->setFirstname($this->cdata);
                break;

            case 'Lastname':
                $this->user_obj->setLastname($this->cdata);
                break;

            case 'Title':
                $this->user_obj->setUTitle($this->cdata);
                break;

            case 'Gender':
                if (!in_array(strtolower($this->cdata), ['n', 'm', 'f', ''])) {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'Gender', $this->stripTags($this->cdata))
                    );
                }
                $this->user_obj->setGender($this->cdata);
                break;

            case 'Email':
                $this->user_obj->setEmail($this->cdata);
                break;
            case 'SecondEmail':
                $this->user_obj->setSecondEmail($this->cdata);
                break;
            case 'Institution':
                $this->user_obj->setInstitution($this->cdata);
                break;

            case 'Street':
                $this->user_obj->setStreet($this->cdata);
                break;

            case 'City':
                $this->user_obj->setCity($this->cdata);
                break;

            case 'PostalCode':
                $this->user_obj->setZipcode($this->cdata);
                break;

            case 'Country':
            case 'SelCountry':
                if (mb_strlen($this->cdata) !== 2) {
                    break;
                }
                $this->user_obj->setCountry($this->cdata);
                break;

            case 'PhoneOffice':
                $this->user_obj->setPhoneOffice($this->cdata);
                break;

            case 'PhoneHome':
                $this->user_obj->setPhoneHome($this->cdata);
                break;

            case 'PhoneMobile':
                $this->user_obj->setPhoneMobile($this->cdata);
                break;

            case 'Fax':
                $this->user_obj->setFax($this->cdata);
                break;

            case 'Hobby':
                $this->user_obj->setHobby($this->cdata);
                break;

            case 'GeneralInterest':
            case 'OfferingHelp':
            case 'LookingForHelp':
                $this->multi_values[$a_name][] = $this->cdata;
                break;

            case 'Comment':
                $this->user_obj->setComment($this->cdata);
                break;

            case 'Department':
                $this->user_obj->setDepartment($this->cdata);
                break;

            case 'Matriculation':
                $this->user_obj->setMatriculation($this->cdata);
                break;

            case 'ExternalAccount':
                $am = ($this->user_obj->getAuthMode() === 'default' || $this->user_obj->getAuthMode() == '')
                    ? ilAuthUtils::_getAuthModeName($this->settings->get('auth_mode'))
                    : $this->user_obj->getAuthMode();
                $loginForExternalAccount = (trim($this->cdata) == '')
                    ? ''
                    : ilObjUser::_checkExternalAuthAccount($am, trim($this->cdata));
                switch ($this->action) {
                    case 'Insert':
                        if ($loginForExternalAccount != '') {
                            $this->logWarning($this->user_obj->getLogin(), $this->lng->txt('usrimport_no_insert_ext_account_exists') . ' (' . $this->stripTags($this->cdata) . ')');
                        }
                        break;

                    case 'Update':
                        if ($loginForExternalAccount != '') {
                            $externalAccountHasChanged = trim($this->cdata) != ilObjUser::_lookupExternalAccount($this->user_id);
                            if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->user_obj->getLogin())) {
                                $this->logWarning(
                                    $this->user_obj->getLogin(),
                                    $this->lng->txt('usrimport_no_update_ext_account_exists') . ' (' . $this->stripTags($this->cdata) . ' for ' . $this->stripTags($loginForExternalAccount) . ')'
                                );
                            }
                        }
                        break;
                }
                if ($externalAccountHasChanged) {
                    $this->user_obj->setExternalAccount(trim($this->cdata));
                }
                break;

            case 'Active':
                if ($this->cdata !== 'true'
                && $this->cdata !== 'false') {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'Active', $this->stripTags($this->cdata))
                    );
                }
                $this->currActive = $this->cdata;
                break;
            case 'TimeLimitOwner':
                if (!preg_match('/\d+/', $this->cdata)) {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitOwner', $this->stripTags($this->cdata))
                    );
                } elseif (!$this->access->checkAccess('cat_administrate_users', '', (int) $this->cdata)) {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitOwner', $this->stripTags($this->cdata))
                    );
                } elseif ($this->object_data_cache->lookupType($this->object_data_cache->lookupObjId((int) $this->cdata)) !== 'cat' && !(int) $this->cdata == USER_FOLDER_ID) {
                    $this->logFailure(
                        $this->user_obj->getLogin(),
                        sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitOwner', $this->stripTags($this->cdata))
                    );
                }
                $this->user_obj->setTimeLimitOwner((int) $this->cdata);
                break;
            case 'TimeLimitUnlimited':
                switch (strtolower($this->cdata)) {
                    case 'true':
                    case '1':
                        $this->user_obj->setTimeLimitUnlimited(true);
                        break;
                    case 'false':
                    case '0':
                        $this->user_obj->setTimeLimitUnlimited(false);
                        break;
                    default:
                        $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitUnlimited', $this->stripTags($this->cdata)));
                        break;
                }
                break;
            case 'TimeLimitFrom':
                if ($this->cdata === '') {
                    break;
                }
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata)) {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitFrom', $this->stripTags($this->cdata)));
                }
                $this->user_obj->setTimeLimitFrom((int) $this->cdata);
                break;
            case 'TimeLimitUntil':
                if ($this->cdata === '') {
                    break;
                }
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata)) {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'TimeLimitUntil', $this->stripTags($this->cdata)));
                }
                $this->user_obj->setTimeLimitUntil((int) $this->cdata);
                break;
            case 'ApproveDate':
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata) && !$this->cdata === '0000-00-00 00:00:00') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'ApproveDate', $this->stripTags($this->cdata)));
                }
                break;
            case 'AgreeDate':
                // Accept datetime or Unix timestamp
                if (strtotime($this->cdata) === false && !is_numeric($this->cdata) && !$this->cdata === '0000-00-00 00:00:00') {
                    $this->logFailure($this->user_obj->getLogin(), sprintf($this->lng->txt('usrimport_xml_element_content_illegal'), 'AgreeDate', $this->stripTags($this->cdata)));
                }
                break;
            case 'Pref':
                if ($this->current_pref_key != null) {
                    $this->verifyPref($this->current_pref_key, $this->cdata);
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
            $a_data = preg_replace('/\t+/', ' ', $a_data);
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
        return $this->user_count;
    }

    /**
     * Writes a warning log message to the protocol.
     */
    public function logWarning(
        string $aLogin,
        string $aMessage
    ): void {
        if (!array_key_exists($aLogin, $this->protocol)) {
            $this->protocol[$aLogin] = [];
        }
        if ($aMessage) {
            $this->protocol[$aLogin][] = $aMessage;
        }
        if ($this->error_level === self::IL_IMPORT_SUCCESS) {
            $this->error_level = self::IL_IMPORT_WARNING;
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
            $this->protocol[$aLogin] = [];
        }
        if ($aMessage) {
            $this->protocol[$aLogin][] = $aMessage;
        }
        $this->error_level = self::IL_IMPORT_FAILURE;
    }

    /**
     * Writes a success log message to the protocol.
     */
    public function logSuccess(
        string $aLogin,
        int $userid,
        string $action
    ): void {
        $this->user_mapping[$userid] = ['login' => $aLogin, 'action' => $action, 'message' => 'successful'];
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
        $block = new ilTemplate('tpl.usr_import_log_block.html', true, true, 'components/ILIAS/User');
        $block->setVariable('TXT_LOG_TITLE', $a_log_title);
        $block->setVariable('TXT_MESSAGE_ID', $this->lng->txt('login'));
        $block->setVariable('TXT_MESSAGE_TEXT', $this->lng->txt('message'));
        foreach ($this->getProtocol() as $login => $messages) {
            $block->setCurrentBlock('log_row');
            $reason = '';
            foreach ($messages as $message) {
                if ($reason == '') {
                    $reason = $message;
                } else {
                    $reason .= '<br>' . $message;
                }
            }
            $block->setVariable('MESSAGE_ID', $login);
            $block->setVariable('MESSAGE_TEXT', $reason);
            $block->parseCurrentBlock();
        }
        return $block->get();
    }

    /**
     * Returns true, if the import was successful.
     */
    public function isSuccess(): bool
    {
        return $this->error_level === self::IL_IMPORT_SUCCESS;
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
        if ($this->req_send_mail != '' ||
            ($this->isSendMail() && $this->user_obj->getEmail() != '')) {
            $this->acc_mail->setUser($this->user_obj);
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
        if ($value === self::IL_USER_MAPPING_ID || $value === self::IL_USER_MAPPING_LOGIN) {
            $this->mapping_mode = $value;
        } else {
            die('wrong argument using methode setUserMappingMethod in ' . __FILE__);
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
        if (is_array($this->required_fields)) {
            return $this->required_fields;
        }
        foreach ($this->settings->getAll() as $field => $value) {
            if (strpos($field, 'require_') === 0 && $value == 1) {
                $value = substr($field, 8);
                $this->required_fields[$value] = $value;
            }
        }
        return $this->required_fields ?: [];
    }

    /**
     * Check if profile is incomplete
     * Will set the usr_data field profile_incomplete if any required field is missing
     */
    private function checkProfileIncomplete(ilObjUser $user_obj): bool
    {
        return $this->user_profile->isProfileIncomplete($user_obj);
    }

    /**
     * determine if a field $fieldname is to a required field (global setting)
     *
     * @param	$fieldname	string value of fieldname, e.g. gender
     * @return true, if field of required fields contains fieldname as key, false otherwise.
     */
    private function isFieldRequired(string $fieldname): bool
    {
        $requiredFields = $this->readRequiredFields();
        $fieldname = strtolower(trim($fieldname));
        return array_key_exists($fieldname, $requiredFields);
    }

    private function verifyPref(string $key, string $value): void
    {
        switch ($key) {
            case 'mail_linebreak':
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
                $this->logFailure('---', "Preference {$this->stripTags($key)} is not supported.");
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
            case 'public_avatar':
            case 'public_zip':
            case 'public_interests_general':
            case 'public_interests_help_offered':
            case 'public_interests_help_looking':
            case 'send_info_mails':
            case 'bs_allow_to_contact_me':
            case 'chat_osc_accept_msg':
            case 'chat_broadcast_typing':
            case 'hide_own_online_status':
                if (!in_array($value, ['y', 'n', ''])) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Value 'y' or 'n' expected for preference {$this->stripTags($key)}.");
                }
                break;
            case 'public_profile':
                if (!in_array($value, ['y', 'n', 'g'])) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Value 'y', 'g' or 'n' expected for preference {$this->stripTags($key)}.");
                }
                break;
            case 'show_users_online':
                if (!in_array($value, ['y', 'n', 'associated'])) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Value 'y' or 'n' or 'associated' expected for preference {$this->stripTags($key)}.");
                }
                break;
            case 'mail_incoming_type':
                if (!in_array((int) $value, ['0','1','2'])) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Value '0' (LOCAL),'1' (EMAIL) or '2' (BOTH) expected for preference {$this->stripTags($key)}.");
                }
                break;
            case 'weekstart':
                if (!in_array($value, ['0','1'])) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Value '0' (Sunday) or '1' (Monday) expected for preference {$this->stripTags($key)}.");
                }
                break;

            case 'mail_signature':
                break;
            case 'user_tz':
                try {
                    ilTimeZone::_getInstance($value);
                    return;
                } catch (ilTimeZoneException $tze) {
                    $this->logFailure('---', "Wrong value '{$this->stripTags($value)}': Invalid timezone $value detected for preference {$this->stripTags($key)}.");
                }
                break;
            default:
                if (!ilUserXMLWriter::isPrefExportable($key)) {
                    $this->logFailure('---', "Preference {$this->stripTags($key)} is not supported.");
                }
                break;
        }
    }

    private function addUDFDataToUser(\ilObjUser $user): \ilObjUser
    {
        return $user->withProfileData(
            array_reduce(
                array_keys($this->udf_data),
                fn(ProfileData $c, string $v): ProfileData =>
                    $c->withAdditionalFieldByIdentifier(
                        $v,
                        $this->udf_data[$v]
                    ),
                $this->user_obj->getProfileData()
            )
        );
    }

    private function updateMailPreferences(int $usr_id): void
    {
        if (array_key_exists('mail_incoming_type', $this->prefs) ||
            array_key_exists('mail_signature', $this->prefs) ||
            array_key_exists('mail_linebreak', $this->prefs)
        ) {
            $mailOptions = new ilMailOptions($usr_id);

            $mailOptions->setSignature(array_key_exists('mail_signature', $this->prefs) ? $this->prefs['mail_signature'] : $mailOptions->getSignature());
            $mailOptions->setIncomingType(array_key_exists('mail_incoming_type', $this->prefs) ? (int) $this->prefs['mail_incoming_type'] : $mailOptions->getIncomingType());
            $mailOptions->updateOptions();
        }
    }

    private function fetchFieldIdFromName(string $name): ?string
    {
        foreach ($this->user_profile->getAllUserDefinedFields() as $field) {
            if ($field->getLabel($this->lng) === $name) {
                return $field->getIdentifier();
            }
        }
        return null;
    }

    private function getCDataWithoutTags(): string
    {
        return $this->stripTags($this->cdata);
    }

    private function stripTags(string $string): string
    {
        return $this->refinery->string()->stripTags()->transform($string);
    }
}
