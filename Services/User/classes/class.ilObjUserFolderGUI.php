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

use ILIAS\User\UserGUIRequest;
use ILIAS\DI\Container as DIContainer;
use ILIAS\Services\User\UserFieldAttributesChangeListener;
use ILIAS\Services\User\InterestedUserFieldChangeListener;
use ILIAS\Services\User\ChangedUserFieldAttribute;
use ILIAS\Filesystem\Filesystem;
use ILIAS\UI\Renderer;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\FileUpload\FileUpload;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @author       Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilUserTableGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ilCustomUserFieldsGUI, ilRepositorySearchGUI, ilUserStartingPointGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ilUserProfileInfoSettingsGUI
 */
class ilObjUserFolderGUI extends ilObjectGUI
{
    use ilTableCommandHelper;

    public const USER_FIELD_TRANSLATION_MAPPING = [
        'visible' => 'user_visible_in_profile',
        'changeable' => 'changeable',
        'searchable' => 'header_searchable',
        'required' => 'required_field',
        'export' => 'export',
        'course_export' => 'course_export',
        'group_export' => 'group_export',
        'prg_export' => 'prg_export',
        'visib_reg' => 'header_visible_registration',
        'visib_lua' => 'usr_settings_visib_lua',
        'changeable_lua' => 'usr_settings_changeable_lua'
    ];

    protected ilPropertyFormGUI $loginSettingsForm;
    protected ilPropertyFormGUI $form;
    protected array $requested_ids; // Missing array type.
    protected string $selected_action;
    protected UserGUIRequest $user_request;
    protected int $user_owner_id = 0;
    protected int $confirm_change = 0;
    protected ilLogger $log;
    protected ilUserSettingsConfig $user_settings_config;
    private bool $usrFieldChangeListenersAccepted = false;

    /**
     * @deprecated
     * 2023-06-06 sk: We just need to have this. Do not use!
     */
    private DIContainer $dic;

    private ilAppEventHandler $event;
    private Filesystem $filesystem;
    private FileUpload $upload;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        global $DIC;
        $this->dic = $DIC;

        $this->event = $DIC['ilAppEventHandler'];
        $this->filesystem = $DIC->filesystem()->storage();
        $this->upload = $DIC['upload'];
        $this->dic->upload();

        $this->type = 'usrf';
        parent::__construct(
            $a_data,
            $a_id,
            $a_call_by_reference,
            false
        );

        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('user');
        $this->lng->loadLanguageModule('tos');
        $this->ctrl->saveParameter(
            $this,
            'letter'
        );

        $this->user_request = new UserGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->selected_action = $this->user_request->getSelectedAction();
        $this->user_settings_config = new ilUserSettingsConfig();

