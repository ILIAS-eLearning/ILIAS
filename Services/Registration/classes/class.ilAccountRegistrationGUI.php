<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesRegistration Services/Registration
 */

/**
* Class ilAccountRegistrationGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilAccountRegistrationGUI:
*
* @ingroup ServicesRegistration
*/


/**
 *
 */
class ilAccountRegistrationGUI
{
    protected $registration_settings; // [object]
    protected $code_enabled; // [bool]
    protected $code_was_used; // [bool]
    /** @var \ilObjUser|null */
    protected $userObj;

    /** @var \ilTermsOfServiceDocumentEvaluation */
    protected $termsOfServiceEvaluation;

    /**
     * @var ilRecommendedContentManager
     */
    protected $recommended_content_manager;

    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC['tpl'];
        $lng = $DIC->language();

        $this->tpl = &$tpl;

        $this->ctrl = &$ilCtrl;
        $this->ctrl->saveParameter($this, 'lang');

        $this->lng = &$lng;
        $this->lng->loadLanguageModule('registration');

        $this->registration_settings = new ilRegistrationSettings();
        
        $this->code_enabled = ($this->registration_settings->registrationCodeRequired() ||
            $this->registration_settings->getAllowCodes());

        $this->termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        $this->recommended_content_manager = new ilRecommendedContentManager();
    }

    public function executeCommand()
    {
        global $DIC;

        if ($this->registration_settings->getRegistrationType() == IL_REG_DISABLED) {
            $ilErr = $DIC['ilErr'];
            $ilErr->raiseError($this->lng->txt('reg_disabled'), $ilErr->FATAL);
        }

        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'saveForm':
                $tpl = $this->$cmd();
                break;
            default:
                $tpl = $this->displayForm();
        }

        $gtpl = $this->tpl;
        $gtpl->setPermanentLink('usr', null, 'registration');
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    /**
     *
     */
    public function displayForm()
    {

        $tpl = ilStartUpGUI::initStartUpTemplate(array('tpl.usr_registration.html', 'Services/Registration'), true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('registration'));

        if (!$this->form) {
            $this->__initForm();
        }
        $tpl->setVariable('FORM', $this->form->getHTML());
        return $tpl;
    }

    protected function __initForm()
    {
        global $DIC;

        $ilUser = $DIC->user();

        $ilUser->setLanguage($this->lng->getLangKey());
        $ilUser->setId(ANONYMOUS_USER_ID);

        // needed for multi-text-fields (interests)
        iljQueryUtil::initjQuery();

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        
        
        // code handling
        
        if ($this->code_enabled) {
            $field = new ilFormSectionHeaderGUI();
            $field->setTitle($this->lng->txt('registration_codes_type_reg'));
            $this->form->addItem($field);
            $code = new ilTextInputGUI($this->lng->txt("registration_code"), "usr_registration_code");
            $code->setSize(40);
            $code->setMaxLength(ilRegistrationCode::CODE_LENGTH);
            if ((bool) $this->registration_settings->registrationCodeRequired()) {
                $code->setRequired(true);
                $code->setInfo($this->lng->txt("registration_code_required_info"));
            } else {
                $code->setInfo($this->lng->txt("registration_code_optional_info"));
            }
            $this->form->addItem($code);
        }
        

        // user defined fields
        $user_defined_data = $ilUser->getUserDefinedData();

        $user_defined_fields = ilUserDefinedFields::_getInstance();
        $custom_fields = array();
        
        foreach ($user_defined_fields->getRegistrationDefinitions() as $field_id => $definition) {
            $fprop = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition(
                $definition,
                true,
                $user_defined_data['f_' . $field_id]
            );
            if ($fprop instanceof ilFormPropertyGUI) {
                $custom_fields['udf_' . $definition['field_id']] = $fprop;
            }
        }
        
        // standard fields
        $up = new ilUserProfile();
        $up->setMode(ilUserProfile::MODE_REGISTRATION);
        $up->skipGroup("preferences");
        
        $up->setAjaxCallback(
            $this->ctrl->getLinkTarget($this, 'doProfileAutoComplete', '', true)
        );

        $this->lng->loadLanguageModule("user");

        // add fields to form
        $up->addStandardFieldsToForm($this->form, null, $custom_fields);
        unset($custom_fields);
        
        
        // set language selection to current display language
        $flang = $this->form->getItemByPostVar("usr_language");
        if ($flang) {
            $flang->setValue($this->lng->getLangKey());
        }
        
        // add information to role selection (if not hidden)
        if ($this->code_enabled) {
            $role = $this->form->getItemByPostVar("usr_roles");
            if ($role && $role->getType() == "select") {
                $role->setInfo($this->lng->txt("registration_code_role_info"));
            }
        }
        
        // #11407
        $domains = array();
        foreach ($this->registration_settings->getAllowedDomains() as $item) {
            if (trim($item)) {
                $domains[] = $item;
            }
        }
        if (sizeof($domains)) {
            $mail_obj = $this->form->getItemByPostVar('usr_email');
            $mail_obj->setInfo(sprintf(
                $this->lng->txt("reg_email_domains"),
                implode(", ", $domains)
            ) . "<br />" .
                ($this->code_enabled ? $this->lng->txt("reg_email_domains_code") : ""));
        }
        
        // #14272
        if ($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION) {
            $mail_obj = $this->form->getItemByPostVar('usr_email');
            if ($mail_obj) { // #16087
                $mail_obj->setRequired(true);
            }
        }

        if (\ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument()) {
            $document = $this->termsOfServiceEvaluation->document();

            $field = new ilFormSectionHeaderGUI();
            $field->setTitle($this->lng->txt('usr_agreement'));
            $this->form->addItem($field);

            $field = new ilCustomInputGUI();
            $field->setHTML('<div id="agreement">' . $document->content() . '</div>');
            $this->form->addItem($field);

            $field = new ilCheckboxInputGUI($this->lng->txt('accept_usr_agreement'), 'accept_terms_of_service');
            $field->setRequired(true);
            $field->setValue(1);
            $this->form->addItem($field);
        }


        if (ilCaptchaUtil::isActiveForRegistration()) {
            $captcha = new ilCaptchaInputGUI($this->lng->txt("captcha_code"), 'captcha_code');
            $captcha->setRequired(true);
            $this->form->addItem($captcha);
        }

        $this->form->addCommandButton("saveForm", $this->lng->txt("register"));
    }
    
    public function saveForm()
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $rbacreview = $DIC->rbac()->review();

        $this->__initForm();
        $form_valid = $this->form->checkInput();
        
        // custom validation
        $valid_code = $valid_role = false;
                
        // code
        if ($this->code_enabled) {
            $code = $this->form->getInput('usr_registration_code');
            // could be optional
            if (
                $this->registration_settings->registrationCodeRequired() ||
                strlen($code)
            ) {
                // code validation
                if (!ilRegistrationCode::isValidRegistrationCode($code)) {
                    $code_obj = $this->form->getItemByPostVar('usr_registration_code');
                    $code_obj->setAlert($this->lng->txt('registration_code_not_valid'));
                    $form_valid = false;
                } else {
                    $valid_code = true;
                    
                    // get role from code, check if (still) valid
                    $role_id = (int) ilRegistrationCode::getCodeRole($code);
                    if ($role_id && $rbacreview->isGlobalRole($role_id)) {
                        $valid_role = $role_id;
                    }
                }
            }
        }
        
        // valid codes override email domain check
        if (!$valid_code) {
            // validate email against restricted domains
            $email = $this->form->getInput("usr_email");
            if ($email) {
                // #10366
                $domains = array();
                foreach ($this->registration_settings->getAllowedDomains() as $item) {
                    if (trim($item)) {
                        $domains[] = $item;
                    }
                }
                if (sizeof($domains)) {
                    $mail_valid = false;
                    foreach ($domains as $domain) {
                        $domain = str_replace("*", "~~~", $domain);
                        $domain = preg_quote($domain);
                        $domain = str_replace("~~~", ".+", $domain);
                        if (preg_match("/^" . $domain . "$/", $email, $hit)) {
                            $mail_valid = true;
                            break;
                        }
                    }
                    if (!$mail_valid) {
                        $mail_obj = $this->form->getItemByPostVar('usr_email');
                        $mail_obj->setAlert(sprintf(
                            $this->lng->txt("reg_email_domains"),
                            implode(", ", $domains)
                        ));
                        $form_valid = false;
                    }
                }
            }
        }

        $error_lng_var = '';
        if (
            !$this->registration_settings->passwordGenerationEnabled() &&
            !ilUtil::isPasswordValidForUserContext($this->form->getInput('usr_password'), $this->form->getInput('username'), $error_lng_var)
        ) {
            $passwd_obj = $this->form->getItemByPostVar('usr_password');
            $passwd_obj->setAlert($this->lng->txt($error_lng_var));
            $form_valid = false;
        }

        $showGlobalTermsOfServieFailure = false;
        if (\ilTermsOfServiceHelper::isEnabled() && !$this->form->getInput('accept_terms_of_service')) {
            $agr_obj = $this->form->getItemByPostVar('accept_terms_of_service');
            if ($agr_obj) {
                $agr_obj->setAlert($this->lng->txt('force_accept_usr_agreement'));
                $form_valid = false;
            } else {
                $showGlobalTermsOfServieFailure = true;
            }
        }

        // no need if role is attached to code
        if (!$valid_role) {
            // manual selection
            if ($this->registration_settings->roleSelectionEnabled()) {
                $selected_role = $this->form->getInput("usr_roles");
                if ($selected_role && ilObjRole::_lookupAllowRegister($selected_role)) {
                    $valid_role = (int) $selected_role;
                }
            }
            // assign by email
            else {
                $registration_role_assignments = new ilRegistrationRoleAssignments();
                $valid_role = (int) $registration_role_assignments->getRoleByEmail($this->form->getInput("usr_email"));
            }
        }

        // no valid role could be determined
        if (!$valid_role) {
            ilUtil::sendInfo($this->lng->txt("registration_no_valid_role"));
            $form_valid = false;
        }

        // validate username
        $login_obj = $this->form->getItemByPostVar('username');
        $login = $this->form->getInput("username");
        $captcha = $this->form->getItemByPostVar("captcha_code");
        if (!ilUtil::isLogin($login)) {
            $login_obj->setAlert($this->lng->txt("login_invalid"));
            $form_valid = false;
        } elseif (ilObjUser::_loginExists($login)) {
            if(!empty($captcha) && empty($captcha->getAlert()) || empty($captcha)) {
                $login_obj->setAlert($this->lng->txt("login_exists"));
            }
            $form_valid = false;
        } elseif ((int) $ilSetting->get('allow_change_loginname') &&
            (int) $ilSetting->get('reuse_of_loginnames') == 0 &&
            ilObjUser::_doesLoginnameExistInHistory($login)) {
            if(!empty($captcha) && empty($captcha->getAlert()) || empty($captcha)) {
                $login_obj->setAlert($this->lng->txt("login_exists"));
            }
            $form_valid = false;
        }

        if (!$form_valid) {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        } elseif ($showGlobalTermsOfServieFailure) {
            $this->lng->loadLanguageModule('tos');
            \ilUtil::sendFailure(sprintf(
                $this->lng->txt('tos_account_reg_not_possible'),
                'mailto:' . ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress())
            ));
        } else {
            $password = $this->__createUser($valid_role);
            $this->__distributeMails($password);
            return $this->login();
        }
        $this->form->setValuesByPost();
        return $this->displayForm();
    }

    protected function __createUser($a_role)
    {
        /**
         * @var $ilSetting ilSetting
         * @var $rbacadmin ilRbacAdmin
         */
        global $DIC;

        $ilSetting = $DIC->settings();
        $rbacadmin = $DIC->rbac()->admin();
        
        
        // something went wrong with the form validation
        if (!$a_role) {
            global $DIC;

            $ilias = $DIC['ilias'];
            $ilias->raiseError("Invalid role selection in registration" .
                ", IP: " . $_SERVER["REMOTE_ADDR"], $ilias->error_obj->FATAL);
        }
        

        $this->userObj = new ilObjUser();

        $up = new ilUserProfile();
        $up->setMode(ilUserProfile::MODE_REGISTRATION);

        $map = array();
        $up->skipGroup("preferences");
        $up->skipGroup("settings");
        $up->skipField("password");
        $up->skipField("birthday");
        $up->skipField("upload");
        foreach ($up->getStandardFields() as $k => $v) {
            if ($v["method"]) {
                $method = "set" . substr($v["method"], 3);
                if (method_exists($this->userObj, $method)) {
                    if ($k != "username") {
                        $k = "usr_" . $k;
                    }
                    $field_obj = $this->form->getItemByPostVar($k);
                    if ($field_obj) {
                        $this->userObj->$method($this->form->getInput($k));
                    }
                }
            }
        }

        $this->userObj->setFullName();

        $birthday_obj = $this->form->getItemByPostVar("usr_birthday");
        if ($birthday_obj) {
            $birthday = $this->form->getInput("usr_birthday");
            $this->userObj->setBirthday($birthday);
        }

        $this->userObj->setTitle($this->userObj->getFullname());
        $this->userObj->setDescription($this->userObj->getEmail());

        if ($this->registration_settings->passwordGenerationEnabled()) {
            $password = ilUtil::generatePasswords(1);
            $password = $password[0];
        } else {
            $password = $this->form->getInput("usr_password");
        }
        $this->userObj->setPasswd($password);
        
        
        // Set user defined data
        $user_defined_fields = &ilUserDefinedFields::_getInstance();
        $defs = $user_defined_fields->getRegistrationDefinitions();
        $udf = array();
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, 4) == "udf_") {
                $f = substr($k, 4);
                $udf[$f] = $v;
            }
        }
        $this->userObj->setUserDefinedData($udf);

        $this->userObj->setTimeLimitOwner(7);
        
        
        $access_limit = null;

        $this->code_was_used = false;
        if ($this->code_enabled) {
            $code_local_roles = $code_has_access_limit = null;
            
            // #10853 - could be optional
            $code = $this->form->getInput('usr_registration_code');
            if ($code) {
                
                // set code to used
                ilRegistrationCode::useCode($code);
                $this->code_was_used = true;
                
                // handle code attached local role(s) and access limitation
                $code_data = ilRegistrationCode::getCodeData($code);
                if ($code_data["role_local"]) {
                    // need user id before we can assign role(s)
                    $code_local_roles = explode(";", $code_data["role_local"]);
                }
                if ($code_data["alimit"]) {
                    // see below
                    $code_has_access_limit = true;
                    
                    switch ($code_data["alimit"]) {
                        case "absolute":
                            $abs = date_parse($code_data["alimitdt"]);
                            $access_limit = mktime(23, 59, 59, $abs['month'], $abs['day'], $abs['year']);
                            break;
                        
                        case "relative":
                            $rel = unserialize($code_data["alimitdt"]);
                            $access_limit = $rel["d"] * 86400 + $rel["m"] * 2592000 +
                                $rel["y"] * 31536000 + time();
                            break;
                    }
                }
            }
        }
        
        // code access limitation will override any other access limitation setting
        if (!($this->code_was_used && $code_has_access_limit) &&
            $this->registration_settings->getAccessLimitation()) {
            $access_limitations_obj = new ilRegistrationRoleAccessLimitations();
            switch ($access_limitations_obj->getMode($a_role)) {
                case 'absolute':
                    $access_limit = $access_limitations_obj->getAbsolute($a_role);
                    break;
                
                case 'relative':
                    $rel_d = (int) $access_limitations_obj->getRelative($a_role, 'd');
                    $rel_m = (int) $access_limitations_obj->getRelative($a_role, 'm');
                    $rel_y = (int) $access_limitations_obj->getRelative($a_role, 'y');
                    $access_limit = $rel_d * 86400 + $rel_m * 2592000 + $rel_y * 31536000 + time();
                    break;
            }
        }
        
        if ($access_limit) {
            $this->userObj->setTimeLimitUnlimited(0);
            $this->userObj->setTimeLimitUntil($access_limit);
        } else {
            $this->userObj->setTimeLimitUnlimited(1);
            $this->userObj->setTimeLimitUntil(time());
        }

        $this->userObj->setTimeLimitFrom(time());

        ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_REGISTRATION);

        $this->userObj->create();

        
        if ($this->registration_settings->getRegistrationType() == IL_REG_DIRECT ||
            $this->registration_settings->getRegistrationType() == IL_REG_CODES ||
            $this->code_was_used) {
            $this->userObj->setActive(1, 0);
        } elseif ($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION) {
            $this->userObj->setActive(0, 0);
        } else {
            $this->userObj->setActive(0, 0);
        }

        $this->userObj->updateOwner();

        // set a timestamp for last_password_change
        // this ts is needed by ilSecuritySettings
        $this->userObj->setLastPasswordChangeTS(time());
        
        $this->userObj->setIsSelfRegistered(true);

        //insert user data in table user_data
        $this->userObj->saveAsNew();

        // setup user preferences
        $this->userObj->setLanguage($this->form->getInput('usr_language'));

        $handleDocument = \ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument();
        if ($handleDocument) {
            $helper = new \ilTermsOfServiceHelper();

            $helper->trackAcceptance($this->userObj, $this->termsOfServiceEvaluation->document());
        }

        $hits_per_page = $ilSetting->get("hits_per_page");
        if ($hits_per_page < 10) {
            $hits_per_page = 10;
        }
        $this->userObj->setPref("hits_per_page", $hits_per_page);
        if (strlen($_GET['target']) > 0) {
            $this->userObj->setPref('reg_target', ilUtil::stripSlashes($_GET['target']));
        }
        /*$show_online = $ilSetting->get("show_users_online");
        if ($show_online == "")
        {
            $show_online = "y";
        }
        $this->userObj->setPref("show_users_online", $show_online);*/
        $this->userObj->setPref('bs_allow_to_contact_me', $ilSetting->get('bs_allow_to_contact_me', 'n'));
        $this->userObj->setPref('chat_osc_accept_msg', $ilSetting->get('chat_osc_accept_msg', 'n'));
        $this->userObj->writePrefs();

        
        $rbacadmin->assignUser((int) $a_role, $this->userObj->getId());
        
        // local roles from code
        if ($this->code_was_used && is_array($code_local_roles)) {
            foreach (array_unique($code_local_roles) as $local_role_obj_id) {
                // is given role (still) valid?
                if (ilObject::_lookupType($local_role_obj_id) == "role") {
                    $rbacadmin->assignUser($local_role_obj_id, $this->userObj->getId());

                    // patch to remove for 45 due to mantis 21953
                    $role_obj = $GLOBALS['DIC']['rbacreview']->getObjectOfRole($local_role_obj_id);
                    switch (ilObject::_lookupType($role_obj)) {
                        case 'crs':
                        case 'grp':
                            $role_refs = ilObject::_getAllReferences($role_obj);
                            $role_ref = end($role_refs);
                            // deactivated for now, see discussion at
                            // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
                            // $this->recommended_content_manager->addObjectRecommendation($this->userObj->getId(), $role_ref);
                            break;
                    }
                }
            }
        }

        return $password;
    }

    protected function __distributeMails($password)
    {

        // Always send mail to approvers
        if ($this->registration_settings->getRegistrationType() == IL_REG_APPROVE && !$this->code_was_used) {
            $mail = new ilRegistrationMailNotification();
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_CONFIRMATION);
            $mail->setRecipients($this->registration_settings->getApproveRecipients());
            $mail->setAdditionalInformation(array('usr' => $this->userObj));
            $mail->send();
        } else {
            $mail = new ilRegistrationMailNotification();
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_APPROVERS);
            $mail->setRecipients($this->registration_settings->getApproveRecipients());
            $mail->setAdditionalInformation(array('usr' => $this->userObj));
            $mail->send();
        }

        // Send mail to new user
        // Registration with confirmation link ist enabled
        if ($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION && !$this->code_was_used) {

            $mail = new ilRegistrationMimeMailNotification();
            $mail->setType(ilRegistrationMimeMailNotification::TYPE_NOTIFICATION_ACTIVATION);
            $mail->setRecipients(array($this->userObj));
            $mail->setAdditionalInformation(
                array(
                     'usr' => $this->userObj,
                     'hash_lifetime' => $this->registration_settings->getRegistrationHashLifetime()
                )
            );
            $mail->send();
        } else {
            $accountMail = new ilAccountRegistrationMail(
                $this->registration_settings,
                $this->lng,
                ilLoggerFactory::getLogger('user')
            );
            $accountMail->withDirectRegistrationMode()->send($this->userObj, $password, $this->code_was_used);
        }
    }

    public function login()
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $tpl = ilStartUpGUI::initStartUpTemplate(array('tpl.usr_registered.html', 'Services/Registration'), false);
        $this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('registration'));

        $tpl->setVariable("TXT_WELCOME", $this->lng->txt("welcome") . ", " . $this->userObj->getTitle() . "!");
        if (
            (
                $this->registration_settings->getRegistrationType() == IL_REG_DIRECT ||
                $this->registration_settings->getRegistrationType() == IL_REG_CODES ||
                $this->code_was_used
            ) &&
            !$this->registration_settings->passwordGenerationEnabled()
        ) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_registered'));

            $login_link = $renderer->render($f->link()->standard($this->lng->txt('login_to_ilias'), './login.php?cmd=force_login&lang=' . $this->userObj->getLanguage()));
            $tpl->setVariable('LOGIN_LINK', $login_link);
        } elseif ($this->registration_settings->getRegistrationType() == IL_REG_APPROVE) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_submitted'));
        } elseif ($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('reg_confirmation_link_successful'));
        } else {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_registered_passw_gen'));
        }
        return $tpl;
    }

    /**
     * Do Login
     * @todo refactor this method should be renamed, but i don't wanted to make changed in
     * tpl.usr_registered.html in stable release.
     */
    protected function showLogin()
    {
        global $DIC;
        /**
         * @var ilAuthSession
         */
        $auth_session = $DIC['ilAuthSession'];
        $auth_session->setAuthenticated(
            true,
            $DIC->user()->getId()
        );
        ilInitialisation::initUserAccount();
        return ilInitialisation::redirectToStartingPage();
    }

    protected function doProfileAutoComplete()
    {
        $field_id = (string) $_REQUEST["f"];
        $term = (string) $_REQUEST["term"];

        $result = ilPublicUserProfileGUI::getAutocompleteResult($field_id, $term);
        if (sizeof($result)) {
            echo ilJsonUtil::encode($result);
        }
        
        exit();
    }
}
