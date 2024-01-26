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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\User\Profile\ProfileChangeMailTokenRepository;
use ILIAS\User\Profile\ProfileChangeMailTokenDBRepository;

/**
 * GUI class for personal profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPersonalProfileGUI: ilPublicUserProfileGUI, ilUserPrivacySettingsGUI
 */
class ilPersonalProfileGUI
{
    private const PERSONAL_DATA_FORM_ID = 'pd';
    public const CHANGE_EMAIL_CMD = 'changeEmail';

    /**
     * @var ilAppEventHandler
     */
    private $eventHandler;

    public $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var mixed
     */
    protected $ctrl;

    public $user_defined_fields = null;

    /** @var \ilTabsGUI */
    protected $tabs;

    /** @var \ilTermsOfServiceDocumentEvaluation */
    protected $termsOfServiceEvaluation;

    /** @var \ilTermsOfServiceHelper */
    protected $termsOfServiceHelper;

    /** @var ilErrorHandling */
    protected $errorHandler;

    /**
     * @var ilProfileChecklistGUI
     */
    protected $checklist;

    /**
     * @var ilUserSettingsConfig
     */
    protected $user_settings_config;

    /**
     * @var ilProfileChecklistStatus
     */
    protected $checklist_status;

    protected $ui_factory;
    protected $ui_renderer;
    protected $auth_session;
    protected $email_change_confirmation_modal = null;
    private $change_mail_token_repo;

    /**
     * constructor
     * @param \ilTermsOfServiceDocumentEvaluation|null $termsOfServiceEvaluation
     * @param ilTermsOfServiceHelper|null $termsOfServiceHelper
     */
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

        if ($termsOfServiceEvaluation === null) {
            $termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        }
        $this->termsOfServiceEvaluation = $termsOfServiceEvaluation;
        if ($termsOfServiceHelper === null) {
            $termsOfServiceHelper = new ilTermsOfServiceHelper();
        }
        $this->termsOfServiceHelper = $termsOfServiceHelper;

        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $this->user_defined_fields = &ilUserDefinedFields::_getInstance();

        $this->lng->loadLanguageModule("jsmath");
        $this->lng->loadLanguageModule("pd");
        $this->upload_error = "";
        $this->password_error = "";
        $this->lng->loadLanguageModule("user");
        $this->ctrl->saveParameter($this, "prompted");

        $this->checklist = new ilProfileChecklistGUI();
        $this->checklist_status = new ilProfileChecklistStatus();

