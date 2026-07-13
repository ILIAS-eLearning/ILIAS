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
use ILIAS\User\UserGUIRequest;
use ILIAS\User\Context;
use ILIAS\User\Settings\Settings as UserSettings;
use ILIAS\User\Settings\SettingsImplementation as UserSettingsImplementation;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Services as ResourceStorageServices;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\LegalDocuments\Conductor;
use ILIAS\Repository\ExternalGUIService as RepositoryGUIs;

/**
 * Class ilObjUserGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjUserGUI: ilLearningProgressGUI, ilObjectOwnershipManagementGUI
 */
class ilObjUserGUI extends ilObjectGUI
{
    private ilPropertyFormGUI $form_gui;
    private UserGUIRequest $user_request;
    private ilHelpGUI $help;
    private ilTabsGUI $tabs;
    private RepositoryGUIs $repository_guis;
    private ilMailMimeSenderFactory $mail_sender_factory;

    private FileUpload $uploads;
    private ResourceStorageServices $irss;
    private ResourceStakeholder $stakeholder;

    private UserSettingsImplementation $user_settings;
    private Profile $user_profile;

    private string $requested_letter = '';
    private string $requested_baseClass = '';
    private string $requested_search = '';
    private array $selectable_roles;
    private int $default_role;
    private string $default_layout_and_style;

    private int $usrf_ref_id;
    private Context $context;
    private Conductor $legal_documents;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = false,
        bool $a_prepare_output = true
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->tabs = $DIC['ilTabs'];
        $this->help = $DIC['ilHelp'];
        $this->repository_guis = $DIC->repository()->gui();
        $this->mail_sender_factory = $DIC->mail()->mime()->senderFactory();

        $local_dic = LocalDIC::dic();
        $this->user_settings = $local_dic[UserSettings::class];
        $this->user_profile = $local_dic[Profile::class];

        $this->default_layout_and_style = $DIC['ilClientIniFile']->readVariable('layout', 'skin') .
                ':' . $DIC['ilClientIniFile']->readVariable('layout', 'style');

        $this->type = 'usr';
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->usrf_ref_id = $this->ref_id;
        $this->context = $this->usrf_ref_id === USER_FOLDER_ID
            ? Context::UserAdministration
            : Context::LocalUserAdministration;

        $this->uploads = $DIC->upload();
        $this->irss = $DIC->resourceStorage();
        $this->stakeholder = new ilUserProfilePictureStakeholder();

        $this->ctrl->saveParameter($this, ['obj_id', 'letter']);
        $this->ctrl->setParameterByClass('ilobjuserfoldergui', 'letter', $this->requested_letter);
        $this->lng->loadLanguageModule('user');

        $this->user_request = new UserGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->requested_letter = $this->user_request->getLetter();
        $this->requested_baseClass = $this->user_request->getBaseClass();
        $this->requested_search = $this->user_request->getSearch();
        $this->legal_documents = $DIC['legalDocuments'];

        $this->lng->loadLanguageModule('crs');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'illearningprogressgui':
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_USER_FOLDER,
                    USER_FOLDER_ID,
                    $this->object->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                break;

            case strtolower(ilObjectOwnershipManagementGUI::class):
                $this->ctrl->forwardCommand(
                    $this->repository_guis->ownershipManagementGUI(
                        $this->object->getId()
                    )
                );
                break;

            default:
                if ($cmd == '' || $cmd == 'view') {
                    $cmd = 'edit';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
    }

    protected function setTitleAndDescription(): void
    {
        if (strtolower(get_class($this->object)) == 'ilobjuser') {
            $this->tpl->setTitle('[' . $this->object->getLogin() . '] ' . $this->object->getTitle());
            $this->tpl->setDescription($this->object->getLongDescription());
            $this->tpl->setTitleIcon(
                ilUtil::getImagePath('standard/icon_' . $this->object->getType() . '.svg'),
                $this->lng->txt('obj_' . $this->object->getType())
            );
        } else {
            parent::setTitleAndDescription();
        }
    }

    public function cancelObject(): void
    {
        ilSession::clear('saved_post');

        if (strtolower($this->requested_baseClass) == 'iladministrationgui') {
            $this->ctrl->redirectByClass('ilobjuserfoldergui', 'view');
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $this->tabs_gui->clearTargets();

        $this->help->setScreenIdComponent('usr');

        if ($this->requested_search) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('search_results'),
                ilSession::get('usr_search_link')
            );

            $this->tabs_gui->addTarget(
                'properties',
                $this->ctrl->getLinkTarget($this, 'edit'),
                ['edit', '', 'view'],
                get_class($this),
                '',
                true
            );
        } else {
            $this->tabs_gui->addTarget(
                'properties',
                $this->ctrl->getLinkTarget($this, 'edit'),
                ['edit', '', 'view'],
                get_class($this)
            );
        }

        if ($this->checkAccessToRolesTab()) {
            $this->tabs_gui->addTarget(
                'role_assignment',
                $this->ctrl->getLinkTarget($this, 'roleassignment'),
                ['roleassignment'],
                get_class($this)
            );
        }

