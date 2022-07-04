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

use Psr\Http\Message\RequestInterface;

/**
 * GUI class for personal profile
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPersonalProfileGUI: ilPublicUserProfileGUI, ilUserPrivacySettingsGUI
 */
class ilPersonalProfileGUI
{
    private ilAppEventHandler $eventHandler;
    protected ilPropertyFormGUI $form;
    protected string $password_error;
    protected string $upload_error;
    protected ilSetting $setting;
    protected ilObjUser $user;
    protected \ILIAS\User\ProfileGUIRequest $profile_request;
    public ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    public ?ilUserDefinedFields $user_defined_fields = null;
    protected ilTabsGUI $tabs;
    protected ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation;
    protected ilTermsOfServiceHelper $termsOfServiceHelper;
    protected ilErrorHandling $errorHandler;
    protected ilProfileChecklistGUI $checklist;
    protected ilUserSettingsConfig $user_settings_config;
    protected ilProfileChecklistStatus $checklist_status;
    private RequestInterface $request;

    public function __construct(
        \ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation = null,
        \ilTermsOfServiceHelper $termsOfServiceHelper = null
    ) {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->setting = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->errorHandler = $DIC['ilErr'];
        $this->eventHandler = $DIC['ilAppEventHandler'];
        $this->request = $DIC->http()->request();

        if ($termsOfServiceEvaluation === null) {
            $termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        }
        $this->termsOfServiceEvaluation = $termsOfServiceEvaluation;
        if ($termsOfServiceHelper === null) {
            $termsOfServiceHelper = new ilTermsOfServiceHelper();
        }
        $this->termsOfServiceHelper = $termsOfServiceHelper;

        $this->user_defined_fields = ilUserDefinedFields::_getInstance();

        $this->lng->loadLanguageModule("jsmath");
        $this->lng->loadLanguageModule("pd");
        $this->upload_error = "";
        $this->password_error = "";
        $this->lng->loadLanguageModule("user");
        $this->ctrl->saveParameter($this, "prompted");

        $this->checklist = new ilProfileChecklistGUI();
        $this->checklist_status = new ilProfileChecklistStatus();

        $this->user_settings_config = new ilUserSettingsConfig();

        $this->profile_request = new \ILIAS\User\ProfileGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function executeCommand() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];

        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilpublicuserprofilegui":
                $pub_profile_gui = new ilPublicUserProfileGUI($ilUser->getId());
                $pub_profile_gui->setBackUrl($ilCtrl->getLinkTarget($this, "showPersonalData"));
                $ilCtrl->forwardCommand($pub_profile_gui);
                $tpl->printToStdout();
                break;

            case "iluserprivacysettingsgui":
                $this->setHeader();
                $this->setTabs();
                $ilTabs->activateTab("visibility_settings");
                $this->showChecklist(ilProfileChecklistStatus::STEP_VISIBILITY_OPTIONS);
                $gui = new ilUserPrivacySettingsGUI();
                $ilCtrl->forwardCommand($gui);
                break;

