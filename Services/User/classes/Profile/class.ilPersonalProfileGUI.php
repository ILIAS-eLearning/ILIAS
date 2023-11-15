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

use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\User\ProfileGUIRequest;
use ILIAS\User\Profile\ProfileChangeMailTokenRepository;
use ILIAS\User\Profile\ProfileChangeMailTokenDBRepository;

/**
 * GUI class for personal profile
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPersonalProfileGUI: ilPublicUserProfileGUI, ilUserPrivacySettingsGUI, ilLegalDocumentsAgreementGUI, ilLegalDocumentsWithdrawalGUI
 */
class ilPersonalProfileGUI
{
    private const PERSONAL_DATA_FORM_ID = 'pd';
    public const CHANGE_EMAIL_CMD = 'changeEmail';

    private ilGlobalTemplateInterface $tpl;
    private ?ilUserDefinedFields $user_defined_fields = null;
    private ilAppEventHandler $eventHandler;
    private ilPropertyFormGUI $form;
    private string $password_error;
    private string $upload_error;
    private ilSetting $settings;
    private ilObjUser $user;
    private ilAuthSession $auth_session;

    private ilLanguage $lng;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private ilHelpGUI $help;
    private ilErrorHandling $error_handler;
    private ilProfileChecklistGUI $checklist;
    private ilUserSettingsConfig $user_settings_config;
    private ilProfileChecklistStatus $checklist_status;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    private ProfileChangeMailTokenRepository $change_mail_token_repo;
    private ProfileGUIRequest $profile_request;

    private FileUpload $uploads;
    private IRSS $irss;
    private ResourceStakeholder $stakeholder;

    private ?Interruptive $email_change_confirmation_modal = null;