        if ((
            $this->context === Context::LocalUserAdministration
                && $this->rbac_system->checkAccess('read', $this->ref_id)
            || $this->context === Context::UserAdministration
                && $this->rbac_system->checkAccess(\ilObjUserFolder::PERM_READ_ALL, $this->ref_id)
        ) && ilObjUserTracking::_enabledLearningProgress()
            && ilObjUserTracking::_enabledUserRelatedData()) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass('illearningprogressgui', ''),
                '',
                ['illplistofobjectsgui', 'illplistofsettingsgui', 'illearningprogressgui', 'illplistofprogressgui']
            );
        }

        $this->tabs_gui->addTarget(
            'user_ownership',
            $this->ctrl->getLinkTargetByClass('ilobjectownershipmanagementgui', ''),
            '',
            'ilobjectownershipmanagementgui'
        );
    }

    private function initCreate(): void
    {
        if ($this->usrf_ref_id !== USER_FOLDER_ID) {
            $this->tabs_gui->clearTargets();
        }

        // role selection
        $obj_list = $this->rbac_review->getRoleListByObject(ROLE_FOLDER_ID);
        $rol = [];
        foreach ($obj_list as $obj_data) {
            // allow only 'assign_users' marked roles if called from category
            if ($this->object->getRefId() !== USER_FOLDER_ID && !in_array(
                SYSTEM_ROLE_ID,
                $this->rbac_review->assignedRoles($this->user->getId())
            )) {
                if (!ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])) {
                    continue;
                }
            }
            // exclude anonymous role from list
            if ($obj_data['obj_id'] !== ANONYMOUS_ROLE_ID) {
                // do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
                if ($obj_data['obj_id'] !== SYSTEM_ROLE_ID || in_array(
                    SYSTEM_ROLE_ID,
                    $this->rbac_review->assignedRoles($this->user->getId())
                )) {
                    $rol[$obj_data['obj_id']] = $obj_data['title'];
                }
            }
        }

        if ($rol === null) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_roles_users_can_be_assigned_to')
            );
            $this->redirectDependingOnParent();
        }

        $keys = array_keys($rol);

        // set pre defined user role to default
        if (in_array(4, $keys)) {
            $this->default_role = 4;
        } elseif (count($keys) > 1 && in_array(2, $keys)) {
            // remove admin role as preselectable role
            foreach ($keys as $key => $val) {
                if ($val == 2) {
                    unset($keys[$key]);
                    break;
                }
            }

            $this->default_role = array_shift($keys);
        }
        $this->selectable_roles = $rol;
    }

    /**
     * Display user create form
     */
    public function createObject(): void
    {
        if (!$this->rbac_system->checkAccess('create_usr', $this->usrf_ref_id)
            && !$this->rbac_system->checkAccess('cat_administrate_users', $this->usrf_ref_id)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('permission_denied')
            );
            $this->redirectDependingOnParent();
        }

        $this->initCreate();
        $this->initForm(true, null);
        $this->renderForm();
    }

    /**
     * save user data
     */
    public function saveObject(): void
    {
        if (!$this->rbac_system->checkAccess('create_usr', $this->usrf_ref_id)
            && !$this->access->checkAccess('cat_administrate_users', '', $this->usrf_ref_id)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_perm_modify_user')
            );
            $this->redirectToRefId($this->usrf_ref_id);
        }

        $this->initCreate();
        $profile_maybe_incomplete = $this->retrieveAllowIncompleteProfileFromPost();
        $this->initForm(!$profile_maybe_incomplete, null);

        if (!$this->form_gui->checkInput()
            || !$this->user_settings->performAdditionalChecks(
                $this->tpl,
                $this->form_gui
            )
        ) {
            $this->form_gui->setValuesByPost();
            $this->renderForm();
            return;
        }

        $new_user = new ilObjUser();
        $new_user->create();
        $new_user->saveAsNew();

        $user_object = $this->user_profile->addFormValuesToUser(
            $this->form_gui,
            $this->context,
            $new_user
        );

        $user_object->setLogin($this->form_gui->getInput('username'));
        if ($this->user->getId() === (int) SYSTEM_USER_ID
            || !in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->object->getId()))
            || in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId()))) {
            $user_object->setPasswd($this->form_gui->getInput('passwd'), ilObjUser::PASSWD_PLAIN);
        }
        if (ilAuthUtils::_isExternalAccountEnabled()) {
            $user_object->setExternalAccount($this->form_gui->getInput('ext_account'));
        }
        $user_object->setLastPasswordChangeTS(time());

        $user_object->setTitle($user_object->getFullname());
        $user_object->setDescription($user_object->getEmail());
        $user_object->update();

        $this->object = $this->user_settings->saveForm(
            $this->form_gui,
            [AvailablePages::MainSettings, AvailablePages::PrivacySettings],
            $this->context,
            $this->addValuesFromSystemInformationToUserSection($user_object, true)
        );

        //set role entries
        $this->rbac_admin->assignUser(
            (int) $this->form_gui->getInput('default_role'),
            $user_object->getId(),
            true
        );

        $msg = $this->lng->txt('user_added');

        $this->user->writePref(
            'send_info_mails',
            $this->form_gui->getInput('send_mail') === 'y' ? 'y' : 'n'
        );

        if ($profile_maybe_incomplete
            && $this->user_profile->isProfileIncomplete($this->object)) {
            $this->object->setProfileIncomplete(true);
            $this->object->update();
        }

        // send new account mail
        if ($this->form_gui->getInput('send_mail') == 'y') {
            $acc_mail = new ilAccountMail();
            $acc_mail->useLangVariablesAsFallback(true);
            $acc_mail->setUserPassword($this->form_gui->getInput('passwd'));
            $acc_mail->setUser($user_object);

            if ($acc_mail->send()) {
                $msg .= '<br />' . $this->lng->txt('mail_sent');
                $this->tpl->setOnScreenMessage('success', $msg, true);
            } else {
                $msg .= '<br />' . $this->lng->txt('mail_not_sent');
                $this->tpl->setOnScreenMessage('info', $msg, true);
            }
        } else {
            $this->tpl->setOnScreenMessage('success', $msg, true);
        }

        if (strtolower($this->requested_baseClass) === strtolower(ilAdministrationGUI::class)) {
            $this->ctrl->redirectByClass('ilobjuserfoldergui', 'view');
            return;
        }

        $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
    }

    public function updateObject(): void
    {
        $this->checkUserWritePermission();

        $profile_maybe_incomplete = $this->retrieveAllowIncompleteProfileFromPost();
        $this->initForm(!$profile_maybe_incomplete, $this->object);

        if (!$this->form_gui->checkInput()
            || !$this->user_settings->performAdditionalChecks(
                $this->tpl,
                $this->form_gui
            ) || !$this->isAccessRangeInputValid()) {
            $this->form_gui->setValuesByPost();
            $this->tabs_gui->activateTab('properties');
            $this->renderForm();
            return;
        }

        try {
            $this->object->updateLogin($this->form_gui->getInput('username'), $this->context);
        } catch (ilUserException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage());
            $this->form_gui->setValuesByPost();
            $this->renderForm();
            return;
        }

        $this->object->setAuthMode($this->form_gui->getInput('auth_mode'));

        $this->object = $this->user_profile->addFormValuesToUser(
            $this->form_gui,
            $this->context,
            $this->object
        );

        $passwd = $this->form_gui->getInput('passwd');
        if (($this->user->getId() === (int) SYSTEM_USER_ID
                || !in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->object->getId()))
                || in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId())))
            && !empty($passwd)) {
            $this->object->setPasswd($passwd, ilObjUser::PASSWD_PLAIN);
        }
        if (ilAuthUtils::_isExternalAccountEnabled()) {
            $this->object->setExternalAccount($this->form_gui->getInput('ext_account'));
        }

        $this->object->setTitle($this->object->getFullname());
        $this->object->setDescription($this->object->getEmail());

        $this->object = $this->user_settings->saveForm(
            $this->form_gui,
            [AvailablePages::MainSettings, AvailablePages::PrivacySettings],
            $this->context,
            $this->addValuesFromSystemInformationToUserSection($this->object, false)
        );

        $this->object->setLastPasswordChangeTS(time());
        $this->object->setProfileIncomplete(false);

        // If the current user is editing its own user account,
        // we update his preferences.
        if ($this->user->getId() === $this->object->getId()) {
            $this->user = $this->object;
        }
        $this->user->writePref(
            'send_info_mails',
            ($this->form_gui->getInput('send_mail') === 'y') ? 'y' : 'n'
        );

        $mail_message = $this->__sendProfileMail();
        $msg = $this->lng->txt('saved_successfully') . $mail_message;

        if ($profile_maybe_incomplete
            && $this->user_profile->isProfileIncomplete($this->object)) {
            $this->object->setProfileIncomplete(true);
            $this->object->update();
        }

        // feedback
        $this->tpl->setOnScreenMessage('success', $msg, true);

        if (strtolower($this->requested_baseClass) === strtolower(ilAdministrationGUI::class)) {
            $this->ctrl->redirectByClass('ilobjuserfoldergui', 'view');
            return;
        }

        $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
    }

    public function editObject(): void
    {
        $this->checkUserWritePermission();

        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            $this->tabs_gui->clearTargets();
        }

        // get form
        $this->initForm(true, $this->object);
        $this->renderForm();
    }

    /**
     * Init user form
     */
    private function initForm(
        bool $do_require,
        ?\ilObjUser $user
    ): void {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction(
            $this->ctrl->getFormActionByClass(self::class)
        );

        $this->form_gui->setTitle($this->lng->txt('usr_new'));
        if ($user !== null) {
            $this->form_gui->setTitle($this->lng->txt('usr_edit'));
        }

        $this->form_gui->addItem(
            $this->buildSectionHeader('login_data')
        );

        $this->form_gui->addItem(
            $this->buildAuthModeInput($user)
        );

        if ($user !== null) {
            $id = $this->buildNonEditableInput('usr_id', (string) $user->getId());
            $this->form_gui->addItem($id);
        }

        $this->form_gui->addItem(
            $this->buildLoginInput($user)
        );

        if ($this->user->getId() === (int) SYSTEM_USER_ID
            || !in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->object->getId()))
            || in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId()))) {
            $this->form_gui->addItem(
                $this->buildPasswordInput($user)
            );
        }

        if (ilAuthUtils::_isExternalAccountEnabled()) {
            $this->form_gui->addItem(
                $this->buildExternalAccountInput($user)
            );
        }

        $this->addSystemInformationSectionToForm($user);

        $this->form_gui = $this->user_profile->addFieldsToForm(
            $this->form_gui,
            $this->context,
            $do_require,
            $user,
            [Alias::class]
        );

        $this->form_gui->addItem(
            $this->buildSectionHeader('settings')
        );

        $this->form_gui = $this->user_settings->addSectionsToLegacyForm(
            $this->form_gui,
            [AvailablePages::MainSettings, AvailablePages::PrivacySettings],
            $this->context,
            $user
        );

        $this->addOptionsSectionToForm();

        if ($user === null) {
            $this->form_gui->addCommandButton('save', $this->lng->txt('save'));
        } else {
            $this->form_gui->addCommandButton('update', $this->lng->txt('save'));
        }
        $this->form_gui->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    private function buildSectionHeader(string $title_lang_var): ilFormSectionHeaderGUI
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt($title_lang_var));
        return $section;
    }

    private function buildNonEditableInput(
        string $identifier,
        string $value
    ): ilNonEditableValueGUI {
        $input = new ilNonEditableValueGUI($this->lng->txt($identifier), $identifier);
        $input->setValue($value);
        return $input;
    }

    private function buildAuthModeInput(
        ?\ilObjUser $user
    ): ilSelectInputGUI {
        $input = new ilSelectInputGUI($this->lng->txt('auth_mode'), 'auth_mode');
        $option = [];
        foreach (ilAuthUtils::_getActiveAuthModes() as $auth_name => $auth_key) {
            if ($auth_name == 'default') {
                $name = $this->lng->txt('auth_' . $auth_name)
                    . ' (' . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName((string) $auth_key)) . ')';
            } else {
                $name = ilAuthUtils::getAuthModeTranslation((string) $auth_key, $auth_name);
            }
            $option[$auth_name] = $name;
        }
        $input->setOptions($option);
        if ($user === null) {
            return $input;
        }

        $input->setValue($this->object->getAuthMode());
        return $input;
    }

    private function buildLoginInput(
        ?\ilObjUser $user
    ): ilUserLoginInputGUI {
        $field = $this->user_profile->getFieldByIdentifier('username');
        $input = $field->getLegacyInput(
            $this->lng,
            $this->context,
            $user
        );
        $input->setDisabled(!$this->context->isFieldChangeable($field, $user));
        $input->setRequired($this->context->isFieldChangeable($field, $user));
        return $input;
    }

    private function buildPasswordInput(
        ?\ilObjUser $user
    ): ilPasswordInputGUI {
        $input = new ilPasswordInputGUI($this->lng->txt('passwd'), 'passwd');
        $input->setUseStripSlashes(false);
        $input->setSize(32);
        $input->setMaxLength(80);
        $input->setValidateAuthPost('auth_mode');
        if ($user === null) {
            $input->setRequiredOnAuth(true);
        }
        if ($this->user->getId() !== (int) SYSTEM_USER_ID
            && in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId()))
            && !in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId()))) {
            $input->setDisabled(true);
        }
        $input->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
        return $input;
    }

    private function buildExternalAccountInput(
        ?\ilObjUser $user
    ): ilTextInputGUI {
        $input = new ilTextInputGUI($this->lng->txt('user_ext_account'), 'ext_account');
        $input->setSize(40);
        $input->setMaxLength(250);
        $input->setInfo($this->lng->txt('user_ext_account_desc'));
        if ($user === null) {
            return $input;
        }
        $input->setValue($user->getExternalAccount());
        return $input;
    }

    private function buildTimeLimitInput(
        ?\ilObjUser $user
    ) {
        $radg = new ilRadioGroupInputGUI($this->lng->txt('time_limit'), 'time_limit_unlimited');
        $radg->setRequired(true);
        $op1 = new ilRadioOption($this->lng->txt('user_access_unlimited'), '1');
        $radg->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt('user_access_limited'), '0');
        foreach ($this->buildTimeLimitDateInputs($user) as $input) {
            $op2->addSubItem($input);
        }
        $radg->addOption($op2);
        $radg->setValue(
            $user?->getTimeLimitUnlimited() ?? true ? '1' : '0'
        );

        return $radg;
    }

    /**
     *
     * @return array<\ilDateTimeInputGUI>
     */
    private function buildTimeLimitDateInputs(
        ?\ilObjUser $user
    ): Generator {
        $from = new ilDateTimeInputGUI($this->lng->txt('crs_from'), 'time_limit_from');
        $from->setRequired(true);
        $from->setShowTime(true);
        $from->setMinuteStepSize(1);
        $from->setDate(
            new ilDateTime($user?->getTimeLimitFrom(), IL_CAL_UNIX)
        );
        yield $from;

        $to = new ilDateTimeInputGUI($this->lng->txt('crs_to'), 'time_limit_until');
        $to->setRequired(true);
        $to->setShowTime(true);
        $to->setMinuteStepSize(1);
        $to->setDate(
            new ilDateTime($user?->getTimeLimitUntil(), IL_CAL_UNIX)
        );
        yield $to;
    }

    private function buildStatisticalInputs(
        ?\ilObjUser $user
    ): Generator {
        yield $this->buildNonEditableInput(
            'create_date',
            ilDatePresentation::formatDate(new ilDateTime(
                $user->getCreateDate(),
                IL_CAL_DATETIME
            ))
        );

        yield $this->buildNonEditableInput(
            'approve_date',
            ilDatePresentation::formatDate(new ilDateTime(
                $user->getApproveDate(),
                IL_CAL_DATETIME
            ))
        );

        yield $this->buildNonEditableInput(
            'last_login',
            ilDatePresentation::formatDate(new ilDateTime(
                $user->getLastLogin(),
                IL_CAL_DATETIME
            ))
        );

        yield $this->buildNonEditableInput('owner', $user->getOwnerName());
    }

    private function addSystemInformationSectionToForm(
        ?\ilObjUser $user
    ): void {
        $this->form_gui->addItem(
            $this->buildSectionHeader('system_information')
        );

        $this->addStatisticalInformationToForm($user);

        $ac = new ilCheckboxInputGUI($this->lng->txt('active'), 'active');
        $ac->setChecked(
            $user === null
                ? true
                : $user->getActive()
        );
        $this->form_gui->addItem($ac);

        $this->form_gui->addItem($this->buildTimeLimitInput($user));
    }

    private function addValuesFromSystemInformationToUserSection(
        \ilObjUser $user,
        bool $user_creation
    ): \ilObjUser {
        $from = $this->form_gui->getItemByPostVar('time_limit_from')->getDate();
        $user->setTimeLimitFrom($from ? $from->get(IL_CAL_UNIX) : null);
        $until = $this->form_gui->getItemByPostVar('time_limit_until')->getDate();
        $user->setTimeLimitUntil($until ? $until->get(IL_CAL_UNIX) : null);
        $user->setTimeLimitUnlimited($this->form_gui->getInput('time_limit_unlimited') === '1');

        if ($user_creation) {
            $user->setTimeLimitOwner($this->usrf_ref_id);
        }

        $active_from_input = $this->form_gui->getInput('active') === '1';
        if ($user->getActive() !== $active_from_input) {
            $user->setActive($active_from_input, $this->user->getId());
        }

        return $user;
    }

    private function addStatisticalInformationToForm(
        ?\ilObjUser $user
    ): void {
        if ($user === null) {
            return;
        }

        foreach ($this->buildStatisticalInputs($user) as $input) {
            $this->form_gui->addItem($input);
        }

        foreach ($this->legal_documents->userManagementFields($this->object) as $identifier => $value) {
            if (is_string($value)) {
                $value = $this->buildNonEditableInput($identifier, $value);
            }
            $this->form_gui->addItem($value);
        }
    }

    private function addOptionsSectionToForm(): void
    {
        $this->form_gui->addItem(
            $this->buildSectionHeader('user_admin_options')
        );

        $se = new ilCheckboxInputGUI($this->lng->txt('inform_user_mail'), 'send_mail');
        $se->setInfo($this->lng->txt('inform_user_mail_info'));
        $se->setValue('y');
        $se->setChecked(($this->user->getPref('send_info_mails') == 'y'));
        $this->form_gui->addItem($se);

        $irf = new ilCheckboxInputGUI($this->lng->txt('ignore_required_fields'), 'ignore_rf');
        $irf->setInfo($this->lng->txt('ignore_required_fields_info'));
        $irf->setValue('1');
        $this->form_gui->addItem($irf);
    }

    private function moveFileToStorage(): ?ResourceIdentification
    {
        $uploads = $this->uploads->getResults();
        $upload_tmp_name = $_FILES['userfile']['tmp_name'];
        $avatar_upload_result = $uploads[$upload_tmp_name] ?? null;

        $existing_rid = $this->object->getAvatarRid();
        $revision_title = 'Avatar for user ' . $this->object->getLogin();
        $this->stakeholder->setOwner($this->object->getId()); // The Resource is owned by the user we are editing

        if ($avatar_upload_result === null && file_exists($upload_tmp_name)) {
            $stream = Streams::ofResource(
                fopen($upload_tmp_name, 'r')
            );

            if ($existing_rid === null) {
                return $this->irss->manage()->stream($stream, $this->stakeholder, $revision_title);
            }

            $this->irss->manage()->appendNewRevisionFromStream(
                $existing_rid,
                $stream,
                $this->stakeholder,
                $revision_title
            );
            return $existing_rid;
        }

        if ($avatar_upload_result === null) {
            return null;
        }

        if ($existing_rid === null) {
            return $this->irss->manage()->upload(
                $avatar_upload_result,
                $this->stakeholder,
                $revision_title
            );
        }

        $this->irss->manage()->replaceWithUpload(
            $existing_rid,
            $avatar_upload_result,
            $this->stakeholder,
            $revision_title
        );
        return $existing_rid;
    }

    public function removeUserPictureObject(): void
    {
        $webspace_dir = ilFileUtils::getWebspaceDir();
        $image_dir = $webspace_dir . '/usr_images';
        $file = $image_dir . '/usr_' . $this->object->getId() . '.' . 'jpg';
        $thumb_file = $image_dir . '/usr_' . $this->object->getId() . '_small.jpg';
        $xthumb_file = $image_dir . '/usr_' . $this->object->getId() . '_xsmall.jpg';
        $xxthumb_file = $image_dir . '/usr_' . $this->object->getId() . '_xxsmall.jpg';
        $upload_file = $image_dir . '/upload_' . $this->object->getId();

        // remove user pref file name
        $this->object->setPref('profile_image', '');
        $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_image_removed'));

        if (is_file($file)) {
            unlink($file);
        }
        if (is_file($thumb_file)) {
            unlink($thumb_file);
        }
        if (is_file($xthumb_file)) {
            unlink($xthumb_file);
        }
        if (is_file($xxthumb_file)) {
            unlink($xxthumb_file);
        }
        if (is_file($upload_file)) {
            unlink($upload_file);
        }

        $this->editObject();
    }

    public function assignSaveObject(): void
    {
        if (!$this->rbac_system->checkAccess('edit_roleassignment', $this->usrf_ref_id)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_perm_assign_role_to_user')
            );
            $this->redirectDependingOnParent();
        }

        $selected_roles = $this->user_request->getRoleIds();
        $posted_roles = $this->user_request->getPostedRoleIds();

        // prevent unassignment of system role from system user
        if ($this->object->getId() == SYSTEM_USER_ID && in_array(SYSTEM_ROLE_ID, $posted_roles)) {
            $selected_roles[] = SYSTEM_ROLE_ID;
        }

        $global_roles_all = $this->rbac_review->getGlobalRoles();
        $assigned_roles_all = $this->rbac_review->assignedRoles($this->object->getId());
        $assigned_roles = array_intersect($assigned_roles_all, $posted_roles);
        $assigned_global_roles_all = array_intersect($assigned_roles_all, $global_roles_all);
        $assigned_global_roles = array_intersect($assigned_global_roles_all, $posted_roles);

        $user_not_allowed_to_change_admin_role_assginements =
            !in_array(SYSTEM_ROLE_ID, $this->rbac_review->assignedRoles($this->user->getId()));

        if ($user_not_allowed_to_change_admin_role_assginements
            && in_array(SYSTEM_ROLE_ID, $assigned_roles_all)) {
            $selected_roles[] = SYSTEM_ROLE_ID;
        }

        $posted_global_roles = array_intersect($selected_roles, $global_roles_all);

        if (empty($selected_roles) && count($assigned_roles_all) === count($assigned_roles)
             || empty($posted_global_roles) && count($assigned_global_roles_all) === count($assigned_global_roles)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                "{$this->lng->txt('action_aborted')}: {$this->lng->txt('msg_min_one_role')}",
                true
            );
            $this->ctrl->redirect($this, 'roleassignment');
        }

        foreach (array_diff($assigned_roles, $selected_roles) as $role) {
            if ($this->object->getId() === (int) SYSTEM_USER_ID && $role === SYSTEM_ROLE_ID
                || $user_not_allowed_to_change_admin_role_assginements && $role === SYSTEM_ROLE_ID) {
                continue;
            }
            $this->rbac_admin->deassignUser($role, $this->object->getId());
        }

        foreach (array_diff($selected_roles, $assigned_roles) as $role) {
            if ($this->object->getId() === (int) SYSTEM_USER_ID && $role === SYSTEM_ROLE_ID
                || $user_not_allowed_to_change_admin_role_assginements && $role === SYSTEM_ROLE_ID) {
                continue;
            }
            $this->rbac_admin->assignUser($role, $this->object->getId(), false);
        }

        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_roleassignment_changed'), true);

        if (strtolower($this->requested_baseClass) == 'iladministrationgui') {
            $this->ctrl->redirect($this, 'roleassignment');
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }

    public function roleassignmentObject(): void
    {
        $this->tabs->activateTab('role_assignment');

        if (!$this->checkAccessToRolesTab()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_perm_view_roles_of_user'),
                true
            );
            $this->ctrl->redirectByClass(self::class, 'edit');
        }

        $req_filtered_roles = $this->user_request->getFilteredRoles();
        ilSession::set(
            'filtered_roles',
            ($req_filtered_roles > 0) ? $req_filtered_roles : ilSession::get('filtered_roles')
        );

        $filtered_roles = ilSession::get('filtered_roles');
        if ($filtered_roles > 5) {
            ilSession::set('filtered_roles', 0);
        }

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.usr_role_assignment.html', 'components/ILIAS/User');

        // init table
        $tab = new ilRoleAssignmentTableGUI($this, 'roleassignment');

        $tab->parse($this->object->getId());
        $this->tpl->setVariable('ROLES_TABLE', $tab->getHTML());
    }

    public function applyFilterObject(): void
    {
        $table_gui = new ilRoleAssignmentTableGUI($this, 'roleassignment');
        $table_gui->writeFilterToSession();
        $table_gui->resetOffset();
        $this->roleassignmentObject();
    }

    public function resetFilterObject(): void
    {
        $table_gui = new ilRoleAssignmentTableGUI($this, 'roleassignment');
        $table_gui->resetOffset();
        $table_gui->resetFilter();
        $this->roleassignmentObject();
    }

    public function __getDateSelect(
        string $a_type,
        string $a_varname,
        string $a_selected
    ): string {
        $year = null;
        switch ($a_type) {
            case 'minute':
                for ($i = 0; $i <= 60; $i++) {
                    $days[$i] = $i < 10 ? '0' . $i : $i;
                }
                return ilLegacyFormElementsUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case 'hour':
                for ($i = 0; $i < 24; $i++) {
                    $days[$i] = $i < 10 ? '0' . $i : $i;
                }
                return ilLegacyFormElementsUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case 'day':
                for ($i = 1; $i < 32; $i++) {
                    $days[$i] = $i < 10 ? '0' . $i : $i;
                }
                return ilLegacyFormElementsUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case 'month':
                for ($i = 1; $i < 13; $i++) {
                    $month[$i] = $i < 10 ? '0' . $i : $i;
                }
                return ilLegacyFormElementsUtil::formSelect($a_selected, $a_varname, $month, false, true);

            case 'year':
                if ($a_selected < date('Y')) {
                    $start = $a_selected;
                } else {
                    $start = date('Y');
                }

                for ($i = $start; $i < ((int) date('Y') + 11); ++$i) {
                    $year[$i] = $i;
                }
                return ilLegacyFormElementsUtil::formSelect($a_selected, $a_varname, $year, false, true);
        }
        return '';
    }

    public function __toUnix(array $a_time_arr): int // Missing array type.
    {
        return mktime(
            $a_time_arr['hour'],
            $a_time_arr['minute'],
            $a_time_arr['second'],
            $a_time_arr['month'],
            $a_time_arr['day'],
            $a_time_arr['year']
        );
    }

    public function __unsetSessionVariables(): void
    {
        ilSession::clear('filtered_roles');
    }

    public function __buildFilterSelect(): string
    {
        $action[0] = $this->lng->txt('assigned_roles');
        $action[1] = $this->lng->txt('all_roles');
        $action[2] = $this->lng->txt('all_global_roles');
        $action[3] = $this->lng->txt('all_local_roles');
        $action[4] = $this->lng->txt('internal_local_roles_only');
        $action[5] = $this->lng->txt('non_internal_local_roles_only');

        return ilLegacyFormElementsUtil::formSelect(
            ilSession::get('filtered_roles'),
            'filter',
            $action,
            false,
            true
        );
    }

    /**
     * should be overwritten to add object specific items
     * (repository items are preloaded)
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        $this->locator->clearItems();

        if ($this->admin_mode == 'settings') {    // system settings
            $this->ctrl->setParameterByClass(
                'ilobjsystemfoldergui',
                'ref_id',
                SYSTEM_FOLDER_ID
            );
            $this->locator->addItem(
                $this->lng->txt('administration'),
                $this->ctrl->getLinkTargetByClass(['iladministrationgui', 'ilobjsystemfoldergui'], ''),
                ilFrameTargetInfo::_getFrame('MainContent')
            );

            if ($this->requested_ref_id == USER_FOLDER_ID) {
                $this->locator->addItem(
                    $this->lng->txt('obj_' . ilObject::_lookupType(
                        ilObject::_lookupObjId($this->requested_ref_id)
                    )),
                    $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'view')
                );
            } elseif ($this->requested_ref_id == ROLE_FOLDER_ID) {
                $this->locator->addItem(
                    $this->lng->txt('obj_' . ilObject::_lookupType(
                        ilObject::_lookupObjId($this->requested_ref_id)
                    )),
                    $this->ctrl->getLinkTargetByClass('ilobjrolefoldergui', 'view')
                );
            }

            if ($this->obj_id > 0) {
                $this->locator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, 'view')
                );
            }
        }
    }

    public function __sendProfileMail(): string
    {
        if ($this->user_request->getSendMail() != 'y') {
            return '';
        }
        if (!strlen($this->object->getEmail())) {
            return '';
        }

        $usr_lang = new ilLanguage($this->object->getLanguage());
        $usr_lang->loadLanguageModule('crs');
        $usr_lang->loadLanguageModule('registration');

        $mmail = new ilMimeMail();
        $mmail->From($this->mail_sender_factory->system());

        $mailOptions = new ilMailOptions($this->object->getId());
        $mmail->To($mailOptions->getExternalEmailAddresses());

        $subject = $usr_lang->txt('profile_changed');
        $body = $usr_lang->txt('reg_mail_body_salutation')
            . ' ' . $this->object->getFullname() . ",\n\n";

        $date = $this->object->getApproveDate();

        if ($date !== null && (time() - strtotime($date)) < 10) {
            $body .= $usr_lang->txt('reg_mail_body_approve') . "\n\n";
        } else {
            $body .= $usr_lang->txt('reg_mail_body_profile_changed') . "\n\n";
        }

        // Append login info only if password has been changed
        if ($this->user_request->getPassword() != '') {
            $body .= $usr_lang->txt('reg_mail_body_text2') . "\n" .
                ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID . "\n" .
                $usr_lang->txt('login') . ': ' . $this->object->getLogin() . "\n" .
                $usr_lang->txt('passwd') . ': ' . $this->user_request->getPassword() . "\n\n";
        }
        $body .= $usr_lang->txt('reg_mail_body_text3') . "\n";
        $body .= $this->object->getProfileAsString($usr_lang);
        $body .= ilMail::_getInstallationSignature();


        $mmail->Subject($subject, true);
        $mmail->Body($body);
        $mmail->Send();

        return '<br/>' . $this->lng->txt('mail_sent');
    }

    /**
     * Goto user profile screen
     */
    public static function _goto(string $a_target): void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        /** @var ilCtrl $ilCtrl */
        $ilCtrl = $DIC['ilCtrl'];

        // #10888
        if ($a_target == md5('usrdelown')) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID &&
                $ilUser->hasDeletionFlag()) {
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->redirectByClass(['ildashboardgui', 'ilpersonalsettingsgui'], 'deleteOwnAccountStep2');
            }
            exit('This account is not flagged for deletion.'); // #12160
        }

        // badges
        if (substr($a_target, -4) == '_bdg') {
            $ilCtrl->redirectByClass('ilDashboardGUI', 'jumpToBadges');
        }

        if ('registration' == $a_target) {
            $ilCtrl->redirectByClass(['ilStartUpGUI', 'ilAccountRegistrationGUI'], '');
        } elseif ('nameassist' == $a_target) {
            $ilCtrl->redirectByClass(['ilStartUpGUI', 'ilPasswordAssistanceGUI'], 'showUsernameAssistanceForm');
        } elseif ('pwassist' == $a_target) {
            $ilCtrl->redirectByClass(['ilStartUpGUI', 'ilPasswordAssistanceGUI'], '');
        } else {
            $target = $DIC['legalDocuments']->findGotoLink($a_target);
            if ($target->isOK()) {
                $ilCtrl->setTargetScript('ilias.php');
                foreach ($target->value()->queryParams() as $key => $value) {
                    $ilCtrl->setParameterByClass($target->value()->guiName(), (string) $key, $value);
                }
                $ilCtrl->redirectByClass($target->value()->guiPath(), $target->value()->command());
            }
        }

        if (strpos($a_target, 'n') === 0) {
            $a_target = ilObjUser::_lookupId(ilUtil::stripSlashes(substr($a_target, 1)));
        }

        $target_user = 0;
        $target_cmd = '';
        if (is_numeric($a_target)) {
            $target_user = (int) $a_target;
        } elseif (($target_array = explode('_', $a_target, 3))) {
            $target_cmd = $target_array[2];
            $target_user = (int) $target_array[0];
        }

        if ($target_user > 0) {
            $ilCtrl->setParameterByClass(PublicProfileGUI::class, 'user_id', $target_user);
        }

        $cmd = 'view';
        if ($target_cmd === 'contact_approved') {
            $cmd = 'approveContactRequest';
        } elseif ($target_cmd === 'contact_ignored') {
            $cmd = 'ignoreContactRequest';
        }
        $ilCtrl->setParameterByClass(PublicProfileGUI::class, 'user_id', (int) $a_target);
        $ilCtrl->redirectByClass([PublicProfileGUI::class], $cmd);
    }

    /**
     * Handles ignored required fields by changing the required flag of form elements
     * @return    bool    A flag whether the user profile is maybe incomplete after saving the form data
     */
    private function handleIgnoredRequiredFields(): bool
    {
        $profile_maybe_incomplete = false;

        foreach ($this->user_profile->getIgnorableRequiredFields() as $fieldName) {
            $elm = $this->form_gui->getItemByPostVar($fieldName);

            if (!$elm) {
                continue;
            }

            if ($elm->getRequired()) {
                $profile_maybe_incomplete = true;

                // Flag as optional
                $elm->setRequired(false);
            }
        }

        foreach ($this->user_profile->getAllUserDefinedFields() as $field) {
            $elm = $this->form_gui->getItemByPostVar('udf_' . $field->getIdentifier());

            if (!$elm) {
                continue;
            }

            if ($elm->getRequired() && $field->isRequired()) {
                $profile_maybe_incomplete = true;

                // Flag as optional
                $elm->setRequired(false);
            }
        }

        return $profile_maybe_incomplete;
    }

    private function checkUserWritePermission(): void
    {
        if ($this->context === Context::UserAdministration
            && !(
                $this->rbac_system->checkAccess(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE, $this->usrf_ref_id)
                || $this->access->checkPositionAccess(\ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $this->usrf_ref_id)
                    && in_array(
                        $this->object->getId(),
                        $this->access->filterUserIdsByPositionOfCurrentUser(
                            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                            USER_FOLDER_ID,
                            [$this->object->getId()]
                        )
                    )
            )) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_perm_modify_user'),
                true
            );
            $this->ctrl->redirectByClass(ilObjUserFolderGUI::class);
        }

        // if called from local administration $this->usrf_ref_id is category id
        // Todo: this has to be fixed. Do not mix user folder id and category id
        if ($this->usrf_ref_id !== USER_FOLDER_ID
            && !$this->rbac_system->checkAccess('cat_administrate_users', $this->object->getTimeLimitOwner())) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('msg_no_perm_modify_user')
            );
            $this->redirectToRefId($this->usrf_ref_id);
        }
    }

    private function renderForm(): void
    {
        $this->tpl->setContent($this->legal_documents->userManagementModals() . $this->form_gui->getHTML());
    }

    private function checkAccessToRolesTab(): bool
    {
        return $this->object->getId() !== (int) ANONYMOUS_USER_ID
            && (
                $this->rbac_system->checkAccess('edit_roleassignment', $this->usrf_ref_id)
                || $this->access->checkPositionAccess(\ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $this->usrf_ref_id)
                    && in_array(
                        $this->object->getId(),
                        $this->access->filterUserIdsByPositionOfCurrentUser(
                            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                            USER_FOLDER_ID,
                            [$this->object->getId()]
                        )
                    )
            );
    }

    private function retrieveAllowIncompleteProfileFromPost(): bool
    {
        return $this->post_wrapper->retrieve(
            'ignore_rf',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(false)
            ])
        );
    }

    private function redirectDependingOnParent(): void
    {
        if ($this->usrf_ref_id === USER_FOLDER_ID) {
            $this->ctrl->redirectByClass(ilObjUserFolderAccess::class);
        }

        $this->redirectToRefId($this->usrf_ref_id);
    }

    private function isAccessRangeInputValid(): bool
    {
        if ($this->form_gui->getInput('time_limit_unlimited') === '1') {
            return true;
        }
        $timefrom = $this->form_gui->getItemByPostVar('time_limit_from');
        $timeuntil = $this->form_gui->getItemByPostVar('time_limit_until');
        if ($timeuntil->getDate()->get(IL_CAL_UNIX) <= $timefrom->getDate()->get(IL_CAL_UNIX)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $timeuntil->setAlert($this->lng->txt('time_limit_not_valid'));
            return false;
        }
        return true;
    }
}
