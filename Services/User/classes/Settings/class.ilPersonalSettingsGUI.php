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
use ILIAS\DI\LoggingServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

/**
 * GUI class for personal profile
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPersonalSettingsGUI: ilMailOptionsGUI
 */
class ilPersonalSettingsGUI
{
    private ilPropertyFormGUI $form;
    private string $entered_new_password;
    private string $entered_current_password;
    private string $password_error;
    private ilUserDefinedFields $user_defined_fields;
    private string $upload_error;
    private UserGUIRequest $request;
    private ilGlobalTemplateInterface $tpl;
    private UIFactory $ui_factory;
    private Renderer $ui_renderer;
    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private LoggingServices $log;
    private ilMailMimeSenderFactory $mail_sender_factory;
    private ilHelpGUI $help;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private ilObjUser $user;
    private ilSetting $settings;
    private ilAuthSession $auth_session;
    private ilRbacSystem $rbac_system;
    private ilStyleDefinition $style_definition;
    private ilNavigationHistory $navigation_history;
    private ilUserSettingsConfig $user_settings_config;

    private ilUserStartingPointRepository $starting_point_repository;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->user_defined_fields = ilUserDefinedFields::_getInstance();

        $this->tpl = $DIC['tpl'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->log = $DIC->logger();
        $this->mail_sender_factory = $DIC->mail()->mime()->senderFactory();
        $this->help = $DIC['ilHelp'];
        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->user = $DIC['ilUser'];
        $this->settings = $DIC['ilSetting'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->style_definition = $DIC['styleDefinition'];
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->upload_error = '';
        $this->password_error = '';
        $this->lng->loadLanguageModule('user');
        $this->ctrl->saveParameter($this, 'user_page');

        $this->user_settings_config = new ilUserSettingsConfig();
        $this->request = new UserGUIRequest(
            $DIC->http(),
            $DIC['refinery']
        );

        $this->starting_point_repository = new ilUserStartingPointRepository(
            $this->user,
            $DIC['ilDB'],
            $DIC['tree'],
            $DIC['rbacreview'],
            $this->settings
        );
        $this->entered_new_password = $this->request->getNewPassword();
        $this->entered_current_password = $this->request->getCurrentPassword();
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case 'ilmailoptionsgui':
                if (!$this->rbac_system->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())) {
                    throw new ilPermissionException($this->lng->txt('permission_denied'));
                }

                $this->initSubTabs('showMailOptions');
                $this->tabs->activateTab('mail_settings');
                $this->setHeader();
                $this->ctrl->forwardCommand(new ilMailOptionsGUI());
                break;

            default:
                $cmd = $this->ctrl->getCmd('showGeneralSettings');
                $this->$cmd();
                break;
        }
    }

    private function initSubTabs(string $a_cmd): void
    {
        $this->help->setScreenIdComponent('user');

        $showPassword = $a_cmd == 'showPassword';
        $showGeneralSettings = $a_cmd == 'showGeneralSettings';

        $this->tabs->addTarget(
            'general_settings',
            $this->ctrl->getLinkTarget($this, 'showGeneralSettings'),
            '',
            '',
            '',
            $showGeneralSettings
        );

        if ($this->allowPasswordChange()) {
            $this->tabs->addTarget(
                'password',
                $this->ctrl->getLinkTarget($this, 'showPassword'),
                '',
                '',
                '',
                $showPassword
            );
        }

        if ($this->rbac_system->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())
            && $this->settings->get('show_mail_settings')) {
            $this->ctrl->setParameter($this, 'referrer', 'ilPersonalSettingsGUI');

            $this->tabs->addTarget(
                'mail_settings',
                $this->ctrl->getLinkTargetByClass('ilMailOptionsGUI'),
                '',
                ['ilMailOptionsGUI']
            );
        }

        if ($this->settings->get('user_delete_own_account') &&
            $this->user->getId() != SYSTEM_USER_ID) {
            $this->tabs->addTab(
                'delacc',
                $this->lng->txt('user_delete_own_account'),
                $this->ctrl->getLinkTarget($this, 'deleteOwnAccountStep1')
            );
        }
    }

    public function setHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt('personal_settings'));
    }

    public function showPassword(
        bool $a_no_init = false,
        bool $hide_form = false
    ): void {
        $this->initSubTabs('showPersonalData');
        $this->tabs->activateTab('password');

        $this->setHeader();
        // check whether password of user have to be changed
        // due to first login or password of user is expired
        if ($this->user->isPasswordChangeDemanded()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('password_change_on_first_login_demand'));
        } elseif ($this->user->isPasswordExpired()) {
            $msg = $this->lng->txt('password_expired');
            $password_age = $this->user->getPasswordAge();
            $this->tpl->setOnScreenMessage('info', sprintf($msg, $password_age));
        }

        if (!$a_no_init && !$hide_form) {
            $this->initPasswordForm();
        }
        $this->tpl->setContent(!$hide_form ? $this->form->getHTML() : '');
        $this->tpl->printToStdout();
    }

    public function initPasswordForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        if ($this->allowPasswordChange()) {
            $pw_info_set = false;
            if ($this->user->getAuthMode(true) == ilAuthUtils::AUTH_LOCAL) {
                // current password
                $cpass = new ilPasswordInputGUI($this->lng->txt('current_password'), 'current_password');
                $cpass->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
                $cpass->setRetype(false);
                $cpass->setSkipSyntaxCheck(true);
                $pw_info_set = true;
                // only if a password exists.
                if ($this->user->getPasswd()) {
                    $cpass->setRequired(true);
                }
                $this->form->addItem($cpass);
            }

            // new password
            $ipass = new ilPasswordInputGUI($this->lng->txt('desired_password'), 'new_password');
            if ($pw_info_set === false) {
                $ipass->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
            }
            $ipass->setRequired(true);
            $ipass->setUseStripSlashes(false);

            $this->form->addItem($ipass);
            $this->form->addCommandButton('savePassword', $this->lng->txt('save'));

            switch ($this->user->getAuthMode(true)) {
                case ilAuthUtils::AUTH_LOCAL:
                    $this->form->setTitle($this->lng->txt('chg_password'));
                    break;

                case ilAuthUtils::AUTH_SHIBBOLETH:
                case ilAuthUtils::AUTH_CAS:
                    if (ilDAVActivationChecker::_isActive()) {
                        $this->form->setTitle($this->lng->txt('chg_ilias_and_webfolder_password'));
                    } else {
                        $this->form->setTitle($this->lng->txt('chg_ilias_password'));
                    }
                    break;
                default:
                    $this->form->setTitle($this->lng->txt('chg_ilias_password'));
                    break;
            }
            $this->form->setFormAction($this->ctrl->getFormAction($this));
        }
    }

    /**
     * Check, whether password change is allowed for user
     */
    protected function allowPasswordChange(): bool
    {
        if (\ilSession::get('used_external_auth_mode')) {
            return false;
        }

        $status = ilAuthUtils::isPasswordModificationEnabled($this->user->getAuthMode(true));
        if ($status) {
            return true;
        }

        return \ilAuthUtils::isPasswordModificationHidden()
            && ($this->user->isPasswordChangeDemanded() || $this->user->isPasswordExpired());
    }

    public function savePassword(): void
    {
        if (!$this->allowPasswordChange()) {
            $this->ctrl->redirect($this, 'showPersonalData');
            return;
        }

        $this->initPasswordForm();
        if ($this->form->checkInput()) {
            $cp = $this->form->getItemByPostVar('current_password');
            $np = $this->form->getItemByPostVar('new_password');
            $error = false;

            if ($this->user->getAuthMode(true) == ilAuthUtils::AUTH_LOCAL) {
                if (!ilUserPasswordManager::getInstance()->verifyPassword($this->user, $this->entered_current_password)) {
                    $error = true;
                    $cp->setAlert($this->lng->txt('passwd_wrong'));
                }
            }

            if (!ilSecuritySettingsChecker::isPassword($this->entered_new_password, $custom_error)) {
                $error = true;
                if ($custom_error != '') {
                    $np->setAlert($custom_error);
                } else {
                    $np->setAlert($this->lng->txt('passwd_invalid'));
                }
            }
            $error_lng_var = '';
            if (!ilSecuritySettingsChecker::isPasswordValidForUserContext(
                $this->entered_new_password,
                $this->user,
                $error_lng_var
            )) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
                $np->setAlert($this->lng->txt($error_lng_var));
                $error = true;
            }
            if (($this->user->isPasswordExpired() || $this->user->isPasswordChangeDemanded())
                && $this->entered_current_password == $this->entered_new_password) {
                $error = true;
                $np->setAlert($this->lng->txt('new_pass_equals_old_pass'));
            }

            if (!$error) {
                $this->user->resetPassword($this->entered_new_password, $this->entered_new_password);
                if ($this->entered_current_password != $this->entered_new_password) {
                    $this->user->setLastPasswordChangeToNow();
                    $this->user->setPasswordPolicyResetStatus(false);
                    $this->user->update();
                }

                if (ilSession::get('orig_request_target')) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                    $target = ilSession::get('orig_request_target');
                    ilSession::set('orig_request_target', '');
                    ilUtil::redirect($target);
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
                    $this->showPassword(true, true);
                    return;
                }
            }
        }
        $this->form->setValuesByPost();
        $this->showPassword(true);
    }

    public function workWithUserSetting(string $setting): bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    public function userSettingVisible(string $setting): bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    public function userSettingEnabled(string $setting): bool
    {
        return $this->user_settings_config->isChangeable($setting);
    }

    public function showGeneralSettings(bool $a_no_init = false): void
    {
        $this->initSubTabs('showPersonalData');
        $this->tabs->activateTab('general_settings');

        $this->setHeader();

        if (!$a_no_init) {
            $this->initGeneralSettingsForm();
        }
        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }

    public function initGeneralSettingsForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        // language
        if ($this->userSettingVisible('language')) {
            $languages = $this->lng->getInstalledLanguages();
            $options = [];
            foreach ($languages as $lang_key) {
                $options[$lang_key] = ilLanguage::_lookupEntry($lang_key, 'meta', 'meta_l_' . $lang_key);
            }

            $si = new ilSelectInputGUI($this->lng->txt('language'), 'language');
            $si->setOptions($options);
            $si->setValue($this->user->getLanguage());
            $si->setDisabled((bool) $this->settings->get('usr_settings_disable_language'));
            $this->form->addItem($si);
        }

        // skin/style
        if ($this->userSettingVisible('skin_style')) {
            $skins = $this->style_definition->getAllSkins();
            if (is_array($skins)) {
                $si = new ilSelectInputGUI($this->lng->txt('skin_style'), 'skin_style');

                $options = [];
                foreach ($skins as $skin) {
                    foreach ($skin->getStyles() as $style) {
                        if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId()) || $style->isSubstyle()) {
                            continue;
                        }

                        $options[$skin->getId() . ':' . $style->getId()] = $skin->getName() . ' / ' . $style->getName();
                    }
                }
                $si->setOptions($options);
                $si->setValue($this->user->skin . ':' . $this->user->prefs['style']);
                $si->setDisabled((bool) $this->settings->get('usr_settings_disable_skin_style'));
                $this->form->addItem($si);
            }
        }

        // help tooltips
        $module_id = (int) $this->settings->get('help_module');
        if (((int) OH_REF_ID > 0 || $module_id > 0)
            && $this->user->getLanguage() == 'de'
            && $this->settings->get('help_mode') != '1') {
            $this->lng->loadLanguageModule('help');
            $cb = new ilCheckboxInputGUI($this->lng->txt('help_toggle_tooltips'), 'help_tooltips');
            $cb->setChecked(!($this->user->prefs['hide_help_tt'] ?? false));
            $cb->setInfo($this->lng->txt('help_toggle_tooltips_info'));
            $this->form->addItem($cb);
        }

        // hits per page
        if ($this->userSettingVisible('hits_per_page')) {
            $si = new ilSelectInputGUI($this->lng->txt('hits_per_page'), 'hits_per_page');

            $hits_options = [10,15,20,30,40,50,100,9999];
            $options = [];

            foreach ($hits_options as $hits_option) {
                $hstr = ($hits_option == 9999)
                    ? $this->lng->txt('no_limit')
                    : $hits_option;
                $options[$hits_option] = $hstr;
            }
            $si->setOptions($options);
            $si->setValue($this->user->prefs['hits_per_page']);
            $si->setDisabled((bool) $this->settings->get('usr_settings_disable_hits_per_page'));
            $this->form->addItem($si);
        }

        $lv = new ilSelectInputGUI($this->lng->txt('user_store_last_visited'), 'store_last_visited');
        $options = [
            0 => $this->lng->txt('user_lv_keep_entries'),
            1 => $this->lng->txt('user_lv_keep_only_for_session'),
            2 => $this->lng->txt('user_lv_do_not_store')
        ];
        $lv->setOptions($options);
        $last_visited = (int) ($this->user->prefs['store_last_visited'] ?? 0);
        $lv->setValue($last_visited);
        $this->form->addItem($lv);


        if (ilSessionReminder::isGloballyActivated()) {
            $cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
            $cb->setInfo($this->lng->txt('session_reminder_info'));
            $cb->setValue('1');
            $cb->setChecked((bool) $this->user->getPref('session_reminder_enabled'));

            $expires = ilSession::getSessionExpireValue();
            $lead_time_gui = new ilNumberInputGUI($this->lng->txt('session_reminder_lead_time'), 'session_reminder_lead_time');
            $lead_time_gui->setInfo(sprintf($this->lng->txt('session_reminder_lead_time_info'), ilDatePresentation::secondsToString($expires, true)));

            $min_value = ilSessionReminder::MIN_LEAD_TIME;
            $max_value = max($min_value, ($expires / 60) - 1);

            $current_user_value = $this->user->getPref('session_reminder_lead_time');
            if ($current_user_value < $min_value ||
               $current_user_value > $max_value) {
                $current_user_value = ilSessionReminder::SUGGESTED_LEAD_TIME;
            }
            $value = min(
                max(
                    $min_value,
                    $current_user_value
                ),
                $max_value
            );

            $lead_time_gui->setValue((string) $value);
            $lead_time_gui->setSize(3);
            $lead_time_gui->setMinValue($min_value);
            $lead_time_gui->setMaxValue($max_value);
            $cb->addSubItem($lead_time_gui);

            $this->form->addItem($cb);
        }

        // calendar settings (copied here to be reachable when calendar is inactive)
        // they cannot be hidden/deactivated

        $this->lng->loadLanguageModule('dateplaner');
        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());

        $select = new ilSelectInputGUI($this->lng->txt('cal_user_timezone'), 'timezone');
        $select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
        $select->setInfo($this->lng->txt('cal_timezone_info'));
        $select->setValue($user_settings->getTimeZone());
        $this->form->addItem($select);

        $year = date('Y');
        $select = new ilSelectInputGUI($this->lng->txt('cal_user_date_format'), 'date_format');
        $select->setOptions([
            ilCalendarSettings::DATE_FORMAT_DMY => '31.10.' . $year,
            ilCalendarSettings::DATE_FORMAT_YMD => $year . '-10-31',
            ilCalendarSettings::DATE_FORMAT_MDY => '10/31/' . $year
        ]);
        $select->setInfo($this->lng->txt('cal_date_format_info'));
        $select->setValue($user_settings->getDateFormat());
        $this->form->addItem($select);

        $select = new ilSelectInputGUI($this->lng->txt('cal_user_time_format'), 'time_format');
        $select->setOptions([
            ilCalendarSettings::TIME_FORMAT_24 => '13:00',
            ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'
        ]);
        $select->setInfo($this->lng->txt('cal_time_format_info'));
        $select->setValue($user_settings->getTimeFormat());
        $this->form->addItem($select);

        if ($this->starting_point_repository->isPersonalStartingPointEnabled()) {
            $this->lng->loadLanguageModule('administration');
            $si = new ilRadioGroupInputGUI($this->lng->txt('adm_user_starting_point'), 'usr_start');
            $si->setRequired(true);
            $si->setInfo($this->lng->txt('adm_user_starting_point_info'));
            $def_opt = new ilRadioOption($this->lng->txt('adm_user_starting_point_inherit'), '0');
            $def_opt->setInfo($this->lng->txt('adm_user_starting_point_inherit_info'));
            $si->addOption($def_opt);
            foreach ($this->starting_point_repository->getPossibleStartingPoints() as $value => $caption) {
                if ($value === ilUserStartingPointRepository::START_REPOSITORY_OBJ) {
                    continue;
                }
                $si->addOption(new ilRadioOption($this->lng->txt($caption), (string) $value));
            }
            $si->setValue((string) $this->starting_point_repository->getCurrentUserPersonalStartingPoint());
            $this->form->addItem($si);

            // starting point: repository object
            $repobj = new ilRadioOption(
                $this->lng->txt('adm_user_starting_point_object'),
                (string) ilUserStartingPointRepository::START_REPOSITORY_OBJ
            );
            $repobj_id = new ilTextInputGUI($this->lng->txt('adm_user_starting_point_ref_id'), 'usr_start_ref_id');
            $repobj_id->setInfo($this->lng->txt('adm_user_starting_point_ref_id_info'));
            $repobj_id->setRequired(true);
            $repobj_id->setSize(5);
            if ($si->getValue() == ilUserStartingPointRepository::START_REPOSITORY_OBJ) {
                $start_ref_id = $this->starting_point_repository->getCurrentUserPersonalStartingObject();
                $repobj_id->setValue($start_ref_id);
                if ($start_ref_id) {
                    $start_obj_id = ilObject::_lookupObjId($start_ref_id);
                    if ($start_obj_id) {
                        $repobj_id->setInfo($this->lng->txt('obj_' . ilObject::_lookupType($start_obj_id)) .
                            ': ' . ilObject::_lookupTitle($start_obj_id));
                    }
                }
            }
            $repobj->addSubItem($repobj_id);
            $si->addOption($repobj);
        }

        $this->form->addCommandButton('saveGeneralSettings', $this->lng->txt('save'));
        $this->form->setTitle($this->lng->txt('general_settings'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    public function saveGeneralSettings(): void
    {
        $this->initGeneralSettingsForm();
        if ($this->form->checkInput()) {
            if ($this->workWithUserSetting('skin_style')) {
                //set user skin and style
                if ($this->form->getInput('skin_style') != '') {
                    $sknst = explode(':', $this->form->getInput('skin_style'));

                    if ($this->user->getPref('style') != $sknst[1] ||
                        $this->user->getPref('skin') != $sknst[0]) {
                        $this->user->setPref('skin', $sknst[0]);
                        $this->user->setPref('style', $sknst[1]);
                    }
                }
            }

            // language
            if ($this->workWithUserSetting('language')) {
                $this->user->setLanguage($this->form->getInput('language'));
            }

            // hits per page
            if ($this->workWithUserSetting('hits_per_page')) {
                if ($this->form->getInput('hits_per_page') != '') {
                    $this->user->setPref('hits_per_page', $this->form->getInput('hits_per_page'));
                }
            }

            // help tooltips
            $module_id = (int) $this->settings->get('help_module');
            if (((int) OH_REF_ID > 0 || $module_id > 0) && $this->user->getLanguage() == 'de' &&
                $this->settings->get('help_mode') != '1') {
                $this->user->setPref('hide_help_tt', (int) !$this->form->getInput('help_tooltips'));
            }

            $this->user->setPref('store_last_visited', $this->form->getInput('store_last_visited'));
            if ((int) $this->form->getInput('store_last_visited') > 0) {
                $this->navigation_history->deleteDBEntries();
                if ((int) $this->form->getInput('store_last_visited') == 2) {
                    $this->navigation_history->deleteSessionEntries();
                }
            }

            if (ilSessionReminder::isGloballyActivated()) {
                $this->user->setPref('session_reminder_enabled', $this->form->getInput('session_reminder_enabled'));
                $this->user->setPref('session_reminder_lead_time', (string) $this->form->getInput('session_reminder_lead_time'));
            }

            if ($this->starting_point_repository->isPersonalStartingPointEnabled()) {
                $s_ref_id = $this->form->getInput('usr_start_ref_id');
                $s_ref_id = ($s_ref_id === '')
                    ? null
                    : (int) $s_ref_id;
                $this->starting_point_repository->setCurrentUserPersonalStartingPoint(
                    (int) $this->form->getInput('usr_start'),
                    $s_ref_id
                );
            }

            $this->user->update();

            // calendar settings
            $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
            $user_settings->setTimeZone($this->form->getInput('timezone'));
            $user_settings->setDateFormat((int) $this->form->getInput('date_format'));
            $user_settings->setTimeFormat((int) $this->form->getInput('time_format'));
            $user_settings->save();

            $this->tpl->setOnScreenMessage('success', $this->lng->txtlng('common', 'msg_obj_modified', $this->user->getLanguage()), true);

            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        $this->form->setValuesByPost();
        $this->showGeneralSettings(true);
    }

    protected function deleteOwnAccountStep1(): void
    {
        if (!(bool) $this->settings->get('user_delete_own_account') ||
            $this->user->getId() == SYSTEM_USER_ID) {
            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        // too make sure
        $this->user->removeDeletionFlag();

        $this->setHeader();
        $this->initSubTabs('deleteOwnAccount');
        $this->tabs->activateTab('delacc');

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('delete_account'),
            $this->lng->txt('user_delete_own_account_logout_confirmation'),
            $this->ctrl->getFormActionByClass(ilPersonalSettingsGUI::class, 'deleteOwnAccountLogout')
        )->withActionButtonLabel($this->lng->txt('user_delete_own_account_logout_button'));

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('user_delete_own_account_info'));
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('btn_next'),
                $modal->getShowSignal()
            )
        );

        $this->tpl->setContent($this->ui_renderer->render($modal));

        $this->tpl->printToStdout();
    }

    protected function abortDeleteOwnAccount(): void
    {
        $this->user->removeDeletionFlag();

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('user_delete_own_account_aborted'), true);
        $this->ctrl->redirect($this, 'showGeneralSettings');
    }

    protected function deleteOwnAccountLogout(): void
    {
        $this->user->activateDeletionFlag();

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();

        $this->ctrl->redirectToURL('login.php?cmd=force_login&target=usr_' . md5('usrdelown'));
    }

    protected function deleteOwnAccountStep2(): void
    {
        if (!(bool) $this->settings->get('user_delete_own_account') ||
            $this->user->getId() == SYSTEM_USER_ID ||
            !$this->user->hasDeletionFlag()) {
            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        $this->setHeader();
        $this->initSubTabs('deleteOwnAccount');
        $this->tabs->activateTab('delacc');

        $this->tpl->setOnScreenMessage(
            'question',
            $this->lng->txt('user_delete_own_account_final_confirmation')
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('confirm'),
                $this->ctrl->getLinkTargetByClass(self::class, 'deleteOwnAccountStep3')
            )
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('cancel'),
                $this->ctrl->getLinkTargetByClass(self::class, 'abortDeleteOwnAccount')
            )
        );
        $this->tpl->printToStdout();
    }

    protected function deleteOwnAccountStep3(): void
    {
        if (!(bool) $this->settings->get('user_delete_own_account') ||
            $this->user->getId() == SYSTEM_USER_ID ||
            !$this->user->hasDeletionFlag()) {
            $this->ctrl->redirect($this, 'showGeneralSettings');
        }

        // build notification

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(['user']);
        $ntf->addAdditionalInfo('profile', $this->user->getProfileAsString($this->lng), true);

        // mail message
        ilDatePresentation::setUseRelativeDates(false);
        $ntf->setIntroductionDirect(
            sprintf(
                $this->lng->txt('user_delete_own_account_email_body'),
                $this->user->getLogin(),
                ILIAS_HTTP_PATH,
                ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))
            )
        );

        $message = $ntf->composeAndGetMessage($this->user->getId(), null, 'read', true);
        $subject = $this->lng->txt('user_delete_own_account_email_subject');


        // send notification
        $user_email = $this->user->getEmail();
        $admin_mail = $this->settings->get('user_delete_own_account_email');

        $mmail = new ilMimeMail();
        $mmail->From($this->sender_factory->system());

        if ($user_email !== '') {
            $mmail->To($user_email);
            $mmail->Bcc($admin_mail);
            $mmail->Subject($subject, true);
            $mmail->Body($message);
            $mmail->Send();
        } elseif ($admin_mail !== null || $admin_mail !== '') {
            $mmail->To($admin_mail);
            $mmail->Subject($subject, true);
            $mmail->Body($message);
            $mmail->Send();
        }

        $this->log->root()->log('Account deleted: ' . $ilUser->getLogin() . ' (' . $ilUser->getId() . ')');

        $this->user->delete();

        // terminate session
        $this->auth_session->logout();
        $this->ctrl->redirectToURL('login.php?accdel=1');
    }
}
