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

use ILIAS\DI\Container;
use ILIAS\Services\User\UserFieldAttributesChangeListener;
use ILIAS\Services\User\InterestedUserFieldChangeListener;
use ILIAS\Services\User\ChangedUserFieldAttribute;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @author       Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilUserTableGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ilAccountCodesGUI, ilCustomUserFieldsGUI, ilRepositorySearchGUI, ilUserStartingPointGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ilUserProfileInfoSettingsGUI
 */
class ilObjUserFolderGUI extends ilObjectGUI
{
    use ilTableCommandHelper;

    public const USER_FIELD_TRANSLATION_MAPPING = [
        "visible" => "user_visible_in_profile",
        "changeable" => "changeable",
        "searchable" => "header_searchable",
        "required" => "required_field",
        "export" => "export",
        "course_export" => "course_export",
        'group_export' => 'group_export',
        "visib_reg" => "header_visible_registration",
        'visib_lua' => 'usr_settings_visib_lua',
        'changeable_lua' => 'usr_settings_changeable_lua'
    ];

    private Container $dic;
    protected ilPropertyFormGUI $loginSettingsForm;
    protected ilPropertyFormGUI $form;
    protected array $requested_ids; // Missing array type.
    protected string $selected_action;
    protected \ILIAS\User\StandardGUIRequest $user_request;
    protected int $user_owner_id = 0;
    protected int $confirm_change = 0;
    protected ilLogger $log;
    protected ilUserSettingsConfig $user_settings_config;
    private bool $usrFieldChangeListenersAccepted = false;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        global $DIC;

        $this->dic = $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->type = "usrf";
        parent::__construct(
            $a_data,
            $a_id,
            $a_call_by_reference,
            false
        );

        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule("user");
        $this->lng->loadLanguageModule('tos');
        $ilCtrl->saveParameter(
            $this,
            "letter"
        );

        $this->user_request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->selected_action = $this->user_request->getSelectedAction();
        $this->user_settings_config = new ilUserSettingsConfig();