        $this->user_settings_config = new ilUserSettingsConfig();

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->change_mail_token_repo = new ProfileChangeMailTokenDBRepository($DIC['ilDB']);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];

        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilpublicuserprofilegui":
                include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
                $_GET["user_id"] = $ilUser->getId();
                $pub_profile_gui = new ilPublicUserProfileGUI($_GET["user_id"]);
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
        return true;
    }


    /**
     * @param string $setting
     * @return bool
     */
    public function workWithUserSetting(string $setting) : bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    /**
     * @param string $setting
     * @return bool
     */
    public function userSettingVisible(string $setting) : bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    /**
     * @param string $setting
     * @return bool
     */
    public function userSettingEnabled(string $setting) : bool
    {
        return $this->user_settings_config->isChangeable($setting);
    }

    /**
    * Upload user image
    */
    public function uploadUserPicture()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if ($this->workWithUserSetting("upload")) {
            if (!$this->form->hasFileUpload("userfile")) {
                if ($this->form->getItemByPostVar("userfile")->getDeletionFlag()) {
                    $ilUser->removeUserPicture();
                }
                return;
            } else {
                $webspace_dir = ilUtil::getWebspaceDir();
                $image_dir = $webspace_dir . "/usr_images";
                $store_file = "usr_" . $ilUser->getID() . "." . "jpg";

                // store filename
                $ilUser->setPref("profile_image", $store_file);
                $ilUser->update();

                // move uploaded file

                $pi = pathinfo($_FILES["userfile"]["name"]);
                $uploaded_file = $this->form->moveFileUpload(
                    $image_dir,
                    "userfile",
                    "upload_" . $ilUser->getId() . "." . $pi["extension"]
                );
                if (!$uploaded_file) {
                    ilUtil::sendFailure($this->lng->txt("upload_error", true));
                    $this->ctrl->redirect($this, "showProfile");
                }
                chmod($uploaded_file, 0770);

                // take quality 100 to avoid jpeg artefacts when uploading jpeg files
                // taking only frame [0] to avoid problems with animated gifs
                $show_file = "$image_dir/usr_" . $ilUser->getId() . ".jpg";
                $thumb_file = "$image_dir/usr_" . $ilUser->getId() . "_small.jpg";
                $xthumb_file = "$image_dir/usr_" . $ilUser->getId() . "_xsmall.jpg";
                $xxthumb_file = "$image_dir/usr_" . $ilUser->getId() . "_xxsmall.jpg";
                $uploaded_file = ilUtil::escapeShellArg($uploaded_file);
                $show_file = ilUtil::escapeShellArg($show_file);
                $thumb_file = ilUtil::escapeShellArg($thumb_file);
                $xthumb_file = ilUtil::escapeShellArg($xthumb_file);
                $xxthumb_file = ilUtil::escapeShellArg($xxthumb_file);

                if (ilUtil::isConvertVersionAtLeast("6.3.8-3")) {
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200^ -gravity center -extent 200x200 -quality 100 JPEG:" . $show_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100^ -gravity center -extent 100x100 -quality 100 JPEG:" . $thumb_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75^ -gravity center -extent 75x75 -quality 100 JPEG:" . $xthumb_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30^ -gravity center -extent 30x30 -quality 100 JPEG:" . $xxthumb_file);
                } else {
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200 -quality 100 JPEG:" . $show_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75 -quality 100 JPEG:" . $xthumb_file);
                    ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30 -quality 100 JPEG:" . $xxthumb_file);
                }
            }
        }

        //		$this->saveProfile();
    }

    /**
    * remove user image
    */
    public function removeUserPicture()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $ilUser->removeUserPicture();

        $this->saveProfile();
    }



    /**
    * save user profile data
    */
    public function saveProfile()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        //init checking var
        $form_valid = true;

        // testing by ratana ty:
        // if people check on check box it will
        // write some datata to table usr_pref
        // if check on Public Profile
        if (($_POST["chk_pub"]) == "on") {
            $ilUser->setPref("public_profile", "y");
        } else {
            $ilUser->setPref("public_profile", "n");
        }

        // if check on Institute
        $val_array = array("institution", "department", "upload", "street",
            "zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
            "fax", "email", "second_email", "hobby", "matriculation");

        // set public profile preferences
        foreach ($val_array as $key => $value) {
            if (($_POST["chk_" . $value]) == "on") {
                $ilUser->setPref("public_" . $value, "y");
            } else {
                $ilUser->setPref("public_" . $value, "n");
            }
        }

        // check dynamically required fields
        foreach ($this->settings as $key => $val) {
            if (substr($key, 0, 8) == "require_") {
                $require_keys[] = substr($key, 8);
            }
        }

        foreach ($require_keys as $key => $val) {
            // exclude required system and registration-only fields
            $system_fields = array("login", "default_role", "passwd", "passwd2");
            if (!in_array($val, $system_fields)) {
                if ($this->workWithUserSetting($val)) {
                    if (isset($this->settings["require_" . $val]) && $this->settings["require_" . $val]) {
                        if (empty($_POST["usr_" . $val])) {
                            ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields") . ": " . $this->lng->txt($val));
                            $form_valid = false;
                        }
                    }
                }
            }
        }

        // Check user defined required fields
        if ($form_valid and !$this->__checkUserDefinedRequiredFields()) {
            ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"));
            $form_valid = false;
        }

        // check email
        if ($this->workWithUserSetting("email")) {
            if (!ilUtil::is_email($_POST["usr_email"]) and !empty($_POST["usr_email"]) and $form_valid) {
                ilUtil::sendFailure($this->lng->txt("email_not_valid"));
                $form_valid = false;
            }
        }
        // check second email
        if ($this->workWithUserSetting("second_email")) {
            if (!ilUtil::is_email($_POST["usr_second_email"]) and !empty($_POST["usr_second_email"]) and $form_valid) {
                ilUtil::sendFailure($this->lng->txt("second_email_not_valid"));
                $form_valid = false;
            }
        }

        //update user data (not saving!)
        if ($this->workWithUserSetting("firstname")) {
            $ilUser->setFirstName(ilUtil::stripSlashes($_POST["usr_firstname"]));
        }
        if ($this->workWithUserSetting("lastname")) {
            $ilUser->setLastName(ilUtil::stripSlashes($_POST["usr_lastname"]));
        }
        if ($this->workWithUserSetting("gender")) {
            $ilUser->setGender($_POST["usr_gender"]);
        }
        if ($this->workWithUserSetting("title")) {
            $ilUser->setUTitle(ilUtil::stripSlashes($_POST["usr_title"]));
        }
        $ilUser->setFullname();
        if ($this->workWithUserSetting("institution")) {
            $ilUser->setInstitution(ilUtil::stripSlashes($_POST["usr_institution"]));
        }
        if ($this->workWithUserSetting("department")) {
            $ilUser->setDepartment(ilUtil::stripSlashes($_POST["usr_department"]));
        }
        if ($this->workWithUserSetting("street")) {
            $ilUser->setStreet(ilUtil::stripSlashes($_POST["usr_street"]));
        }
        if ($this->workWithUserSetting("zipcode")) {
            $ilUser->setZipcode(ilUtil::stripSlashes($_POST["usr_zipcode"]));
        }
        if ($this->workWithUserSetting("city")) {
            $ilUser->setCity(ilUtil::stripSlashes($_POST["usr_city"]));
        }
        if ($this->workWithUserSetting("country")) {
            $ilUser->setCountry(ilUtil::stripSlashes($_POST["usr_country"]));
        }
        if ($this->workWithUserSetting("phone_office")) {
            $ilUser->setPhoneOffice(ilUtil::stripSlashes($_POST["usr_phone_office"]));
        }
        if ($this->workWithUserSetting("phone_home")) {
            $ilUser->setPhoneHome(ilUtil::stripSlashes($_POST["usr_phone_home"]));
        }
        if ($this->workWithUserSetting("phone_mobile")) {
            $ilUser->setPhoneMobile(ilUtil::stripSlashes($_POST["usr_phone_mobile"]));
        }
        if ($this->workWithUserSetting("fax")) {
            $ilUser->setFax(ilUtil::stripSlashes($_POST["usr_fax"]));
        }
        if ($this->workWithUserSetting("email")) {
            $ilUser->setEmail(ilUtil::stripSlashes($_POST["usr_email"]));
        }
        if ($this->workWithUserSetting("second_email")) {
            $ilUser->setSecondEmail(ilUtil::stripSlashes($_POST["usr_second_email"]));
        }
        if ($this->workWithUserSetting("hobby")) {
            $ilUser->setHobby(ilUtil::stripSlashes($_POST["usr_hobby"]));
        }
        if ($this->workWithUserSetting("referral_comment")) {
            $ilUser->setComment(ilUtil::stripSlashes($_POST["usr_referral_comment"]));
        }
        if ($this->workWithUserSetting("matriculation")) {
            $ilUser->setMatriculation(ilUtil::stripSlashes($_POST["usr_matriculation"]));
        }

        // Set user defined data
        $ilUser->setUserDefinedData($_POST['udf']);

        // everthing's ok. save form data
        if ($form_valid) {
            // init reload var. page should only be reloaded if skin or style were changed
            $reload = false;

            if ($this->workWithUserSetting("skin_style")) {
                //set user skin and style
                if ($_POST["usr_skin_style"] != "") {
                    $sknst = explode(":", $_POST["usr_skin_style"]);

                    if ($ilUser->getPref("style") != $sknst[1] ||
                        $ilUser->getPref("skin") != $sknst[0]) {
                        $ilUser->setPref("skin", $sknst[0]);
                        $ilUser->setPref("style", $sknst[1]);
                        $reload = true;
                    }
                }
            }

            if ($this->workWithUserSetting("language")) {
                // reload page if language was changed
                //if ($_POST["usr_language"] != "" and $_POST["usr_language"] != $_SESSION['lang'])
                // (this didn't work as expected, alex)
                if ($_POST["usr_language"] != $ilUser->getLanguage()) {
                    $reload = true;
                }

                // set user language
                $ilUser->setLanguage($_POST["usr_language"]);
            }
            if ($this->workWithUserSetting("hits_per_page")) {
                // set user hits per page
                if ($_POST["hits_per_page"] != "") {
                    $ilUser->setPref("hits_per_page", $_POST["hits_per_page"]);
                }
            }

            // set show users online
            /*if ($this->workWithUserSetting("show_users_online"))
            {
                $ilUser->setPref("show_users_online", $_POST["show_users_online"]);
            }*/

            // set hide own online_status
            if ($this->workWithUserSetting("hide_own_online_status")) {
                if ($_POST["chk_hide_own_online_status"] != "") {
                    $ilUser->setPref("hide_own_online_status", "y");
                } else {
                    $ilUser->setPref("hide_own_online_status", "n");
                }
            }

            // personal desktop items in news block
            /* Subscription Concept is abandonded for now, we show all news of pd items (Alex)
                        if ($_POST["pd_items_news"] != "")
                        {
                            $ilUser->setPref("pd_items_news","y");
                        }
                        else
                        {
                            $ilUser->setPref("pd_items_news","n");
                        }
            */

            // profile ok
            $ilUser->setProfileIncomplete(false);

            // save user data & object_data
            $ilUser->setTitle($ilUser->getFullname());
            $ilUser->setDescription($ilUser->getEmail());

            $ilUser->update();

            // update lucene index
            include_once './Services/Search/classes/Lucene/class.ilLuceneIndexer.php';
            ilLuceneIndexer::updateLuceneIndex(array($GLOBALS['DIC']['ilUser']->getId()));


            // reload page only if skin or style were changed
            // feedback
            if (!empty($this->password_error)) {
                ilUtil::sendFailure($this->password_error, true);
            } elseif (!empty($this->upload_error)) {
                ilUtil::sendFailure($this->upload_error, true);
            } elseif ($reload) {
                // feedback
                ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
                $this->ctrl->redirect($this, "");
            } else {
                ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
            }
        }

        $this->showProfile();
    }

    /**
    * show profile form
    *
    * /OLD IMPLEMENTATION DEPRECATED
    */
    public function showProfile()
    {
        $this->showPersonalData();
    }

    protected function showUserAgreement()
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $tpl = new \ilTemplate('tpl.view_terms_of_service.html', true, true, 'Services/Init');

        $this->tpl->setTitle($this->lng->txt('usr_agreement'));

        $noAgreement = true;
        if (!$this->user->isAnonymous() && (int) $this->user->getId() > 0 && $this->user->getAgreeDate()) {
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
                    'mailto:' . ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress())
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
            $this->ctrl->redirectToUrl($target);
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

        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $withdrawalType = 0;
        if (
            $this->user->getAuthMode() == AUTH_LDAP ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == AUTH_LDAP)
        ) {
            $withdrawalType = 2;
        } elseif ((bool) $this->setting->get('tos_withdrawal_usr_deletion', false)) {
            $withdrawalType = 1;
        }

        $domainEvent = new ilTermsOfServiceEventWithdrawn($this->user);
        $this->eventHandler->raise(
            'Services/TermsOfService',
            'ilTermsOfServiceEventWithdrawn',
            ['event' => $domainEvent]
        );

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();

        $this->ctrl->redirectToUrl('login.php?tos_withdrawal_type=' . $withdrawalType . '&cmd=force_login');
    }

    /**
     * Add location fields to form if activated
     *
     * @param ilPropertyFormGUI $a_form
     * @param ilObjUser $a_user
     */
    public function addLocationToForm(ilPropertyFormGUI $a_form, ilObjUser $a_user)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        // check map activation
        include_once("./Services/Maps/classes/class.ilMapUtil.php");
        if (!ilMapUtil::isActivated()) {
            return;
        }

        // Don't really know if this is still necessary...
        $this->lng->loadLanguageModule("maps");

        // Get user settings
        $latitude = $a_user->getLatitude();
        $longitude = $a_user->getLongitude();
        $zoom = $a_user->getLocationZoom();

        // Get Default settings, when nothing is set
        if ($latitude == 0 && $longitude == 0 && $zoom == 0) {
            $def = ilMapUtil::getDefaultSettings();
            $latitude = $def["latitude"];
            $longitude = $def["longitude"];
            $zoom = $def["zoom"];
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
    public function setTabs()
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


    public function __showOtherInformations()
    {
        $d_set = new ilSetting("delicous");
        if ($this->userSettingVisible("matriculation") or count($this->user_defined_fields->getVisibleDefinitions())
            or $d_set->get("user_profile") == "1") {
            return true;
        }
        return false;
    }

    public function __showUserDefinedFields()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $user_defined_data = $ilUser->getUserDefinedData();
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            if ($definition['field_type'] == UDF_TYPE_TEXT) {
                $this->tpl->setCurrentBlock("field_text");
                $this->tpl->setVariable("FIELD_VALUE", ilUtil::prepareFormOutput($user_defined_data[$field_id]));
                if (!$definition['changeable']) {
                    $this->tpl->setVariable("DISABLED_FIELD", 'disabled=\"disabled\"');
                    $this->tpl->setVariable("FIELD_NAME", 'udf[' . $definition['field_id'] . ']');
                } else {
                    $this->tpl->setVariable("FIELD_NAME", 'udf[' . $definition['field_id'] . ']');
                }
                $this->tpl->parseCurrentBlock();
            } else {
                if ($definition['changeable']) {
                    $name = 'udf[' . $definition['field_id'] . ']';
                    $disabled = false;
                } else {
                    $name = '';
                    $disabled = true;
                }
                $this->tpl->setCurrentBlock("field_select");
                $this->tpl->setVariable("SELECT_BOX", ilUtil::formSelect(
                    $user_defined_data[$field_id],
                    $name,
                    $this->user_defined_fields->fieldValuesToSelectArray(
                        $definition['field_values']
                    ),
                    false,
                    true,
                    0,
                    '',
                    '',
                    $disabled
                ));
                $this->tpl->parseCurrentBlock();
            }
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

    public function __checkUserDefinedRequiredFields()
    {
        foreach ($this->user_defined_fields->getVisibleDefinitions() as $definition) {
            $field_id = $definition['field_id'];
            if ($definition['required'] and !strlen($_POST['udf'][$field_id])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set header
     */
    public function setHeader()
    {
        $this->tpl->setTitle($this->lng->txt('personal_profile'));
    }

    //
    //
    //	PERSONAL DATA FORM
    //
    //

    /**
    * Personal data form.
    */
    public function showPersonalData($a_no_init = false, $a_migration_started = false)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilTabs = $DIC['ilTabs'];
        $prompt_service = new ilUserProfilePromptService();

        $ilTabs->activateTab("personal_data");
        $ctrl = $DIC->ctrl();

        $it = "";
        if ($_GET["prompted"] == 1) {
            $it = $prompt_service->data()->getSettings()->getPromptText($ilUser->getLanguage());
        }
        if ($it === "") {
            $it = $prompt_service->data()->getSettings()->getInfoText($ilUser->getLanguage());
        }
        if (trim($it) !== "") {
            $pub_prof = in_array($ilUser->prefs["public_profile"], array("y", "n", "g"))
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
                ilUtil::sendInfo($lng->txt("profile_incomplete"));
            }
        }

        $modal = '';
        if ($this->email_change_confirmation_modal !== null) {
            $modal = $this->ui_renderer->render($this->email_change_confirmation_modal);
        }

        $this->tpl->setContent($it . $this->form->getHTML() . $modal);
        $this->tpl->printToStdout();
    }

    /**
    * Init personal form
    */
    public function initPersonalDataForm()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setId(self::PERSONAL_DATA_FORM_ID);

        // user defined fields
        $user_defined_data = $ilUser->getUserDefinedData();


        foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
            $value = $user_defined_data["f_" . $field_id];

            include_once './Services/User/classes/class.ilCustomUserFieldsHelper.php';
            $fprop = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition(
                $definition,
                $definition['changeable'],
                $value
            );
            if ($fprop instanceof ilFormPropertyGUI) {
                $this->input['udf_' . $definition['field_id']] = $fprop;
            }
        }

        // standard fields
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipField("password");
        $up->skipGroup("settings");
        $up->skipGroup("preferences");

        $up->setAjaxCallback(
            $this->ctrl->getLinkTargetByClass('ilPublicUserProfileGUI', 'doProfileAutoComplete', '', true)
        );

        // standard fields
        $up->addStandardFieldsToForm($this->form, $ilUser, $this->input);

        $this->addLocationToForm($this->form, $ilUser);

        $this->form->addCommandButton("savePersonalData", $lng->txt("user_save_continue"));
    }

    /**
    * Save personal data form
    *
    */
    public function savePersonalData()
    {
        $this->initPersonalDataForm();

        if (!$this->form->checkInput()
            || !$this->emailCompletionForced()
                && $this->emailChanged()
                && $this->addEmailChangeModal()
            || $this->loginChanged() && !$this->updateLoginOrSetErrorMessages()) {
            $this->form->setValuesByPost();
            $this->showPersonalData(true);
            return;
        }
        $this->savePersonalDataForm();

        $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PROFILE_DATA);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, "showPublicProfile");
    }

    private function emailChanged() : bool
    {
        $email_input = $this->form->getItemByPostVar('usr_email');
        if ($email_input !== null
            && $email_input !== false
            && !$email_input->getDisabled()
            && $this->form->getInput('usr_email') !== $this->user->getEmail()) {
            return true;
        }

        return false;
    }

    private function emailCompletionForced() : bool
    {
        $current_email = $this->user->getEmail();
        if (
            $this->user->getProfileIncomplete()
            && $this->setting->get('require_email') === '1'
            && ($current_email === null || $current_email === '')
        ) {
            return true;
        }

        return false;
    }

    private function addEmailChangeModal() : bool
    {
        $form_id = 'form_' . self::PERSONAL_DATA_FORM_ID;
        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $this->lng->txt('confirm_logout_for_email_change'),
            '#'
        )->withActionButtonLabel('change');
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

    private function loginChanged() : bool
    {
        $login = $this->form->getInput('username');
        if ((int) $this->setting->get('allow_change_loginname')
           && $login !== $this->user->getLogin()) {
            return true;
        }

        return false;
    }

    private function updateLoginOrSetErrorMessages() : bool
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

    public function goToEmailConfirmation() : void
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

    private function savePersonalDataForm() : void
    {
        // if form field name differs from setter
        $map = [
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
        ];
        $up = new ilUserProfile();
        foreach (array_keys($up->getStandardFields()) as $f) {
            // if item is part of form, it is currently valid (if not disabled)
            $item = $this->form->getItemByPostVar("usr_" . $f);
            if (!$item || $item->getDisabled()) {
                continue;
            }

            $value = $this->form->getInput("usr_" . $f);
            switch ($f) {
                case 'email':
                    if ($this->emailCompletionForced()) {
                        $this->user->setEmail($value);
                    }

                    break;
                case "birthday":
                    $value = $item->getDate();
                    $this->user->setBirthday($value
                        ? $value->get(IL_CAL_DATE)
                        : "");
                    break;
                case "second_email":
                    $this->user->setSecondEmail($value);
                    break;
                default:
                    $m = $map[$f] ?? ucfirst($f);
                    $this->user->{"set" . $m}($value);
                    break;
            }
        }
        $this->user->setFullname();

        // check map activation
        if (ilMapUtil::isActivated()) {
            // #17619 - proper escaping
            $location = $this->form->getInput("location");
            $lat = ilUtil::stripSlashes($location["latitude"]);
            $long = ilUtil::stripSlashes($location["longitude"]);
            $zoom = ilUtil::stripSlashes($location["zoom"]);
            $this->user->setLatitude(is_numeric($lat) ? $lat : null);
            $this->user->setLongitude(is_numeric($long) ? $long : null);
            $this->user->setLocationZoom(is_numeric($zoom) ? $zoom : null);
        }

        // Set user defined data
        $defs = $this->user_defined_fields->getVisibleDefinitions();
        $udf = [];
        foreach ($defs as $definition) {
            $f = "udf_" . $definition['field_id'];
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

    public function changeEmail() : void
    {
        $token = $_GET['token'];
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

    //
    //
    //	PUBLIC PROFILE FORM
    //
    //

    /**
    * Public profile form
    */
    public function showPublicProfile($a_no_init = false)
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
     *
     * @return int
     */
    protected function getProfilePortfolio()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        if ($ilSetting->get('user_portfolios')) {
            include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
            return ilObjPortfolio::getDefaultPortfolio($ilUser->getId());
        }
    }

    /**
    * Init public profile form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initPublicProfileForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
            $info->setHTML($prtf);
            $this->form->addItem($info);
            $this->showPublicProfileFields($this->form, $ilUser->prefs);
        }

        if (is_object($op2)) {
            $this->showPublicProfileFields($this->form, $ilUser->prefs, $op2, false, "-1");
        }
        if (is_object($op3)) {
            $this->showPublicProfileFields($this->form, $ilUser->prefs, $op3, false, "-2");
        }
        $this->form->setForceTopButtons(true);
        $this->form->addCommandButton("savePublicProfile", $lng->txt("user_save_continue"));
    }

    /**
     * Add fields to form
     *
     * @param ilPropertyformGUI $form
     * @param array $prefs
     * @param object $parent
     * @param bool $a_anonymized
     */
    public function showPublicProfileFields(
        ilPropertyformGUI $form,
        array $prefs,
        $parent = null,
        $anonymized = false,
        $key_suffix = ""
    ) {
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
        include_once("./Services/Maps/classes/class.ilMapUtil.php");
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
                if ($prefs["public_" . $key] == "y") {
                    $cb->setChecked(true);
                }
                //$cb->setInfo($value);
                $cb->setOptionTitle($value);

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
            $cb->setOptionTitle($user_defined_data["f_" . $definition["field_id"]]);
            if ($prefs["public_udf_" . $definition["field_id"]] == "y") {
                $cb->setChecked(true);
            }

            if (!$parent) {
                $form->addItem($cb);
            } else {
                $parent->addSubItem($cb);
            }
        }

        if (!$anonymized) {
            include_once "Services/Badge/classes/class.ilBadgeHandler.php";
            $handler = ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badge_options = array();

                include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
                include_once "Services/Badge/classes/class.ilBadge.php";
                foreach (ilBadgeAssignment::getInstancesByUserId($ilUser->getId()) as $ass) {
                    // only active
                    if ($ass->getPosition()) {
                        $badge = new ilBadge($ass->getBadgeId());
                        $badge_options[] = $badge->getTitle();
                    }
                }

                if (sizeof($badge_options) > 1) {
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

    /**
    * Save public profile form
    *
    */
    public function savePublicProfile()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        $this->initPublicProfileForm();
        if ($this->form->checkInput()) {
            // with active portfolio no options are presented
            if (isset($_POST["public_profile"])) {
                $ilUser->setPref("public_profile", $_POST["public_profile"]);
            }

            // if check on Institute
            $val_array = array("title", "birthday", "gender", "org_units", "institution", "department", "upload",
                "street", "zipcode", "city", "country", "sel_country", "phone_office", "phone_home", "phone_mobile",
                "fax", "email", "second_email", "hobby", "matriculation", "location",
                "interests_general", "interests_help_offered", "interests_help_looking");

            // set public profile preferences
            $checked_values = $this->getCheckedValues();
            foreach ($val_array as $key => $value) {
                if (($checked_values["chk_" . $value])) {
                    $ilUser->setPref("public_" . $value, "y");
                } else {
                    $ilUser->setPref("public_" . $value, "n");
                }
            }

            // additional defined user data fields
            foreach ($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition) {
                if (($checked_values["chk_udf_" . $definition["field_id"]])) {
                    $ilUser->setPref("public_udf_" . $definition["field_id"], "y");
                } else {
                    $ilUser->setPref("public_udf_" . $definition["field_id"], "n");
                }
            }

            $ilUser->update();

            switch ($_POST["public_profile"]) {
                case "y":
                    $key_suffix = "-1";
                    break;
                case "g":
                    $key_suffix = "-2";
                    break;
            }

            include_once "Services/Badge/classes/class.ilBadgeHandler.php";
            $handler = ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badgePositions = [];
                if (isset($_POST["bpos" . $key_suffix]) && is_array($_POST["bpos" . $key_suffix])) {
                    $badgePositions = $_POST["bpos" . $key_suffix];
                }

                if (count($badgePositions) > 0) {
                    include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
                    ilBadgeAssignment::updatePositions($ilUser->getId(), $badgePositions);
                }
            }

            // update lucene index
            include_once './Services/Search/classes/Lucene/class.ilLuceneIndexer.php';
            ilLuceneIndexer::updateLuceneIndex(array((int) $GLOBALS['DIC']['ilUser']->getId()));

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

            $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_PUBLISH_OPTIONS);

            if (ilSession::get('orig_request_target')) {
                $target = ilSession::get('orig_request_target');
                ilSession::set('orig_request_target', '');
                ilUtil::redirect($target);
            } elseif ($redirect = $_SESSION['profile_complete_redirect']) {
                unset($_SESSION['profile_complete_redirect']);
                ilUtil::redirect($redirect);
            } else {
                $ilCtrl->redirectByClass("iluserprivacysettingsgui", "");
            }
        }
        $this->form->setValuesByPost();
        $tpl->showPublicProfile(true);
    }

    /**
     * Get checked values
     *
     * @return array
     */
    protected function getCheckedValues()
    {
        $checked_values = [];
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, 4) == "chk_") {
                $k = str_replace("-1", "", $k);
                $k = str_replace("-2", "", $k);
                $checked_values[$k] = $v;
            }
        }
        return $checked_values;
    }


    /**
     * Show export/import
     *
     * @param
     * @return
     */
    public function showExportImport()
    {
        global $DIC;

        $ilToolbar = $DIC['ilToolbar'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilUser = $DIC['ilUser'];

        $ilTabs->activateTab("export");
        $this->setHeader();

        include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
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


    /**
     * Export personal data
     */
    public function exportPersonalData()
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
     *
     * @param
     * @return
     */
    public function downloadPersonalData()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $ilUser->sendPersonalDataFile();
    }

    /**
     * Import personal data selection
     *
     * @param
     * @return
     */
    public function importPersonalDataSelection()
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

    /**
     * Init personal data import form
     *
     * @param
     * @return
     */
    public function initPersonalDataImportForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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


    /**
     * Import personal data
     *
     * @param
     * @return
     */
    public function importPersonalData()
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
                (int) $_POST["profile_data"],
                (int) $_POST["settings"],
                (int) $_POST["notes"],
                (int) $_POST["calendar"]
            );
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $ilTabs->activateTab("export");
            $this->setHeader();
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
            $tpl->printToStdout();
        }
    }

    /**
     * Show checklist
     *
     * @param int
     */
    protected function showChecklist($active_step)
    {
        $main_tpl = $this->tpl;

        $main_tpl->setRightContent($this->checklist->render($active_step));
    }
}