    public function __construct(
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->user = $DIC['ilUser'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->lng = $DIC['lng'];
        $this->settings = $DIC['ilSetting'];
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->error_handler = $DIC['ilErr'];
        $this->eventHandler = $DIC['ilAppEventHandler'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->uploads = $DIC['upload'];
        $this->irss = $DIC['resource_storage'];
        $this->stakeholder = new ilUserProfilePictureStakeholder();

        $this->user_defined_fields = ilUserDefinedFields::_getInstance();

        $this->change_mail_token_repo = new ProfileChangeMailTokenDBRepository($DIC['ilDB']);

        $this->lng->loadLanguageModule('jsmath');
        $this->lng->loadLanguageModule('pd');
        $this->upload_error = '';
        $this->password_error = '';
        $this->lng->loadLanguageModule('user');
        $this->ctrl->saveParameter($this, 'prompted');

        $this->checklist = new ilProfileChecklistGUI();
        $this->checklist_status = new ilProfileChecklistStatus();

        $this->user_settings_config = new ilUserSettingsConfig();

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->change_mail_token_repo = new ProfileChangeMailTokenDBRepository($DIC['ilDB']);

        $this->profile_request = new ProfileGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case 'ilpublicuserprofilegui':
                $pub_profile_gui = new ilPublicUserProfileGUI($this->user->getId());
                $pub_profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showPersonalData'));
                $this->ctrl->forwardCommand($pub_profile_gui);
                $this->tpl->printToStdout();
                break;

            case 'iluserprivacysettingsgui':
                $this->setHeader();
                $this->setTabs();
                $this->tabs->activateTab('visibility_settings');
                $this->showChecklist(ilProfileChecklistStatus::STEP_VISIBILITY_OPTIONS);
                $gui = new ilUserPrivacySettingsGUI();
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilLegalDocumentsAgreementGUI::class):
                $this->ctrl->forwardCommand(new ilLegalDocumentsAgreementGUI());
                $this->tpl->printToStdout();
                break;

            case strtolower(ilLegalDocumentsWithdrawalGUI::class):
                $this->ctrl->forwardCommand(new ilLegalDocumentsWithdrawalGUI());
                $this->tpl->printToStdout();
                break;

            default:
                $this->setTabs();
                $cmd = $this->ctrl->getCmd('showPersonalData');
                $this->$cmd();
                break;
        }
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

    public function uploadUserPicture(): void
    {
        if (!$this->workWithUserSetting('upload')) {
            return;
        }

        if (!$this->form->hasFileUpload('userfile')
            && $this->profile_request->getUserFileCapture() === '') {
            if ($this->form->getItemByPostVar('userfile')->getDeletionFlag()) {
                $this->user->removeUserPicture();
            }
            return;
        }

        // User has uploaded a file of a captured image
        $this->uploads->process();
        $existing_rid = $this->irss->manage()->find($this->user->getAvatarRid());
        $revision_title = 'Avatar for user ' . $this->user->getLogin();

        // move uploaded file
        if ($this->form->hasFileUpload('userfile') && $this->uploads->hasBeenProcessed()) {
            $uploads = $this->uploads->getResults();
            // this implementation uses the $_FILES superglobal since
            // the file has to be identified by the name of the input field
            $upload_tmp_name = $_FILES['userfile']['tmp_name'];
            $avatar_upload_result = $uploads[$upload_tmp_name] ?? null;
            if ($avatar_upload_result !== null) {
                if ($existing_rid === null) {
                    $rid = $this->irss->manage()->upload(
                        $avatar_upload_result,
                        $this->stakeholder,
                        $revision_title
                    );
                } else {
                    $rid = $existing_rid;
                    $this->irss->manage()->replaceWithUpload(
                        $existing_rid,
                        $avatar_upload_result,
                        $this->stakeholder,
                        $revision_title
                    );
                }
            }
            if ($avatar_upload_result === null || !isset($rid)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('upload_error', true));
                $this->ctrl->redirect($this, 'showProfile');
            }
            $this->user->setAvatarRid($rid->serialize());
            $this->irss->flavours()->ensure($rid, new ilUserProfilePictureDefinition()); // Create different sizes
            $this->user->update();
            return;
        }

        $capture = $this->profile_request->getUserFileCapture();
        if ($capture === null) {
            return;
        }

        $img = str_replace(
            ['data:image/png;base64,', ' '],
            ['', '+'],
            $capture
        );
        $data = base64_decode($img);
        if ($data === false) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('upload_error', true));
            $this->ctrl->redirect($this, 'showProfile');
        }
        $stream = Streams::ofString($data);

        if ($existing_rid === null) {
            $rid = $this->irss->manage()->stream(
                $stream,
                $this->stakeholder,
                $revision_title
            );
        } else {
            $rid = $existing_rid;
            $this->irss->manage()->replaceWithStream(
                $rid,
                $stream,
                $this->stakeholder,
                $revision_title
            );
        }
        $this->user->setAvatarRid($rid->serialize());
        $this->irss->flavours()->ensure($rid, new ilUserProfilePictureDefinition()); // Create different sizes
        $this->user->update();
    }

    public function removeUserPicture(): void
    {
        $this->user->removeUserPicture();
    }

    /**
    * show profile form
    *
    * /OLD IMPLEMENTATION DEPRECATED
    */
    public function showProfile(): void
    {
        $this->showPersonalData();
    }

    /**
     * Add location fields to form if activated
     */
    public function addLocationToForm(ilPropertyFormGUI $a_form, ilObjUser $a_user): void
    {
        // check map activation
        if (!ilMapUtil::isActivated()) {
            return;
        }

        // Don't really know if this is still necessary...
        $this->lng->loadLanguageModule('maps');

        // Get user settings
        $latitude = ($a_user->getLatitude() != '')
            ? (float) $a_user->getLatitude()
            : null;
        $longitude = ($a_user->getLongitude() != '')
            ? (float) $a_user->getLongitude()
            : null;
        $zoom = $a_user->getLocationZoom();

        // Get Default settings, when nothing is set
        if ($latitude == null && $longitude == null && $zoom == 0) {
            $def = ilMapUtil::getDefaultSettings();
            $latitude = (float) $def['latitude'];
            $longitude = (float) $def['longitude'];
            $zoom = (int) $def['zoom'];
        }

        $street = $a_user->getStreet();
        if (!$street) {
            $street = $this->lng->txt('street');
        }
        $city = $a_user->getCity();
        if (!$city) {
            $city = $this->lng->txt('city');
        }
        $country = $a_user->getCountry();
        if (!$country) {
            $country = $this->lng->txt('country');
        }

        // location property
        $loc_prop = new ilLocationInputGUI(
            $this->lng->txt('location'),
            'location'
        );
        $loc_prop->setLatitude($latitude);
        $loc_prop->setLongitude($longitude);
        $loc_prop->setZoom($zoom);
        $loc_prop->setAddress($street . ',' . $city . ',' . $country);

        $a_form->addItem($loc_prop);
    }

    // init sub tabs
    public function setTabs(): void
    {
        $this->help->setScreenIdComponent('user');

        // personal data
        $this->tabs->addTab(
            'personal_data',
            $this->lng->txt('user_profile_data'),
            $this->ctrl->getLinkTarget($this, 'showPersonalData')
        );

        // publishing options
        $this->tabs->addTab(
            'public_profile',
            $this->lng->txt('user_publish_options'),
            $this->ctrl->getLinkTarget($this, 'showPublicProfile')
        );

        // visibility settings
        $txt_visibility = $this->checklist_status->anyVisibilitySettings()
            ? $this->lng->txt('user_visibility_settings')
            : $this->lng->txt('preview');
        $this->tabs->addTab(
            'visibility_settings',
            $txt_visibility,
            $this->ctrl->getLinkTargetByClass('ilUserPrivacySettingsGUI', '')
        );

        // export
        $this->tabs->addTab(
            'export',
            $this->lng->txt('export') . '/' . $this->lng->txt('import'),
            $this->ctrl->getLinkTarget($this, 'showExportImport')
        );
    }


    public function __showOtherInformations(): bool
    {
        $d_set = new ilSetting('delicous');
        if ($this->userSettingVisible('matriculation') or count($this->user_defined_fields->getVisibleDefinitions())
            or $d_set->get('user_profile') == '1') {
            return true;
        }
        return false;
    }

    public function __showUserDefinedFields(): bool
    {
        $user_defined_data = $this->user->getUserDefinedData();
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            if ($definition['field_type'] == UDF_TYPE_TEXT) {
                $this->tpl->setCurrentBlock('field_text');
                $this->tpl->setVariable(
                    'FIELD_VALUE',
                    ilLegacyFormElementsUtil::prepareFormOutput($user_defined_data[$field_id])
                );
                if (!$definition['changeable']) {
                    $this->tpl->setVariable('DISABLED_FIELD', 'disabled="disabled"');
                }
                $this->tpl->setVariable('FIELD_NAME', 'udf[' . $definition['field_id'] . ']');
            } else {
                if ($definition['changeable']) {
                    $name = 'udf[' . $definition['field_id'] . ']';
                    $disabled = false;
                } else {
                    $name = '';
                    $disabled = true;
                }
                $this->tpl->setCurrentBlock('field_select');
                $this->tpl->setVariable(
                    'SELECT_BOX',
                    ilLegacyFormElementsUtil::formSelect(
                        $user_defined_data[$field_id],
                        $name,
                        $this->user_defined_fields->fieldValuesToSelectArray(
                            $definition['field_values']
                        ),
                        false,
                        true,
                        0,
                        '',
                        [],
                        $disabled
                    )
                );
            }
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('user_defined');

            if ($definition['required']) {
                $name = $definition['field_name'] . '<span class="asterisk">*</span>';
            } else {
                $name = $definition['field_name'];
            }
            $this->tpl->setVariable('TXT_FIELD_NAME', $name);
            $this->tpl->parseCurrentBlock();
        }
        return true;
    }

    public function setHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt('personal_profile'));
    }

    public function showPersonalData(
        bool $a_no_init = false
    ): void {
        $prompt_service = new ilUserProfilePromptService();

        $this->tabs->activateTab('personal_data');

        $it = '';
        if ($this->profile_request->getPrompted() == 1) {
            $it = $prompt_service->data()->getSettings()->getPromptText($this->user->getLanguage());
        }
        if ($it === '') {
            $it = $prompt_service->data()->getSettings()->getInfoText($this->user->getLanguage());
        }
        if (trim($it) !== '') {
            $pub_prof = in_array($this->user->prefs['public_profile'] ?? '', ['y', 'n', 'g'])
                ? $this->user->prefs['public_profile']
                : 'n';
            $box = $this->ui_factory->messageBox()->info($it);
            if ($pub_prof === 'n') {
                $box = $box->withLinks(
                    [$this->ui_factory->link()->standard(
                        $this->lng->txt('user_make_profile_public'),
                        $this->ctrl->getLinkTarget($this, 'showPublicProfile')
                    )]
                );
            }
            $it = $this->ui_renderer->render($box);
        }
        $this->setHeader();

        $this->showChecklist(ilProfileChecklistStatus::STEP_PROFILE_DATA);

        if (!$a_no_init) {
            $this->initPersonalDataForm();
            // catch feedback message
            if ($this->user->getProfileIncomplete()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('profile_incomplete'));
            }
        }

        $modal = '';
        if ($this->email_change_confirmation_modal !== null) {
            $modal = $this->ui_renderer->render($this->email_change_confirmation_modal);
        }

        $this->tpl->setContent($it . $this->form->getHTML() . $modal);

        $this->tpl->printToStdout();
    }

    public function initPersonalDataForm(): void
    {
        $input = [];

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setId(self::PERSONAL_DATA_FORM_ID);

        // user defined fields
        $user_defined_data = $this->user->getUserDefinedData();


        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            $value = $user_defined_data['f_' . $field_id] ?? '';
            $changeable = $definition['changeable'] === 1 ? true : false;
            $fprop = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition(
                $definition,
                $changeable,
                $value
            );
            if ($fprop instanceof ilFormPropertyGUI) {
                $input['udf_' . $definition['field_id']] = $fprop;
            }
        }

        // standard fields
        $up = new ilUserProfile();
        $up->skipField('password');
        $up->skipGroup('settings');
        $up->skipGroup('preferences');

        $up->setAjaxCallback(
            $this->ctrl->getLinkTargetByClass('ilPublicUserProfileGUI', 'doProfileAutoComplete', '', true)
        );

        // standard fields
        $up->addStandardFieldsToForm($this->form, $this->user, $input);

        $this->addLocationToForm($this->form, $this->user);

        $this->form->addCommandButton('savePersonalData', $this->lng->txt('user_save_continue'));
    }

    public function savePersonalData(): void
    {
        $this->initPersonalDataForm();

        if (!$this->form->checkInput()
            || !$this->emailCompletionForced()
                && $this->emailChanged()
                && $this->addEmailChangeModal()
            || $this->loginChanged() && !$this->updateLoginOrSetErrorMessages()) {
            $this->form->setValuesByPost();
            $this->tempStorePicture();
            $this->showPersonalData(true);
            return;
        }

        $this->savePersonalDataForm();

        $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PROFILE_DATA);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);

        $this->ctrl->redirect($this, 'showPublicProfile');
    }

    private function emailChanged(): bool
    {
        $email_input = $this->form->getItemByPostVar('usr_email');
        if ($email_input !== null && !$email_input->getDisabled()
            && $this->form->getInput('usr_email') !== $this->user->getEmail()) {
            return true;
        }

        return false;
    }

    private function emailCompletionForced(): bool
    {
        $current_email = $this->user->getEmail();
        if (
            $this->user->getProfileIncomplete()
            && $this->settings->get('require_email') === '1'
            && ($current_email === null || $current_email === '')
        ) {
            return true;
        }

        return false;
    }

    private function addEmailChangeModal(): bool
    {
        $form_id = 'form_' . self::PERSONAL_DATA_FORM_ID;
        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('confirm_logout_for_email_change'),
            '#'
        )->withActionButtonLabel($this->lng->txt('change'));
        $this->email_change_confirmation_modal = $modal->withOnLoad($modal->getShowSignal())
            ->withAdditionalOnLoadCode(
                static function ($id) use ($form_id) {
                    return "var button = {$id}.querySelector('input[type=\"submit\"]'); "
                    . "button.addEventListener('click', (e) => {e.preventDefault();"
                    . "document.getElementById('{$form_id}').submit();});";
                }
            );

        $this->form->setFormAction($this->ctrl->getFormActionByClass(self::class, 'goToEmailConfirmation'));
        return true;
    }

    private function loginChanged(): bool
    {
        $login = $this->form->getInput('username');
        if ((int) $this->settings->get('allow_change_loginname')
           && $login !== $this->user->getLogin()) {
            return true;
        }

        return false;
    }

    private function updateLoginOrSetErrorMessages(): bool
    {
        $login = $this->form->getInput('username');
        if ($login === '' || !ilUtil::isLogin($login)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('login_invalid'));
            return false;
        }

        if (ilObjUser::_loginExists($login, $this->user->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('loginname_already_exists'));
            return false;
        }

        $this->user->setLogin($login);

        try {
            $this->user->updateLogin($this->user->getLogin());
            return true;
        } catch (ilUserException $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($e->getMessage());
            return false;
        }
    }

    public function goToEmailConfirmation(): void
    {
        $this->initPersonalDataForm();
        if (!$this->form->checkInput()
            || $this->loginChanged() && !$this->updateLoginOrSetErrorMessages()) {
            $this->form->setValuesByPost();
            $this->showPersonalData(true);
            return;
        }
        $this->savePersonalDataForm();

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();
        session_unset();
        $token = $this->change_mail_token_repo->getNewTokenForUser($this->user, $this->form->getInput('usr_email'));
        $this->ctrl->redirectToURL('login.php?cmd=force_login&target=usr_' . self::CHANGE_EMAIL_CMD . $token);
    }

    private function savePersonalDataForm(): void
    {
        // if form field name differs from setter
        $map = [
            'firstname' => 'FirstName',
            'lastname' => 'LastName',
            'title' => 'UTitle',
            'sel_country' => 'SelectedCountry',
            'phone_office' => 'PhoneOffice',
            'phone_home' => 'PhoneHome',
            'phone_mobile' => 'PhoneMobile',
            'referral_comment' => 'Comment',
            'interests_general' => 'GeneralInterests',
            'interests_help_offered' => 'OfferingHelp',
            'interests_help_looking' => 'LookingForHelp'
        ];
        $up = new ilUserProfile();
        foreach ($up->getStandardFields() as $f => $p) {
            // if item is part of form, it is currently valid (if not disabled)
            $item = $this->form->getItemByPostVar('usr_' . $f);
            if ($item && !$item->getDisabled()) {
                $value = $this->form->getInput('usr_' . $f);
                switch ($f) {
                    case 'email':
                        if ($this->emailCompletionForced()) {
                            $this->user->setEmail($value);
                        }
                        break;
                    case 'birthday':
                        $value = $item->getDate();
                        $this->user->setBirthday($value
                            ? $value->get(IL_CAL_DATE)
                            : '');
                        break;
                    case 'second_email':
                        $this->user->setSecondEmail($value);
                        break;
                    default:
                        $m = $map[$f] ?? ucfirst($f);
                        $this->user->{'set' . $m}($value);
                        break;
                }
            }
        }
        $this->user->setFullname();

        // check map activation
        if (ilMapUtil::isActivated()) {
            // #17619 - proper escaping
            $location = $this->form->getInput('location');
            $this->user->setLatitude(is_numeric($location['latitude']) ? (string) $location['latitude'] : null);
            $this->user->setLongitude(is_numeric($location['longitude']) ? (string) $location['longitude'] : null);
            $this->user->setLocationZoom(is_numeric($location['zoom']) ? $location['zoom'] : null);
        }

        // Set user defined data
        $defs = $this->user_defined_fields->getVisibleDefinitions();
        $udf = [];
        foreach ($defs as $definition) {
            $f = 'udf_' . $definition['field_id'];
            $item = $this->form->getItemByPostVar($f);
            if ($item && !$item->getDisabled()) {
                $udf[$definition['field_id']] = $this->form->getInput($f);
            }
        }
        $this->user->setUserDefinedData($udf);

        $this->uploadUserPicture();

        // profile ok
        $this->user->setProfileIncomplete(false);

        // save user data & object_data
        $this->user->setTitle($this->user->getFullname());
        $this->user->setDescription($this->user->getEmail());

        $this->user->update();
    }

    public function changeEmail(): void
    {
        $token = $this->profile_request->getToken();
        $new_email = $this->change_mail_token_repo->getNewEmailForUser($this->user, $token);

        if ($new_email !== '') {
            $this->user->setEmail($new_email);
            $this->user->update();
            $this->change_mail_token_repo->deleteEntryByToken($token);
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('saved_successfully')
            );
            $this->showPublicProfile();
            return;
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('email_could_not_be_changed'));
        $this->showPublicProfile();
    }

    public function showPublicProfile(bool $a_no_init = false): void
    {
        $this->tabs->activateTab('public_profile');
        $this->showChecklist(ilProfileChecklistStatus::STEP_PUBLISH_OPTIONS);

        $this->setHeader();

        if (!$a_no_init) {
            $this->initPublicProfileForm();
        }

        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }

    protected function getProfilePortfolio(): ?int
    {
        if ($this->settings->get('user_portfolios')) {
            return ilObjPortfolio::getDefaultPortfolio($this->user->getId());
        }
        return null;
    }

    public function initPublicProfileForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        $this->form->setTitle($this->lng->txt('user_publish_options'));
        $this->form->setDescription($this->lng->txt('user_public_profile_info'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        $portfolio_id = $this->getProfilePortfolio();

        if (!$portfolio_id) {
            // Activate public profile
            $radg = new ilRadioGroupInputGUI($this->lng->txt('user_activate_public_profile'), 'public_profile');
            $info = $this->lng->txt('user_activate_public_profile_info');
            $profile_mode = new ilPersonalProfileMode($this->user, $this->settings);
            $pub_prof = $profile_mode->getMode();
            $radg->setValue($pub_prof);
            $op1 = new ilRadioOption($this->lng->txt('usr_public_profile_disabled'), 'n', $this->lng->txt('usr_public_profile_disabled_info'));
            $radg->addOption($op1);
            $op2 = new ilRadioOption($this->lng->txt('usr_public_profile_logged_in'), 'y');
            $radg->addOption($op2);
            if ($this->settings->get('enable_global_profiles')) {
                $op3 = new ilRadioOption($this->lng->txt('usr_public_profile_global'), 'g');
                $radg->addOption($op3);
            }
            $this->form->addItem($radg);

            // #11773
            if ($this->settings->get('user_portfolios')) {
                // #10826
                $prtf = '<br />' . $this->lng->txt('user_profile_portfolio');
                $prtf .= '<br /><a href="ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio">&raquo; ' .
                    $this->lng->txt('user_portfolios') . '</a>';
                $info .= $prtf;
            }

            $radg->setInfo($info);
        } else {
            $prtf = $this->lng->txt('user_profile_portfolio_selected');
            $prtf .= '<br /><a href="ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio&prt_id=' . $portfolio_id . '">&raquo; ' .
                $this->lng->txt('portfolio') . '</a>';

            $info = new ilCustomInputGUI($this->lng->txt('user_activate_public_profile'));
            $info->setHtml($prtf);
            $this->form->addItem($info);
            $this->showPublicProfileFields($this->form, $this->user->prefs);
        }

        if (isset($op2)) {
            $this->showPublicProfileFields($this->form, $this->user->prefs, $op2, false, '-1');
        }
        if (isset($op3)) {
            $this->showPublicProfileFields($this->form, $this->user->prefs, $op3, false, '-2');
        }
        $this->form->setForceTopButtons(true);
        $this->form->addCommandButton('savePublicProfile', $this->lng->txt('user_save_continue'));
    }

    public function showPublicProfileFields(
        ilPropertyFormGUI $form,
        array $prefs,
        ?object $parent = null,
        bool $anonymized = false,
        string $key_suffix = ''
    ): void {
        $birthday = $this->user->getBirthday();
        if ($birthday) {
            $birthday = ilDatePresentation::formatDate(new ilDate($birthday, IL_CAL_DATE));
        }
        $gender = $this->user->getGender();
        if ($gender) {
            $gender = $this->lng->txt('gender_' . $gender);
        }

        $txt_sel_country = '';
        if ($this->user->getSelectedCountry() != '') {
            $this->lng->loadLanguageModule('meta');
            $txt_sel_country = $this->lng->txt('meta_c_' . $this->user->getSelectedCountry());
        }

        // profile picture
        $pic = ilObjUser::_getPersonalPicturePath($this->user->getId(), 'xsmall', true, true);
        if ($pic) {
            $pic = '<img src="' . $pic . '" />';
        }

        // personal data
        $val_array = [
            'title' => $this->user->getUTitle(),
            'birthday' => $birthday,
            'gender' => $gender,
            'upload' => $pic,
            'interests_general' => $this->user->getGeneralInterestsAsText(),
            'interests_help_offered' => $this->user->getOfferingHelpAsText(),
            'interests_help_looking' => $this->user->getLookingForHelpAsText(),
            'org_units' => $this->user->getOrgUnitsRepresentation(),
            'institution' => $this->user->getInstitution(),
            'department' => $this->user->getDepartment(),
            'street' => $this->user->getStreet(),
            'zipcode' => $this->user->getZipcode(),
            'city' => $this->user->getCity(),
            'country' => $this->user->getCountry(),
            'sel_country' => $txt_sel_country,
            'phone_office' => $this->user->getPhoneOffice(),
            'phone_home' => $this->user->getPhoneHome(),
            'phone_mobile' => $this->user->getPhoneMobile(),
            'fax' => $this->user->getFax(),
            'email' => $this->user->getEmail(),
            'second_email' => $this->user->getSecondEmail(),
            'hobby' => $this->user->getHobby(),
            'matriculation' => $this->user->getMatriculation()
        ];

        // location
        if (ilMapUtil::isActivated()) {
            $val_array['location'] = ((int) $this->user->getLatitude() +
                (int) $this->user->getLongitude()
                + (int) $this->user->getLocationZoom() > 0)
                ? ' '
                : '';
        }
        foreach ($val_array as $key => $value) {
            if (in_array($value, ['', '-']) && !$anonymized) {
                continue;
            }
            if ($anonymized) {
                $value = null;
            }

            if ($this->userSettingVisible($key)) {
                // #18795 - we should use ilUserProfile
                switch ($key) {
                    case 'upload':
                        $caption = 'personal_picture';
                        break;

                    case 'title':
                        $caption = 'person_title';
                        break;

                    default:
                        $caption = $key;
                }
                $cb = new ilCheckboxInputGUI($this->lng->txt($caption), 'chk_' . $key . $key_suffix);
                if (isset($prefs['public_' . $key]) && $prefs['public_' . $key] == 'y') {
                    $cb->setChecked(true);
                }
                $cb->setOptionTitle((string) $value);

                if (!$parent) {
                    $form->addItem($cb);
                } else {
                    $parent->addSubItem($cb);
                }
            }
        }

        // additional defined user data fields
        $user_defined_data = [];
        if (!$anonymized) {
            $user_defined_data = $this->user->getUserDefinedData();
        }
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            // public setting
            $cb = new ilCheckboxInputGUI($definition['field_name'], 'chk_udf_' . $definition['field_id'] . $key_suffix);
            $cb->setOptionTitle($user_defined_data['f_' . $definition['field_id']] ?? '');
            $public_udf = (string) ($prefs['public_udf_' . $definition['field_id']] ?? '');
            if ($public_udf === 'y') {
                $cb->setChecked(true);
            }

            if (!$parent) {
                $form->addItem($cb);
            } else {
                $parent->addSubItem($cb);
            }
        }

        if (!$anonymized) {
            $handler = ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badge_options = [];

                foreach (ilBadgeAssignment::getInstancesByUserId($this->user->getId()) as $ass) {
                    // only active
                    if ($ass->getPosition()) {
                        $badge = new ilBadge($ass->getBadgeId());
                        $badge_options[] = $badge->getTitle();
                    }
                }

                if (count($badge_options) > 1) {
                    $badge_order = new ilNonEditableValueGUI($this->lng->txt('obj_bdga'), 'bpos' . $key_suffix);
                    $badge_order->setMultiValues($badge_options);
                    $badge_order->setValue(array_shift($badge_options));
                    $badge_order->setMulti(true, true, false);

                    if (!$parent) {
                        $form->addItem($badge_order);
                    } else {
                        $parent->addSubItem($badge_order);
                    }
                }
            }
        }

        // permalink
        $ne = new ilNonEditableValueGUI($this->lng->txt('perma_link'), '');
        $ne->setValue(ilLink::_getLink($this->user->getId(), 'usr'));
        if (!$parent) {
            $form->addItem($ne);
        } else {
            $parent->addSubItem($ne);
        }
    }

    public function savePublicProfile(): void
    {
        $key_suffix = '';

        $this->initPublicProfileForm();
        if ($this->form->checkInput()) {
            // with active portfolio no options are presented
            if ($this->form->getInput('public_profile') != '') {
                $this->user->setPref('public_profile', $this->form->getInput('public_profile'));
            }

            // if check on Institute
            $val_array = ['title', 'birthday', 'gender', 'org_units',
                'institution', 'department', 'upload', 'street', 'zipcode',
                'city', 'country', 'sel_country', 'phone_office', 'phone_home',
                'phone_mobile', 'fax', 'email', 'second_email', 'hobby',
                'matriculation', 'location', 'interests_general',
                'interests_help_offered', 'interests_help_looking'];

            // set public profile preferences
            $checked_values = $this->getCheckedValues();
            foreach ($val_array as $key => $value) {
                if ($checked_values['chk_' . $value] ?? false) {
                    $this->user->setPref('public_' . $value, 'y');
                } else {
                    $this->user->setPref('public_' . $value, 'n');
                }
            }
            // additional defined user data fields
            foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
                if ($checked_values['chk_udf_' . $definition['field_id']] ?? false) {
                    $this->user->setPref('public_udf_' . $definition['field_id'], 'y');
                } else {
                    $this->user->setPref('public_udf_' . $definition['field_id'], 'n');
                }
            }

            $this->user->update();

            switch ($this->form->getInput('public_profile')) {
                case 'y':
                    $key_suffix = '-1';
                    break;
                case 'g':
                    $key_suffix = '-2';
                    break;
            }

            $handler = ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badgePositions = [];
                $bpos = $this->form->getInput('bpos' . $key_suffix);
                if (isset($bpos) && is_array($bpos)) {
                    $badgePositions = $bpos;
                }

                if (count($badgePositions) > 0) {
                    ilBadgeAssignment::updatePositions($this->user->getId(), $badgePositions);
                }
            }

            // update lucene index
            ilLuceneIndexer::updateLuceneIndex([(int) $this->user->getId()]);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);

            $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PUBLISH_OPTIONS);

            if (ilSession::get('orig_request_target')) {
                $target = ilSession::get('orig_request_target');
                ilSession::set('orig_request_target', '');
                ilUtil::redirect($target);
            } else {
                $this->ctrl->redirectByClass('iluserprivacysettingsgui', '');
            }
        }
        $this->form->setValuesByPost();
        $this->tpl->showPublicProfile(true);
    }

    protected function getCheckedValues(): array
    {
        $key_suffix = '';
        switch ($this->form->getInput('public_profile')) {
            case 'y':
                $key_suffix = '-1';
                break;
            case 'g':
                $key_suffix = '-2';
                break;
        }

        $checked_values = [];
        $post = $this->profile_request->getParsedBody();
        foreach ($post as $k => $v) {
            if (strpos($k, 'chk_') === 0 && substr($k, -2) === $key_suffix) {
                $k = str_replace(['-1', '-2'], '', $k);
                $checked_values[$k] = $v;
            }
        }
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            if (isset($post['chk_udf_' . $definition['field_id'] . $key_suffix])) {
                $checked_values['chk_udf_' . $definition['field_id']] = '1';
            }
        }
        return $checked_values;
    }

    public function showExportImport(): void
    {
        $this->tabs->activateTab('export');
        $this->setHeader();

        $button = $this->ui_factory->link()->standard(
            $this->lng->txt('pd_export_profile'),
            $this->ctrl->getLinkTarget($this, 'exportPersonalData')
        );
        $this->toolbar->addStickyItem($button);

        $exp_file = $this->user->getPersonalDataExportFile();
        if ($exp_file != '') {
            $this->toolbar->addSeparator();
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt("pd_download_last_export_file"),
                    $this->ctrl->getLinkTarget($this, "downloadPersonalData")
                )
            );
        }

        $this->toolbar->addSeparator();
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt("pd_import_personal_data"),
                $this->ctrl->getLinkTarget($this, "importPersonalDataSelection")
            )
        );

        $this->tpl->printToStdout();
    }

    public function exportPersonalData(): void
    {
        $this->user->exportPersonalData();
        $this->user->sendPersonalDataFile();
        $this->ctrl->redirect($this, 'showExportImport');
    }

    /**
     * Download personal data export file
     */
    public function downloadPersonalData(): void
    {
        $this->user->sendPersonalDataFile();
    }

    public function importPersonalDataSelection(): void
    {
        $this->tabs->activateTab('export');
        $this->setHeader();

        $this->initPersonalDataImportForm();

        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }

    public function initPersonalDataImportForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        // input file
        $fi = new ilFileInputGUI($this->lng->txt('file'), 'file');
        $fi->setRequired(true);
        $fi->setSuffixes(['zip']);
        $this->form->addItem($fi);

        // profile data
        $cb = new ilCheckboxInputGUI($this->lng->txt('pd_profile_data'), 'profile_data');
        $this->form->addItem($cb);

        // settings
        $cb = new ilCheckboxInputGUI($this->lng->txt('settings'), 'settings');
        $this->form->addItem($cb);

        // personal notes
        $cb = new ilCheckboxInputGUI($this->lng->txt('notes'), 'notes');
        $this->form->addItem($cb);

        // calendar entries
        $cb = new ilCheckboxInputGUI($this->lng->txt('pd_private_calendars'), 'calendar');
        $this->form->addItem($cb);

        $this->form->addCommandButton('importPersonalData', $this->lng->txt('import'));
        $this->form->addCommandButton('showExportImport', $this->lng->txt('cancel'));

        $this->form->setTitle($this->lng->txt('pd_import_personal_data'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    public function importPersonalData(): void
    {
        $this->initPersonalDataImportForm();
        if ($this->form->checkInput()) {
            $this->user->importPersonalData(
                $_FILES['file'],
                (bool) $this->form->getInput('profile_data'),
                (bool) $this->form->getInput('settings'),
                (bool) $this->form->getInput('notes'),
                (bool) $this->form->getInput('calendar')
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, '');
        } else {
            $this->tabs->activateTab('export');
            $this->setHeader();
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            $this->tpl->printToStdout();
        }
    }

    protected function showChecklist(int $active_step): void
    {
        $main_tpl = $this->tpl;
        $main_tpl->setRightContent($this->checklist->render($active_step));
    }

    private function tempStorePicture(): void
    {
        $capture = $this->profile_request->getUserFileCapture();

        if ($capture !== '') {
            $this->form->getItemByPostVar('userfile')->setImage($capture);
            $hidden_user_picture_carry = new ilHiddenInputGUI('user_picture_carry');
            $hidden_user_picture_carry->setValue($capture);
            $this->form->addItem($hidden_user_picture_carry);
        }
    }
}