        $this->log = ilLoggerFactory::getLogger("user");
        $this->requested_ids = $this->user_request->getIds();
    }

    private function getTranslationForField(
        string $fieldName,
        array $properties
    ): string {
        $translation = (!isset($properties["lang_var"]) || $properties["lang_var"] === "")
            ? $fieldName
            : $properties["lang_var"];

        if ($fieldName === "country") {
            $translation = "country_free_text";
        }
        if ($fieldName === "sel_country") {
            $translation = "country_selection";
        }

        return $this->lng->txt($translation);
    }

    public function setUserOwnerId(int $a_id): void
    {
        $this->user_owner_id = $a_id;
    }

    public function getUserOwnerId(): int
    {
        return $this->user_owner_id ?: USER_FOLDER_ID;
    }

    public function executeCommand(): void
    {
        global $DIC;

        $ilTabs = $DIC->tabs();
        $access = $DIC->access();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilusertablegui':
                $u_table = new ilUserTableGUI(
                    $this,
                    "view"
                );
                $u_table->initFilter();
                $this->ctrl->setReturn(
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($u_table);
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilrepositorysearchgui':

                if (!$access->checkRbacOrPositionPermissionAccess(
                    "read_users",
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                    $this->ilias->raiseError(
                        $this->lng->txt("permission_denied"),
                        $this->ilias->error_obj->MESSAGE
                    );
                }

                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($this->lng->txt("search_user_extended")); // #17502
                $user_search->enableSearchableCheck(false);
                $user_search->setUserLimitations(false);
                $user_search->setCallback(
                    $this,
                    'searchResultHandler',
                    $this->getUserMultiCommands(true)
                );
                $user_search->addUserAccessFilterCallable(array($this, "searchUserAccessFilterCallable"));
                $this->tabs_gui->setTabActive('search_user_extended');
                $this->ctrl->setReturn(
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($user_search);
                break;

            case 'ilaccountcodesgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("account_codes");
                $acc = new ilAccountCodesGUI($this->ref_id);
                $this->ctrl->forwardCommand($acc);
                break;

            case 'ilcustomuserfieldsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("user_defined_fields");
                $cf = new ilCustomUserFieldsGUI(
                    $this->requested_ref_id,
                    $this->user_request->getFieldId()
                );
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserstartingpointgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("starting_points");
                $cf = new ilUserStartingPointGUI($this->ref_id);
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserprofileinfosettingsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("user_profile_info");
                $ps = new ilUserProfileInfoSettingsGUI();
                $this->ctrl->forwardCommand($ps);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function checkAccess(string $a_permission): void
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (!$this->checkAccessBool($a_permission)) {
            $ilErr->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $ilErr->WARNING
            );
        }
    }

    protected function checkAccessBool(string $a_permission): bool
    {
        return $this->access->checkAccess(
            $a_permission,
            '',
            $this->ref_id
        );
    }

    public function resetFilterObject(): void
    {
        $utab = new ilUserTableGUI(
            $this,
            "view"
        );
        $utab->resetOffset();
        $utab->resetFilter();
        $this->viewObject(true);
    }

    /**
     * Add new user
     */
    public function addUserObject(): void
    {
        $this->ctrl->setParameterByClass(
            "ilobjusergui",
            "new_type",
            "usr"
        );
        $this->ctrl->redirectByClass(
            array("iladministrationgui", "ilobjusergui"),
            "create"
        );
    }

    public function applyFilterObject(): void
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $utab = new ilUserTableGUI(
            $this,
            "view"
        );
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->viewObject();
        $ilTabs->activateTab("usrf");
    }

    /**
     * list users
     */
    public function viewObject(
        bool $reset_filter = false
    ): void {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilToolbar = $DIC->toolbar();
        $tpl = $DIC['tpl'];
        $ilSetting = $DIC['ilSetting'];
        $access = $DIC->access();
        $user_filter = null;

        if ($rbacsystem->checkAccess(
            'create_usr',
            $this->object->getRefId()
        ) ||
            $rbacsystem->checkAccess(
                'cat_administrate_users',
                $this->object->getRefId()
            )) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("usr_add");
            $button->setUrl(
                $this->ctrl->getLinkTarget(
                    $this,
                    "addUser"
                )
            );
            $ilToolbar->addButtonInstance($button);

            $button = ilLinkButton::getInstance();
            $button->setCaption("import_users");
            $button->setUrl(
                $this->ctrl->getLinkTarget(
                    $this,
                    "importUserForm"
                )
            );
            $ilToolbar->addButtonInstance($button);
        }

        if (
            !$access->checkAccess(
                'read_users',
                '',
                USER_FOLDER_ID
            ) &&
            $access->checkRbacOrPositionPermissionAccess(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID
            )) {
            $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
            $user_filter = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $users
            );
        }

        // alphabetical navigation
        if ((int) $ilSetting->get('user_adm_alpha_nav')) {
            if (count($ilToolbar->getItems()) > 0) {
                $ilToolbar->addSeparator();
            }

            // alphabetical navigation
            $ai = new ilAlphabetInputGUI(
                "",
                "first"
            );
            $ai->setLetters(ilObjUser::getFirstLettersOfLastnames($user_filter));
            $ai->setParentCommand(
                $this,
                "chooseLetter"
            );
            $ai->setHighlighted($this->user_request->getLetter());
            $ilToolbar->addInputItem(
                $ai,
                true
            );
        }

        $utab = new ilUserTableGUI(
            $this,
            "view",
            ilUserTableGUI::MODE_USER_FOLDER,
            false
        );
        $utab->addFilterItemValue(
            'user_ids',
            $user_filter
        );
        $utab->getItems();

        $tpl->setContent($utab->getHTML());
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoCompleteObject(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->addUserAccessFilterCallable(\Closure::fromCallable([$this, 'filterUserIdsByRbacOrPositionOfCurrentUser']));
        // [$this, 'filterUserIdsByRbacOrPositionOfCurrentUser']);
        $auto->setSearchFields(array('login', 'firstname', 'lastname', 'email', 'second_email'));
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if ($this->user_request->getFetchAll()) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($this->user_request->getTerm());
        exit();
    }

    /**
     * @param int[] $user_ids
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $user_ids): array
    {
        global $DIC;

        $access = $DIC->access();
        return $access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_users',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID,
            $user_ids
        );
    }

    public function chooseLetterObject(): void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirect(
            $this,
            "view"
        );
    }

    /**
     * show possible subobjects (pulldown menu)
     * overwritten to prevent displaying of role templates in local role folders
     */
    protected function showPossibleSubObjects(): void
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $subobj = null;

        $d = $this->obj_definition->getCreatableSubObjects($this->object->getType());

        if (!$rbacsystem->checkAccess(
            'create_usr',
            $this->object->getRefId()
        )) {
            unset($d["usr"]);
        }

        if (count($d) > 0) {
            foreach ($d as $row) {
                $count = 0;
                if ($row["max"] > 0) {
                    //how many elements are present?
                    for ($i = 0, $iMax = count($this->data["ctrl"]); $i < $iMax; $i++) {
                        if ($this->data["ctrl"][$i]["type"] == $row["name"]) {
                            $count++;
                        }
                    }
                }
                if ($row["max"] == "" || $count < $row["max"]) {
                    $subobj[] = $row["name"];
                }
            }
        }

        if (is_array($subobj)) {
            //build form
            $opts = ilLegacyFormElementsUtil::formSelect(
                12,
                "new_type",
                $subobj
            );
            $this->tpl->setCurrentBlock("add_object");
            $this->tpl->setVariable(
                "SELECT_OBJTYPE",
                $opts
            );
            $this->tpl->setVariable(
                "BTN_NAME",
                "create"
            );
            $this->tpl->setVariable(
                "TXT_ADD",
                $this->lng->txt("add")
            );
            $this->tpl->parseCurrentBlock();
        }
    }

    public function cancelUserFolderActionObject(): void
    {
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }

    public function cancelSearchActionObject(): void
    {
        $this->ctrl->redirectByClass(
            'ilrepositorysearchgui',
            'showSearchResults'
        );
    }

    /**
     * Set the selected users active
     */
    public function confirmactivateObject(): void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_perm_write"),
                $this->ilias->error_obj->WARNING
            );
        }

        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setActive(
                    true,
                    $ilUser->getId()
                );
                $obj->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("user_activated"), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        }
    }

    /**
     * Set the selected users inactive
     */
    public function confirmdeactivateObject(): void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_perm_write"),
                $this->ilias->error_obj->WARNING
            );
        }
        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setActive(
                    false,
                    $ilUser->getId()
                );
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("user_deactivated"), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        }
    }

    protected function confirmaccessFreeObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_perm_write"),
                $this->ilias->error_obj->WARNING
            );
        }

        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(1);
                $obj->setTimeLimitFrom("");
                $obj->setTimeLimitUntil("");
                $obj->setTimeLimitMessage(0);
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("access_free_granted"), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        }
    }

    public function setAccessRestrictionObject(
        ?ilPropertyFormGUI $a_form = null,
        bool $a_from_search = false
    ): bool {
        if (!$a_form) {
            $a_form = $this->initAccessRestrictionForm($a_from_search);
        }
        $this->tpl->setContent($a_form->getHTML());

        // #10963
        return true;
    }

    protected function initAccessRestrictionForm(
        bool $a_from_search = false
    ): ?ilPropertyFormGUI {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->viewObject();
            return null;
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("time_limit_add_time_limit_for_selected"));
        $form->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                "confirmaccessRestrict"
            )
        );

        $from = new ilDateTimeInputGUI(
            $this->lng->txt("access_from"),
            "from"
        );
        $from->setShowTime(true);
        $from->setRequired(true);
        $form->addItem($from);

        $to = new ilDateTimeInputGUI(
            $this->lng->txt("access_until"),
            "to"
        );
        $to->setRequired(true);
        $to->setShowTime(true);
        $form->addItem($to);

        $form->addCommandButton(
            "confirmaccessRestrict",
            $this->lng->txt("confirm")
        );
        $form->addCommandButton(
            "view",
            $this->lng->txt("cancel")
        );

        foreach ($user_ids as $user_id) {
            $ufield = new ilHiddenInputGUI("id[]");
            $ufield->setValue($user_id);
            $form->addItem($ufield);
        }

        // return to search?
        if ($a_from_search || $this->user_request->getFrSearch()) {
            $field = new ilHiddenInputGUI("frsrch");
            $field->setValue(1);
            $form->addItem($field);
        }

        return $form;
    }

    /**
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function confirmaccessRestrictObject(): bool
    {
        $form = $this->initAccessRestrictionForm();
        if (!$form->checkInput()) {
            return $this->setAccessRestrictionObject($form);
        }

        $timefrom = $form->getItemByPostVar("from")->getDate()->get(IL_CAL_UNIX);
        $timeuntil = $form->getItemByPostVar("to")->getDate()->get(IL_CAL_UNIX);
        if ($timeuntil <= $timefrom) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("time_limit_not_valid"));
            return $this->setAccessRestrictionObject($form);
        }

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_perm_write"),
                $this->ilias->error_obj->WARNING
            );
        }
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(0);
                $obj->setTimeLimitFrom($timefrom);
                $obj->setTimeLimitUntil($timeuntil);
                $obj->setTimeLimitMessage(0);
                $obj->update();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("access_restricted"), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        }
        return false;
    }

    public function confirmdeleteObject(): void
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        // FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
        if (!$rbacsystem->checkAccess(
            'delete',
            $this->object->getRefId()
        )) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_delete"), true);
            $ilCtrl->redirect(
                $this,
                "view"
            );
        }

        $ids = $this->user_request->getIds();
        if (in_array(
            $ilUser->getId(),
            $ids
        )) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_delete_yourself"),
                $this->ilias->error_obj->WARNING
            );
        }

        // FOR ALL SELECTED OBJECTS
        foreach ($ids as $id) {
            // instatiate correct object class (usr)
            $obj = ilObjectFactory::getInstanceByObjId($id);
            $obj->delete();
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("user_deleted"), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        }
    }

    /**
     * Get selected items for table action
     * @return int[]
     */
    protected function getActionUserIds(): array
    {
        global $DIC;
        $access = $DIC->access();

        if ($this->getSelectAllPostArray()['select_cmd_all']) {
            include_once("./Services/User/classes/class.ilUserTableGUI.php");
            $utab = new ilUserTableGUI(
                $this,
                "view",
                ilUserTableGUI::MODE_USER_FOLDER,
                false
            );

            if (!$access->checkAccess(
                'read_users',
                '',
                USER_FOLDER_ID
            ) &&
                $access->checkRbacOrPositionPermissionAccess(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
                $filtered_users = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID,
                    $users
                );

                $utab->addFilterItemValue(
                    "user_ids",
                    $filtered_users
                );
            }

            return $utab->getUserIdsForFilter();
        } else {
            return $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $this->requested_ids
            );
        }
    }

    /**
     * Check if current user has access to manipulate user data
     */
    private function checkUserManipulationAccessBool(): bool
    {
        global $DIC;

        $access = $DIC->access();
        return $access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        );
    }

    /**
     * display activation confirmation screen
     */
    public function showActionConfirmation(
        string $action,
        bool $a_from_search = false
    ): bool {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->ilias->raiseError(
                $this->lng->txt("no_checkbox"),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (!$a_from_search) {
            $ilTabs->activateTab("obj_usrf");
        } else {
            $ilTabs->activateTab("search_user_extended");
        }

        if (strcmp(
            $action,
            "accessRestrict"
        ) == 0) {
            return $this->setAccessRestrictionObject(
                null,
                $a_from_search
            );
        }
        if (strcmp(
            $action,
            "mail"
        ) == 0) {
            $this->mailObject();
            return false;
        }
        if (strcmp($action, 'addToClipboard') === 0) {
            $this->addToClipboardObject();
            return false;
        }

        unset($this->data);

        if (!$a_from_search) {
            $cancel = "cancelUserFolderAction";
        } else {
            $cancel = "cancelSearchAction";
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_" . $action . "_sure"));
        $cgui->setCancel(
            $this->lng->txt("cancel"),
            $cancel
        );
        $cgui->setConfirm(
            $this->lng->txt("confirm"),
            "confirm" . $action
        );

        if ($a_from_search) {
            $cgui->addHiddenItem(
                "frsrch",
                1
            );
        }

        foreach ($user_ids as $id) {
            $user = new ilObjUser($id);

            $login = $user->getLastLogin();
            if (!$login) {
                $login = $this->lng->txt("never");
            } else {
                $login = ilDatePresentation::formatDate(
                    new ilDateTime(
                        $login,
                        IL_CAL_DATETIME
                    )
                );
            }

            $caption = $user->getFullname() . " (" . $user->getLogin() . ")" . ", " .
                $user->getEmail() . " -  " . $this->lng->txt("last_login") . ": " . $login;

            $cgui->addItem(
                "id[]",
                $id,
                $caption
            );
        }

        $this->tpl->setContent($cgui->getHTML());

        return true;
    }

    public function deleteUsersObject(): void
    {
        $this->showActionConfirmation("delete");
    }

    public function activateUsersObject(): void
    {
        $this->showActionConfirmation("activate");
    }

    public function deactivateUsersObject(): void
    {
        $this->showActionConfirmation("deactivate");
    }

    public function restrictAccessObject(): void
    {
        $this->showActionConfirmation("accessRestrict");
    }

    /**
     * Free access
     */
    public function freeAccessObject(): void
    {
        $this->showActionConfirmation("accessFree");
    }

    public function userActionObject(): void
    {
        $this->showActionConfirmation($this->user_request->getSelectedAction());
    }

    /**
     * display form for user import
     */
    public function importUserFormObject(): void
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC->ctrl();
        $access = $DIC->access();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $ilCtrl->getLinkTarget(
                $this,
                'view'
            )
        );
        if (
            !$rbacsystem->checkAccess('create_usr', $this->object->getRefId()) &&
            !$access->checkAccess('cat_administrate_users', '', $this->object->getRefId())
        ) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $this->initUserImportForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init user import form.
     */
    public function initUserImportForm(): void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->form = new ilPropertyFormGUI();

        // Import File
        $fi = new ilFileInputGUI(
            $lng->txt("import_file"),
            "importFile"
        );
        $fi->setSuffixes(array("xml", "zip"));
        $fi->setRequired(true);
        //$fi->enableFileNameSelection();
        //$fi->setInfo($lng->txt(""));
        $this->form->addItem($fi);

        $this->form->addCommandButton(
            "importUserRoleAssignment",
            $lng->txt("import")
        );
        $this->form->addCommandButton(
            "importCancelled",
            $lng->txt("cancel")
        );

        $this->form->setTitle($lng->txt("import_users"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    protected function inAdministration(): bool
    {
        return (strtolower($this->user_request->getBaseClass()) === 'iladministrationgui');
    }

    public function importCancelledObject(): void
    {
        global $DIC;
        $filesystem = $DIC->filesystem()->storage();

        // purge user import directory
        $import_dir = $this->getImportDir();
        if ($filesystem->hasDir($import_dir)) {
            $filesystem->deleteDir($import_dir);
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        } else {
            $this->ctrl->redirectByClass(
                'ilobjcategorygui',
                'listUsers'
            );
        }
    }

    public function getImportDir(): string
    {
        // For each user session a different directory must be used to prevent
        // that one user session overwrites the import data that another session
        // is currently importing.
        global $DIC;

        $ilUser = $DIC->user();

        $importDir = 'user_import/usr_' . $ilUser->getId() . '_' . session_id();

        return $importDir;
    }

    /**
     * display form for user import with new FileSystem implementation
     */
    public function importUserRoleAssignmentObject(): void
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $renderer = $DIC->ui()->renderer();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $ilCtrl->getLinkTarget(
                $this,
                'view'
            )
        );

        $this->initUserImportForm();
        if ($this->form->checkInput()) {
            $xml_file = $this->handleUploadedFiles();
            //importParser needs the full path to xml file
            $xml_file_full_path = ilFileUtils::getDataDir() . '/' . $xml_file;

            $form = $this->initUserRoleAssignmentForm($xml_file_full_path);

            $tpl->setContent($renderer->render($form));
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * @throws ilCtrlException
     */
    private function initUserRoleAssignmentForm(string $xml_file_full_path): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        global $DIC;

        $ilUser = $DIC->user();
        $rbacreview = $DIC->rbac()->review();
        $rbacsystem = $DIC->rbac()->system();
        $ui = $DIC->ui()->factory();
        $global_roles_assignment_info = null;
        $local_roles_assignment_info = null;

        $importParser = new ilUserImportParser(
            $xml_file_full_path,
            IL_VERIFY
        );
        $importParser->startParsing();

        $this->verifyXmlData($importParser);

        $xml_file_name = explode(
            "/",
            $xml_file_full_path
        );
        $roles_import_filename = $ui->input()->field()->text($this->lng->txt("import_file"))
                                    ->withDisabled(true)
                                    ->withValue(end($xml_file_name));

        $roles_import_count = $ui->input()->field()->numeric($this->lng->txt("num_users"))
                                 ->withDisabled(true)
                                 ->withValue($importParser->getUserCount());

        $importParser = new ilUserImportParser(
            $xml_file_full_path,
            IL_EXTRACT_ROLES
        );
        $importParser->startParsing();
        // Extract the roles
        $roles = $importParser->getCollectedRoles();

        // get global roles
        $all_gl_roles = $rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
        $gl_roles = array();
        $roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
        foreach ($all_gl_roles as $obj_data) {
            // check assignment permission if called from local admin
            if ($this->object->getRefId() != USER_FOLDER_ID) {
                if (!in_array(
                    SYSTEM_ROLE_ID,
                    $roles_of_user
                ) && !ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])) {
                    continue;
                }
            }
            // exclude anonymous role from list
            if ($obj_data["obj_id"] != ANONYMOUS_ROLE_ID) {
                // do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
                if ($obj_data["obj_id"] != SYSTEM_ROLE_ID or in_array(
                    SYSTEM_ROLE_ID,
                    $roles_of_user
                )) {
                    $gl_roles[$obj_data["obj_id"]] = $obj_data["title"];
                }
            }
        }

        // global roles
        $got_globals = false;
        $global_selects = array();
        foreach ($roles as $role_id => $role) {
            if ($role["type"] == "Global") {
                if (!$got_globals) {
                    $got_globals = true;

                    $global_roles_assignment_info = $ui->input()->field()->text(
                        $this->lng->txt("roles_of_import_global")
                    )
                                                       ->withDisabled(true)
                                                       ->withValue($this->lng->txt("assign_global_role"));
                }

                //select options for new form input to still have both ids
                $select_options = array();
                foreach ($gl_roles as $key => $value) {
                    $select_options[$role_id . "-" . $key] = $value;
                }

                // pre selection for role
                $pre_select = array_search(
                    $role["name"],
                    $select_options
                );
                if (!$pre_select) {
                    switch ($role["name"]) {
                        case "Administrator":    // ILIAS 2/3 Administrator
                            $pre_select = array_search(
                                "Administrator",
                                $select_options
                            );
                            break;

                        case "Autor":            // ILIAS 2 Author
                            $pre_select = array_search(
                                "User",
                                $select_options
                            );
                            break;

                        case "Lerner":            // ILIAS 2 Learner
                            $pre_select = array_search(
                                "User",
                                $select_options
                            );
                            break;

                        case "Gast":            // ILIAS 2 Guest
                            $pre_select = array_search(
                                "Guest",
                                $select_options
                            );
                            break;

                        default:
                            $pre_select = array_search(
                                "User",
                                $select_options
                            );
                            break;
                    }
                }

                $select = $ui->input()->field()->select(
                    $role["name"],
                    $select_options
                )
                             ->withValue($pre_select)
                             ->withRequired(true);
                $global_selects[] = $select;
            }
        }

        // Check if local roles need to be assigned
        $got_locals = false;
        foreach ($roles as $role_id => $role) {
            if ($role["type"] == "Local") {
                $got_locals = true;
                break;
            }
        }

        if ($got_locals) {
            $local_roles_assignment_info = $ui->input()->field()->text($this->lng->txt("roles_of_import_local"))
                                              ->withDisabled(true)
                                              ->withValue($this->lng->txt("assign_local_role"));

            // get local roles
            if ($this->object->getRefId() == USER_FOLDER_ID) {
                // The import function has been invoked from the user folder
                // object. In this case, we show only matching roles,
                // because the user folder object is considered the parent of all
                // local roles and may contains thousands of roles on large ILIAS
                // installations.
                $loc_roles = array();

                $roleMailboxSearch = new \ilRoleMailboxSearch(new \ilMailRfc822AddressParserFactory());
                foreach ($roles as $role_id => $role) {
                    if ($role["type"] == "Local") {
                        $searchName = (strpos($role['name'], '#') === 0) ? $role['name'] : '#' . $role['name'];
                        $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                        foreach ($matching_role_ids as $mid) {
                            if (!in_array(
                                $mid,
                                $loc_roles
                            )) {
                                $loc_roles[] = $mid;
                            }
                        }
                    }
                }
            } else {
                // The import function has been invoked from a locally
                // administrated category. In this case, we show all roles
                // contained in the subtree of the category.
                $loc_roles = $rbacreview->getAssignableRolesInSubtree($this->object->getRefId());
            }
            $l_roles = array();

            // create a search array with  .
            foreach ($loc_roles as $key => $loc_role) {
                // fetch context path of role
                $rolf = $rbacreview->getFoldersAssignedToRole(
                    $loc_role,
                    true
                );

                // only process role folders that are not set to status "deleted"
                // and for which the user has write permissions.
                // We also don't show the roles which are in the ROLE_FOLDER_ID folder.
                // (The ROLE_FOLDER_ID folder contains the global roles).
                if (
                    !$rbacreview->isDeleted($rolf[0]) &&
                    $rbacsystem->checkAccess(
                        'write',
                        $rolf[0]
                    ) &&
                    $rolf[0] != ROLE_FOLDER_ID
                ) {
                    // A local role is only displayed, if it is contained in the subtree of
                    // the localy administrated category. If the import function has been
                    // invoked from the user folder object, we show all local roles, because
                    // the user folder object is considered the parent of all local roles.
                    // Thus, if we start from the user folder object, we initialize the
                    // isInSubtree variable with true. In all other cases it is initialized
                    // with false, and only set to true if we find the object id of the
                    // locally administrated category in the tree path to the local role.
                    $isInSubtree = $this->object->getRefId() == USER_FOLDER_ID;

                    $path_array = array();
                    if ($this->tree->isInTree($rolf[0])) {
                        // Create path. Paths which have more than 4 segments
                        // are truncated in the middle.
                        $tmpPath = $this->tree->getPathFull($rolf[0]);
                        $tmpPath[] = $rolf[0];//adds target item to list

                        for ($i = 1, $n = count($tmpPath) - 1; $i < $n; $i++) {
                            if ($i < 3 || $i > $n - 3) {
                                $path_array[] = $tmpPath[$i]['title'];
                            } elseif ($i == 3 || $i == $n - 3) {
                                $path_array[] = '...';
                            }

                            $isInSubtree |= $tmpPath[$i]['obj_id'] == $this->object->getId();
                        }
                        //revert this path for a better readability in dropdowns #18306
                        $path = implode(
                            " < ",
                            array_reverse($path_array)
                        );
                    } else {
                        $path = "<b>Rolefolder " . $rolf[0] . " not found in tree! (Role " . $loc_role . ")</b>";
                    }
                    $roleMailboxAddress = (new \ilRoleMailboxAddress($loc_role))->value();
                    $l_roles[$loc_role] = $roleMailboxAddress . ', ' . $path;
                }
            } //foreach role

            natcasesort($l_roles);
            $l_roles["ignore"] = $this->lng->txt("usrimport_ignore_role");

            $roleMailboxSearch = new \ilRoleMailboxSearch(new \ilMailRfc822AddressParserFactory());
            $local_selects = [];
            foreach ($roles as $role_id => $role) {
                if ($role["type"] == "Local") {
                    /*$this->tpl->setCurrentBlock("local_role");
                    $this->tpl->setVariable("TXT_IMPORT_LOCAL_ROLE", $role["name"]);*/
                    $searchName = (strpos($role['name'], '#') === 0) ? $role['name'] : '#' . $role['name'];
                    $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                    $pre_select = count($matching_role_ids) == 1 ? $role_id . "-" . $matching_role_ids[0] : "ignore";

                    $selectable_roles = [];
                    if ($this->object->getRefId() == USER_FOLDER_ID) {
                        // There are too many roles in a large ILIAS installation
                        // that's why whe show only a choice with the the option "ignore",
                        // and the matching roles.
                        $selectable_roles["ignore"] = $this->lng->txt("usrimport_ignore_role");
                        foreach ($matching_role_ids as $id) {
                            $selectable_roles[$role_id . "-" . $id] = $l_roles[$id];
                        }
                    } else {
                        foreach ($l_roles as $local_role_id => $value) {
                            if ($local_role_id !== "ignore") {
                                $selectable_roles[$role_id . "-" . $local_role_id] = $value;
                            }
                        }
                    }

                    if (count($selectable_roles) > 0) {
                        $select = $ui->input()->field()
                            ->select($role["name"], $selectable_roles)
                            ->withRequired(true);
                        if (array_key_exists($pre_select, $selectable_roles)) {
                            $select = $select->withValue($pre_select);
                        }
                        $local_selects[] = $select;
                    }
                }
            }
        }

        $handlers = array(
            IL_IGNORE_ON_CONFLICT => $this->lng->txt("ignore_on_conflict"),
            IL_UPDATE_ON_CONFLICT => $this->lng->txt("update_on_conflict")
        );

        $conflict_action_select = $ui->input()->field()->select(
            $this->lng->txt("conflict_handling"),
            $handlers,
            str_replace(
                '\n',
                '<br>',
                $this->lng->txt("usrimport_conflict_handling_info")
            )
        )
                                     ->withValue(IL_IGNORE_ON_CONFLICT)
                                     ->withRequired(true);

        // new account mail
        $this->lng->loadLanguageModule("mail");
        $amail = ilObjUserFolder::_lookupNewAccountMail($this->lng->getDefaultLanguage());
        $mail_section = null;
        if (trim($amail["body"] ?? "") != "" && trim($amail["subject"] ?? "") != "") {
            $send_checkbox = $ui->input()->field()->checkbox($this->lng->txt("user_send_new_account_mail"))
                                ->withValue(true);

            $mail_section = $ui->input()->field()->section(
                [$send_checkbox],
                $this->lng->txt("mail_account_mail")
            );
        }

        $file_info_section = $ui->input()->field()->section(
            [
                "filename" => $roles_import_filename,
                "import_count" => $roles_import_count,
            ],
            $this->lng->txt("file_info")
        );

        $form_action = $DIC->ctrl()->getFormActionByClass('ilObjUserFolderGui', 'importUsers');

        $form_elements = [
            "file_info" => $file_info_section
        ];

        if (!empty($global_selects)) {
            $global_role_info_section = $ui->input()
                ->field()
                ->section([$global_roles_assignment_info], $this->lng->txt("global_role_assignment"));
            $global_role_selection_section = $ui->input()->field()->section($global_selects, "");
            $form_elements["global_role_info"] = $global_role_info_section;
            $form_elements["global_role_selection"] = $global_role_selection_section;
        }

        if (!empty($local_selects)) {
            $local_role_info_section = $ui->input()->field()->section(
                [$local_roles_assignment_info],
                $this->lng->txt("local_role_assignment")
            );
            $local_role_selection_section = $ui->input()->field()->section(
                $local_selects,
                ""
            );

            $form_elements["local_role_info"] = $local_role_info_section;
            $form_elements["local_role_selection"] = $local_role_selection_section;
        }

        $form_elements["conflict_action"] = $ui->input()->field()->section([$conflict_action_select], "");

        if ($mail_section !== null) {
            $form_elements["send_mail"] = $mail_section;
        }

        return $ui->input()->container()->form()->standard(
            $form_action,
            $form_elements
        );
    }

    /**
     * Handles uploaded zip/xmp files with Filesystem implementation
     */
    private function handleUploadedFiles(): string
    {
        global $DIC;

        $ilUser = $DIC->user();
        $subdir = "";
        $xml_file = "";

        $upload = $DIC->upload();

        $filesystem = $DIC->filesystem()->storage();
        $import_dir = $this->getImportDir();

        if (!$upload->hasBeenProcessed()) {
            $upload->process();
        }

        // recreate user import directory
        if ($filesystem->hasDir($import_dir)) {
            $filesystem->deleteDir($import_dir);
        }
        $filesystem->createDir($import_dir);

        foreach ($upload->getResults() as $single_file_upload) {
            $file_name = $single_file_upload->getName();
            $parts = pathinfo($file_name);

            //check if upload status is ok
            if (!$single_file_upload->isOK()) {
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt("no_import_file_found"),
                    $this->ilias->error_obj->MESSAGE
                );
            }

            // move uploaded file to user import directory
            $upload->moveFilesTo(
                $import_dir,
                \ILIAS\FileUpload\Location::STORAGE
            );

            // handle zip file
            if ($single_file_upload->getMimeType() == "application/zip") {
                // Workaround: unzip function needs full path to file. Should be replaced once Filesystem has own unzip implementation
                $full_path = ilFileUtils::getDataDir() . '/user_import/usr_' . $ilUser->getId() . '_' . session_id() . "/" . $file_name;
                ilFileUtils::unzip($full_path);

                $xml_file = null;
                $file_list = $filesystem->listContents($import_dir);

                foreach ($file_list as $key => $a_file) {
                    if (substr(
                        $a_file->getPath(),
                        -4
                    ) == '.xml') {
                        unset($file_list[$key]);
                        $xml_file = $a_file->getPath();
                        break;
                    }
                }

                //Removing all files except the one to be imported, to make sure to get the right one in import-function
                foreach ($file_list as $a_file) {
                    $filesystem->delete($a_file->getPath());
                }

                if (is_null($xml_file)) {
                    $subdir = basename(
                        $parts["basename"],
                        "." . $parts["extension"]
                    );
                    $xml_file = $import_dir . "/" . $subdir . "/" . $subdir . ".xml";
                }
            } // handle xml file
            else {
                $a = $filesystem->listContents($import_dir);
                $file = end($a);
                $xml_file = $file->getPath();
            }

            // check xml file
            if (!$filesystem->has($xml_file)) {
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt("no_xml_file_found_in_zip")
                    . " " . $subdir . "/" . $subdir . ".xml",
                    $this->ilias->error_obj->MESSAGE
                );
            }
        }

        return $xml_file;
    }

    public function verifyXmlData(ilUserImportParser $importParser): void
    {
        global $DIC;

        $filesystem = $DIC->filesystem()->storage();

        $import_dir = $this->getImportDir();
        switch ($importParser->getErrorLevel()) {
            case IL_IMPORT_SUCCESS:
                break;
            case IL_IMPORT_WARNING:
                $this->tpl->setVariable(
                    "IMPORT_LOG",
                    $importParser->getProtocolAsHTML($this->lng->txt("verification_warning_log"))
                );
                break;
            case IL_IMPORT_FAILURE:
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt("verification_failed") . $importParser->getProtocolAsHTML(
                        $this->lng->txt("verification_failure_log")
                    ),
                    $this->ilias->error_obj->MESSAGE
                );
                return;
        }
    }

    /**
     * Import Users with new form implementation
     */
    public function importUsersObject(): void
    {
        global $DIC;

        $result = [];
        $xml_file = "";
        $ilUser = $DIC->user();
        $request = $DIC->http()->request();
        $rbacreview = $DIC->rbac()->review();
        $rbacsystem = $DIC->rbac()->system();
        $filesystem = $DIC->filesystem()->storage();
        $import_dir = $this->getImportDir();

        $file_list = $filesystem->listContents($import_dir);

        //Make sure there's only one file in the import directory at this point
        if (count($file_list) > 1) {
            $filesystem->deleteDir($import_dir);
            $this->ilias->raiseError(
                $this->lng->txt("usrimport_wrong_file_count"),
                $this->ilias->error_obj->MESSAGE
            );
            if ($this->inAdministration()) {
                $this->ctrl->redirect(
                    $this,
                    "view"
                );
            } else {
                $this->ctrl->redirectByClass(
                    'ilobjcategorygui',
                    'listUsers'
                );
            }
        } else {
            $xml_file = $file_list[0]->getPath();
        }

        //Need full path to xml file to initialise form
        $xml_path = ilFileUtils::getDataDir() . '/' . $xml_file;

        if ($request->getMethod() == "POST") {
            $form = $this->initUserRoleAssignmentForm($xml_path)->withRequest($request);
            $result = $form->getData();
        } else {
            $this->ilias->raiseError(
                $this->lng->txt("usrimport_form_not_evaluabe"),
                $this->ilias->error_obj->MESSAGE
            );
            if ($this->inAdministration()) {
                $this->ctrl->redirect(
                    $this,
                    "view"
                );
            } else {
                $this->ctrl->redirectByClass(
                    'ilobjcategorygui',
                    'listUsers'
                );
            }
        }

        $rule = $result["conflict_action"][0];

        //If local roles exist, merge the roles that are to be assigned, otherwise just take the array that has global roles
        $local_role_selection = (array) ($result['local_role_selection'] ?? []);
        $global_role_selection = (array) ($result['global_role_selection'] ?? []);
        $roles = array_merge(
            $local_role_selection,
            $global_role_selection
        );

        $role_assignment = array();
        foreach ($roles as $value) {
            $keys = explode(
                "-",
                $value
            );
            $role_assignment[$keys[0]] = $keys[1];
        }

        $importParser = new ilUserImportParser(
            $xml_path,
            IL_USER_IMPORT,
            $rule
        );
        $importParser->setFolderId($this->getUserOwnerId());

        // Catch hack attempts
        // We check here again, if the role folders are in the tree, and if the
        // user has permission on the roles.
        if (!empty($role_assignment)) {
            $global_roles = $rbacreview->getGlobalRoles();
            $roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
            foreach ($role_assignment as $role_id) {
                if ($role_id != "") {
                    if (in_array(
                        $role_id,
                        $global_roles
                    )) {
                        if (!in_array(
                            SYSTEM_ROLE_ID,
                            $roles_of_user
                        )) {
                            if (($role_id == SYSTEM_ROLE_ID && !in_array(
                                SYSTEM_ROLE_ID,
                                $roles_of_user
                            ))
                                || ($this->object->getRefId() != USER_FOLDER_ID
                                    && !ilObjRole::_getAssignUsersStatus($role_id))
                            ) {
                                $filesystem->deleteDir($import_dir);
                                $this->ilias->raiseError(
                                    $this->lng->txt("usrimport_with_specified_role_not_permitted"),
                                    $this->ilias->error_obj->MESSAGE
                                );
                            }
                        }
                    } else {
                        $rolf = $rbacreview->getFoldersAssignedToRole(
                            $role_id,
                            true
                        );
                        if ($rbacreview->isDeleted($rolf[0])
                            || !$rbacsystem->checkAccess(
                                'write',
                                $rolf[0]
                            )) {
                            $filesystem->deleteDir($import_dir);
                            $this->ilias->raiseError(
                                $this->lng->txt("usrimport_with_specified_role_not_permitted"),
                                $this->ilias->error_obj->MESSAGE
                            );
                            return;
                        }
                    }
                }
            }
        }

        if (isset($result['send_mail'])) {
            $importParser->setSendMail($result['send_mail'][0]);
        }

        $importParser->setRoleAssignment($role_assignment);
        $importParser->startParsing();

        // purge user import directory
        $filesystem->deleteDir($import_dir);

        switch ($importParser->getErrorLevel()) {
            case IL_IMPORT_SUCCESS:
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("user_imported"), true);
                break;
            case IL_IMPORT_WARNING:
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("user_imported_with_warnings") . $importParser->getProtocolAsHTML(
                    $this->lng->txt("import_warning_log")
                ), true);
                break;
            case IL_IMPORT_FAILURE:
                $this->ilias->raiseError(
                    $this->lng->txt("user_import_failed")
                    . $importParser->getProtocolAsHTML($this->lng->txt("import_failure_log")),
                    $this->ilias->error_obj->MESSAGE
                );
                break;
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                "view"
            );
        } else {
            $this->ctrl->redirectByClass(
                'ilobjcategorygui',
                'listUsers'
            );
        }
    }

    /**
     * Show user account general settings
     */
    protected function generalSettingsObject(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->initFormGeneralSettings();

        $aset = ilUserAccountSettings::getInstance();

        $show_blocking_time_in_days = $ilSetting->get('loginname_change_blocking_time') / 86400;
        $show_blocking_time_in_days = (float) $show_blocking_time_in_days;

        $security = ilSecuritySettings::_getInstance();

        $settings = [
            'lua' => $aset->isLocalUserAdministrationEnabled(),
            'lrua' => $aset->isUserAccessRestricted(),
            'allow_change_loginname' => (bool) $ilSetting->get('allow_change_loginname'),
            'create_history_loginname' => (bool) $ilSetting->get('create_history_loginname'),
            'reuse_of_loginnames' => (bool) $ilSetting->get('reuse_of_loginnames'),
            'loginname_change_blocking_time' => $show_blocking_time_in_days,
            'user_adm_alpha_nav' => (int) $ilSetting->get('user_adm_alpha_nav'),
            // 'user_ext_profiles' => (int)$ilSetting->get('user_ext_profiles')
            'user_reactivate_code' => (int) $ilSetting->get('user_reactivate_code'),
            'user_own_account' => (int) $ilSetting->get('user_delete_own_account'),
            'user_own_account_email' => $ilSetting->get('user_delete_own_account_email'),
            'tos_withdrawal_usr_deletion' => (bool) $ilSetting->get('tos_withdrawal_usr_deletion'),

            'session_handling_type' => $ilSetting->get(
                'session_handling_type',
                ilSession::SESSION_HANDLING_FIXED
            ),
            'session_reminder_enabled' => $ilSetting->get('session_reminder_enabled'),
            'session_max_count' => $ilSetting->get(
                'session_max_count',
                ilSessionControl::DEFAULT_MAX_COUNT
            ),
            'session_min_idle' => $ilSetting->get(
                'session_min_idle',
                ilSessionControl::DEFAULT_MIN_IDLE
            ),
            'session_max_idle' => $ilSetting->get(
                'session_max_idle',
                ilSessionControl::DEFAULT_MAX_IDLE
            ),
            'session_max_idle_after_first_request' => $ilSetting->get(
                'session_max_idle_after_first_request',
                ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST
            ),

            'login_max_attempts' => $security->getLoginMaxAttempts(),
            'ps_prevent_simultaneous_logins' => (int) $security->isPreventionOfSimultaneousLoginsEnabled(),
            'password_assistance' => (bool) $ilSetting->get("password_assistance"),
            'letter_avatars' => (int) $ilSetting->get('letter_avatars'),
            'password_change_on_first_login_enabled' => $security->isPasswordChangeOnFirstLoginEnabled() ? 1 : 0,
            'password_max_age' => $security->getPasswordMaxAge()
        ];

        $passwordPolicySettings = $this->getPasswordPolicySettingsMap($security);
        $this->form->setValuesByArray(
            array_merge(
                $settings,
                $passwordPolicySettings,
                ['pw_policy_hash' => md5(
                    implode(
                        '',
                        $passwordPolicySettings
                    )
                )
                ]
            )
        );

        $this->tpl->setContent($this->form->getHTML());
    }

    private function getPasswordPolicySettingsMap(\ilSecuritySettings $security): array // Missing array type.
    {
        return [
            'password_must_not_contain_loginame' => $security->getPasswordMustNotContainLoginnameStatus() ? 1 : 0,
            'password_chars_and_numbers_enabled' => $security->isPasswordCharsAndNumbersEnabled() ? 1 : 0,
            'password_special_chars_enabled' => $security->isPasswordSpecialCharsEnabled() ? 1 : 0,
            'password_min_length' => $security->getPasswordMinLength(),
            'password_max_length' => $security->getPasswordMaxLength(),
            'password_ucase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
            'password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
        ];
    }

    /**
     * Save user account settings
     */
    public function saveGeneralSettingsObject(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->initFormGeneralSettings();
        if ($this->form->checkInput()) {
            $valid = true;
            if (!strlen($this->form->getInput('loginname_change_blocking_time'))) {
                $valid = false;
                $this->form->getItemByPostVar('loginname_change_blocking_time')
                           ->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
            }

            $security = ilSecuritySettings::_getInstance();

            // account security settings
            $security->setPasswordCharsAndNumbersEnabled(
                (bool) $this->form->getInput("password_chars_and_numbers_enabled")
            );
            $security->setPasswordSpecialCharsEnabled(
                (bool) $this->form->getInput("password_special_chars_enabled")
            );
            $security->setPasswordMinLength(
                (int) $this->form->getInput("password_min_length")
            );
            $security->setPasswordMaxLength(
                (int) $this->form->getInput("password_max_length")
            );
            $security->setPasswordNumberOfUppercaseChars(
                (int) $this->form->getInput("password_ucase_chars_num")
            );
            $security->setPasswordNumberOfLowercaseChars(
                (int) $this->form->getInput("password_lowercase_chars_num")
            );
            $security->setPasswordMaxAge(
                (int) $this->form->getInput("password_max_age")
            );
            $security->setLoginMaxAttempts(
                (int) $this->form->getInput("login_max_attempts")
            );
            $security->setPreventionOfSimultaneousLogins(
                (bool) $this->form->getInput("ps_prevent_simultaneous_logins")
            );
            $security->setPasswordChangeOnFirstLoginEnabled(
                (bool) $this->form->getInput("password_change_on_first_login_enabled")
            );
            $security->setPasswordMustNotContainLoginnameStatus(
                (bool) $this->form->getInput("password_must_not_contain_loginame")
            );

            if (!is_null($security->validate($this->form))) {
                $valid = false;
            }

            if ($valid) {
                $security->save();

                ilUserAccountSettings::getInstance()->enableLocalUserAdministration($this->form->getInput('lua'));
                ilUserAccountSettings::getInstance()->restrictUserAccess($this->form->getInput('lrua'));
                ilUserAccountSettings::getInstance()->update();

                $ilSetting->set(
                    'allow_change_loginname',
                    (int) $this->form->getInput('allow_change_loginname')
                );
                $ilSetting->set(
                    'create_history_loginname',
                    (int) $this->form->getInput('create_history_loginname')
                );
                $ilSetting->set(
                    'reuse_of_loginnames',
                    (int) $this->form->getInput('reuse_of_loginnames')
                );
                $save_blocking_time_in_seconds = (int) ($this->form->getInput(
                    'loginname_change_blocking_time'
                ) * 86400);
                $ilSetting->set(
                    'loginname_change_blocking_time',
                    $save_blocking_time_in_seconds
                );
                $ilSetting->set(
                    'user_adm_alpha_nav',
                    (int) $this->form->getInput('user_adm_alpha_nav')
                );
                $ilSetting->set(
                    'user_reactivate_code',
                    (int) $this->form->getInput('user_reactivate_code')
                );

                $ilSetting->set(
                    'user_delete_own_account',
                    (int) $this->form->getInput('user_own_account')
                );
                $ilSetting->set(
                    'user_delete_own_account_email',
                    $this->form->getInput('user_own_account_email')
                );
                $ilSetting->set(
                    'tos_withdrawal_usr_deletion',
                    (string) ((int) $this->form->getInput('tos_withdrawal_usr_deletion'))
                );

                $ilSetting->set(
                    "password_assistance",
                    $this->form->getInput("password_assistance")
                );

                // BEGIN SESSION SETTINGS
                $ilSetting->set(
                    'session_handling_type',
                    (int) $this->form->getInput('session_handling_type')
                );

                if ($this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_FIXED) {
                    $ilSetting->set(
                        'session_reminder_enabled',
                        $this->form->getInput('session_reminder_enabled')
                    );
                } elseif ($this->form->getInput(
                    'session_handling_type'
                ) == ilSession::SESSION_HANDLING_LOAD_DEPENDENT) {
                    if (
                        $ilSetting->get(
                            'session_allow_client_maintenance',
                            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
                        )
                    ) {
                        // has to be done BEFORE updating the setting!
                        ilSessionStatistics::updateLimitLog((int) $this->form->getInput('session_max_count'));

                        $ilSetting->set(
                            'session_max_count',
                            (int) $this->form->getInput('session_max_count')
                        );
                        $ilSetting->set(
                            'session_min_idle',
                            (int) $this->form->getInput('session_min_idle')
                        );
                        $ilSetting->set(
                            'session_max_idle',
                            (int) $this->form->getInput('session_max_idle')
                        );
                        $ilSetting->set(
                            'session_max_idle_after_first_request',
                            (int) $this->form->getInput('session_max_idle_after_first_request')
                        );
                    }
                }
                // END SESSION SETTINGS
                $ilSetting->set(
                    'letter_avatars',
                    (int) $this->form->getInput('letter_avatars')
                );

                $requestPasswordReset = false;
                if ($this->form->getInput('pw_policy_hash')) {
                    $oldSettingsHash = $this->form->getInput('pw_policy_hash');
                    $currentSettingsHash = md5(
                        implode(
                            '',
                            $this->getPasswordPolicySettingsMap($security)
                        )
                    );
                    $requestPasswordReset = ($oldSettingsHash !== $currentSettingsHash);
                }

                if ($requestPasswordReset) {
                    $this->ctrl->redirect(
                        $this,
                        'askForUserPasswordReset'
                    );
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
                }
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    protected function forceUserPasswordResetObject(): void
    {
        \ilUserPasswordManager::getInstance()->resetLastPasswordChangeForLocalUsers();
        $this->lng->loadLanguageModule('ps');

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_passwd_policy_change_force_user_reset_succ'), true);
        $this->ctrl->redirect(
            $this,
            'generalSettings'
        );
    }

    protected function askForUserPasswordResetObject(): void
    {
        $this->lng->loadLanguageModule('ps');

        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                'askForUserPasswordReset'
            )
        );
        $confirmation->setHeaderText($this->lng->txt('ps_passwd_policy_changed_force_user_reset'));
        $confirmation->setConfirm(
            $this->lng->txt('yes'),
            'forceUserPasswordReset'
        );
        $confirmation->setCancel(
            $this->lng->txt('no'),
            'generalSettings'
        );

        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     * init general settings form
     */
    protected function initFormGeneralSettings(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('general_settings');

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                'saveGeneralSettings'
            )
        );

        $this->form->setTitle($this->lng->txt('general_settings'));

        $lua = new ilCheckboxInputGUI(
            $this->lng->txt('enable_local_user_administration'),
            'lua'
        );
        $lua->setInfo($this->lng->txt('enable_local_user_administration_info'));
        $lua->setValue(1);
        $this->form->addItem($lua);

        $lrua = new ilCheckboxInputGUI(
            $this->lng->txt('restrict_user_access'),
            'lrua'
        );
        $lrua->setInfo($this->lng->txt('restrict_user_access_info'));
        $lrua->setValue(1);
        $this->form->addItem($lrua);

        // enable alphabetical navigation in user administration
        $alph = new ilCheckboxInputGUI(
            $this->lng->txt('user_adm_enable_alpha_nav'),
            'user_adm_alpha_nav'
        );
        //$alph->setInfo($this->lng->txt('restrict_user_access_info'));
        $alph->setValue(1);
        $this->form->addItem($alph);

        // account codes
        $code = new ilCheckboxInputGUI(
            $this->lng->txt("user_account_code_setting"),
            "user_reactivate_code"
        );
        $code->setInfo($this->lng->txt('user_account_code_setting_info'));
        $this->form->addItem($code);

        // delete own account
        $own = new ilCheckboxInputGUI(
            $this->lng->txt("user_allow_delete_own_account"),
            "user_own_account"
        );
        $this->form->addItem($own);
        $own_email = new ilEMailInputGUI(
            $this->lng->txt("user_delete_own_account_notification_email"),
            "user_own_account_email"
        );
        $own->addSubItem($own_email);

        $withdrawalProvokesDeletion = new ilCheckboxInputGUI(
            $this->lng->txt('tos_withdrawal_usr_deletion'),
            'tos_withdrawal_usr_deletion'
        );
        $withdrawalProvokesDeletion->setInfo($this->lng->txt('tos_withdrawal_usr_deletion_info'));
        $withdrawalProvokesDeletion->setValue('1');
        $this->form->addItem($withdrawalProvokesDeletion);

        // BEGIN SESSION SETTINGS

        // create session handling radio group
        $ssettings = new ilRadioGroupInputGUI(
            $this->lng->txt('sess_mode'),
            'session_handling_type'
        );

        // first option, fixed session duration
        $fixed = new ilRadioOption(
            $this->lng->txt('sess_fixed_duration'),
            ilSession::SESSION_HANDLING_FIXED
        );

        // create session reminder subform
        $cb = new ilCheckboxInputGUI(
            $this->lng->txt("session_reminder"),
            "session_reminder_enabled"
        );
        $expires = ilSession::getSessionExpireValue();
        $time = ilDatePresentation::secondsToString(
            $expires,
            true
        );
        $cb->setInfo(
            $this->lng->txt("session_reminder_info") . "<br />" .
            sprintf(
                $this->lng->txt('session_reminder_session_duration'),
                $time
            )
        );
        $fixed->addSubItem($cb);

        // add session handling to radio group
        $ssettings->addOption($fixed);

        // second option, session control
        $ldsh = new ilRadioOption(
            $this->lng->txt('sess_load_dependent_session_handling'),
            ilSession::SESSION_HANDLING_LOAD_DEPENDENT
        );

        // add session control subform

        // this is the max count of active sessions
        // that are getting started simlutanously
        $sub_ti = new ilTextInputGUI(
            $this->lng->txt('session_max_count'),
            'session_max_count'
        );
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_count_info'));
        if (!$ilSetting->get(
            'session_allow_client_maintenance',
            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        )) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // after this (min) idle time the session can be deleted,
        // if there are further requests for new sessions,
        // but max session count is reached yet
        $sub_ti = new ilTextInputGUI(
            $this->lng->txt('session_min_idle'),
            'session_min_idle'
        );
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_min_idle_info'));
        if (!$ilSetting->get(
            'session_allow_client_maintenance',
            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        )) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // after this (max) idle timeout the session expires
        // and become invalid, so it is not considered anymore
        // when calculating current count of active sessions
        $sub_ti = new ilTextInputGUI(
            $this->lng->txt('session_max_idle'),
            'session_max_idle'
        );
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_idle_info'));
        if (!$ilSetting->get(
            'session_allow_client_maintenance',
            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        )) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // this is the max duration that can elapse between the first and the secnd
        // request to the system before the session is immidietly deleted
        $sub_ti = new ilTextInputGUI(
            $this->lng->txt('session_max_idle_after_first_request'),
            'session_max_idle_after_first_request'
        );
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_idle_after_first_request_info'));
        if (!$ilSetting->get(
            'session_allow_client_maintenance',
            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        )) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // add session control to radio group
        $ssettings->addOption($ldsh);

        // add radio group to form
        if ($ilSetting->get(
            'session_allow_client_maintenance',
            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        )) {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $this->form->addItem($ssettings);
        } else {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $ti = new ilNonEditableValueGUI(
                $this->lng->txt('session_config'),
                "session_config"
            );
            $ti->setValue($this->lng->txt('session_config_maintenance_disabled'));
            $ssettings->setDisabled(true);
            $ti->addSubItem($ssettings);
            $this->form->addItem($ti);
        }

        // END SESSION SETTINGS

        $this->lng->loadLanguageModule('ps');

        $pass = new ilFormSectionHeaderGUI();
        $pass->setTitle($this->lng->txt('ps_password_settings'));
        $this->form->addItem($pass);

        $check = new ilCheckboxInputGUI(
            $this->lng->txt('ps_password_change_on_first_login_enabled'),
            'password_change_on_first_login_enabled'
        );
        $check->setInfo($this->lng->txt('ps_password_change_on_first_login_enabled_info'));
        $this->form->addItem($check);

        $check = new ilCheckboxInputGUI(
            $this->lng->txt('ps_password_must_not_contain_loginame'),
            'password_must_not_contain_loginame'
        );
        $check->setInfo($this->lng->txt('ps_password_must_not_contain_loginame_info'));
        $this->form->addItem($check);

        $check = new ilCheckboxInputGUI(
            $this->lng->txt('ps_password_chars_and_numbers_enabled'),
            'password_chars_and_numbers_enabled'
        );
        //$check->setOptionTitle($this->lng->txt('ps_password_chars_and_numbers_enabled'));
        $check->setInfo($this->lng->txt('ps_password_chars_and_numbers_enabled_info'));
        $this->form->addItem($check);

        $check = new ilCheckboxInputGUI(
            $this->lng->txt('ps_password_special_chars_enabled'),
            'password_special_chars_enabled'
        );
        //$check->setOptionTitle($this->lng->txt('ps_password_special_chars_enabled'));
        $check->setInfo($this->lng->txt('ps_password_special_chars_enabled_info'));
        $this->form->addItem($check);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_password_min_length'),
            'password_min_length'
        );
        $text->setInfo($this->lng->txt('ps_password_min_length_info'));
        $text->setSize(1);
        $text->setMaxLength(2);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_password_max_length'),
            'password_max_length'
        );
        $text->setInfo($this->lng->txt('ps_password_max_length_info'));
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_password_uppercase_chars_num'),
            'password_ucase_chars_num'
        );
        $text->setInfo($this->lng->txt('ps_password_uppercase_chars_num_info'));
        $text->setMinValue(0);
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_password_lowercase_chars_num'),
            'password_lowercase_chars_num'
        );
        $text->setInfo($this->lng->txt('ps_password_lowercase_chars_num_info'));
        $text->setMinValue(0);
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_password_max_age'),
            'password_max_age'
        );
        $text->setInfo($this->lng->txt('ps_password_max_age_info'));
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        // password assistance
        $cb = new ilCheckboxInputGUI(
            $this->lng->txt("enable_password_assistance"),
            "password_assistance"
        );
        $cb->setInfo($this->lng->txt("password_assistance_info"));
        $this->form->addItem($cb);

        $pass = new ilFormSectionHeaderGUI();
        $pass->setTitle($this->lng->txt('ps_security_protection'));
        $this->form->addItem($pass);

        $text = new ilNumberInputGUI(
            $this->lng->txt('ps_login_max_attempts'),
            'login_max_attempts'
        );
        $text->setInfo($this->lng->txt('ps_login_max_attempts_info'));
        $text->setSize(1);
        $text->setMaxLength(2);
        $this->form->addItem($text);

        // prevent login from multiple pcs at the same time
        $objCb = new ilCheckboxInputGUI(
            $this->lng->txt('ps_prevent_simultaneous_logins'),
            'ps_prevent_simultaneous_logins'
        );
        $objCb->setValue(1);
        $objCb->setInfo($this->lng->txt('ps_prevent_simultaneous_logins_info'));
        $this->form->addItem($objCb);

        $log = new ilFormSectionHeaderGUI();
        $log->setTitle($this->lng->txt('loginname_settings'));
        $this->form->addItem($log);

        $chbChangeLogin = new ilCheckboxInputGUI(
            $this->lng->txt('allow_change_loginname'),
            'allow_change_loginname'
        );
        $chbChangeLogin->setValue(1);
        $this->form->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI(
            $this->lng->txt('history_loginname'),
            'create_history_loginname'
        );
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue(1);

        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI(
            $this->lng->txt('reuse_of_loginnames_contained_in_history'),
            'reuse_of_loginnames'
        );
        $chbReuseLoginnames->setValue(1);
        $chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));

        $chbChangeLogin->addSubItem($chbReuseLoginnames);
        $chbChangeBlockingTime = new ilNumberInputGUI(
            $this->lng->txt('loginname_change_blocking_time'),
            'loginname_change_blocking_time'
        );
        $chbChangeBlockingTime->allowDecimals(true);
        $chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
        $chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
        $chbChangeBlockingTime->setSize(10);
        $chbChangeBlockingTime->setMaxLength(10);
        $chbChangeLogin->addSubItem($chbChangeBlockingTime);

        $la = new ilCheckboxInputGUI(
            $this->lng->txt('usr_letter_avatars'),
            'letter_avatars'
        );
        $la->setValue(1);
        $la->setInfo($this->lng->txt('usr_letter_avatars_info'));
        $this->form->addItem($la);

        $passwordPolicySettingsHash = new \ilHiddenInputGUI('pw_policy_hash');
        $this->form->addItem($passwordPolicySettingsHash);

        $this->form->addCommandButton(
            'saveGeneralSettings',
            $this->lng->txt('save')
        );
    }

    /**
     * Global user settings
     * Allows to define global settings for user accounts
     * Note: The Global user settings form allows to specify default values
     *       for some user preferences. To avoid redundant implementations,
     *       specification of default values can be done elsewhere in ILIAS
     *       are not supported by this form.
     */
    public function settingsObject(): void
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];

        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("mail");
        $lng->loadLanguageModule("chatroom");
        $this->setSubTabs('settings');
        $ilTabs->activateTab('settings');
        $ilTabs->activateSubTab('standard_fields');

        $tab = new ilUserFieldSettingsTableGUI(
            $this,
            "settings"
        );
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $tpl->setContent($tab->getHTML());
    }

    public function confirmSavedObject(): void
    {
        $this->saveGlobalUserSettingsObject("save");
    }

    public function saveGlobalUserSettingsObject(string $action = ""): void
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilSetting = $DIC['ilSetting'];

        $checked = $this->user_request->getChecked();
        $selected = $this->user_request->getSelect();

        $user_settings_config = $this->user_settings_config;

        // see ilUserFieldSettingsTableGUI
        $up = new ilUserProfile();
        $up->skipField("username");
        $field_properties = $up->getStandardFields();
        $profile_fields = array_keys($field_properties);

        $valid = true;
        foreach ($profile_fields as $field) {
            if (($checked["required_" . $field] ?? false) &&
                !(int) $checked['visib_reg_' . $field]
            ) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            global $DIC;

            $lng = $DIC['lng'];
            $this->tpl->setOnScreenMessage('failure', $lng->txt('invalid_visible_required_options_selected'));
            $this->confirm_change = 1;
            $this->settingsObject();
            return;
        }

        // For the following fields, the required state can not be changed
        $fixed_required_fields = array(
            "firstname" => 1,
            "lastname" => 1,
            "upload" => 0,
            "password" => 0,
            "language" => 0,
            "skin_style" => 0,
            "hits_per_page" => 0,
            /*"show_users_online" => 0,*/
            "hide_own_online_status" => 0
        );

        // Reset user confirmation
        if ($action == 'save') {
            ilMemberAgreement::_reset();
        }

        $changedFields = $this->collectChangedFields();
        if ($this->handleChangeListeners($changedFields, $field_properties)) {
            return;
        }

        foreach ($profile_fields as $field) {
            // Enable disable searchable
            if (ilUserSearchOptions::_isSearchable($field)) {
                ilUserSearchOptions::_saveStatus(
                    $field,
                    (bool) ($checked['searchable_' . $field] ?? false)
                );
            }

            if (!($checked["visible_" . $field] ?? false) && !($field_properties[$field]["visible_hide"] ?? false)) {
                $user_settings_config->setVisible(
                    $field,
                    false
                );
            } else {
                $user_settings_config->setVisible(
                    $field,
                    true
                );
            }

            if (!($checked["changeable_" . $field] ?? false) &&
                !($field_properties[$field]["changeable_hide"] ?? false)) {
                $user_settings_config->setChangeable(
                    $field,
                    false
                );
            } else {
                $user_settings_config->setChangeable(
                    $field,
                    true
                );
            }

            // registration visible
            if (($checked['visib_reg_' . $field] ?? false) && !($field_properties[$field]["visib_reg_hide"] ?? false)) {
                $ilSetting->set(
                    'usr_settings_visib_reg_' . $field,
                    '1'
                );
            } else {
                $ilSetting->set(
                    'usr_settings_visib_reg_' . $field,
                    '0'
                );
            }

            if ($checked['visib_lua_' . $field] ?? false) {
                $ilSetting->set(
                    'usr_settings_visib_lua_' . $field,
                    '1'
                );
            } else {
                $ilSetting->set(
                    'usr_settings_visib_lua_' . $field,
                    '0'
                );
            }

            if ((int) ($checked['changeable_lua_' . $field] ?? false)) {
                $ilSetting->set(
                    'usr_settings_changeable_lua_' . $field,
                    '1'
                );
            } else {
                $ilSetting->set(
                    'usr_settings_changeable_lua_' . $field,
                    '0'
                );
            }

            if (($checked["export_" . $field] ?? false) && !$field_properties[$field]["export_hide"]) {
                $ilias->setSetting(
                    "usr_settings_export_" . $field,
                    "1"
                );
            } else {
                $ilias->deleteSetting("usr_settings_export_" . $field);
            }

            // Course export/visibility
            if (($checked["course_export_" . $field] ?? false) && !$field_properties[$field]["course_export_hide"]) {
                $ilias->setSetting(
                    "usr_settings_course_export_" . $field,
                    "1"
                );
            } else {
                $ilias->deleteSetting("usr_settings_course_export_" . $field);
            }

            // Group export/visibility
            if (($checked["group_export_" . $field] ?? false) && !$field_properties[$field]["group_export_hide"]) {
                $ilias->setSetting(
                    "usr_settings_group_export_" . $field,
                    "1"
                );
            } else {
                $ilias->deleteSetting("usr_settings_group_export_" . $field);
            }

            $is_fixed = array_key_exists(
                $field,
                $fixed_required_fields
            );
            if (($is_fixed && $fixed_required_fields[$field]) || (!$is_fixed && ($checked["required_" . $field] ?? false))) {
                $ilias->setSetting(
                    "require_" . $field,
                    "1"
                );
            } else {
                $ilias->deleteSetting("require_" . $field);
            }
        }

        if ($selected["default_hits_per_page"]) {
            $ilias->setSetting(
                "hits_per_page",
                $selected["default_hits_per_page"]
            );
        }

        if ($checked["export_preferences"] ?? false) {
            $ilias->setSetting(
                "usr_settings_export_preferences",
                $checked["export_preferences"]
            );
        } else {
            $ilias->deleteSetting("usr_settings_export_preferences");
        }

        $ilias->setSetting(
            'mail_incoming_mail',
            (int) $selected['default_mail_incoming_mail']
        );
        $ilias->setSetting(
            'chat_osc_accept_msg',
            $selected['default_chat_osc_accept_msg']
        );
        $ilias->setSetting(
            'chat_broadcast_typing',
            $selected['default_chat_broadcast_typing']
        );
        $ilias->setSetting(
            'bs_allow_to_contact_me',
            $selected['default_bs_allow_to_contact_me']
        );
        $ilias->setSetting(
            'hide_own_online_status',
            $selected['default_hide_own_online_status']
        );

        if ($this->usrFieldChangeListenersAccepted && count($changedFields) > 0) {
            $this->dic->event()->raise(
                "Services/User",
                "onUserFieldAttributesChanged",
                $changedFields
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("usr_settings_saved"));
        $this->settingsObject();
    }

    public function confirmUsrFieldChangeListenersObject(): void
    {
        $this->usrFieldChangeListenersAccepted = true;
        $this->confirmSavedObject();
    }

    /**
     * @param InterestedUserFieldChangeListener[] $interestedChangeListeners
     */
    public function showFieldChangeComponentsListeningConfirmDialog(
        array $interestedChangeListeners
    ): void {
        $post = $this->dic->http()->request()->getParsedBody();
        $confirmDialog = new ilConfirmationGUI();
        $confirmDialog->setHeaderText($this->lng->txt("usr_field_change_components_listening"));
        $confirmDialog->setFormAction($this->ctrl->getFormActionByClass(
            [self::class],
            "settings"
        ));
        $confirmDialog->addButton($this->lng->txt("confirm"), "confirmUsrFieldChangeListeners");
        $confirmDialog->addButton($this->lng->txt("cancel"), "settings");

        $tpl = new ilTemplate(
            "tpl.usr_field_change_listener_confirm.html",
            true,
            true,
            "Services/User"
        );

        foreach ($interestedChangeListeners as $interestedChangeListener) {
            $tpl->setVariable("FIELD_NAME", $interestedChangeListener->getName());
            foreach ($interestedChangeListener->getAttributes() as $attribute) {
                $tpl->setVariable("ATTRIBUTE_NAME", $attribute->getName());
                foreach ($attribute->getComponents() as $component) {
                    $tpl->setVariable("COMPONENT_NAME", $component->getComponentName());
                    $tpl->setVariable("DESCRIPTION", $component->getDescription());
                    $tpl->setCurrentBlock("component");
                    $tpl->parseCurrentBlock("component");
                }
                $tpl->setCurrentBlock("attribute");
                $tpl->parseCurrentBlock("attribute");
            }
            $tpl->setCurrentBlock("field");
            $tpl->parseCurrentBlock("field");
        }

        $confirmDialog->addItem("", 0, $tpl->get());

        foreach ($post["chb"] as $postVar => $value) {
            $confirmDialog->addHiddenItem("chb[$postVar]", $value);
        }
        foreach ($post["select"] as $postVar => $value) {
            $confirmDialog->addHiddenItem("select[$postVar]", $value);
        }
        foreach ($post["current"] as $postVar => $value) {
            $confirmDialog->addHiddenItem("current[$postVar]", $value);
        }
        $this->tpl->setContent($confirmDialog->getHTML());
    }

    /**
     * @param array<string, ChangedUserFieldAttribute> $changedFields
     * @param array<string, array>                     $fieldProperties => See ilUserProfile::getStandardFields()
     * @return bool
     */
    public function handleChangeListeners(
        array $changedFields,
        array $fieldProperties
    ): bool {
        if (count($changedFields) > 0) {
            $interestedChangeListeners = [];
            foreach ($fieldProperties as $fieldName => $properties) {
                if (!isset($properties["change_listeners"])) {
                    continue;
                }

                foreach ($properties["change_listeners"] as $changeListenerClassName) {
                    /**
                     * @var UserFieldAttributesChangeListener $listener
                     */
                    $listener = new $changeListenerClassName($this->dic);
                    foreach ($changedFields as $changedField) {
                        $attributeName = $changedField->getAttributeName();
                        $descriptionForField = $listener->getDescriptionForField($fieldName, $attributeName);
                        if ($descriptionForField !== null && $descriptionForField !== "") {
                            $interestedChangeListener = null;
                            foreach ($interestedChangeListeners as $interestedListener) {
                                if ($interestedListener->getFieldName() === $fieldName) {
                                    $interestedChangeListener = $interestedListener;
                                    break;
                                }
                            }

                            if ($interestedChangeListener === null) {
                                $interestedChangeListener = new InterestedUserFieldChangeListener(
                                    $this->getTranslationForField($fieldName, $properties),
                                    $fieldName
                                );
                                $interestedChangeListeners[] = $interestedChangeListener;
                            }

                            $interestedAttribute = $interestedChangeListener->addAttribute($attributeName);
                            $interestedAttribute->addComponent(
                                $listener->getComponentName(),
                                $descriptionForField
                            );
                        }
                    }
                }
            }

            if (!$this->usrFieldChangeListenersAccepted && count($interestedChangeListeners) > 0) {
                $this->showFieldChangeComponentsListeningConfirmDialog($interestedChangeListeners);
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, ChangedUserFieldAttribute>
     */
    private function collectChangedFields(): array
    {
        $changedFields = [];
        $post = $this->dic->http()->request()->getParsedBody();
        if (
            !isset($post["chb"])
            && !is_array($post["chb"])
            && !isset($post["current"])
            && !is_array($post["current"])
        ) {
            return $changedFields;
        }

        $old = $post["current"];
        $new = $post["chb"];

        foreach ($old as $key => $oldValue) {
            if (!isset($new[$key])) {
                $isBoolean = filter_var($oldValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $new[$key] = $isBoolean ? "0" : $oldValue;
            }
        }

        $oldToNewDiff = array_diff_assoc($old, $new);

        foreach ($oldToNewDiff as $key => $oldValue) {
            $changedFields[$key] = new ChangedUserFieldAttribute($key, $oldValue, $new[$key]);
        }

        return $changedFields;
    }

    /**
     * build select form to distinguish between active and non-active users
     */
    public function __buildUserFilterSelect(): string
    {
        $action[-1] = $this->lng->txt('all_users');
        $action[1] = $this->lng->txt('usr_active_only');
        $action[0] = $this->lng->txt('usr_inactive_only');
        $action[2] = $this->lng->txt('usr_limited_access_only');
        $action[3] = $this->lng->txt('usr_without_courses');
        $action[4] = $this->lng->txt('usr_filter_lastlogin');
        $action[5] = $this->lng->txt("usr_filter_coursemember");
        $action[6] = $this->lng->txt("usr_filter_groupmember");
        $action[7] = $this->lng->txt("usr_filter_role");

        return ilLegacyFormElementsUtil::formSelect(
            ilSession::get("user_filter"),
            "user_filter",
            $action,
            false,
            true
        );
    }

    /**
     * Download selected export files
     * Sends a selected export file for download
     */
    public function downloadExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        if (count($files) == 0) {
            $this->ilias->raiseError(
                $this->lng->txt("no_checkbox"),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (count($files) > 1) {
            $this->ilias->raiseError(
                $this->lng->txt("select_max_one_item"),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $file = basename($files[0]);

        $export_dir = $this->object->getExportDirectory();
        ilFileDelivery::deliverFileLegacy(
            $export_dir . "/" . $file,
            $file
        );
    }

    /**
     * confirmation screen for export file deletion
     */
    public function confirmDeleteExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        if (count($files) == 0) {
            $this->ilias->raiseError(
                $this->lng->txt("no_checkbox"),
                $this->ilias->error_obj->MESSAGE
            );
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel(
            $this->lng->txt("cancel"),
            "cancelDeleteExportFile"
        );
        $cgui->setConfirm(
            $this->lng->txt("confirm"),
            "deleteExportFile"
        );

        // BEGIN TABLE DATA
        foreach ($files as $file) {
            $cgui->addItem(
                "file[]",
                $file,
                $file,
                ilObject::_getIcon($this->object->getId()),
                $this->lng->txt("obj_usrf")
            );
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of export files
     */
    public function cancelDeleteExportFileObject(): void
    {
        $this->ctrl->redirectByClass(
            "ilobjuserfoldergui",
            "export"
        );
    }

    /**
     * delete export files
     */
    public function deleteExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        $export_dir = $this->object->getExportDirectory();
        foreach ($files as $file) {
            $file = basename($file);

            $exp_file = $export_dir . "/" . $file;
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
        }
        $this->ctrl->redirectByClass(
            "ilobjuserfoldergui",
            "export"
        );
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function performExportObject(): void
    {
        $this->checkPermission("write,read_users");

        $this->object->buildExportFile($this->user_request->getExportType());
        $this->ctrl->redirect(
            $this,
            'export'
        );
    }

    public function exportObject(): void
    {
        global $DIC;

        $this->checkPermission("write,read_users");

        $button = ilSubmitButton::getInstance();
        $button->setCaption('create_export_file');
        $button->setCommand('performExport');
        $toolbar = $DIC->toolbar();
        $toolbar->setFormAction($this->ctrl->getFormAction($this));

        $export_types = array(
            "userfolder_export_excel_x86",
            "userfolder_export_csv",
            "userfolder_export_xml"
        );
        $options = [];
        foreach ($export_types as $type) {
            $options[$type] = $this->lng->txt($type);
        }
        $type_selection = new \ilSelectInputGUI(
            '',
            'export_type'
        );
        $type_selection->setOptions($options);

        $toolbar->addInputItem(
            $type_selection,
            true
        );
        $toolbar->addButtonInstance($button);

        $table = new \ilUserExportFileTableGUI(
            $this,
            'export'
        );
        $table->init();
        $table->parse($this->object->getExportFiles());

        $this->tpl->setContent($table->getHTML());
    }

    protected function initNewAccountMailForm(): ilPropertyFormGUI
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $lng->loadLanguageModule("meta");
        $lng->loadLanguageModule("mail");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));

        $form->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));
        $form->setTitle($lng->txt("user_new_account_mail"));
        $form->setDescription($lng->txt("user_new_account_mail_desc"));

        $langs = $lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            $amail = ilObjUserFolder::_lookupNewAccountMail($lang_key);

            $title = $lng->txt("meta_l_" . $lang_key);
            if ($lang_key == $lng->getDefaultLanguage()) {
                $title .= " (" . $lng->txt("default") . ")";
            }

            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($title);
            $form->addItem($header);

            $subj = new ilTextInputGUI(
                $lng->txt("subject"),
                "subject_" . $lang_key
            );
            // $subj->setRequired(true);
            $subj->setValue($amail["subject"] ?? "");
            $form->addItem($subj);

            $salg = new ilTextInputGUI(
                $lng->txt("mail_salutation_general"),
                "sal_g_" . $lang_key
            );
            // $salg->setRequired(true);
            $salg->setValue($amail["sal_g"] ?? "");
            $form->addItem($salg);

            $salf = new ilTextInputGUI(
                $lng->txt("mail_salutation_female"),
                "sal_f_" . $lang_key
            );
            // $salf->setRequired(true);
            $salf->setValue($amail["sal_f"] ?? "");
            $form->addItem($salf);

            $salm = new ilTextInputGUI(
                $lng->txt("mail_salutation_male"),
                "sal_m_" . $lang_key
            );
            // $salm->setRequired(true);
            $salm->setValue($amail["sal_m"] ?? "");
            $form->addItem($salm);

            $body = new ilTextAreaInputGUI(
                $lng->txt("message_content"),
                "body_" . $lang_key
            );
            // $body->setRequired(true);
            $body->setValue($amail["body"] ?? "");
            $body->setRows(10);
            $body->setCols(100);
            $form->addItem($body);

            $att = new ilFileInputGUI(
                $lng->txt("attachment"),
                "att_" . $lang_key
            );
            $att->setALlowDeletion(true);
            if ($amail["att_file"] ?? false) {
                $att->setValue($amail["att_file"]);
            }
            $form->addItem($att);
        }

        $form->addCommandButton(
            "saveNewAccountMail",
            $lng->txt("save")
        );
        $form->addCommandButton(
            "cancelNewAccountMail",
            $lng->txt("cancel")
        );

        return $form;
    }

    /**
     * new account mail administration
     */
    public function newAccountMailObject(): void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('user_new_account_mail');

        $form = $this->initNewAccountMailForm();

        $ftpl = new ilTemplate(
            'tpl.usrf_new_account_mail.html',
            true,
            true,
            'Services/User'
        );
        $ftpl->setVariable(
            "FORM",
            $form->getHTML()
        );
        unset($form);

        // placeholder help text
        $ftpl->setVariable(
            "TXT_USE_PLACEHOLDERS",
            $lng->txt("mail_nacc_use_placeholder")
        );
        $ftpl->setVariable(
            "TXT_MAIL_SALUTATION",
            $lng->txt("mail_nacc_salutation")
        );
        $ftpl->setVariable(
            "TXT_FIRST_NAME",
            $lng->txt("firstname")
        );
        $ftpl->setVariable(
            "TXT_LAST_NAME",
            $lng->txt("lastname")
        );
        $ftpl->setVariable(
            "TXT_EMAIL",
            $lng->txt("email")
        );
        $ftpl->setVariable(
            "TXT_LOGIN",
            $lng->txt("mail_nacc_login")
        );
        $ftpl->setVariable(
            "TXT_PASSWORD",
            $lng->txt("password")
        );
        $ftpl->setVariable(
            "TXT_PASSWORD_BLOCK",
            $lng->txt("mail_nacc_pw_block")
        );
        $ftpl->setVariable(
            "TXT_NOPASSWORD_BLOCK",
            $lng->txt("mail_nacc_no_pw_block")
        );
        $ftpl->setVariable(
            "TXT_ADMIN_MAIL",
            $lng->txt("mail_nacc_admin_mail")
        );
        $ftpl->setVariable(
            "TXT_ILIAS_URL",
            $lng->txt("mail_nacc_ilias_url")
        );
        $ftpl->setVariable(
            "TXT_INSTALLATION_NAME",
            $lng->txt("mail_nacc_installation_name")
        );
        $ftpl->setVariable(
            "TXT_TARGET",
            $lng->txt("mail_nacc_target")
        );
        $ftpl->setVariable(
            "TXT_TARGET_TITLE",
            $lng->txt("mail_nacc_target_title")
        );
        $ftpl->setVariable(
            "TXT_TARGET_TYPE",
            $lng->txt("mail_nacc_target_type")
        );
        $ftpl->setVariable(
            "TXT_TARGET_BLOCK",
            $lng->txt("mail_nacc_target_block")
        );
        $ftpl->setVariable(
            "TXT_IF_TIMELIMIT",
            $lng->txt("mail_nacc_if_timelimit")
        );
        $ftpl->setVariable(
            "TXT_TIMELIMIT",
            $lng->txt("mail_nacc_timelimit")
        );

        $this->tpl->setContent($ftpl->get());
    }

    public function cancelNewAccountMailObject(): void
    {
        $this->ctrl->redirect(
            $this,
            "settings"
        );
    }

    public function saveNewAccountMailObject(): void
    {
        global $DIC;

        $lng = $DIC['lng'];

        $langs = $lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            ilObjUserFolder::_writeNewAccountMail(
                $lang_key,
                $this->user_request->getMailSubject($lang_key),
                $this->user_request->getMailSalutation("g", $lang_key),
                $this->user_request->getMailSalutation("f", $lang_key),
                $this->user_request->getMailSalutation("m", $lang_key),
                $this->user_request->getMailBody($lang_key)
            );

            if ($_FILES["att_" . $lang_key]["tmp_name"]) {
                ilObjUserFolder::_updateAccountMailAttachment(
                    $lang_key,
                    $_FILES["att_" . $lang_key]["tmp_name"],
                    $_FILES["att_" . $lang_key]["name"]
                );
            }

            if ($this->user_request->getMailAttDelete($lang_key)) {
                ilObjUserFolder::_deleteAccountMailAttachment($lang_key);
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect(
            $this,
            "newAccountMail"
        );
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $access = $DIC->access();

        if ($rbacsystem->checkAccess(
            "visible,read",
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                "usrf",
                $this->ctrl->getLinkTarget(
                    $this,
                    "view"
                ),
                array("view", "delete", "resetFilter", "userAction", ""),
                "",
                ""
            );
        }

        if ($access->checkRbacOrPositionPermissionAccess(
            "read_users",
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            $this->tabs_gui->addTarget(
                "search_user_extended",
                $this->ctrl->getLinkTargetByClass(
                    'ilRepositorySearchGUI',
                    ''
                ),
                array(),
                "ilrepositorysearchgui",
                ""
            );
        }

        if ($rbacsystem->checkAccess(
            "write,read_users",
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget(
                    $this,
                    "generalSettings"
                ),
                array('askForUserPasswordReset',
                      'forceUserPasswordReset',
                      'settings',
                      'generalSettings',
                      'listUserDefinedField',
                      'newAccountMail'
                )
            );

            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTarget(
                    $this,
                    "export"
                ),
                "export",
                "",
                ""
            );
        }

        if ($rbacsystem->checkAccess(
            'edit_permission',
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(
                    array(get_class($this), 'ilpermissiongui'),
                    "perm"
                ),
                array("perm", "info", "owner"),
                'ilpermissiongui'
            );
        }
    }

    public function setSubTabs(string $a_tab): void
    {
        global $DIC;

        switch ($a_tab) {
            case "settings":
                $this->tabs_gui->addSubTabTarget(
                    'general_settings',
                    $this->ctrl->getLinkTarget(
                        $this,
                        'generalSettings'
                    ),
                    'generalSettings',
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "standard_fields",
                    $this->ctrl->getLinkTarget(
                        $this,
                        'settings'
                    ),
                    array("settings", "saveGlobalUserSettings"),
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "user_defined_fields",
                    $this->ctrl->getLinkTargetByClass(
                        "ilcustomuserfieldsgui",
                        "listUserDefinedFields"
                    ),
                    "listUserDefinedFields",
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "user_new_account_mail",
                    $this->ctrl->getLinkTarget(
                        $this,
                        'newAccountMail'
                    ),
                    "newAccountMail",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "starting_points",
                    $this->ctrl->getLinkTargetByClass(
                        "iluserstartingpointgui",
                        "startingPoints"
                    ),
                    "startingPoints",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "user_profile_info",
                    $this->ctrl->getLinkTargetByClass(
                        "ilUserProfileInfoSettingsGUI",
                        ''
                    ),
                    "",
                    "ilUserProfileInfoSettingsGUI"
                );

                #$this->tabs_gui->addSubTab("account_codes", $this->lng->txt("user_account_codes"),
                #							 $this->ctrl->getLinkTargetByClass("ilaccountcodesgui"));
                break;
        }
    }

    public function showLoginnameSettingsObject(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $show_blocking_time_in_days = (int) $ilSetting->get('loginname_change_blocking_time') / 86400;

        $this->initLoginSettingsForm();
        $this->loginSettingsForm->setValuesByArray(
            array(
                'allow_change_loginname' => (bool) $ilSetting->get('allow_change_loginname'),
                'create_history_loginname' => (bool) $ilSetting->get('create_history_loginname'),
                'reuse_of_loginnames' => (bool) $ilSetting->get('reuse_of_loginnames'),
                'loginname_change_blocking_time' => (float) $show_blocking_time_in_days
            )
        );

        $this->tpl->setVariable(
            'ADM_CONTENT',
            $this->loginSettingsForm->getHTML()
        );
    }

    private function initLoginSettingsForm(): void
    {
        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('loginname_settings');

        $this->loginSettingsForm = new ilPropertyFormGUI();
        $this->loginSettingsForm->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                'saveLoginnameSettings'
            )
        );
        $this->loginSettingsForm->setTitle($this->lng->txt('loginname_settings'));

        $chbChangeLogin = new ilCheckboxInputGUI(
            $this->lng->txt('allow_change_loginname'),
            'allow_change_loginname'
        );
        $chbChangeLogin->setValue(1);
        $this->loginSettingsForm->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI(
            $this->lng->txt('history_loginname'),
            'create_history_loginname'
        );
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue(1);
        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI(
            $this->lng->txt('reuse_of_loginnames_contained_in_history'),
            'reuse_of_loginnames'
        );
        $chbReuseLoginnames->setValue(1);
        $chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));
        $chbChangeLogin->addSubItem($chbReuseLoginnames);
        $chbChangeBlockingTime = new ilNumberInputGUI(
            $this->lng->txt('loginname_change_blocking_time'),
            'loginname_change_blocking_time'
        );
        $chbChangeBlockingTime->allowDecimals(true);
        $chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
        $chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
        $chbChangeBlockingTime->setSize(10);
        $chbChangeBlockingTime->setMaxLength(10);
        $chbChangeLogin->addSubItem($chbChangeBlockingTime);

        $this->loginSettingsForm->addCommandButton(
            'saveLoginnameSettings',
            $this->lng->txt('save')
        );
    }

    public function saveLoginnameSettingsObject(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->initLoginSettingsForm();
        if ($this->loginSettingsForm->checkInput()) {
            $valid = true;

            if (!strlen($this->loginSettingsForm->getInput('loginname_change_blocking_time'))) {
                $valid = false;
                $this->loginSettingsForm->getItemByPostVar('loginname_change_blocking_time')
                                        ->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
            }

            if ($valid) {
                $save_blocking_time_in_seconds = (int) $this->loginSettingsForm->getInput(
                    'loginname_change_blocking_time'
                ) * 86400;

                $ilSetting->set(
                    'allow_change_loginname',
                    (int) $this->loginSettingsForm->getInput('allow_change_loginname')
                );
                $ilSetting->set(
                    'create_history_loginname',
                    (int) $this->loginSettingsForm->getInput('create_history_loginname')
                );
                $ilSetting->set(
                    'reuse_of_loginnames',
                    (int) $this->loginSettingsForm->getInput('reuse_of_loginnames')
                );
                $ilSetting->set(
                    'loginname_change_blocking_time',
                    (int) $save_blocking_time_in_seconds
                );

                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        }
        $this->loginSettingsForm->setValuesByPost();

        $this->tpl->setVariable(
            'ADM_CONTENT',
            $this->loginSettingsForm->getHTML()
        );
    }

    public static function _goto(int $a_user): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        $a_target = USER_FOLDER_ID;

        if ($ilAccess->checkAccess(
            "read",
            "",
            $a_target
        )) {
            ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $a_target . "&jmpToUser=" . $a_user);
            exit;
        } else {
            if ($ilAccess->checkAccess(
                "read",
                "",
                ROOT_FOLDER_ID
            )) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError(
            $lng->txt("msg_no_perm_read"),
            $ilErr->FATAL
        );
    }

    /**
     * Jump to edit screen for user
     */
    public function jumpToUserObject(): void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $jmpToUser = $this->user_request->getJumpToUser();
        if (ilObject::_lookupType($jmpToUser) == "usr") {
            $ilCtrl->setParameterByClass(
                "ilobjusergui",
                "obj_id",
                $jmpToUser
            );
            $ilCtrl->redirectByClass(
                "ilobjusergui",
                "view"
            );
        }
    }

    public function searchUserAccessFilterCallable(array $a_user_ids): array // Missing array type.
    {
        global $DIC;
        $access = $DIC->access();

        if (!$this->checkPermissionBool("read_users")) {
            $a_user_ids = $access->filterUserIdsByPositionOfCurrentUser(
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $a_user_ids
            );
        }

        return $a_user_ids;
    }

    /**
     * Handles multi command from repository search gui
     */
    public function searchResultHandler(
        array $a_usr_ids,
        string $a_cmd
    ): bool {
        if (!count($a_usr_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            return false;
        }

        $this->requested_ids = $a_usr_ids;

        // no real confirmation here
        if (stripos($a_cmd, "export") !== false) {
            $cmd = $a_cmd . "Object";
            return $this->$cmd();
        }

        return $this->showActionConfirmation(
            $a_cmd,
            true
        );
    }

    public function getUserMultiCommands(bool $a_search_form = false): array // Missing array type.
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        $cmds = array();
        // see searchResultHandler()
        if ($a_search_form) {
            if ($this->checkAccessBool('write')) {
                $cmds = array(
                    'activate' => $this->lng->txt('activate'),
                    'deactivate' => $this->lng->txt('deactivate'),
                    'accessRestrict' => $this->lng->txt('accessRestrict'),
                    'accessFree' => $this->lng->txt('accessFree')
                );
            }

            if ($this->checkAccessBool('delete')) {
                $cmds["delete"] = $this->lng->txt("delete");
            }
        } // show confirmation
        else {
            if ($this->checkAccessBool('write')) {
                $cmds = array(
                    'activateUsers' => $this->lng->txt('activate'),
                    'deactivateUsers' => $this->lng->txt('deactivate'),
                    'restrictAccess' => $this->lng->txt('accessRestrict'),
                    'freeAccess' => $this->lng->txt('accessFree')
                );
            }

            if ($this->checkAccessBool('delete')) {
                $cmds["deleteUsers"] = $this->lng->txt("delete");
            }
        }

        if ($this->checkAccessBool('write')) {
            $export_types = array("userfolder_export_excel_x86", "userfolder_export_csv", "userfolder_export_xml");
            foreach ($export_types as $type) {
                $cmd = explode(
                    "_",
                    $type
                );
                $cmd = array_pop($cmd);
                $cmds['usrExport' . ucfirst($cmd)] = $this->lng->txt('export') . ' - ' .
                    $this->lng->txt($type);
            }
        }

        // check if current user may send mails
        $mail = new ilMail($ilUser->getId());
        if ($rbacsystem->checkAccess(
            'internal_mail',
            $mail->getMailObjectReferenceId()
        )) {
            $cmds["mail"] = $this->lng->txt("send_mail");
        }

        $cmds['addToClipboard'] = $this->lng->txt('clipboard_add_btn');

        return $cmds;
    }

    /**
     * Export excel
     */
    protected function usrExportX86Object(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        if ($this->checkPermissionBool('write,read_users')) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_EXCEL,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                "ilobjuserfoldergui",
                "export"
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_EXCEL,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname . '.xlsx',
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_EXCEL) . '.xlsx',
                '',
                false,
                true
            );
        }
    }

    /**
     * Export csv
     */
    protected function usrExportCsvObject(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        if ($this->checkPermissionBool("write,read_users")) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_CSV,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                "ilobjuserfoldergui",
                "export"
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_CSV,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname,
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_CSV),
                '',
                false,
                true
            );
        }
    }

    /**
     * Export xml
     */
    protected function usrExportXmlObject(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
        if ($this->checkPermissionBool("write,read_users")) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_XML,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                "ilobjuserfoldergui",
                "export"
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_XML,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname,
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_XML),
                '',
                false,
                true
            );
        }
    }

    protected function mailObject(): void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
            return;
        }

        // remove existing (temporary) lists
        $list = new ilMailingLists($ilUser);
        $list->deleteTemporaryLists();

        // create (temporary) mailing list
        $list = new ilMailingList($ilUser);
        $list->setMode(ilMailingList::MODE_TEMPORARY);
        $list->setTitle("-TEMPORARY SYSTEM LIST-");
        $list->setDescription("-USER ACCOUNTS MAIL-");
        $list->setCreatedate(date("Y-m-d H:i:s"));
        $list->insert();
        $list_id = $list->getId();

        // after list has been saved...
        foreach ($user_ids as $user_id) {
            $list->assignUser($user_id);
        }

        $umail = new ilFormatMail($ilUser->getId());
        $mail_data = $umail->getSavedData();

        if (!is_array($mail_data)) {
            $mail_data = array("user_id" => $ilUser->getId());
        }

        // ???
        // $mail_data = $umail->appendSearchResult(array('#il_ml_'.$list_id), 'to');

        $umail->savePostData(
            $mail_data['user_id'],
            $mail_data['attachments'],
            '#il_ml_' . $list_id,
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        ilUtil::redirect(
            ilMailFormCall::getRedirectTarget(
                $this,
                '',
                array(),
                array(
                    'type' => 'search_res'
                )
            )
        );
    }

    public function addToExternalSettingsForm(int $a_form_id): array // Missing array type.
    {
        global $DIC;

        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:
                $security = ilSecuritySettings::_getInstance();

                $fields = array();

                $subitems = array(
                    'ps_password_change_on_first_login_enabled' => array($security->isPasswordChangeOnFirstLoginEnabled(),
                                                                         ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_password_must_not_contain_loginame' => array($security->getPasswordMustNotContainLoginnameStatus(
                    ),
                                                                     ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_password_chars_and_numbers_enabled' => array($security->isPasswordCharsAndNumbersEnabled(),
                                                                     ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_password_special_chars_enabled' => array($security->isPasswordSpecialCharsEnabled(),
                                                                 ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_password_min_length' => $security->getPasswordMinLength(),
                    'ps_password_max_length' => $security->getPasswordMaxLength(),
                    'ps_password_uppercase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
                    'ps_password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
                    'ps_password_max_age' => $security->getPasswordMaxAge()
                );
                $fields['ps_password_settings'] = array(null, null, $subitems);

                $subitems = array(
                    'ps_login_max_attempts' => $security->getLoginMaxAttempts(),
                    'ps_prevent_simultaneous_logins' => array($security->isPreventionOfSimultaneousLoginsEnabled(),
                                                              ilAdministrationSettingsFormHandler::VALUE_BOOL
                    )
                );
                $fields['ps_security_protection'] = array(null, null, $subitems);

                return array(array("generalSettings", $fields));

            case ilAdministrationSettingsFormHandler::FORM_TOS:
                return [
                    [
                        'generalSettings',
                        [
                            'tos_withdrawal_usr_deletion' => [
                                (bool) $DIC->settings()->get(
                                    'tos_withdrawal_usr_deletion',
                                    '0'
                                ),
                                ilAdministrationSettingsFormHandler::VALUE_BOOL
                            ],
                        ]
                    ],
                ];
        }
        return [];
    }

    /**
     * Add users to clipboard
     */
    protected function addToClipboardObject(): void
    {
        $users = $this->getActionUserIds();
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->add($users);
        $clip->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }
}