            default:
                $this->setTabs();
                $cmd = $this->ctrl->getCmd("showPersonalData");
                $this->$cmd();
                break;
        }
    }


    public function workWithUserSetting(string $setting) : bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    public function userSettingVisible(string $setting) : bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    public function userSettingEnabled(string $setting) : bool
    {
        return $this->user_settings_config->isChangeable($setting);
    }

    public function uploadUserPicture() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if ($this->workWithUserSetting("upload")) {
            if (!$this->form->hasFileUpload("userfile") && $this->profile_request->getUserFileCapture() == "") {
                if ($this->form->getItemByPostVar("userfile")->getDeletionFlag()) {
                    $ilUser->removeUserPicture();
                }
            } else {
                $webspace_dir = ilFileUtils::getWebspaceDir();
                $image_dir = $webspace_dir . "/usr_images";
                $store_file = "usr_" . $ilUser->getID() . "." . "jpg";

                // store filename
                $ilUser->setPref("profile_image", $store_file);
                $ilUser->update();

                // move uploaded file
                // begin patch profile-image-patch – Killing 1.3.2021
                if ($this->form->hasFileUpload("userfile")) {
                    $pi = pathinfo($_FILES["userfile"]["name"]);
                    $uploaded_file = $this->form->moveFileUpload(
                        $image_dir,
                        "userfile",
                        "upload_" . $ilUser->getId() . "." . $pi["extension"]
                    );
                    if (!$uploaded_file) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("upload_error", true));
                        $this->ctrl->redirect($this, "showProfile");
                    }
                } else {        // cam capture png
                    $uploaded_file = $image_dir . "/" . "upload_" . $ilUser->getId() . ".png";
                    $img = $this->profile_request->getUserFileCapture();
                    $img = str_replace(['data:image/png;base64,', ' '], ['', '+'], $img);
                    $data = base64_decode($img);
                    $success = file_put_contents($uploaded_file, $data);
                    if (!$success) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("upload_error", true));
                        $this->ctrl->redirect($this, "showProfile");
                    }
                }
                // end patch profile-image-patch – Killing 1.3.2021
                chmod($uploaded_file, 0770);

                // take quality 100 to avoid jpeg artefacts when uploading jpeg files
                // taking only frame [0] to avoid problems with animated gifs
                $show_file = "$image_dir/usr_" . $ilUser->getId() . ".jpg";
                $thumb_file = "$image_dir/usr_" . $ilUser->getId() . "_small.jpg";
                $xthumb_file = "$image_dir/usr_" . $ilUser->getId() . "_xsmall.jpg";
                $xxthumb_file = "$image_dir/usr_" . $ilUser->getId() . "_xxsmall.jpg";
                $uploaded_file = ilShellUtil::escapeShellArg($uploaded_file);
                $show_file = ilShellUtil::escapeShellArg($show_file);
                $thumb_file = ilShellUtil::escapeShellArg($thumb_file);
                $xthumb_file = ilShellUtil::escapeShellArg($xthumb_file);
                $xxthumb_file = ilShellUtil::escapeShellArg($xxthumb_file);
                
                if (ilShellUtil::isConvertVersionAtLeast("6.3.8-3")) {
                    ilShellUtil::execConvert(
                        $uploaded_file . "[0] -geometry 200x200^ -gravity center -extent 200x200 -quality 100 JPEG:" . $show_file
                    );
                    ilShellUtil::execConvert(
                        $uploaded_file . "[0] -geometry 100x100^ -gravity center -extent 100x100 -quality 100 JPEG:" . $thumb_file
                    );
                    ilShellUtil::execConvert(
                        $uploaded_file . "[0] -geometry 75x75^ -gravity center -extent 75x75 -quality 100 JPEG:" . $xthumb_file
                    );
                    ilShellUtil::execConvert(
                        $uploaded_file . "[0] -geometry 30x30^ -gravity center -extent 30x30 -quality 100 JPEG:" . $xxthumb_file
                    );
                } else {
                    ilShellUtil::execConvert($uploaded_file . "[0] -geometry 200x200 -quality 100 JPEG:" . $show_file);
                    ilShellUtil::execConvert($uploaded_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
                    ilShellUtil::execConvert($uploaded_file . "[0] -geometry 75x75 -quality 100 JPEG:" . $xthumb_file);
                    ilShellUtil::execConvert($uploaded_file . "[0] -geometry 30x30 -quality 100 JPEG:" . $xxthumb_file);
                }
            }
        }
    }

    public function removeUserPicture() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilUser->removeUserPicture();
    }

    /**
    * show profile form
    *
    * /OLD IMPLEMENTATION DEPRECATED
    */
    public function showProfile() : void
    {
        $this->showPersonalData();
    }

    protected function showUserAgreement() : void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $tpl = new \ilTemplate('tpl.view_terms_of_service.html', true, true, 'Services/Init');

        $this->tpl->setTitle($this->lng->txt('usr_agreement'));

        $noAgreement = true;
        if (!$this->user->isAnonymous() && $this->user->getId() > 0 && $this->user->getAgreeDate()) {
            $helper = new \ilTermsOfServiceHelper();

            $entity = $helper->getCurrentAcceptanceForUser($this->user);
            if ($entity->getId()) {
                $noAgreement = false;
                $tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $entity->getText());
            }
        } else {
            $handleDocument = \ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument();
            if ($handleDocument) {
                $noAgreement = false;
                $document = $this->termsOfServiceEvaluation->document();
                $tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $document->content());
            }
        }

        if ($noAgreement) {
            $tpl->setVariable(
                'TERMS_OF_SERVICE_CONTENT',
                sprintf(
                    $this->lng->txt('no_agreement_description'),
                    'mailto:' . ilLegacyFormElementsUtil::prepareFormOutput(
                        ilSystemSupportContacts::getMailsToAddress()
                    )
                )
            );
        }

        $this->tpl->setContent($tpl->get());
        $this->tpl->setPermanentLink('usr', null, 'agreement');
        $this->tpl->printToStdout();
    }

    protected function showConsentWithdrawalConfirmation() : void
    {
        if (
            !$this->user->getPref('consent_withdrawal_requested') ||
            !$this->termsOfServiceHelper->isIncludedUser($this->user)
        ) {
            $this->errorHandler->raiseError($this->lng->txt('permission_denied'), $this->errorHandler->MESSAGE);
        }

        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();
        $this->tpl->setTitle($this->lng->txt('refuse_tos_acceptance'));

        $tosWithdrawalGui = new ilTermsOfServiceWithdrawalGUIHelper($this->user);
        $content = $tosWithdrawalGui->getConsentWithdrawalConfirmation($this);

        $this->tpl->setContent($content);
        $this->tpl->setPermanentLink('usr', null, 'agreement');
        $this->tpl->printToStdout();
    }

    protected function cancelWithdrawal() : void
    {
        if (!$this->termsOfServiceHelper->isIncludedUser($this->user)) {
            $this->errorHandler->raiseError($this->lng->txt('permission_denied'), $this->errorHandler->MESSAGE);
        }

        $this->user->deletePref('consent_withdrawal_requested');

        if (ilSession::get('orig_request_target')) {
            $target = ilSession::get('orig_request_target');
            ilSession::set('orig_request_target', '');
            $this->ctrl->redirectToURL($target);
        } else {
            ilInitialisation::redirectToStartingPage();
        }
    }

    protected function withdrawAcceptance() : void
    {
        if (
            !$this->user->getPref('consent_withdrawal_requested') ||
            !$this->termsOfServiceHelper->isIncludedUser($this->user)
        ) {
            $this->errorHandler->raiseError($this->lng->txt('permission_denied'), $this->errorHandler->MESSAGE);
        }
        $this->termsOfServiceHelper->resetAcceptance($this->user);

        $defaultAuth = ilAuthUtils::AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $withdrawalType = 0;
        if (
            $this->user->getAuthMode() == ilAuthUtils::AUTH_LDAP ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == ilAuthUtils::AUTH_LDAP)
        ) {
            $withdrawalType = 2;
        } elseif ($this->setting->get('tos_withdrawal_usr_deletion', false)) {
            $withdrawalType = 1;
        }

        $domainEvent = new ilTermsOfServiceEventWithdrawn($this->user);
        $this->eventHandler->raise(
            'Services/TermsOfService',
            'ilTermsOfServiceEventWithdrawn',
            ['event' => $domainEvent]
        );

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $GLOBALS['DIC']['ilAuthSession']->logout();

        $this->ctrl->redirectToURL('login.php?tos_withdrawal_type=' . $withdrawalType . '&cmd=force_login');
    }

    /**
     * Add location fields to form if activated
     */
    public function addLocationToForm(ilPropertyFormGUI $a_form, ilObjUser $a_user) : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        // check map activation
        if (!ilMapUtil::isActivated()) {
            return;
        }
        
        // Don't really know if this is still necessary...
        $this->lng->loadLanguageModule("maps");

        // Get user settings
        $latitude = ($a_user->getLatitude() != "")
            ? (float) $a_user->getLatitude()
            : null;
        $longitude = ($a_user->getLongitude() != "")
            ? (float) $a_user->getLongitude()
            : null;
        $zoom = $a_user->getLocationZoom();
        
        // Get Default settings, when nothing is set
        if ($latitude == null && $longitude == null && $zoom == 0) {
            $def = ilMapUtil::getDefaultSettings();
            $latitude = (float) $def["latitude"];
            $longitude = (float) $def["longitude"];
            $zoom = (int) $def["zoom"];
        }
        
        $street = $a_user->getStreet();
        if (!$street) {
            $street = $this->lng->txt("street");
        }
        $city = $a_user->getCity();
        if (!$city) {
            $city = $this->lng->txt("city");
        }
        $country = $a_user->getCountry();
        if (!$country) {
            $country = $this->lng->txt("country");
        }
        
        // location property
        $loc_prop = new ilLocationInputGUI(
            $this->lng->txt("location"),
            "location"
        );
        $loc_prop->setLatitude($latitude);
        $loc_prop->setLongitude($longitude);
        $loc_prop->setZoom($zoom);
        $loc_prop->setAddress($street . "," . $city . "," . $country);
        
        $a_form->addItem($loc_prop);
    }

    // init sub tabs
    public function setTabs() : void
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $ilHelp = $DIC['ilHelp'];

        $ilHelp->setScreenIdComponent("user");
        
        // personal data
        $ilTabs->addTab(
            "personal_data",
            $this->lng->txt("user_profile_data"),
            $this->ctrl->getLinkTarget($this, "showPersonalData")
        );
        
        // publishing options
        $ilTabs->addTab(
            "public_profile",
            $this->lng->txt("user_publish_options"),
            $this->ctrl->getLinkTarget($this, "showPublicProfile")
        );

        // visibility settings
        $txt_visibility = $this->checklist_status->anyVisibilitySettings()
            ? $this->lng->txt("user_visibility_settings")
            : $this->lng->txt("preview");
        $ilTabs->addTab(
            "visibility_settings",
            $txt_visibility,
            $this->ctrl->getLinkTargetByClass("ilUserPrivacySettingsGUI", "")
        );

        // export
        $ilTabs->addTab(
            "export",
            $this->lng->txt("export") . "/" . $this->lng->txt("import"),
            $this->ctrl->getLinkTarget($this, "showExportImport")
        );
    }


    public function __showOtherInformations() : bool
    {
        $d_set = new ilSetting("delicous");
        if ($this->userSettingVisible("matriculation") or count($this->user_defined_fields->getVisibleDefinitions())
            or $d_set->get("user_profile") == "1") {
            return true;
        }
        return false;
    }

    public function __showUserDefinedFields() : bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $user_defined_data = $ilUser->getUserDefinedData();
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            if ($definition['field_type'] == UDF_TYPE_TEXT) {
                $this->tpl->setCurrentBlock("field_text");
                $this->tpl->setVariable(
                    "FIELD_VALUE",
                    ilLegacyFormElementsUtil::prepareFormOutput($user_defined_data[$field_id])
                );
                if (!$definition['changeable']) {
                    $this->tpl->setVariable("DISABLED_FIELD", 'disabled=\"disabled\"');
                }
                $this->tpl->setVariable("FIELD_NAME", 'udf[' . $definition['field_id'] . ']');
            } else {
                if ($definition['changeable']) {
                    $name = 'udf[' . $definition['field_id'] . ']';
                    $disabled = false;
                } else {
                    $name = '';
                    $disabled = true;
                }
                $this->tpl->setCurrentBlock("field_select");
                $this->tpl->setVariable(
                    "SELECT_BOX",
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
            $this->tpl->setCurrentBlock("user_defined");

            if ($definition['required']) {
                $name = $definition['field_name'] . "<span class=\"asterisk\">*</span>";
            } else {
                $name = $definition['field_name'];
            }
            $this->tpl->setVariable("TXT_FIELD_NAME", $name);
            $this->tpl->parseCurrentBlock();
        }
        return true;
    }

    public function setHeader() : void
    {
        $this->tpl->setTitle($this->lng->txt('personal_profile'));
    }

    //
    //
    //	PERSONAL DATA FORM
    //
    //
    
    public function showPersonalData(
        bool $a_no_init = false,
        bool $a_migration_started = false
    ) : void {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $prompt_service = new ilUserProfilePromptService();

        $ilTabs->activateTab("personal_data");
        $ctrl = $DIC->ctrl();

        $it = "";
        if ($this->profile_request->getPrompted() == 1) {
            $it = $prompt_service->data()->getSettings()->getPromptText($ilUser->getLanguage());
        }
        if ($it === "") {
            $it = $prompt_service->data()->getSettings()->getInfoText($ilUser->getLanguage());
        }
        if (trim($it) !== "") {
            $pub_prof = in_array($ilUser->prefs["public_profile"] ?? "", array("y", "n", "g"))
                ? $ilUser->prefs["public_profile"]
                : "n";
            $box = $DIC->ui()->factory()->messageBox()->info($it);
            if ($pub_prof === "n") {
                $box = $box->withLinks(
                    [$DIC->ui()->factory()->link()->standard(
                        $lng->txt("user_make_profile_public"),
                        $ctrl->getLinkTarget($this, "showPublicProfile")
                    )]
                );
            }
            $it = $DIC->ui()->renderer()->render($box);
        }
        $this->setHeader();

        $this->showChecklist(ilProfileChecklistStatus::STEP_PROFILE_DATA);

        if (!$a_no_init) {
            $this->initPersonalDataForm();
            // catch feedback message
            if ($ilUser->getProfileIncomplete()) {
                $this->tpl->setOnScreenMessage('info', $lng->txt("profile_incomplete"));
            }
        }
        $this->tpl->setContent($it . $this->form->getHTML());

        $this->tpl->printToStdout();
    }

    public function initPersonalDataForm() : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $input = [];

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        // user defined fields
        $user_defined_data = $ilUser->getUserDefinedData();

        
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            $value = $user_defined_data["f_" . $field_id] ?? "";
            
            $fprop = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition(
                $definition,
                $definition['changeable'] ?? false,
                $value
            );
            if ($fprop instanceof ilFormPropertyGUI) {
                $input['udf_' . $definition['field_id']] = $fprop;
            }
        }
        
        // standard fields
        $up = new ilUserProfile();
        $up->skipField("password");
        $up->skipGroup("settings");
        $up->skipGroup("preferences");
        
        $up->setAjaxCallback(
            $this->ctrl->getLinkTargetByClass('ilPublicUserProfileGUI', 'doProfileAutoComplete', '', true)
        );
        
        // standard fields
        $up->addStandardFieldsToForm($this->form, $ilUser, $input);
        
        $this->addLocationToForm($this->form, $ilUser);

        $this->form->addCommandButton("savePersonalData", $lng->txt("user_save_continue"));
    }

    public function savePersonalData() : void
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
    
        $this->initPersonalDataForm();
        if ($this->form->checkInput()) {
            $form_valid = true;
            
            // if form field name differs from setter
            $map = array(
                "firstname" => "FirstName",
                "lastname" => "LastName",
                "title" => "UTitle",
                "sel_country" => "SelectedCountry",
                "phone_office" => "PhoneOffice",
                "phone_home" => "PhoneHome",
                "phone_mobile" => "PhoneMobile",
                "referral_comment" => "Comment",
                "interests_general" => "GeneralInterests",
                "interests_help_offered" => "OfferingHelp",
                "interests_help_looking" => "LookingForHelp"
            );
            $up = new ilUserProfile();
            foreach ($up->getStandardFields() as $f => $p) {
                // if item is part of form, it is currently valid (if not disabled)
                $item = $this->form->getItemByPostVar("usr_" . $f);
                if ($item && !$item->getDisabled()) {
                    $value = $this->form->getInput("usr_" . $f);
                    switch ($f) {
                        case "birthday":
                            $value = $item->getDate();
                            $ilUser->setBirthday($value
                                ? $value->get(IL_CAL_DATE)
                                : "");
                            break;
                        case "second_email":
                            $ilUser->setSecondEmail($value);
                            break;
                        default:
                            $m = $map[$f] ?? ucfirst($f);
                            $ilUser->{"set" . $m}($value);
                            break;
                    }
                }
            }
            $ilUser->setFullname();

            // check map activation
            if (ilMapUtil::isActivated()) {
                // #17619 - proper escaping
                $location = $this->form->getInput("location");
                $lat = ilUtil::stripSlashes($location["latitude"]);
                $long = ilUtil::stripSlashes($location["longitude"]);
                $zoom = ilUtil::stripSlashes($location["zoom"]);
                $ilUser->setLatitude(is_numeric($lat) ? $lat : null);
                $ilUser->setLongitude(is_numeric($long) ? $long : null);
                $ilUser->setLocationZoom(is_numeric($zoom) ? $zoom : null);
            }
            
            // Set user defined data
            $defs = $this->user_defined_fields->getVisibleDefinitions();
            $udf = array();
            foreach ($defs as $definition) {
                $f = "udf_" . $definition['field_id'];
                $item = $this->form->getItemByPostVar($f);
                if ($item && !$item->getDisabled()) {
                    $udf[$definition['field_id']] = $this->form->getInput($f);
                }
            }
            $ilUser->setUserDefinedData($udf);
        
            // if loginname is changeable -> validate
            $un = $this->form->getInput('username');
            if ((int) $ilSetting->get('allow_change_loginname') &&
               $un != $ilUser->getLogin()) {
                if (!strlen($un) || !ilUtil::isLogin($un)) {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt('form_input_not_valid'));
                    $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('login_invalid'));
                    $form_valid = false;
                } elseif (ilObjUser::_loginExists($un, $ilUser->getId())) {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt('form_input_not_valid'));
                    $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('loginname_already_exists'));
                    $form_valid = false;
                } else {
                    $ilUser->setLogin($un);
                    
                    try {
                        $ilUser->updateLogin($ilUser->getLogin());
                    } catch (ilUserException $e) {
                        $this->tpl->setOnScreenMessage('failure', $lng->txt('form_input_not_valid'));
                        $this->form->getItemByPostVar('username')->setAlert($e->getMessage());
                        $form_valid = false;
                    }
                }
            }

            // everthing's ok. save form data
            if ($form_valid) {
                $this->uploadUserPicture();
                
                // profile ok
                $ilUser->setProfileIncomplete(false);
    
                // save user data & object_data
                $ilUser->setTitle($ilUser->getFullname());
                $ilUser->setDescription($ilUser->getEmail());
    
                $ilUser->update();

                $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PROFILE_DATA);
                $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

                $ilCtrl->redirect($this, "showPublicProfile");
            }
        }
        
        $this->form->setValuesByPost();
        $this->showPersonalData(true);
    }
    
    //
    //
    //	PUBLIC PROFILE FORM
    //
    //
    
    public function showPublicProfile(bool $a_no_init = false) : void
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("public_profile");
        $this->showChecklist(ilProfileChecklistStatus::STEP_PUBLISH_OPTIONS);

        $this->setHeader();

        if (!$a_no_init) {
            $this->initPublicProfileForm();
        }
        
        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }
    
    /**
     * has profile set to a portfolio?
     */
    protected function getProfilePortfolio() : ?int
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        if ($ilSetting->get('user_portfolios')) {
            return ilObjPortfolio::getDefaultPortfolio($ilUser->getId());
        }
        return null;
    }

    public function initPublicProfileForm() : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->form = new ilPropertyFormGUI();
        
        $this->form->setTitle($lng->txt("user_publish_options"));
        $this->form->setDescription($lng->txt("user_public_profile_info"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        $portfolio_id = $this->getProfilePortfolio();
    
        if (!$portfolio_id) {
            // Activate public profile
            $radg = new ilRadioGroupInputGUI($lng->txt("user_activate_public_profile"), "public_profile");
            $info = $this->lng->txt("user_activate_public_profile_info");
            $profile_mode = new ilPersonalProfileMode($ilUser, $ilSetting);
            $pub_prof = $profile_mode->getMode();
            $radg->setValue($pub_prof);
            $op1 = new ilRadioOption($lng->txt("usr_public_profile_disabled"), "n", $lng->txt("usr_public_profile_disabled_info"));
            $radg->addOption($op1);
            $op2 = new ilRadioOption($lng->txt("usr_public_profile_logged_in"), "y");
            $radg->addOption($op2);
            if ($ilSetting->get('enable_global_profiles')) {
                $op3 = new ilRadioOption($lng->txt("usr_public_profile_global"), "g");
                $radg->addOption($op3);
            }
            $this->form->addItem($radg);
            
            // #11773
            if ($ilSetting->get('user_portfolios')) {
                // #10826
                $prtf = "<br />" . $lng->txt("user_profile_portfolio");
                $prtf .= "<br /><a href=\"ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio\">&raquo; " .
                    $lng->txt("user_portfolios") . "</a>";
                $info .= $prtf;
            }
            
            $radg->setInfo($info);
        } else {
            $prtf = $lng->txt("user_profile_portfolio_selected");
            $prtf .= "<br /><a href=\"ilias.php?baseClass=ilDashboardGUI&cmd=jumpToPortfolio&prt_id=" . $portfolio_id . "\">&raquo; " .
                $lng->txt("portfolio") . "</a>";
            
            $info = new ilCustomInputGUI($lng->txt("user_activate_public_profile"));
            $info->setHtml($prtf);
            $this->form->addItem($info);
            $this->showPublicProfileFields($this->form, $ilUser->prefs);
        }

        if (isset($op2)) {
            $this->showPublicProfileFields($this->form, $ilUser->prefs, $op2, false, "-1");
        }
        if (isset($op3)) {
            $this->showPublicProfileFields($this->form, $ilUser->prefs, $op3, false, "-2");
        }
        $this->form->setForceTopButtons(true);
        $this->form->addCommandButton("savePublicProfile", $lng->txt("user_save_continue"));
    }

    public function showPublicProfileFields(
        ilPropertyFormGUI $form,
        array $prefs,
        ?object $parent = null,
        bool $anonymized = false,
        string $key_suffix = ""
    ) : void {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $birthday = $ilUser->getBirthday();
        if ($birthday) {
            $birthday = ilDatePresentation::formatDate(new ilDate($birthday, IL_CAL_DATE));
        }
        $gender = $ilUser->getGender();
        if ($gender) {
            $gender = $this->lng->txt("gender_" . $gender);
        }

        $txt_sel_country = "";
        if ($ilUser->getSelectedCountry() != "") {
            $this->lng->loadLanguageModule("meta");
            $txt_sel_country = $this->lng->txt("meta_c_" . $ilUser->getSelectedCountry());
        }
        
        // profile picture
        $pic = ilObjUser::_getPersonalPicturePath($ilUser->getId(), "xsmall", true, true);
        if ($pic) {
            $pic = "<img src=\"" . $pic . "\" />";
        }

        // personal data
        $val_array = array(
            "title" => $ilUser->getUTitle(),
            "birthday" => $birthday,
            "gender" => $gender,
            "upload" => $pic,
            "interests_general" => $ilUser->getGeneralInterestsAsText(),
            "interests_help_offered" => $ilUser->getOfferingHelpAsText(),
            "interests_help_looking" => $ilUser->getLookingForHelpAsText(),
            "org_units" => $ilUser->getOrgUnitsRepresentation(),
            "institution" => $ilUser->getInstitution(),
            "department" => $ilUser->getDepartment(),
            "street" => $ilUser->getStreet(),
            "zipcode" => $ilUser->getZipcode(),
            "city" => $ilUser->getCity(),
            "country" => $ilUser->getCountry(),
            "sel_country" => $txt_sel_country,
            "phone_office" => $ilUser->getPhoneOffice(),
            "phone_home" => $ilUser->getPhoneHome(),
            "phone_mobile" => $ilUser->getPhoneMobile(),
            "fax" => $ilUser->getFax(),
            "email" => $ilUser->getEmail(),
            "second_email" => $ilUser->getSecondEmail(),
            "hobby" => $ilUser->getHobby(),
            "matriculation" => $ilUser->getMatriculation()
        );
        
        // location
        if (ilMapUtil::isActivated()) {
            $val_array["location"] = ((int) $ilUser->getLatitude() + (int) $ilUser->getLongitude() + (int) $ilUser->getLocationZoom() > 0)
                ? " "
                : "";
        }
        foreach ($val_array as $key => $value) {
            if (in_array($value, ["", "-"]) && !$anonymized) {
                continue;
            }
            if ($anonymized) {
                $value = null;
            }
            
            if ($this->userSettingVisible($key)) {
                // #18795 - we should use ilUserProfile
                switch ($key) {
                    case "upload":
                        $caption = "personal_picture";
                        break;
                    
                    case "title":
                        $caption = "person_title";
                        break;
                    
                    default:
                        $caption = $key;
                }
                $cb = new ilCheckboxInputGUI($this->lng->txt($caption), "chk_" . $key . $key_suffix);
                if (isset($prefs["public_" . $key]) && $prefs["public_" . $key] == "y") {
                    $cb->setChecked(true);
                }
                //$cb->setInfo($value);
                $cb->setOptionTitle((string) $value);

                if (!$parent) {
                    $form->addItem($cb);
                } else {
                    $parent->addSubItem($cb);
                }
            }
        }

        // additional defined user data fields
        $user_defined_data = array();
        if (!$anonymized) {
            $user_defined_data = $ilUser->getUserDefinedData();
        }
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            // public setting
            $cb = new ilCheckboxInputGUI($definition["field_name"], "chk_udf_" . $definition["field_id"]);
            $cb->setOptionTitle($user_defined_data["f_" . $definition["field_id"]] ?? "");
            $public_udf = (string) ($prefs["public_udf_" . $definition["field_id"]] ?? '');
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
                $badge_options = array();

                foreach (ilBadgeAssignment::getInstancesByUserId($ilUser->getId()) as $ass) {
                    // only active
                    if ($ass->getPosition()) {
                        $badge = new ilBadge($ass->getBadgeId());
                        $badge_options[] = $badge->getTitle();
                    }
                }

                if (count($badge_options) > 1) {
                    $badge_order = new ilNonEditableValueGUI($this->lng->txt("obj_bdga"), "bpos" . $key_suffix);
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
        $ne = new ilNonEditableValueGUI($this->lng->txt("perma_link"), "");
        $ne->setValue(ilLink::_getLink($this->user->getId(), "usr"));
        if (!$parent) {
            $form->addItem($ne);
        } else {
            $parent->addSubItem($ne);
        }
    }
    
    public function savePublicProfile() : void
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $key_suffix = "";
    
        $this->initPublicProfileForm();
        if ($this->form->checkInput()) {
            // with active portfolio no options are presented
            if ($this->form->getInput("public_profile") != "") {
                $ilUser->setPref("public_profile", $this->form->getInput("public_profile"));
            }

            // if check on Institute
            $val_array = array("title", "birthday", "gender", "org_units", "institution", "department", "upload",
                "street", "zipcode", "city", "country", "sel_country", "phone_office", "phone_home", "phone_mobile",
                "fax", "email", "second_email", "hobby", "matriculation", "location",
                "interests_general", "interests_help_offered", "interests_help_looking");
    
            // set public profile preferences
            $checked_values = $this->getCheckedValues();
            foreach ($val_array as $key => $value) {
                if ($checked_values["chk_" . $value] ?? false) {
                    $ilUser->setPref("public_" . $value, "y");
                } else {
                    $ilUser->setPref("public_" . $value, "n");
                }
            }
    
            // additional defined user data fields
            foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
                if ($checked_values["chk_udf_" . $definition["field_id"]] ?? false) {
                    $ilUser->setPref("public_udf_" . $definition["field_id"], "y");
                } else {
                    $ilUser->setPref("public_udf_" . $definition["field_id"], "n");
                }
            }

            $ilUser->update();

            switch ($this->form->getInput("public_profile")) {
                case "y":
                    $key_suffix = "-1";
                    break;
                case "g":
                    $key_suffix = "-2";
                    break;
            }

            $handler = ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badgePositions = [];
                $bpos = $this->form->getInput("bpos" . $key_suffix);
                if (isset($bpos) && is_array($bpos)) {
                    $badgePositions = $bpos;
                }

                if (count($badgePositions) > 0) {
                    ilBadgeAssignment::updatePositions($ilUser->getId(), $badgePositions);
                }
            }
            
            // update lucene index
            ilLuceneIndexer::updateLuceneIndex(array((int) $GLOBALS['DIC']['ilUser']->getId()));
            
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

            $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PUBLISH_OPTIONS);

            if (ilSession::get('orig_request_target')) {
                $target = ilSession::get('orig_request_target');
                ilSession::set('orig_request_target', '');
                ilUtil::redirect($target);
            } else {
                $ilCtrl->redirectByClass("iluserprivacysettingsgui", "");
            }
        }
        $this->form->setValuesByPost();
        $tpl->showPublicProfile(true);
    }

    protected function getCheckedValues() : array // Missing array type.
    {
        $checked_values = [];
        foreach ($this->request->getParsedBody() as $k => $v) {
            if (strpos($k, "chk_") === 0) {
                $k = str_replace(["-1", "-2"], "", $k);
                $checked_values[$k] = $v;
            }
        }
        return $checked_values;
    }

    public function showExportImport() : void
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilUser = $DIC['ilUser'];
        
        $ilTabs->activateTab("export");
        $this->setHeader();
        
        $button = ilLinkButton::getInstance();
        $button->setCaption("pd_export_profile");
        $button->setUrl($ilCtrl->getLinkTarget($this, "exportPersonalData"));
        $ilToolbar->addStickyItem($button);
                
        $exp_file = $ilUser->getPersonalDataExportFile();
        if ($exp_file != "") {
            $ilToolbar->addSeparator();
            $ilToolbar->addButton(
                $this->lng->txt("pd_download_last_export_file"),
                $ilCtrl->getLinkTarget($this, "downloadPersonalData")
            );
        }

        $ilToolbar->addSeparator();
        $ilToolbar->addButton(
            $this->lng->txt("pd_import_personal_data"),
            $ilCtrl->getLinkTarget($this, "importPersonalDataSelection")
        );
        
        $tpl->printToStdout();
    }
    
    public function exportPersonalData() : void
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        $ilUser->exportPersonalData();
        $ilUser->sendPersonalDataFile();
        $ilCtrl->redirect($this, "showExportImport");
    }
    
    /**
     * Download personal data export file
     */
    public function downloadPersonalData() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $ilUser->sendPersonalDataFile();
    }
    
    public function importPersonalDataSelection() : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
    
        $ilTabs->activateTab("export");
        $this->setHeader();
        
        $this->initPersonalDataImportForm();
        
        $tpl->setContent($this->form->getHTML());
        $tpl->printToStdout();
    }
    
    public function initPersonalDataImportForm() : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->form = new ilPropertyFormGUI();
        
        // input file
        $fi = new ilFileInputGUI($lng->txt("file"), "file");
        $fi->setRequired(true);
        $fi->setSuffixes(array("zip"));
        $this->form->addItem($fi);

        // profile data
        $cb = new ilCheckboxInputGUI($this->lng->txt("pd_profile_data"), "profile_data");
        $this->form->addItem($cb);
        
        // settings
        $cb = new ilCheckboxInputGUI($this->lng->txt("settings"), "settings");
        $this->form->addItem($cb);

        // personal notes
        $cb = new ilCheckboxInputGUI($this->lng->txt("notes"), "notes");
        $this->form->addItem($cb);
        
        // calendar entries
        $cb = new ilCheckboxInputGUI($this->lng->txt("pd_private_calendars"), "calendar");
        $this->form->addItem($cb);

        $this->form->addCommandButton("importPersonalData", $lng->txt("import"));
        $this->form->addCommandButton("showExportImport", $lng->txt("cancel"));
                    
        $this->form->setTitle($lng->txt("pd_import_personal_data"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    public function importPersonalData() : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $this->initPersonalDataImportForm();
        if ($this->form->checkInput()) {
            $ilUser->importPersonalData(
                $_FILES["file"],
                (int) $this->form->getInput("profile_data"),
                (int) $this->form->getInput("settings"),
                (int) $this->form->getInput("notes"),
                (int) $this->form->getInput("calendar")
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $ilTabs->activateTab("export");
            $this->setHeader();
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            $tpl->printToStdout();
        }
    }

    protected function showChecklist(int $active_step) : void
    {
        $main_tpl = $this->tpl;
        $main_tpl->setRightContent($this->checklist->render($active_step));
    }
}
