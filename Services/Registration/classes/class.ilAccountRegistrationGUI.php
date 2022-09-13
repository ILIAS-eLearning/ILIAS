<?php

declare(strict_types=1);

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

/**
 * Class ilAccountRegistrationGUI
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilAccountRegistrationGUI:
 */
class ilAccountRegistrationGUI
{
    protected ilRegistrationSettings $registration_settings;
    protected bool $code_enabled = false;
    protected bool $code_was_used;
    protected ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation;
    protected ilRecommendedContentManager $recommended_content_manager;

    protected ?ilPropertyFormGUI $form = null;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;
    protected ?ilObjUser $userObj = null;
    protected ilObjUser $globalUser;
    protected ilSetting $settings;
    protected ilRbacReview $rbacreview;
    protected ilRbacAdmin $rbacadmin;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;

    protected ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Services $http;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->ctrl->saveParameter($this, 'lang');
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('registration');
        $this->error = $DIC['ilErr'];
        $this->settings = $DIC->settings();
        $this->globalUser = $DIC->user();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->registration_settings = new ilRegistrationSettings();
        $this->code_enabled = ($this->registration_settings->registrationCodeRequired() ||
            $this->registration_settings->getAllowCodes());

        $this->termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        $this->recommended_content_manager = new ilRecommendedContentManager();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function executeCommand(): void
    {
        if ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_DISABLED) {
            $this->error->raiseError($this->lng->txt('reg_disabled'), $this->error->FATAL);
        }

        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'saveForm':
                $tpl = $this->$cmd();
                break;
            default:
                $tpl = $this->displayForm();
        }

        $this->tpl->setPermanentLink('usr', 0, 'registration');
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    public function displayForm(): ilGlobalTemplateInterface
    {
        $tpl = ilStartUpGUI::initStartUpTemplate(['tpl.usr_registration.html', 'Services/Registration'], true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('registration'));

        if (!$this->form) {
            $this->initForm();
        }
        $tpl->setVariable('FORM', $this->form->getHTML());
        return $tpl;
    }

    protected function initForm(): void
    {
        $this->globalUser->setLanguage($this->lng->getLangKey());
        $this->globalUser->setId(ANONYMOUS_USER_ID);

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
            if ($this->registration_settings->registrationCodeRequired()) {
                $code->setRequired(true);
                $code->setInfo($this->lng->txt("registration_code_required_info"));
            } else {
                $code->setInfo($this->lng->txt("registration_code_optional_info"));
            }
            $this->form->addItem($code);
        }

        // user defined fields
        $user_defined_data = $this->globalUser->getUserDefinedData();
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        $custom_fields = [];

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
        //TODO-PHP8-REVIEW please check if there is a need for this static call. It looks like of odd to me, that
        //we need a global static state variable in class that changes the behaviour of all instances.
        $up = new ilUserProfile();
        ilUserProfile::setMode(ilUserProfile::MODE_REGISTRATION);
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
            if ($role && $role->getType() === "select") {
                $role->setInfo($this->lng->txt("registration_code_role_info"));
            }
        }

        // #11407
        $domains = [];
        foreach ($this->registration_settings->getAllowedDomains() as $item) {
            if (trim($item)) {
                $domains[] = $item;
            }
        }
        if (count($domains)) {
            $mail_obj = $this->form->getItemByPostVar('usr_email');
            $mail_obj->setInfo(sprintf(
                $this->lng->txt("reg_email_domains"),
                implode(", ", $domains)
            ) . "<br />" .
                ($this->code_enabled ? $this->lng->txt("reg_email_domains_code") : ""));
        }

        // #14272
        if ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_ACTIVATION) {
            $mail_obj = $this->form->getItemByPostVar('usr_email');
            if ($mail_obj) { // #16087
                $mail_obj->setRequired(true);
            }
        }

        if (ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument()) {
            $document = $this->termsOfServiceEvaluation->document();

            $field = new ilFormSectionHeaderGUI();
            $field->setTitle($this->lng->txt('usr_agreement'));
            $this->form->addItem($field);

            $field = new ilCustomInputGUI();
            $field->setHtml('<div id="agreement">' . $document->content() . '</div>');
            $this->form->addItem($field);

            $field = new ilCheckboxInputGUI($this->lng->txt('accept_usr_agreement'), 'accept_terms_of_service');
            $field->setRequired(true);
            $field->setValue('1');
            $this->form->addItem($field);
        }

        $this->form->addCommandButton("saveForm", $this->lng->txt("register"));
    }

    public function saveForm(): ilGlobalTemplateInterface
    {
        $this->initForm();
        $form_valid = $this->form->checkInput();

        // custom validation
        $valid_code = $valid_role = false;

        // code
        if ($this->code_enabled) {
            $code = $this->form->getInput('usr_registration_code');
            // could be optional
            if (
                $code !== '' ||
                $this->registration_settings->registrationCodeRequired()
            ) {
                // code validation
                if (!ilRegistrationCode::isValidRegistrationCode($code)) {
                    $code_obj = $this->form->getItemByPostVar('usr_registration_code');
                    $code_obj->setAlert($this->lng->txt('registration_code_not_valid'));
                    $form_valid = false;
                } else {
                    $valid_code = true;

                    // get role from code, check if (still) valid
                    $role_id = ilRegistrationCode::getCodeRole($code);
                    if ($role_id && $this->rbacreview->isGlobalRole($role_id)) {
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
                $domains = [];
                foreach ($this->registration_settings->getAllowedDomains() as $item) {
                    if (trim($item)) {
                        $domains[] = $item;
                    }
                }
                if (count($domains)) {
                    $mail_valid = false;
                    foreach ($domains as $domain) {
                        $domain = str_replace("*", "~~~", $domain);
                        $domain = preg_quote($domain, '/');
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
            !ilSecuritySettingsChecker::isPasswordValidForUserContext(
                $this->form->getInput('usr_password'),
                $this->form->getInput('username'),
                $error_lng_var
            )
        ) {
            $passwd_obj = $this->form->getItemByPostVar('usr_password');
            $passwd_obj->setAlert($this->lng->txt($error_lng_var));
            $form_valid = false;
        }

        $showGlobalTermsOfServieFailure = false;
        if (ilTermsOfServiceHelper::isEnabled() && !$this->form->getInput('accept_terms_of_service')) {
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
                if ($selected_role && ilObjRole::_lookupAllowRegister((int) $selected_role)) {
                    $valid_role = (int) $selected_role;
                }
            } // assign by email
            else {
                $registration_role_assignments = new ilRegistrationRoleAssignments();
                $valid_role = $registration_role_assignments->getRoleByEmail($this->form->getInput("usr_email"));
            }
        }

        // no valid role could be determined
        if (!$valid_role) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("registration_no_valid_role"));
            $form_valid = false;
        }

        // validate username
        $login_obj = $this->form->getItemByPostVar('username');
        $login = $this->form->getInput("username");
        if (!ilUtil::isLogin($login)) {
            $login_obj->setAlert($this->lng->txt("login_invalid"));
            $form_valid = false;
        } elseif (ilObjUser::_loginExists($login)) {
            $login_obj->setAlert($this->lng->txt("login_exists"));
            $form_valid = false;
        } elseif ((int) $this->settings->get('allow_change_loginname') &&
            (int) $this->settings->get('reuse_of_loginnames') === 0 &&
            ilObjUser::_doesLoginnameExistInHistory($login)) {
            $login_obj->setAlert($this->lng->txt('login_exists'));
            $form_valid = false;
        }

        if (!$form_valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        } elseif ($showGlobalTermsOfServieFailure) {
            $this->lng->loadLanguageModule('tos');
            $this->tpl->setOnScreenMessage('failure', sprintf(
                $this->lng->txt('tos_account_reg_not_possible'),
                'mailto:' . ilLegacyFormElementsUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress())
            ));
        } else {
            $password = $this->createUser($valid_role);
            $this->distributeMails($password);
            return $this->login();
        }
        $this->form->setValuesByPost();
        return $this->displayForm();
    }

    protected function createUser(int $a_role): string
    {
        // something went wrong with the form validation
        if (!$a_role) {
            global $DIC;

            $ilias = $DIC['ilias'];
            $ilias->raiseError("Invalid role selection in registration" .
                ", IP: " . $_SERVER["REMOTE_ADDR"], $ilias->error_obj->FATAL);
        }

        $this->userObj = new ilObjUser();

        $up = new ilUserProfile();
        ilUserProfile::setMode(ilUserProfile::MODE_REGISTRATION);

        $map = [];
        $up->skipGroup("preferences");
        $up->skipGroup("settings");
        $up->skipField("password");
        $up->skipField("birthday");
        $up->skipField("upload");
        foreach ($up->getStandardFields() as $k => $v) {
            if ($v["method"]) {
                $method = "set" . substr($v["method"], 3);
                if (method_exists($this->userObj, $method)) {
                    if ($k !== "username") {
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
            $password = ilSecuritySettingsChecker::generatePasswords(1);
            $password = $password[0];
        } else {
            $password = $this->form->getInput("usr_password");
        }
        $this->userObj->setPasswd($password);

        // Set user defined data
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        $defs = $user_defined_fields->getRegistrationDefinitions();
        $udf = [];
        foreach ($_POST as $k => $v) {
            if (strpos($k, "udf_") === 0) {
                $f = substr($k, 4);
                $udf[$f] = $v;
            }
        }
        $this->userObj->setUserDefinedData($udf);

        $this->userObj->setTimeLimitOwner(7);

        $access_limit = null;

        $this->code_was_used = false;
        $code_has_access_limit = false;
        $code_local_roles = [];
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
                            $rel = unserialize($code_data["alimitdt"], ['allowed_classes' => false]);
                            $access_limit = $rel["d"] * 86400 + $rel["m"] * 2592000 + time();
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
                    $rel_d = $access_limitations_obj->getRelative($a_role, 'd');
                    $rel_m = $access_limitations_obj->getRelative($a_role, 'm');
                    $access_limit = $rel_d * 86400 + $rel_m * 2592000 + time();
                    break;
            }
        }

        if ($access_limit) {
            $this->userObj->setTimeLimitUnlimited(false);
            $this->userObj->setTimeLimitUntil($access_limit);
        } else {
            $this->userObj->setTimeLimitUnlimited(true);
            $this->userObj->setTimeLimitUntil(time());
        }

        $this->userObj->setTimeLimitFrom(time());

        ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_REGISTRATION);

        $this->userObj->create();

        if ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_DIRECT ||
            $this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_CODES ||
            $this->code_was_used) {
            $this->userObj->setActive(true, 0);
        } elseif ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_ACTIVATION) {
            $this->userObj->setActive(false, 0);
        } else {
            $this->userObj->setActive(false, 0);
        }

        // set a timestamp for last_password_change
        // this ts is needed by ilSecuritySettings
        $this->userObj->setLastPasswordChangeTS(time());

        $this->userObj->setIsSelfRegistered(true);

        //insert user data in table user_data
        $this->userObj->saveAsNew();

        // don't update owner before the first save. updateOwner rereads the object which fails if it not save before
        $this->userObj->updateOwner();

        // setup user preferences
        $this->userObj->setLanguage($this->form->getInput('usr_language'));

        $handleDocument = ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument();
        if ($handleDocument) {
            $helper = new ilTermsOfServiceHelper();

            $helper->trackAcceptance($this->userObj, $this->termsOfServiceEvaluation->document());
        }

        $hits_per_page = $this->settings->get("hits_per_page");
        if ($hits_per_page < 10) {
            $hits_per_page = 10;
        }
        $this->userObj->setPref("hits_per_page", $hits_per_page);
        if ($this->http->wrapper()->query()->has('target')) {
            $this->userObj->setPref(
                'reg_target',
                $this->http->wrapper()->query()->retrieve(
                    'target',
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        $this->userObj->setPref('bs_allow_to_contact_me', $this->settings->get('bs_allow_to_contact_me', 'n'));
        $this->userObj->setPref('chat_osc_accept_msg', $this->settings->get('chat_osc_accept_msg', 'n'));
        $this->userObj->setPref('chat_broadcast_typing', $this->settings->get('chat_broadcast_typing', 'n'));
        $this->userObj->writePrefs();

        $this->rbacadmin->assignUser($a_role, $this->userObj->getId());

        // local roles from code
        if ($this->code_was_used && is_array($code_local_roles)) {
            foreach (array_unique($code_local_roles) as $local_role_obj_id) {
                // is given role (still) valid?
                if (ilObject::_lookupType($local_role_obj_id) === "role") {
                    $this->rbacadmin->assignUser($local_role_obj_id, $this->userObj->getId());

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

        return (string) $password;
    }

    protected function distributeMails(string $password): void
    {
        // Always send mail to approvers
        $mail = new ilRegistrationMailNotification();
        if ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_APPROVE && !$this->code_was_used) {
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_CONFIRMATION);
        } else {
            $mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_APPROVERS);
        }
        $mail->setRecipients($this->registration_settings->getApproveRecipients());
        $mail->setAdditionalInformation(['usr' => $this->userObj]);
        $mail->send();

        // Send mail to new user
        // Registration with confirmation link ist enabled
        if ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_ACTIVATION && !$this->code_was_used) {
            $mail = new ilRegistrationMimeMailNotification();
            $mail->setType(ilRegistrationMimeMailNotification::TYPE_NOTIFICATION_ACTIVATION);
            $mail->setRecipients([$this->userObj]);
            $mail->setAdditionalInformation(
                [
                    'usr' => $this->userObj,
                    'hash_lifetime' => $this->registration_settings->getRegistrationHashLifetime()
                ]
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

    public function login(): ilGlobalTemplateInterface
    {
        $tpl = ilStartUpGUI::initStartUpTemplate(['tpl.usr_registered.html', 'Services/Registration'], false);
        $this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('registration'));

        $tpl->setVariable("TXT_WELCOME", $this->lng->txt("welcome") . ", " . $this->userObj->getTitle() . "!");
        if (
            (
                $this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_DIRECT ||
                $this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_CODES ||
                $this->code_was_used
            ) &&
            !$this->registration_settings->passwordGenerationEnabled()
        ) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_registered'));

            $login_link = $this->ui_renderer->render(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('login_to_ilias'),
                    './login.php?cmd=force_login&lang=' . $this->userObj->getLanguage()
                )
            );
            $tpl->setVariable('LOGIN_LINK', $login_link);
        } elseif ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_APPROVE) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_submitted'));
        } elseif ($this->registration_settings->getRegistrationType() === ilRegistrationSettings::IL_REG_ACTIVATION) {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('reg_confirmation_link_successful'));
        } else {
            $tpl->setVariable('TXT_REGISTERED', $this->lng->txt('txt_registered_passw_gen'));
        }
        return $tpl;
    }

    protected function doProfileAutoComplete(): void
    {
        $field_id = (string) $_REQUEST["f"];
        $term = (string) $_REQUEST["term"];

        $result = ilPublicUserProfileGUI::getAutocompleteResult($field_id, $term);
        if (count($result)) {
            echo json_encode($result, JSON_THROW_ON_ERROR);
        }

        exit();
    }
}