        $this->log = ilLoggerFactory::getLogger('user');
        $this->requested_ids = $this->user_request->getIds();
    }

    private function getTranslationForField(
        string $field_name,
        array $properties
    ): string {
        $translation = (!isset($properties['lang_var']) || $properties['lang_var'] === '')
            ? $field_name
            : $properties['lang_var'];

        if ($field_name === 'country') {
            $translation = 'country_free_text';
        }
        if ($field_name === 'sel_country') {
            $translation = 'country_selection';
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
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilusertablegui':
                $u_table = new ilUserTableGUI(
                    $this,
                    'view'
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

                if (!$this->access->checkRbacOrPositionPermissionAccess(
                    'read',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                    $this->ilias->raiseError(
                        $this->lng->txt('permission_denied'),
                        $this->ilias->error_obj->MESSAGE
                    );
                }

                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($this->lng->txt('search_user_extended')); // #17502
                $user_search->enableSearchableCheck(false);
                $user_search->setUserLimitations(false);
                $user_search->setCallback(
                    $this,
                    'searchResultHandler',
                    $this->getUserMultiCommands(true)
                );
                $user_search->addUserAccessFilterCallable([$this, 'searchUserAccessFilterCallable']);
                $this->tabs_gui->setTabActive('search_user_extended');
                $this->ctrl->setReturn(
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($user_search);
                break;

            case 'ilcustomuserfieldsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs('settings');
                $this->tabs_gui->activateSubTab('user_defined_fields');
                $cf = new ilCustomUserFieldsGUI(
                    $this->requested_ref_id,
                    $this->user_request->getFieldId()
                );
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserstartingpointgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs('settings');
                $this->tabs_gui->activateSubTab('starting_points');
                $cf = new ilUserStartingPointGUI($this->ref_id);
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserprofileinfosettingsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs('settings');
                $this->tabs_gui->activateSubTab('user_profile_info');
                $ps = new ilUserProfileInfoSettingsGUI();
                $this->ctrl->forwardCommand($ps);
                break;

            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
    }

    public function resetFilterObject(): void
    {
        $utab = new ilUserTableGUI(
            $this,
            'view'
        );
        $utab->resetOffset();
        $utab->resetFilter();
        $this->viewObject();
    }

    /**
     * Add new user
     */
    public function addUserObject(): void
    {
        $this->ctrl->setParameterByClass(
            'ilobjusergui',
            'new_type',
            'usr'
        );
        $this->ctrl->redirectByClass(
            ['iladministrationgui', 'ilobjusergui'],
            'create'
        );
    }

    public function applyFilterObject(): void
    {
        $utab = new ilUserTableGUI(
            $this,
            'view'
        );
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->viewObject();
        $this->tabs_gui->activateTab('usrf');
    }

    /**
     * list users
     */
    public function viewObject(
    ): void {
        if ($this->rbac_system->checkAccess('create_usr', $this->object->getRefId())
            || $this->rbac_system->checkAccess('cat_administrate_users', $this->object->getRefId())) {
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('usr_add'),
                    $this->ctrl->getLinkTarget($this, 'addUser')
                )
            );

            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('import_users'),
                    $this->ctrl->getLinkTarget($this, 'importUserForm')
                )
            );
        }

        $list_of_users = null;
        if (!$this->access->checkAccess('read_users', '', USER_FOLDER_ID)
            && $this->access->checkRbacOrPositionPermissionAccess(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID
            )) {
            $list_of_users = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId())
            );
        }

        $utab = new ilUserTableGUI(
            $this,
            'view',
            ilUserTableGUI::MODE_USER_FOLDER,
            false
        );
        $utab->addFilterItemValue(
            'user_ids',
            $list_of_users
        );
        $utab->getItems();

        $this->tpl->setContent($utab->getHTML());
    }

    protected function addUserAutoCompleteObject(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->addUserAccessFilterCallable(\Closure::fromCallable([$this, 'filterUserIdsByRbacOrPositionOfCurrentUser']));
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email', 'second_email']);
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if ($this->user_request->getFetchAll()) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($this->user_request->getTerm());
        exit();
    }

    /**
     * @param array<int> $user_ids
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $user_ids): array
    {
        return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_users',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID,
            $user_ids
        );
    }

    public function chooseLetterObject(): void
    {
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }

    /**
     * show possible subobjects (pulldown menu)
     * overwritten to prevent displaying of role templates in local role folders
     */
    protected function showPossibleSubObjects(): void
    {
        $subobj = null;

        $d = $this->obj_definition->getCreatableSubObjects($this->object->getType());

        if (!$this->rbac_system->checkAccess(
            'create_usr',
            $this->object->getRefId()
        )) {
            unset($d['usr']);
        }

        if (count($d) > 0) {
            foreach ($d as $row) {
                $count = 0;
                if ($row['max'] > 0) {
                    //how many elements are present?
                    for ($i = 0, $iMax = count($this->data['ctrl']); $i < $iMax; $i++) {
                        if ($this->data['ctrl'][$i]['type'] == $row['name']) {
                            $count++;
                        }
                    }
                }
                if ($row['max'] == '' || $count < $row['max']) {
                    $subobj[] = $row['name'];
                }
            }
        }

        if (is_array($subobj)) {
            //build form
            $opts = ilLegacyFormElementsUtil::formSelect(
                12,
                'new_type',
                $subobj
            );
            $this->tpl->setCurrentBlock('add_object');
            $this->tpl->setVariable(
                'SELECT_OBJTYPE',
                $opts
            );
            $this->tpl->setVariable(
                'BTN_NAME',
                'create'
            );
            $this->tpl->setVariable(
                'TXT_ADD',
                $this->lng->txt('add')
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

    public function confirmactivateObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
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
                    $this->user->getId()
                );
                $obj->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_activated'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    public function confirmdeactivateObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
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
                    $this->user->getId()
                );
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_deactivated'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    protected function confirmaccessFreeObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }

        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(true);
                $obj->setTimeLimitFrom(null);
                $obj->setTimeLimitUntil(null);
                $obj->setTimeLimitMessage('');
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('access_free_granted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
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
        $form->setTitle($this->lng->txt('time_limit_add_time_limit_for_selected'));
        $form->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                'confirmaccessRestrict'
            )
        );

        $from = new ilDateTimeInputGUI(
            $this->lng->txt('access_from'),
            'from'
        );
        $from->setShowTime(true);
        $from->setRequired(true);
        $form->addItem($from);

        $to = new ilDateTimeInputGUI(
            $this->lng->txt('access_until'),
            'to'
        );
        $to->setRequired(true);
        $to->setShowTime(true);
        $form->addItem($to);

        $form->addCommandButton(
            'confirmaccessRestrict',
            $this->lng->txt('confirm')
        );
        $form->addCommandButton(
            'view',
            $this->lng->txt('cancel')
        );

        foreach ($user_ids as $user_id) {
            $ufield = new ilHiddenInputGUI('id[]');
            $ufield->setValue((string) $user_id);
            $form->addItem($ufield);
        }

        // return to search?
        if ($a_from_search || $this->user_request->getFrSearch()) {
            $field = new ilHiddenInputGUI('frsrch');
            $field->setValue('1');
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

        $timefrom = $form->getItemByPostVar('from')->getDate()->get(IL_CAL_UNIX);
        $timeuntil = $form->getItemByPostVar('to')->getDate()->get(IL_CAL_UNIX);
        if ($timeuntil <= $timefrom) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('time_limit_not_valid'));
            return $this->setAccessRestrictionObject($form);
        }

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(false);
                $obj->setTimeLimitFrom((int) $timefrom);
                $obj->setTimeLimitUntil((int) $timeuntil);
                $obj->setTimeLimitMessage('');
                $obj->update();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('access_restricted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
        return false;
    }

    public function confirmdeleteObject(): void
    {
        if (!$this->rbac_system->checkAccess(
            'delete',
            $this->object->getRefId()
        )) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_delete'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        $ids = $this->user_request->getIds();
        if (in_array(
            $this->user->getId(),
            $ids
        )) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_delete_yourself'),
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
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_deleted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    /**
     * Get selected items for table action
     * @return array<int>
     */
    protected function getActionUserIds(): array
    {
        if ($this->getSelectAllPostArray()['select_cmd_all']) {
            $utab = new ilUserTableGUI(
                $this,
                'view',
                ilUserTableGUI::MODE_USER_FOLDER,
                false
            );

            if (!$this->access->checkAccess(
                'read_users',
                '',
                USER_FOLDER_ID
            ) &&
                $this->access->checkRbacOrPositionPermissionAccess(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
                $filtered_users = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID,
                    $users
                );

                $utab->addFilterItemValue(
                    'user_ids',
                    $filtered_users
                );
            }

            return $utab->getUserIdsForFilter();
        } else {
            return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
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
        return $this->access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        );
    }

    public function showActionConfirmation(
        string $action,
        bool $a_from_search = false
    ): bool {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'));
            $this->viewObject();
            return false;
        }

        if (!$a_from_search) {
            $this->tabs_gui->activateTab('obj_usrf');
        } else {
            $this->tabs_gui->activateTab('search_user_extended');
        }

        if (strcmp(
            $action,
            'accessRestrict'
        ) == 0) {
            return $this->setAccessRestrictionObject(
                null,
                $a_from_search
            );
        }
        if (strcmp(
            $action,
            'mail'
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
            $cancel = 'cancelUserFolderAction';
        } else {
            $cancel = 'cancelSearchAction';
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt('info_' . $action . '_sure'));
        $cgui->setCancel(
            $this->lng->txt('cancel'),
            $cancel
        );
        $cgui->setConfirm(
            $this->lng->txt('confirm'),
            'confirm' . $action
        );

        if ($a_from_search) {
            $cgui->addHiddenItem(
                'frsrch',
                '1'
            );
        }

        foreach ($user_ids as $id) {
            $user = new ilObjUser((int) $id);

            $login = $user->getLastLogin();
            if (!$login) {
                $login = $this->lng->txt('never');
            } else {
                $login = ilDatePresentation::formatDate(
                    new ilDateTime(
                        $login,
                        IL_CAL_DATETIME
                    )
                );
            }

            $caption = $user->getFullname() . ' (' . $user->getLogin() . ')' . ', ' .
                $user->getEmail() . ' -  ' . $this->lng->txt('last_login') . ': ' . $login;

            $cgui->addItem(
                'id[]',
                (string) $id,
                $caption
            );
        }

        $this->tpl->setContent($cgui->getHTML());

        return true;
    }

    public function deleteUsersObject(): void
    {
        if (in_array($this->user->getId(), $this->getActionUserIds())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_delete_yourself'));
            $this->viewObject();
            return;
        }
        $this->showActionConfirmation('delete');
    }

    public function activateUsersObject(): void
    {
        $this->showActionConfirmation('activate');
    }

    public function deactivateUsersObject(): void
    {
        if (in_array($this->user->getId(), $this->getActionUserIds())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_deactivate_yourself'));
            $this->viewObject();
            return;
        }
        $this->showActionConfirmation('deactivate');
    }

    public function restrictAccessObject(): void
    {
        $this->showActionConfirmation('accessRestrict');
    }

    public function freeAccessObject(): void
    {
        $this->showActionConfirmation('accessFree');
    }

    public function userActionObject(): void
    {
        $this->showActionConfirmation($this->user_request->getSelectedAction());
    }

    public function importUserFormObject(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $this->ctrl->getLinkTarget(
                $this,
                'view'
            )
        );
        if (
            !$this->rbac_system->checkAccess('create_usr', $this->object->getRefId()) &&
            !$this->access->checkAccess('cat_administrate_users', '', $this->object->getRefId())
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'));
            return;
        }
        $this->initUserImportForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initUserImportForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        // Import File
        $fi = new ilFileInputGUI(
            $this->lng->txt('import_file'),
            'importFile'
        );
        $fi->setSuffixes(['xml']);
        $fi->setRequired(true);
        $this->form->addItem($fi);

        $this->form->addCommandButton(
            'importUserRoleAssignment',
            $this->lng->txt('import')
        );
        $this->form->addCommandButton(
            'importCancelled',
            $this->lng->txt('cancel')
        );

        $this->form->setTitle($this->lng->txt('import_users'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    protected function inAdministration(): bool
    {
        return (strtolower($this->user_request->getBaseClass()) === 'iladministrationgui');
    }

    public function importCancelledObject(): void
    {
        $import_dir = $this->getImportDir();
        if ($this->fi->hasDir($import_dir)) {
            $this->filesystem->deleteDir($import_dir);
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                'view'
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

        $importDir = 'user_import/usr_' . $this->user->getId() . '_' . mb_substr(session_id(), 0, 8);

        return $importDir;
    }

    /**
     * display form for user import with new FileSystem implementation
     */
    public function importUserRoleAssignmentObject(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $this->ctrl->getLinkTarget(
                $this,
                'view'
            )
        );

        $this->initUserImportForm();
        if ($this->form->checkInput()) {
            $xml_file = $this->handleUploadedFiles();
            $xml_file_full_path = ilFileUtils::getDataDir() . '/' . $xml_file;

            list($form, $message) = $this->initUserRoleAssignmentForm($xml_file_full_path);

            $this->tpl->setContent($message . $this->ui_renderer->render($form));
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * @throws ilCtrlException
     * @return array<\ILIAS\UI\Component\Input\Container\Form\Standard, string>
     */
    private function initUserRoleAssignmentForm(string $xml_file_full_path): array
    {
        $global_roles_assignment_info = null;
        $local_roles_assignment_info = null;

        $import_parser = new ilUserImportParser(
            $xml_file_full_path,
            ilUserImportParser::IL_VERIFY
        );
        $import_parser->startParsing();

        $message = $this->verifyXmlData($import_parser);

        $xml_file_name = explode(
            '/',
            $xml_file_full_path
        );
        $roles_import_filename = $this->ui_factory->input()->field()
            ->text($this->lng->txt('import_file'))
            ->withDisabled(true)
            ->withValue(end($xml_file_name));

        $roles_import_count = $this->ui_factory->input()->field()
            ->numeric($this->lng->txt('num_users'))
            ->withDisabled(true)
            ->withValue($import_parser->getUserCount());

        $import_parser = new ilUserImportParser(
            $xml_file_full_path,
            ilUserImportParser::IL_EXTRACT_ROLES
        );
        $import_parser->startParsing();

        $roles = $import_parser->getCollectedRoles();
        $all_gl_roles = $this->rbac_review->getRoleListByObject(ROLE_FOLDER_ID);
        $gl_roles = [];
        $roles_of_user = $this->rbac_review->assignedRoles($this->user->getId());
        foreach ($all_gl_roles as $obj_data) {
            // check assignment permission if called from local admin
            if ($this->object->getRefId() != USER_FOLDER_ID
                && !in_array(SYSTEM_ROLE_ID, $roles_of_user)
                && !ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])
            ) {
                continue;
            }
            // exclude anonymous role from list
            if ($obj_data['obj_id'] != ANONYMOUS_ROLE_ID
                && ($obj_data['obj_id'] != SYSTEM_ROLE_ID
                    || in_array(SYSTEM_ROLE_ID, $roles_of_user))
            ) {
                $gl_roles[$obj_data['obj_id']] = $obj_data['title'];
            }
        }

        // global roles
        $got_globals = false;
        $global_selects = [];
        foreach ($roles as $role_id => $role) {
            if ($role['type'] == 'Global') {
                if (!$got_globals) {
                    $got_globals = true;

                    $global_roles_assignment_info = $this->ui_factory->input()->field()
                        ->text($this->lng->txt('roles_of_import_global'))
                        ->withDisabled(true)
                        ->withValue($this->lng->txt('assign_global_role'));
                }

                //select options for new form input to still have both ids
                $select_options = [];
                foreach ($gl_roles as $key => $value) {
                    $select_options[$role_id . '-' . $key] = $value;
                }

                // pre selection for role
                $pre_select = array_search(
                    $role['name'],
                    $select_options
                );
                if (!$pre_select) {
                    switch ($role['name']) {
                        case 'Administrator':    // ILIAS 2/3 Administrator
                            $pre_select = array_search(
                                'Administrator',
                                $select_options
                            );
                            break;

                        case 'Autor':            // ILIAS 2 Author
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;

                        case 'Lerner':            // ILIAS 2 Learner
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;

                        case 'Gast':            // ILIAS 2 Guest
                            $pre_select = array_search(
                                'Guest',
                                $select_options
                            );
                            break;

                        default:
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;
                    }
                }

                $select = $this->ui_factory->input()->field()
                    ->select(
                        $role['name'],
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
            if ($role['type'] == 'Local') {
                $got_locals = true;
                break;
            }
        }

        if ($got_locals) {
            $local_roles_assignment_info = $this->ui_factory->input()->field()
                ->text($this->lng->txt('roles_of_import_local'))
                ->withDisabled(true)
                ->withValue($this->lng->txt('assign_local_role'));

            // get local roles
            if ($this->object->getRefId() == USER_FOLDER_ID) {
                // The import function has been invoked from the user folder
                // object. In this case, we show only matching roles,
                // because the user folder object is considered the parent of all
                // local roles and may contains thousands of roles on large ILIAS
                // installations.
                $loc_roles = [];

                $roleMailboxSearch = new ilRoleMailboxSearch(new ilMailRfc822AddressParserFactory());
                foreach ($roles as $role_id => $role) {
                    if ($role['type'] == 'Local') {
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
                $loc_roles = $this->rbac_review->getAssignableRolesInSubtree($this->object->getRefId());
            }
            $l_roles = [];

            // create a search array with  .
            foreach ($loc_roles as $key => $loc_role) {
                // fetch context path of role
                $rolf = $this->rbac_review->getFoldersAssignedToRole(
                    $loc_role,
                    true
                );

                // only process role folders that are not set to status 'deleted'
                // and for which the user has write permissions.
                // We also don't show the roles which are in the ROLE_FOLDER_ID folder.
                // (The ROLE_FOLDER_ID folder contains the global roles).
                if (
                    !$this->rbac_review->isDeleted($rolf[0]) &&
                    $this->rbac_system->checkAccess(
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
                    $is_in_subtree = $this->object->getRefId() == USER_FOLDER_ID;

                    $path_array = [];
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

                            $is_in_subtree |= $tmpPath[$i]['obj_id'] == $this->object->getId();
                        }
                        //revert this path for a better readability in dropdowns #18306
                        $path = implode(
                            ' < ',
                            array_reverse($path_array)
                        );
                    } else {
                        $path = '<b>Rolefolder ' . $rolf[0] . ' not found in tree! (Role ' . $loc_role . ')</b>';
                    }
                    $roleMailboxAddress = (new \ilRoleMailboxAddress($loc_role))->value();
                    $l_roles[$loc_role] = $roleMailboxAddress . ', ' . $path;
                }
            }

            natcasesort($l_roles);
            $l_roles['ignore'] = $this->lng->txt('usrimport_ignore_role');

            $roleMailboxSearch = new ilRoleMailboxSearch(new ilMailRfc822AddressParserFactory());
            $local_selects = [];
            foreach ($roles as $role_id => $role) {
                if ($role['type'] == 'Local') {
                    $searchName = (strpos($role['name'], '#') === 0) ? $role['name'] : '#' . $role['name'];
                    $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                    $pre_select = count($matching_role_ids) == 1 ? $role_id . '-' . $matching_role_ids[0] : 'ignore';

                    $selectable_roles = [];
                    if ($this->object->getRefId() == USER_FOLDER_ID) {
                        // There are too many roles in a large ILIAS installation
                        // that's why whe show only a choice with the the option 'ignore',
                        // and the matching roles.
                        $selectable_roles['ignore'] = $this->lng->txt('usrimport_ignore_role');
                        foreach ($matching_role_ids as $id) {
                            $selectable_roles[$role_id . '-' . $id] = $l_roles[$id];
                        }
                    } else {
                        foreach ($l_roles as $local_role_id => $value) {
                            if ($local_role_id !== 'ignore') {
                                $selectable_roles[$role_id . '-' . $local_role_id] = $value;
                            }
                        }
                    }

                    if (count($selectable_roles) > 0) {
                        $select = $this->ui_factory->input()->field()
                            ->select($role['name'], $selectable_roles)
                            ->withRequired(true);
                        if (array_key_exists($pre_select, $selectable_roles)) {
                            $select = $select->withValue($pre_select);
                        }
                        $local_selects[] = $select;
                    }
                }
            }
        }

        $handlers = [
            ilUserImportParser::IL_IGNORE_ON_CONFLICT => $this->lng->txt('ignore_on_conflict'),
            ilUserImportParser::IL_UPDATE_ON_CONFLICT => $this->lng->txt('update_on_conflict')
        ];

        $conflict_action_select = $this->ui_factory->input()->field()
            ->select(
                $this->lng->txt('conflict_handling'),
                $handlers,
                str_replace(
                    '\n',
                    '<br>',
                    $this->lng->txt('usrimport_conflict_handling_info')
                )
            )
            ->withValue(ilUserImportParser::IL_IGNORE_ON_CONFLICT)
            ->withRequired(true);

        // new account mail
        $this->lng->loadLanguageModule('mail');
        $amail = ilObjUserFolder::_lookupNewAccountMail($this->lng->getDefaultLanguage());
        $mail_section = null;
        if (trim($amail['body'] ?? '') != '' && trim($amail['subject'] ?? '') != '') {
            $send_checkbox = $this->ui_factory->input()->field()->checkbox($this->lng->txt('user_send_new_account_mail'))
                                ->withValue(true);

            $mail_section = $this->ui_factory->input()->field()->section(
                [$send_checkbox],
                $this->lng->txt('mail_account_mail')
            );
        }

        $file_info_section = $this->ui_factory->input()->field()->section(
            [
                'filename' => $roles_import_filename,
                'import_count' => $roles_import_count,
            ],
            $this->lng->txt('file_info')
        );

        $form_action = $this->ctrl->getFormActionByClass('ilObjUserFolderGui', 'importUsers');

        $form_elements = [
            'file_info' => $file_info_section
        ];

        if (!empty($global_selects)) {
            $global_role_info_section = $this->ui_factory->input()
                ->field()
                ->section([$global_roles_assignment_info], $this->lng->txt('global_role_assignment'));
            $global_role_selection_section = $this->ui_factory->input()->field()->section($global_selects, '');
            $form_elements['global_role_info'] = $global_role_info_section;
            $form_elements['global_role_selection'] = $global_role_selection_section;
        }

        if (!empty($local_selects)) {
            $local_role_info_section = $this->ui_factory->input()->field()->section(
                [$local_roles_assignment_info],
                $this->lng->txt('local_role_assignment')
            );
            $local_role_selection_section = $this->ui_factory->input()->field()->section(
                $local_selects,
                ''
            );

            $form_elements['local_role_info'] = $local_role_info_section;
            $form_elements['local_role_selection'] = $local_role_selection_section;
        }

        $form_elements['conflict_action'] = $this->ui_factory->input()->field()->section([$conflict_action_select], '');

        if ($mail_section !== null) {
            $form_elements['send_mail'] = $mail_section;
        }

        return [$this->ui_factory->input()->container()->form()->standard(
            $form_action,
            $form_elements
        ), $message];
    }

    private function handleUploadedFiles(): string
    {
        $subdir = '';
        $xml_file = '';

        $import_dir = $this->getImportDir();

        if (!$this->upload->hasBeenProcessed()) {
            $this->upload->process();
        }

        // recreate user import directory
        if ($this->filesystem->hasDir($import_dir)) {
            $this->filesystem->deleteDir($import_dir);
        }
        $this->filesystem->createDir($import_dir);

        foreach ($this->upload->getResults() as $single_file_upload) {
            $file_name = $single_file_upload->getName();
            $parts = pathinfo($file_name);

            //check if upload status is ok
            if (!$single_file_upload->isOK()) {
                $this->filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt('no_import_file_found'),
                    $this->ilias->error_obj->MESSAGE
                );
            }

            // move uploaded file to user import directory
            $this->upload->moveFilesTo(
                $import_dir,
                \ILIAS\FileUpload\Location::STORAGE
            );

            // handle zip file
            if ($single_file_upload->getMimeType() == 'application/zip') {
                // Workaround: unzip function needs full path to file. Should be replaced once Filesystem has own unzip implementation
                $full_path = ilFileUtils::getDataDir() . '/user_import/usr_'
                    . $this->user->getId() . '_' . session_id() . '/' . $file_name;
                $this->dic->legacyArchives()->unzip($full_path);

                $xml_file = null;
                $file_list = $this->filesystem->listContents($import_dir);

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
                    $this->filesystem->delete($a_file->getPath());
                }

                if (is_null($xml_file)) {
                    $subdir = basename(
                        $parts['basename'],
                        '.' . $parts['extension']
                    );
                    $xml_file = $import_dir . '/' . $subdir . '/' . $subdir . '.xml';
                }
            } // handle xml file
            else {
                $a = $this->filesystem->listContents($import_dir);
                $file = end($a);
                $xml_file = $file->getPath();
            }

            // check xml file
            if (!$this->filesystem->has($xml_file)) {
                $this->filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt('no_xml_file_found_in_zip')
                    . ' ' . $subdir . '/' . $subdir . '.xml',
                    $this->ilias->error_obj->MESSAGE
                );
            }
        }

        return $xml_file;
    }

    public function verifyXmlData(ilUserImportParser $import_parser): string
    {
        $import_dir = $this->getImportDir();
        switch ($import_parser->getErrorLevel()) {
            case ilUserImportParser::IL_IMPORT_SUCCESS:
                return '';
            case ilUserImportParser::IL_IMPORT_WARNING:
                return $import_parser->getProtocolAsHTML($this->lng->txt("verification_warning_log"));
            case ilUserImportParser::IL_IMPORT_FAILURE:
                $this->filesystem->deleteDir($import_dir);
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('verification_failed') . $import_parser->getProtocolAsHTML(
                        $this->lng->txt('verification_failure_log')
                    ),
                    true
                );
                $this->ctrl->redirectByClass(self::class, 'importUserForm');
        }
    }

    /**
     * Import Users with new form implementation
     */
    public function importUsersObject(): void
    {
        $result = [];
        $xml_file = '';
        $import_dir = $this->getImportDir();

        $file_list = $this->filesystem->listContents($import_dir);

        if (count($file_list) > 1) {
            $this->filesystem->deleteDir($import_dir);
            $this->ilias->raiseError(
                $this->lng->txt('usrimport_wrong_file_count'),
                $this->ilias->error_obj->MESSAGE
            );
            if ($this->inAdministration()) {
                $this->ctrl->redirect(
                    $this,
                    'view'
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

        if ($this->user_request->isPost()) {
            $form = $this->initUserRoleAssignmentForm($xml_path)[0]->withRequest($this->user_request->getRequest());
            $result = $form->getData();
        } else {
            $this->ilias->raiseError(
                $this->lng->txt('usrimport_form_not_evaluabe'),
                $this->ilias->error_obj->MESSAGE
            );
            if ($this->inAdministration()) {
                $this->ctrl->redirect(
                    $this,
                    'view'
                );
            } else {
                $this->ctrl->redirectByClass(
                    'ilobjcategorygui',
                    'listUsers'
                );
            }
        }

        $rule = $result['conflict_action'][0] ?? 1;

        //If local roles exist, merge the roles that are to be assigned, otherwise just take the array that has global roles
        $local_role_selection = (array) ($result['local_role_selection'] ?? []);
        $global_role_selection = (array) ($result['global_role_selection'] ?? []);
        $roles = array_merge(
            $local_role_selection,
            $global_role_selection
        );

        $role_assignment = [];
        foreach ($roles as $value) {
            $keys = explode(
                '-',
                $value
            );
            if (count($keys) === 2) {
                $role_assignment[$keys[0]] = $keys[1];
            }
        }

        $import_parser = new ilUserImportParser(
            $xml_path,
            ilUserImportParser::IL_USER_IMPORT,
            (int) $rule
        );
        $import_parser->setFolderId($this->getUserOwnerId());

        // Catch hack attempts
        // We check here again, if the role folders are in the tree, and if the
        // user has permission on the roles.
        if (!empty($role_assignment)) {
            $global_roles = $this->rbac_review->getGlobalRoles();
            $roles_of_user = $this->rbac_review->assignedRoles($this->user->getId());
            foreach ($role_assignment as $role_id) {
                if ($role_id != '') {
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
                                $this->filesystem->deleteDir($import_dir);
                                $this->ilias->raiseError(
                                    $this->lng->txt('usrimport_with_specified_role_not_permitted'),
                                    $this->ilias->error_obj->MESSAGE
                                );
                            }
                        }
                    } else {
                        $rolf = $this->rbac_review->getFoldersAssignedToRole(
                            $role_id,
                            true
                        );
                        if ($this->rbac_review->isDeleted($rolf[0])
                            || !$this->rbac_system->checkAccess(
                                'write',
                                $rolf[0]
                            )) {
                            $this->filesystem->deleteDir($import_dir);
                            $this->ilias->raiseError(
                                $this->lng->txt('usrimport_with_specified_role_not_permitted'),
                                $this->ilias->error_obj->MESSAGE
                            );
                            return;
                        }
                    }
                }
            }
        }

        if (isset($result['send_mail'])) {
            $import_parser->setSendMail($result['send_mail'][0]);
        }

        $import_parser->setRoleAssignment($role_assignment);
        $import_parser->startParsing();

        // purge user import directory
        $this->filesystem->deleteDir($import_dir);

        switch ($import_parser->getErrorLevel()) {
            case ilUserImportParser::IL_IMPORT_SUCCESS:
                $this->tpl->setOnScreenMessage(
                    'success',
                    $this->lng->txt('user_imported'),
                    true
                );
                break;
            case ilUserImportParser::IL_IMPORT_WARNING:
                $this->tpl->setOnScreenMessage(
                    'success',
                    $this->lng->txt('user_imported_with_warnings')
                    . $import_parser->getProtocolAsHTML(
                        $this->lng->txt('import_warning_log')
                    ),
                    true
                );
                break;
            case ilUserImportParser::IL_IMPORT_FAILURE:
                $this->ilias->raiseError(
                    $this->lng->txt('user_import_failed')
                    . $import_parser->getProtocolAsHTML($this->lng->txt('import_failure_log')),
                    $this->ilias->error_obj->MESSAGE
                );
                break;
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                'view'
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
        $this->initFormGeneralSettings();

        $aset = ilUserAccountSettings::getInstance();

        $show_blocking_time_in_days = $this->settings->get('loginname_change_blocking_time') / 86400;
        $show_blocking_time_in_days = (float) $show_blocking_time_in_days;

        $security = ilSecuritySettings::_getInstance();

        $settings = [
            'lua' => $aset->isLocalUserAdministrationEnabled(),
            'lrua' => $aset->isUserAccessRestricted(),
            'allow_change_loginname' => (bool) $this->settings->get('allow_change_loginname'),
            'create_history_loginname' => (bool) $this->settings->get('create_history_loginname'),
            'reuse_of_loginnames' => (bool) $this->settings->get('reuse_of_loginnames'),
            'loginname_change_blocking_time' => $show_blocking_time_in_days,
            'user_reactivate_code' => (int) $this->settings->get('user_reactivate_code'),
            'user_own_account' => (int) $this->settings->get('user_delete_own_account'),
            'user_own_account_email' => $this->settings->get('user_delete_own_account_email'),
            'dpro_withdrawal_usr_deletion' => (bool) $this->settings->get('dpro_withdrawal_usr_deletion'),
            'tos_withdrawal_usr_deletion' => (bool) $this->settings->get('tos_withdrawal_usr_deletion'),

            'session_handling_type' => $this->settings->get(
                'session_handling_type',
                (string) ilSession::SESSION_HANDLING_FIXED
            ),
            'session_reminder_enabled' => $this->settings->get('session_reminder_enabled'),
            'session_max_count' => $this->settings->get(
                'session_max_count',
                (string) ilSessionControl::DEFAULT_MAX_COUNT
            ),
            'session_min_idle' => $this->settings->get(
                'session_min_idle',
                (string) ilSessionControl::DEFAULT_MIN_IDLE
            ),
            'session_max_idle' => $this->settings->get(
                'session_max_idle',
                (string) ilSessionControl::DEFAULT_MAX_IDLE
            ),
            'session_max_idle_after_first_request' => $this->settings->get(
                'session_max_idle_after_first_request',
                (string) ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST
            ),

            'login_max_attempts' => $security->getLoginMaxAttempts(),
            'ps_prevent_simultaneous_logins' => (int) $security->isPreventionOfSimultaneousLoginsEnabled(),
            'password_assistance' => (bool) $this->settings->get('password_assistance'),
            'letter_avatars' => (int) $this->settings->get('letter_avatars'),
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
        $this->initFormGeneralSettings();
        if ($this->form->checkInput()) {
            $valid = true;
            if ($this->form->getInput('allow_change_loginname') === '1' &&
               !is_numeric($this->form->getInput('loginname_change_blocking_time'))) {
                $valid = false;
                $this->form->getItemByPostVar('loginname_change_blocking_time')
                           ->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
            }

            $security = ilSecuritySettings::_getInstance();

            // account security settings
            $security->setPasswordCharsAndNumbersEnabled(
                (bool) $this->form->getInput('password_chars_and_numbers_enabled')
            );
            $security->setPasswordSpecialCharsEnabled(
                (bool) $this->form->getInput('password_special_chars_enabled')
            );
            $security->setPasswordMinLength(
                (int) $this->form->getInput('password_min_length')
            );
            $security->setPasswordMaxLength(
                (int) $this->form->getInput('password_max_length')
            );
            $security->setPasswordNumberOfUppercaseChars(
                (int) $this->form->getInput('password_ucase_chars_num')
            );
            $security->setPasswordNumberOfLowercaseChars(
                (int) $this->form->getInput('password_lowercase_chars_num')
            );
            $security->setPasswordMaxAge(
                (int) $this->form->getInput('password_max_age')
            );
            $security->setLoginMaxAttempts(
                (int) $this->form->getInput('login_max_attempts')
            );
            $security->setPreventionOfSimultaneousLogins(
                (bool) $this->form->getInput('ps_prevent_simultaneous_logins')
            );
            $security->setPasswordChangeOnFirstLoginEnabled(
                (bool) $this->form->getInput('password_change_on_first_login_enabled')
            );
            $security->setPasswordMustNotContainLoginnameStatus(
                (bool) $this->form->getInput('password_must_not_contain_loginame')
            );

            if ($security->validate($this->form) !== null) {
                $valid = false;
            }

            if ($valid) {
                $security->save();

                ilUserAccountSettings::getInstance()->enableLocalUserAdministration((bool) $this->form->getInput('lua'));
                ilUserAccountSettings::getInstance()->restrictUserAccess((bool) $this->form->getInput('lrua'));
                ilUserAccountSettings::getInstance()->update();

                $this->settings->set(
                    'allow_change_loginname',
                    $this->form->getInput('allow_change_loginname')
                );
                $this->settings->set(
                    'create_history_loginname',
                    $this->form->getInput('create_history_loginname')
                );
                $this->settings->set(
                    'reuse_of_loginnames',
                    $this->form->getInput('reuse_of_loginnames')
                );
                $save_blocking_time_in_seconds = (string) ((int) $this->form->getInput(
                    'loginname_change_blocking_time'
                ) * 86400);
                $this->settings->set(
                    'loginname_change_blocking_time',
                    $save_blocking_time_in_seconds
                );
                $this->settings->set(
                    'user_reactivate_code',
                    $this->form->getInput('user_reactivate_code')
                );

                $this->settings->set(
                    'user_delete_own_account',
                    $this->form->getInput('user_own_account')
                );
                $this->settings->set(
                    'user_delete_own_account_email',
                    $this->form->getInput('user_own_account_email')
                );
                $this->settings->set(
                    'dpro_withdrawal_usr_deletion',
                    $this->form->getInput('dpro_withdrawal_usr_deletion') === '1' ? '1' : '0'
                );
                $this->settings->set(
                    'tos_withdrawal_usr_deletion',
                    $this->form->getInput('tos_withdrawal_usr_deletion') === '1' ? '1' : '0'
                );

                $this->settings->set(
                    'password_assistance',
                    $this->form->getInput('password_assistance')
                );

                // BEGIN SESSION SETTINGS
                $this->settings->set(
                    'session_handling_type',
                    $this->form->getInput('session_handling_type')
                );

                if ($this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_FIXED) {
                    $this->settings->set(
                        'session_reminder_enabled',
                        $this->form->getInput('session_reminder_enabled')
                    );
                } elseif ($this->form->getInput(
                    'session_handling_type'
                ) == ilSession::SESSION_HANDLING_LOAD_DEPENDENT) {
                    if (
                        $this->settings->get(
                            'session_allow_client_maintenance',
                            (string) ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
                        )
                    ) {
                        // has to be done BEFORE updating the setting!
                        ilSessionStatistics::updateLimitLog((int) $this->form->getInput('session_max_count'));

                        $this->settings->set(
                            'session_max_count',
                            $this->form->getInput('session_max_count')
                        );
                        $this->settings->set(
                            'session_min_idle',
                            $this->form->getInput('session_min_idle')
                        );
                        $this->settings->set(
                            'session_max_idle',
                            $this->form->getInput('session_max_idle')
                        );
                        $this->settings->set(
                            'session_max_idle_after_first_request',
                            $this->form->getInput('session_max_idle_after_first_request')
                        );
                    }
                }
                // END SESSION SETTINGS
                $this->settings->set(
                    'letter_avatars',
                    $this->form->getInput('letter_avatars')
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
        ilUserPasswordManager::getInstance()->resetLastPasswordChangeForLocalUsers();
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

        $this->tpl->setOnScreenMessage(
            'question',
            $this->lng->txt('ps_passwd_policy_changed_force_user_reset')
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('yes'),
                $this->ctrl->getLinkTargetByClass(self::class, 'forceUserPasswordReset')
            )
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('no'),
                $this->ctrl->getLinkTargetByClass(self::class, 'generalSettings')
            )
        );
    }

    protected function initFormGeneralSettings(): void
    {
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
        $lua->setValue('1');
        $this->form->addItem($lua);

        $lrua = new ilCheckboxInputGUI(
            $this->lng->txt('restrict_user_access'),
            'lrua'
        );
        $lrua->setInfo($this->lng->txt('restrict_user_access_info'));
        $lrua->setValue('1');
        $this->form->addItem($lrua);

        $code = new ilCheckboxInputGUI(
            $this->lng->txt('user_account_code_setting'),
            'user_reactivate_code'
        );
        $code->setInfo($this->lng->txt('user_account_code_setting_info'));
        $this->form->addItem($code);

        $own = new ilCheckboxInputGUI(
            $this->lng->txt('user_allow_delete_own_account'),
            'user_own_account'
        );
        $this->form->addItem($own);
        $own_email = new ilEMailInputGUI(
            $this->lng->txt('user_delete_own_account_notification_email'),
            'user_own_account_email'
        );
        $own->addSubItem($own_email);

        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('dpro');
        $this->form->addItem($this->checkbox('tos_withdrawal_usr_deletion'));
        $this->form->addItem($this->checkbox('dpro_withdrawal_usr_deletion'));

        $allow_client_maintenance = $this->settings->get(
            'session_allow_client_maintenance',
            (string) ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
        );

        $ssettings = new ilRadioGroupInputGUI(
            $this->lng->txt('sess_mode'),
            'session_handling_type'
        );

        // first option, fixed session duration
        $fixed = new ilRadioOption(
            $this->lng->txt('sess_fixed_duration'),
            (string) ilSession::SESSION_HANDLING_FIXED
        );

        // create session reminder subform
        $cb = new ilCheckboxInputGUI(
            $this->lng->txt('session_reminder'),
            'session_reminder_enabled'
        );
        $expires = ilSession::getSessionExpireValue();
        $time = ilDatePresentation::secondsToString(
            $expires,
            true
        );
        $cb->setInfo(
            $this->lng->txt('session_reminder_info') . '<br />' .
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
            (string) ilSession::SESSION_HANDLING_LOAD_DEPENDENT
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
        if (!$allow_client_maintenance) {
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
        if (!$allow_client_maintenance) {
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
        if (!$allow_client_maintenance) {
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
        if (!$allow_client_maintenance) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // add session control to radio group
        $ssettings->addOption($ldsh);

        // add radio group to form
        if ($allow_client_maintenance) {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $this->form->addItem($ssettings);
        } else {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $ti = new ilNonEditableValueGUI(
                $this->lng->txt('session_config'),
                'session_config'
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
            $this->lng->txt('enable_password_assistance'),
            'password_assistance'
        );
        $cb->setInfo($this->lng->txt('password_assistance_info'));
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
        $objCb->setValue('1');
        $objCb->setInfo($this->lng->txt('ps_prevent_simultaneous_logins_info'));
        $this->form->addItem($objCb);

        $log = new ilFormSectionHeaderGUI();
        $log->setTitle($this->lng->txt('loginname_settings'));
        $this->form->addItem($log);

        $chbChangeLogin = new ilCheckboxInputGUI(
            $this->lng->txt('allow_change_loginname'),
            'allow_change_loginname'
        );
        $chbChangeLogin->setValue('1');
        $this->form->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI(
            $this->lng->txt('history_loginname'),
            'create_history_loginname'
        );
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue('1');

        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI(
            $this->lng->txt('reuse_of_loginnames_contained_in_history'),
            'reuse_of_loginnames'
        );
        $chbReuseLoginnames->setValue('1');
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
        $la->setValue('1');
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
        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('mail');
        $this->lng->loadLanguageModule('chatroom');
        $this->setSubTabs('settings');
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->activateSubTab('standard_fields');

        $tab = new ilUserFieldSettingsTableGUI(
            $this,
            'settings'
        );
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $this->tpl->setContent($tab->getHTML());
    }

    public function confirmSavedObject(): void
    {
        $this->saveGlobalUserSettingsObject('save');
    }

    public function saveGlobalUserSettingsObject(string $action = ''): void
    {
        $checked = $this->user_request->getChecked();
        $selected = $this->user_request->getSelect();

        $user_settings_config = $this->user_settings_config;

        // see ilUserFieldSettingsTableGUI
        $up = new ilUserProfile();
        $up->skipField('username');
        $field_properties = $up->getStandardFields();
        $profile_fields = array_keys($field_properties);

        $valid = true;
        foreach ($profile_fields as $field) {
            if (($checked['required_' . $field] ?? false) &&
                !(int) ($checked['visib_reg_' . $field] ?? null)
            ) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('invalid_visible_required_options_selected'));
            $this->confirm_change = 1;
            $this->settingsObject();
            return;
        }

        // For the following fields, the required state can not be changed
        $fixed_required_fields = [
            'firstname' => 1,
            'lastname' => 1,
            'upload' => 0,
            'password' => 0,
            'language' => 0,
            'skin_style' => 0,
            'hits_per_page' => 0,
            'hide_own_online_status' => 0
        ];

        // Reset user confirmation
        if ($action == 'save') {
            ilMemberAgreement::_reset();
        }

        $changed_fields = $this->collectChangedFields();
        if ($this->handleChangeListeners($changed_fields, $field_properties)) {
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

            if (!($checked['visible_' . $field] ?? false) && !($field_properties[$field]['visible_hide'] ?? false)) {
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

            if (!($checked['changeable_' . $field] ?? false) &&
                !($field_properties[$field]['changeable_hide'] ?? false)) {
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
            if (($checked['visib_reg_' . $field] ?? false) && !($field_properties[$field]['visib_reg_hide'] ?? false)) {
                $this->settings->set(
                    'usr_settings_visib_reg_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_visib_reg_' . $field,
                    '0'
                );
            }

            if ($checked['visib_lua_' . $field] ?? false) {
                $this->settings->set(
                    'usr_settings_visib_lua_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_visib_lua_' . $field,
                    '0'
                );
            }

            if ((int) ($checked['changeable_lua_' . $field] ?? false)) {
                $this->settings->set(
                    'usr_settings_changeable_lua_' . $field,
                    '1'
                );
            } else {
                $this->settings->set(
                    'usr_settings_changeable_lua_' . $field,
                    '0'
                );
            }

            if (($checked['export_' . $field] ?? false) && !($field_properties[$field]['export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_export_' . $field);
            }

            // Course export/visibility
            if (($checked['course_export_' . $field] ?? false) && !($field_properties[$field]['course_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_course_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_course_export_' . $field);
            }

            // Group export/visibility
            if (($checked['group_export_' . $field] ?? false) && !($field_properties[$field]['group_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_group_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_group_export_' . $field);
            }

            if (($checked['prg_export_' . $field] ?? false) && !($field_properties[$field]['prg_export_hide'] ?? false)) {
                $this->ilias->setSetting(
                    'usr_settings_prg_export_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('usr_settings_prg_export_' . $field);
            }

            $is_fixed = array_key_exists(
                $field,
                $fixed_required_fields
            );
            if (($is_fixed && $fixed_required_fields[$field]) || (!$is_fixed && ($checked['required_' . $field] ?? false))) {
                $this->ilias->setSetting(
                    'require_' . $field,
                    '1'
                );
            } else {
                $this->ilias->deleteSetting('require_' . $field);
            }
        }

        if ($selected['default_hits_per_page']) {
            $this->ilias->setSetting(
                'hits_per_page',
                $selected['default_hits_per_page']
            );
        }

        if (isset($checked['export_preferences']) && $checked['export_preferences'] === 1) {
            $this->ilias->setSetting(
                'usr_settings_export_preferences',
                '1'
            );
        } else {
            $this->ilias->deleteSetting('usr_settings_export_preferences');
        }

        $this->ilias->setSetting(
            'mail_incoming_mail',
            $selected['default_mail_incoming_mail']
        );
        $this->ilias->setSetting(
            'chat_osc_accept_msg',
            $selected['default_chat_osc_accept_msg']
        );
        $this->ilias->setSetting(
            'chat_broadcast_typing',
            $selected['default_chat_broadcast_typing']
        );
        $this->ilias->setSetting(
            'bs_allow_to_contact_me',
            $selected['default_bs_allow_to_contact_me']
        );
        $this->ilias->setSetting(
            'hide_own_online_status',
            $selected['default_hide_own_online_status']
        );

        if ($this->usrFieldChangeListenersAccepted && count($changed_fields) > 0) {
            $this->event->raise(
                'Services/User',
                'onUserFieldAttributesChanged',
                $changed_fields
            );
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('usr_settings_saved'));
        $this->settingsObject();
    }

    public function confirmUsrFieldChangeListenersObject(): void
    {
        $this->usrFieldChangeListenersAccepted = true;
        $this->confirmSavedObject();
    }

    /**
     * @param InterestedUserFieldChangeListener[] $interested_change_listeners
     */
    public function showFieldChangeComponentsListeningConfirmDialog(
        array $interested_change_listeners
    ): void {
        $post = $this->user_request->getParsedBody();
        $confirmDialog = new ilConfirmationGUI();
        $confirmDialog->setHeaderText($this->lng->txt('usr_field_change_components_listening'));
        $confirmDialog->setFormAction($this->ctrl->getFormActionByClass(
            [self::class],
            'settings'
        ));
        $confirmDialog->setConfirm($this->lng->txt('confirm'), 'confirmUsrFieldChangeListeners');
        $confirmDialog->setCancel($this->lng->txt('cancel'), 'settings');

        $tpl = new ilTemplate(
            'tpl.usr_field_change_listener_confirm.html',
            true,
            true,
            'Services/User'
        );

        foreach ($interested_change_listeners as $interested_change_listener) {
            $tpl->setVariable('FIELD_NAME', $interested_change_listener->getName());
            foreach ($interested_change_listener->getAttributes() as $attribute) {
                $tpl->setVariable('ATTRIBUTE_NAME', $attribute->getName());
                foreach ($attribute->getComponents() as $component) {
                    $tpl->setVariable('COMPONENT_NAME', $component->getComponentName());
                    $tpl->setVariable('DESCRIPTION', $component->getDescription());
                    $tpl->setCurrentBlock('component');
                    $tpl->parseCurrentBlock('component');
                }
                $tpl->setCurrentBlock('attribute');
                $tpl->parseCurrentBlock('attribute');
            }
            $tpl->setCurrentBlock('field');
            $tpl->parseCurrentBlock('field');
        }

        $confirmDialog->addItem('', '0', $tpl->get());

        foreach ($post['chb'] as $postVar => $value) {
            $confirmDialog->addHiddenItem('chb[$postVar]', $value);
        }
        foreach ($post['select'] as $postVar => $value) {
            $confirmDialog->addHiddenItem('select[$postVar]', $value);
        }
        foreach ($post['current'] as $postVar => $value) {
            $confirmDialog->addHiddenItem('current[$postVar]', $value);
        }
        $this->tpl->setContent($confirmDialog->getHTML());
    }

    /**
     * @param array<string, ChangedUserFieldAttribute> $changed_fields
     * @param array<string, array>                     $field_properties => See ilUserProfile::getStandardFields()
     * @return bool
     */
    public function handleChangeListeners(
        array $changed_fields,
        array $field_properties
    ): bool {
        if (count($changed_fields) > 0) {
            $interested_change_listeners = [];
            foreach ($field_properties as $field_name => $properties) {
                if (!isset($properties['change_listeners'])) {
                    continue;
                }

                foreach ($properties['change_listeners'] as $change_listener_class_name) {
                    /**
                     * @var UserFieldAttributesChangeListener $listener
                     */
                    $listener = new $change_listener_class_name($this->dic);
                    foreach ($changed_fields as $changed_field) {
                        $attribute_name = $changed_field->getAttributeName();
                        $description_for_field = $listener->getDescriptionForField($field_name, $attribute_name);
                        if ($description_for_field !== null && $description_for_field !== '') {
                            $interested_change_listener = null;
                            foreach ($interested_change_listeners as $interested_listener) {
                                if ($interested_listener->getFieldName() === $field_name) {
                                    $interested_change_listener = $interested_listener;
                                    break;
                                }
                            }

                            if ($interested_change_listener === null) {
                                $interested_change_listener = new InterestedUserFieldChangeListener(
                                    $this->getTranslationForField($field_name, $properties),
                                    $field_name
                                );
                                $interested_change_listeners[] = $interested_change_listener;
                            }

                            $interested_attribute = $interested_change_listener->addAttribute($attribute_name);
                            $interested_attribute->addComponent(
                                $listener->getComponentName(),
                                $description_for_field
                            );
                        }
                    }
                }
            }

            if (!$this->usrFieldChangeListenersAccepted && count($interested_change_listeners) > 0) {
                $this->showFieldChangeComponentsListeningConfirmDialog($interested_change_listeners);
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
        $changed_fields = [];
        $post = $this->user_request->getParsedBody();
        if (
            !isset($post['chb'])
            && !is_array($post['chb'])
            && !isset($post['current'])
            && !is_array($post['current'])
        ) {
            return $changed_fields;
        }

        $old = $post['current'];
        $new = $post['chb'];

        foreach ($old as $key => $oldValue) {
            if (!isset($new[$key])) {
                $isBoolean = filter_var($oldValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $new[$key] = $isBoolean ? '0' : $oldValue;
            }
        }

        $oldToNewDiff = array_diff_assoc($old, $new);

        foreach ($oldToNewDiff as $key => $oldValue) {
            $changed_fields[$key] = new ChangedUserFieldAttribute($key, $oldValue, $new[$key]);
        }

        return $changed_fields;
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
        $action[5] = $this->lng->txt('usr_filter_coursemember');
        $action[6] = $this->lng->txt('usr_filter_groupmember');
        $action[7] = $this->lng->txt('usr_filter_role');

        return ilLegacyFormElementsUtil::formSelect(
            ilSession::get('user_filter'),
            'user_filter',
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
                $this->lng->txt('no_checkbox'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (count($files) > 1) {
            $this->ilias->raiseError(
                $this->lng->txt('select_max_one_item'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $file = basename($files[0]);

        $export_dir = $this->object->getExportDirectory();
        ilFileDelivery::deliverFileLegacy(
            $export_dir . '/' . $file,
            $file
        );
    }

    public function confirmDeleteExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        if (count($files) == 0) {
            $this->ilias->raiseError(
                $this->lng->txt('no_checkbox'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt('info_delete_sure'));
        $cgui->setCancel(
            $this->lng->txt('cancel'),
            'cancelDeleteExportFile'
        );
        $cgui->setConfirm(
            $this->lng->txt('confirm'),
            'deleteExportFile'
        );

        // BEGIN TABLE DATA
        foreach ($files as $file) {
            $cgui->addItem(
                'file[]',
                $file,
                $file,
                ilObject::_getIcon($this->object->getId()),
                $this->lng->txt('obj_usrf')
            );
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelDeleteExportFileObject(): void
    {
        $this->ctrl->redirectByClass(
            'ilobjuserfoldergui',
            'export'
        );
    }

    public function deleteExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        $export_dir = $this->object->getExportDirectory();
        foreach ($files as $file) {
            $file = basename($file);

            $exp_file = $export_dir . '/' . $file;
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
        }
        $this->ctrl->redirectByClass(
            'ilobjuserfoldergui',
            'export'
        );
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function performExportObject(): void
    {
        $this->checkPermission('write,read_users');

        $this->object->buildExportFile($this->user_request->getExportType());
        $this->ctrl->redirect(
            $this,
            'export'
        );
    }

    public function exportObject(): void
    {
        $this->checkPermission('write,read_users');

        $export_types = [
            'userfolder_export_excel_x86',
            'userfolder_export_csv',
            'userfolder_export_xml'
        ];
        $options = [];
        foreach ($export_types as $type) {
            $this->ctrl->setParameterByClass(self::class, 'export_type', $type);
            $options[] = $this->ui_factory->button()->shy(
                $this->lng->txt($type),
                $this->ctrl->getLinkTargetByClass(self::class, 'performExport')
            );
        }
        $type_selection = $this->ui_factory->dropdown()->standard($options)
            ->withLabel($this->lng->txt('create_export_file'));

        $this->toolbar->addComponent(
            $type_selection,
            true
        );

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
        $this->lng->loadLanguageModule('meta');
        $this->lng->loadLanguageModule('mail');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitleIcon(ilUtil::getImagePath('standard/icon_mail.svg'));
        $form->setTitle($this->lng->txt('user_new_account_mail'));
        $form->setDescription($this->lng->txt('user_new_account_mail_desc'));

        $langs = $this->lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            $amail = ilObjUserFolder::_lookupNewAccountMail($lang_key);

            $title = $this->lng->txt('meta_l_' . $lang_key);
            if ($lang_key == $this->lng->getDefaultLanguage()) {
                $title .= ' (' . $this->lng->txt('default') . ')';
            }

            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($title);
            $form->addItem($header);

            $subj = new ilTextInputGUI(
                $this->lng->txt('subject'),
                'subject_' . $lang_key
            );
            $subj->setValue($amail['subject'] ?? '');
            $form->addItem($subj);

            $salg = new ilTextInputGUI(
                $this->lng->txt('mail_salutation_general'),
                'sal_g_' . $lang_key
            );
            $salg->setValue($amail['sal_g'] ?? '');
            $form->addItem($salg);

            $salf = new ilTextInputGUI(
                $this->lng->txt('mail_salutation_female'),
                'sal_f_' . $lang_key
            );
            $salf->setValue($amail['sal_f'] ?? '');
            $form->addItem($salf);

            $salm = new ilTextInputGUI(
                $this->lng->txt('mail_salutation_male'),
                'sal_m_' . $lang_key
            );
            $salm->setValue($amail['sal_m'] ?? '');
            $form->addItem($salm);

            $body = new ilTextAreaInputGUI(
                $this->lng->txt('message_content'),
                'body_' . $lang_key
            );
            $body->setValue($amail['body'] ?? '');
            $body->setRows(10);
            $body->setCols(100);
            $form->addItem($body);

            $att = new ilFileInputGUI(
                $this->lng->txt('attachment'),
                'att_' . $lang_key
            );
            $att->setAllowDeletion(true);
            if ($amail['att_file'] ?? false) {
                $att->setValue($amail['att_file']);
            }
            $form->addItem($att);
        }

        $form->addCommandButton(
            'saveNewAccountMail',
            $this->lng->txt('save')
        );
        $form->addCommandButton(
            'cancelNewAccountMail',
            $this->lng->txt('cancel')
        );

        return $form;
    }

    public function newAccountMailObject(ilPropertyFormGUI $form = null): void
    {
        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('user_new_account_mail');

        if ($form === null) {
            $form = $this->initNewAccountMailForm();
        }

        $ftpl = new ilTemplate(
            'tpl.usrf_new_account_mail.html',
            true,
            true,
            'Services/User'
        );
        $ftpl->setVariable(
            'FORM',
            $form->getHTML()
        );

        // placeholder help text
        $ftpl->setVariable(
            'TXT_USE_PLACEHOLDERS',
            $this->lng->txt('mail_nacc_use_placeholder')
        );
        $ftpl->setVariable(
            'TXT_MAIL_SALUTATION',
            $this->lng->txt('mail_nacc_salutation')
        );
        $ftpl->setVariable(
            'TXT_FIRST_NAME',
            $this->lng->txt('firstname')
        );
        $ftpl->setVariable(
            'TXT_LAST_NAME',
            $this->lng->txt('lastname')
        );
        $ftpl->setVariable(
            'TXT_EMAIL',
            $this->lng->txt('email')
        );
        $ftpl->setVariable(
            'TXT_LOGIN',
            $this->lng->txt('mail_nacc_login')
        );
        $ftpl->setVariable(
            'TXT_PASSWORD',
            $this->lng->txt('password')
        );
        $ftpl->setVariable(
            'TXT_PASSWORD_BLOCK',
            $this->lng->txt('mail_nacc_pw_block')
        );
        $ftpl->setVariable(
            'TXT_NOPASSWORD_BLOCK',
            $this->lng->txt('mail_nacc_no_pw_block')
        );
        $ftpl->setVariable(
            'TXT_ADMIN_MAIL',
            $this->lng->txt('mail_nacc_admin_mail')
        );
        $ftpl->setVariable(
            'TXT_ILIAS_URL',
            $this->lng->txt('mail_nacc_ilias_url')
        );
        $ftpl->setVariable(
            'TXT_INSTALLATION_NAME',
            $this->lng->txt('mail_nacc_installation_name')
        );
        $ftpl->setVariable(
            'TXT_TARGET',
            $this->lng->txt('mail_nacc_target')
        );
        $ftpl->setVariable(
            'TXT_TARGET_TITLE',
            $this->lng->txt('mail_nacc_target_title')
        );
        $ftpl->setVariable(
            'TXT_TARGET_TYPE',
            $this->lng->txt('mail_nacc_target_type')
        );
        $ftpl->setVariable(
            'TXT_TARGET_BLOCK',
            $this->lng->txt('mail_nacc_target_block')
        );
        $ftpl->setVariable(
            'TXT_IF_TIMELIMIT',
            $this->lng->txt('mail_nacc_if_timelimit')
        );
        $ftpl->setVariable(
            'TXT_TIMELIMIT',
            $this->lng->txt('mail_nacc_timelimit')
        );

        $this->tpl->setContent($ftpl->get());
    }

    public function cancelNewAccountMailObject(): void
    {
        $this->ctrl->redirect(
            $this,
            'settings'
        );
    }

    public function saveNewAccountMailObject(): void
    {
        $form = $this->initNewAccountMailForm();

        // If all forms in ILIAS use the UI/KS forms (here and in Services/Mail), we should move this to a proper constraint/trafo
        $is_valid_template_syntax = $this->dic->refinery()->custom()->constraint(function ($value): bool {
            try {
                $this->dic->mail()->mustacheFactory()->getBasicEngine()->render((string) $value, []);
                return true;
            } catch (Exception) {
                return false;
            }
        }, $this->dic->language()->txt('mail_template_invalid_tpl_syntax'));

        $valid_templates = true;
        $langs = $this->lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            $subject = $this->user_request->getMailSubject($lang_key);
            try {
                $is_valid_template_syntax->check($subject);
            } catch (Exception) {
                $form->getItemByPostVar('subject_' . $lang_key)->setAlert(
                    $is_valid_template_syntax->problemWith($subject)
                );
                $valid_templates = false;
            }

            $body = $this->user_request->getMailBody($lang_key);
            try {
                $is_valid_template_syntax->check($body);
            } catch (Exception) {
                $form->getItemByPostVar('body_' . $lang_key)->setAlert(
                    $is_valid_template_syntax->problemWith($body)
                );
                $valid_templates = false;
            }
        }
        if (!$valid_templates) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->newAccountMailObject($form);
            return;
        }

        foreach ($langs as $lang_key) {
            ilObjUserFolder::_writeNewAccountMail(
                $lang_key,
                $this->user_request->getMailSubject($lang_key),
                $this->user_request->getMailSalutation('g', $lang_key),
                $this->user_request->getMailSalutation('f', $lang_key),
                $this->user_request->getMailSalutation('m', $lang_key),
                $this->user_request->getMailBody($lang_key)
            );

            if ($_FILES['att_' . $lang_key]['tmp_name']) {
                ilObjUserFolder::_updateAccountMailAttachment(
                    $lang_key,
                    $_FILES['att_' . $lang_key]['tmp_name'],
                    $_FILES['att_' . $lang_key]['name']
                );
            }

            if ($this->user_request->getMailAttDelete($lang_key)) {
                ilObjUserFolder::_deleteAccountMailAttachment($lang_key);
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect(
            $this,
            'newAccountMail'
        );
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        if ($this->rbac_system->checkAccess(
            'visible,read',
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                'usrf',
                $this->ctrl->getLinkTarget(
                    $this,
                    'view'
                ),
                ['view', 'delete', 'resetFilter', 'userAction', ''],
                '',
                ''
            );
        }

        if ($this->access->checkRbacOrPositionPermissionAccess(
            'read',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            $this->tabs_gui->addTarget(
                'search_user_extended',
                $this->ctrl->getLinkTargetByClass(
                    'ilRepositorySearchGUI',
                    ''
                ),
                [],
                'ilrepositorysearchgui',
                ''
            );
        }

        if ($this->rbac_system->checkAccess(
            'write',
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTarget(
                    $this,
                    'generalSettings'
                ),
                [
                    'askForUserPasswordReset',
                    'forceUserPasswordReset',
                    'settings',
                    'generalSettings',
                    'listUserDefinedField',
                    'newAccountMail'
                ]
            );

            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTarget(
                    $this,
                    'export'
                ),
                'export',
                '',
                ''
            );
        }

        if ($this->rbac_system->checkAccess(
            'edit_permission',
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass(
                    [get_class($this), 'ilpermissiongui'],
                    'perm'
                ),
                ['perm', 'info', 'owner'],
                'ilpermissiongui'
            );
        }
    }

    public function setSubTabs(string $a_tab): void
    {
        switch ($a_tab) {
            case 'settings':
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
                    'standard_fields',
                    $this->ctrl->getLinkTarget(
                        $this,
                        'settings'
                    ),
                    ['settings', 'saveGlobalUserSettings'],
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    'user_defined_fields',
                    $this->ctrl->getLinkTargetByClass(
                        'ilcustomuserfieldsgui',
                        'listUserDefinedFields'
                    ),
                    'listUserDefinedFields',
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    'user_new_account_mail',
                    $this->ctrl->getLinkTarget(
                        $this,
                        'newAccountMail'
                    ),
                    'newAccountMail',
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    'starting_points',
                    $this->ctrl->getLinkTargetByClass(
                        'iluserstartingpointgui',
                        'startingPoints'
                    ),
                    'startingPoints',
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    'user_profile_info',
                    $this->ctrl->getLinkTargetByClass(
                        'ilUserProfileInfoSettingsGUI',
                        ''
                    ),
                    '',
                    'ilUserProfileInfoSettingsGUI'
                );

                break;
        }
    }

    public function showLoginnameSettingsObject(): void
    {
        $show_blocking_time_in_days = (int) $this->settings->get('loginname_change_blocking_time') / 86400;

        $this->initLoginSettingsForm();
        $this->loginSettingsForm->setValuesByArray(
            [
                'allow_change_loginname' => (bool) $this->settings->get('allow_change_loginname'),
                'create_history_loginname' => (bool) $this->settings->get('create_history_loginname'),
                'reuse_of_loginnames' => (bool) $this->settings->get('reuse_of_loginnames'),
                'loginname_change_blocking_time' => (float) $show_blocking_time_in_days
            ]
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
        $chbChangeLogin->setValue('1');
        $this->loginSettingsForm->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI(
            $this->lng->txt('history_loginname'),
            'create_history_loginname'
        );
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue('1');
        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI(
            $this->lng->txt('reuse_of_loginnames_contained_in_history'),
            'reuse_of_loginnames'
        );
        $chbReuseLoginnames->setValue('1');
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

                $this->settings->set(
                    'allow_change_loginname',
                    (string) $this->loginSettingsForm->getInput('allow_change_loginname')
                );
                $this->settings->set(
                    'create_history_loginname',
                    (string) $this->loginSettingsForm->getInput('create_history_loginname')
                );
                $this->settings->set(
                    'reuse_of_loginnames',
                    (string) $this->loginSettingsForm->getInput('reuse_of_loginnames')
                );
                $this->settings->set(
                    'loginname_change_blocking_time',
                    (string) $save_blocking_time_in_seconds
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

    public static function _goto(string $a_user): void
    {
        global $DIC;

        $a_user = (int) $a_user;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ctrl = $DIC['ilCtrl'];

        $a_target = USER_FOLDER_ID;

        if ($ilAccess->checkAccess(
            'read',
            '',
            $a_target
        )) {
            $ctrl->redirectToURL('ilias.php?baseClass=ilAdministrationGUI&ref_id=' . $a_target . '&jmpToUser=' . $a_user);
            exit;
        } else {
            if ($ilAccess->checkAccess(
                'read',
                '',
                ROOT_FOLDER_ID
            )) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError(
            $lng->txt('msg_no_perm_read'),
            $ilErr->FATAL
        );
    }

    /**
     * Jump to edit screen for user
     */
    public function jumpToUserObject(): void
    {
        $jump_to_user = $this->user_request->getJumpToUser();
        if (ilObject::_lookupType($jump_to_user) == 'usr') {
            $this->ctrl->setParameterByClass(
                'ilobjusergui',
                'obj_id',
                $jump_to_user
            );
            $this->ctrl->redirectByClass(
                'ilobjusergui',
                'view'
            );
        }
    }

    public function searchUserAccessFilterCallable(array $a_user_ids): array // Missing array type.
    {
        if (!$this->checkPermissionBool('read_users')) {
            $a_user_ids = $this->access->filterUserIdsByPositionOfCurrentUser(
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
        if (stripos($a_cmd, 'export') !== false) {
            $cmd = $a_cmd . 'Object';
            return $this->$cmd();
        }

        return $this->showActionConfirmation(
            $a_cmd,
            true
        );
    }

    public function getUserMultiCommands(bool $a_search_form = false): array // Missing array type.
    {
        $cmds = [];
        // see searchResultHandler()
        if ($a_search_form) {
            if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
                $cmds = [
                    'activate' => $this->lng->txt('activate'),
                    'deactivate' => $this->lng->txt('deactivate'),
                    'accessRestrict' => $this->lng->txt('accessRestrict'),
                    'accessFree' => $this->lng->txt('accessFree')
                ];
            }

            if ($this->rbac_system->checkAccess('delete', $this->object->getRefId())) {
                $cmds['delete'] = $this->lng->txt('delete');
            }
        } else {
            if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
                $cmds = [
                    'activateUsers' => $this->lng->txt('activate'),
                    'deactivateUsers' => $this->lng->txt('deactivate'),
                    'restrictAccess' => $this->lng->txt('accessRestrict'),
                    'freeAccess' => $this->lng->txt('accessFree')
                ];
            }

            if ($this->rbac_system->checkAccess('delete', $this->object->getRefId())) {
                $cmds['deleteUsers'] = $this->lng->txt('delete');
            }
        }

        if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $export_types = [
                'userfolder_export_excel_x86',
                'userfolder_export_csv',
                'userfolder_export_xml'
            ];
            foreach ($export_types as $type) {
                $cmd = explode(
                    '_',
                    $type
                );
                $cmd = array_pop($cmd);
                $cmds['usrExport' . ucfirst($cmd)] = $this->lng->txt('export') . ' - ' .
                    $this->lng->txt($type);
            }
        }

        // check if current user may send mails
        $mail = new ilMail($this->user->getId());
        if ($this->rbac_system->checkAccess(
            'internal_mail',
            $mail->getMailObjectReferenceId()
        )) {
            $cmds['mail'] = $this->lng->txt('send_mail');
        }

        $cmds['addToClipboard'] = $this->lng->txt('clipboard_add_btn');

        return $cmds;
    }

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
                'ilobjuserfoldergui',
                'export'
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

        if ($this->checkPermissionBool('write,read_users')) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_CSV,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                'ilobjuserfoldergui',
                'export'
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
        if ($this->checkPermissionBool('write,read_users')) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_XML,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                'ilobjuserfoldergui',
                'export'
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
        $list = new ilMailingLists($this->user);
        $list->deleteTemporaryLists();

        // create (temporary) mailing list
        $list = new ilMailingList($this->user);
        $list->setMode(ilMailingList::MODE_TEMPORARY);
        $list->setTitle('-TEMPORARY SYSTEM LIST-');
        $list->setDescription('-USER ACCOUNTS MAIL-');
        $list->setCreatedate(date('Y-m-d H:i:s'));
        $list->insert();
        $list_id = $list->getId();

        // after list has been saved...
        foreach ($user_ids as $user_id) {
            $list->assignUser((int) $user_id);
        }

        $umail = new ilFormatMail($this->user->getId());
        $mail_data = $umail->retrieveFromStage();

        $umail->persistToStage(
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

        $this->ctrl->redirectToURL(
            ilMailFormCall::getRedirectTarget(
                $this,
                '',
                [],
                ['type' => 'search_res']
            )
        );
    }

    public function addToExternalSettingsForm(int $a_form_id): array // Missing array type.
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:
                $security = ilSecuritySettings::_getInstance();

                $fields = [];

                $subitems = [
                    'ps_password_change_on_first_login_enabled' => [
                        $security->isPasswordChangeOnFirstLoginEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_must_not_contain_loginame' => [
                        $security->getPasswordMustNotContainLoginnameStatus(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_chars_and_numbers_enabled' => [
                        $security->isPasswordCharsAndNumbersEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_special_chars_enabled' => [
                        $security->isPasswordSpecialCharsEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_min_length' => $security->getPasswordMinLength(),
                    'ps_password_max_length' => $security->getPasswordMaxLength(),
                    'ps_password_uppercase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
                    'ps_password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
                    'ps_password_max_age' => $security->getPasswordMaxAge()
                ];
                $fields['ps_password_settings'] = [null, null, $subitems];

                $subitems = [
                    'ps_login_max_attempts' => $security->getLoginMaxAttempts(),
                    'ps_prevent_simultaneous_logins' => [
                        $security->isPreventionOfSimultaneousLoginsEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ]
                ];
                $fields['ps_security_protection'] = [null, null, $subitems];

                return [['generalSettings', $fields]];
        }
        return [];
    }

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

    private function checkbox(string $name): ilCheckboxInputGUI
    {
        $checkbox = new ilCheckboxInputGUI($this->lng->txt($name), $name);
        $checkbox->setInfo($this->lng->txt($name . '_desc'));
        $checkbox->setValue('1');

        return $checkbox;
    }
}
