<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * Password assistance facility for users who have forgotten their password
 * or for users for whom no password has been assigned yet.
 * @author  Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesInit
 */
class ilPasswordAssistanceGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilRbacReview $rbacreview;
    protected ilGlobalTemplateInterface $tpl;
    protected ilSetting $settings;
    protected ilErrorHandling $ilErr;
    protected RefineryFactory $refinery;
    protected HTTPServices $http;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
        $this->ilErr = $DIC['ilErr'];

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * as replacement for "this->ilias"
     */
    protected function getClientId() : string
    {
        return CLIENT_ID;
    }

    public function executeCommand()
    {
        // check hack attempts
        if (!$this->settings->get('password_assistance')) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->FATAL);
        }

        // check correct setup
        if (!$this->settings->get('setup_ok')) {
            $this->ilErr->raiseError('Setup is not completed. Please run setup routine again.', $this->ilErr->FATAL);
        }

        // Change the language, if necessary.
        // And load the 'pwassist' language module
        $lang = '';
        if ($this->http->wrapper()->query()->has('lang')) {
            $lang = $this->http->wrapper()->query()->retrieve(
                'lang',
                $this->refinery->kindlyTo()->string()
            );
        }
        $key = '';
        if ($this->http->wrapper()->query()->has('key')) {
            $lang = $this->http->wrapper()->query()->retrieve(
                'key',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->lng->loadLanguageModule('pwassist');
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                if ($cmd != '' && method_exists($this, $cmd)) {
                    return $this->$cmd();
                } elseif (!empty($key)) { //ToDo PHP8: This will never happen. smeyer: why? $_GET['key'] != '' && $_GET['cmd'] = ''
                    $this->showAssignPasswordForm();
                } else {
                    $this->showAssistanceForm();
                }
                break;
        }
    }

    /**
     * Returns the ILIAS http path without a trailing /
     */
    protected function getBaseUrl() : string
    {
        return rtrim(ILIAS_HTTP_PATH, '/');
    }

    /**
     * @param string $script
     * @param array  $queryParameters
     * @return string
     */
    protected function buildUrl(string $script, array $queryParameters) : string
    {
        $url = implode('/', [
            $this->getBaseUrl(),
            ltrim($script, '/')
        ]);

        $url = \ilUtil::appendUrlParameterString(
            $url,
            http_build_query($queryParameters, null, '&')
        );

        return $url;
    }

    protected function getAssistanceForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setTitle($this->lng->txt('password_assistance'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'submitAssistanceForm'));
        $form->setTarget('_parent');

        $username = new ilTextInputGUI($this->lng->txt('username'), 'username');
        $username->setRequired(true);
        $form->addItem($username);

        $email = new ilEMailInputGUI($this->lng->txt('email'), 'email');
        $email->setRequired(true);
        $form->addItem($email);

        $form->addCommandButton('submitAssistanceForm', $this->lng->txt('submit'));
        return $form;
    }

    public function showAssistanceForm(ilPropertyFormGUI $form = null) : void
    {
        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assistance.html', true);
        $this->tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
        $this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

        $tpl->setVariable(
            'TXT_ENTER_USERNAME_AND_EMAIL',
            str_replace(
                "\\n",
                '<br />',
                sprintf(
                    $this->lng->txt('pwassist_enter_username_and_email'),
                    '<a href="mailto:' . ilLegacyFormElementsUtil::prepareFormOutput(
                        $this->settings->get('admin_email')
                    ) . '">' . ilLegacyFormElementsUtil::prepareFormOutput($this->settings->get('admin_email')) . '</a>'
                )
            )
        );

        if (!$form) {
            $form = $this->getAssistanceForm();
        }
        $tpl->setVariable('FORM', $form->getHTML());
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    /**
     * Reads the submitted data from the password assistance form.
     * The following form fields are read as HTTP POST parameters:
     * username
     * email
     * If the submitted username and email address matches an entry in the user data
     * table, then ILIAS creates a password assistance session for the user, and
     * sends a password assistance mail to the email address.
     * For details about the creation of the session and the e-mail see function
     * sendPasswordAssistanceMail().
     */
    public function submitAssistanceForm() : void
    {
        $form = $this->getAssistanceForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showAssistanceForm($form);
            return;
        }

        $username = $form->getInput('username');
        $email = $form->getInput('email');

        $usrId = \ilObjUser::getUserIdByLogin($username);
        if (!is_numeric($usrId) || !($usrId > 0)) {
            \ilLoggerFactory::getLogger('usr')->info(sprintf(
                'Could not process password assistance form (reason: no user found) %s / %s',
                $username,
                $email
            ));

            $this->showMessageForm(sprintf($this->lng->txt('pwassist_mail_sent'), $email));
            return;
        }

        $defaultAuth = ilAuthUtils::AUTH_LOCAL;
        if ($GLOBALS['DIC']['ilSetting']->get('auth_mode')) {
            $defaultAuth = $GLOBALS['DIC']['ilSetting']->get('auth_mode');
        }

        $user = new \ilObjUser($usrId);
        $emailAddresses = array_map('strtolower', [$user->getEmail(), $user->getSecondEmail()]);

        if (!in_array(strtolower($email), $emailAddresses)) {
            if (0 === strlen(implode('', $emailAddresses))) {
                \ilLoggerFactory::getLogger('usr')->info(sprintf(
                    'Could not process password assistance form (reason: account without email addresses): %s / %s',
                    $username,
                    $email
                ));
            } else {
                \ilLoggerFactory::getLogger('usr')->info(sprintf(
                    'Could not process password assistance form (reason: account email addresses differ from input): %s / %s',
                    $username,
                    $email
                ));
            }
        } elseif (
            (
                $user->getAuthMode(true) != ilAuthUtils::AUTH_LOCAL ||
                ($user->getAuthMode(true) == $defaultAuth && $defaultAuth != ilAuthUtils::AUTH_LOCAL)
            ) && !(
                $user->getAuthMode(true) == ilAuthUtils::AUTH_SAML
            )
        ) {
            \ilLoggerFactory::getLogger('usr')->info(sprintf(
                'Could not process password assistance form (reason: not permitted for accounts using external authentication sources): %s / %s',
                $username,
                $email
            ));
        } elseif (
            $this->rbacreview->isAssigned($user->getId(), ANONYMOUS_ROLE_ID) ||
            $this->rbacreview->isAssigned($user->getId(), SYSTEM_ROLE_ID)
        ) {
            \ilLoggerFactory::getLogger('usr')->info(sprintf(
                'Could not process password assistance form (reason: not permitted for system user or anonymous): %s / %s',
                $username,
                $email
            ));
        } else {
            $this->sendPasswordAssistanceMail($user);
        }

        $this->showMessageForm(sprintf($this->lng->txt('pwassist_mail_sent'), $email));
    }

    /**
     * Creates (or reuses) a password assistance session, and sends a password
     * assistance mail to the specified user.
     * Note: To prevent DOS attacks, a new session is created only, if no session
     * exists, or if the existing session has been expired.
     * The password assistance mail contains an URL, which points to this script
     * and contains the following URL parameters:
     * client_id
     * key
     */
    public function sendPasswordAssistanceMail(ilObjUser $userObj) : void
    {
        global $DIC;

        require_once 'include/inc.pwassist_session_handler.php';

        // Create a new session id
        // #9700 - this didn't do anything before?!
        // db_set_save_handler();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $pwassist_session['pwassist_id'] = db_pwassist_create_id();
        session_destroy();
        db_pwassist_session_write(
            $pwassist_session['pwassist_id'],
            3600,
            $userObj->getId()
        );

        $pwassist_url = $this->buildUrl(
            'pwassist.php',
            [
                'client_id' => $this->getClientId(),
                'lang' => $this->lng->getLangKey(),
                'key' => $pwassist_session['pwassist_id']
            ]
        );

        $alternative_pwassist_url = $this->buildUrl(
            'pwassist.php',
            [
                'client_id' => $this->getClientId(),
                'lang' => $this->lng->getLangKey(),
                'key' => $pwassist_session['pwassist_id']
            ]
        );

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $DIC["mail.mime.sender.factory"];
        $sender = $senderFactory->system();

        $mm = new ilMimeMail();
        $mm->Subject($this->lng->txt('pwassist_mail_subject'), true);
        $mm->From($sender);
        $mm->To($userObj->getEmail());
        $mm->Body(
            str_replace(
                array("\\n", "\\t"),
                array("\n", "\t"),
                sprintf(
                    $this->lng->txt('pwassist_mail_body'),
                    $pwassist_url,
                    $this->getBaseUrl() . '/',
                    $_SERVER['REMOTE_ADDR'],
                    $userObj->getLogin(),
                    'mailto:' . $DIC->settings()->get("admin_email"),
                    $alternative_pwassist_url
                )
            )
        );
        $mm->Send();
    }

    protected function getAssignPasswordForm(string $pwassist_id) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'submitAssignPasswordForm'));
        $form->setTarget('_parent');

        $username = new ilTextInputGUI($this->lng->txt('username'), 'username');
        $username->setRequired(true);
        $form->addItem($username);

        $password = new ilPasswordInputGUI($this->lng->txt('password'), 'password');
        $password->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
        $password->setRequired(true);
        $form->addItem($password);

        $key = new ilHiddenInputGUI('key');
        $key->setValue($pwassist_id);
        $form->addItem($key);
        $form->addCommandButton('submitAssignPasswordForm', $this->lng->txt('submit'));
        return $form;
    }

    /**
     * Assign password form.
     * This form is used to assign a password to a username.
     * To use this form, the following data must be provided as HTTP GET parameter,
     * or in argument pwassist_id:
     * key
     * The key is used to retrieve the password assistance session.
     * If the key is missing, or if the password assistance session has expired, the
     * password assistance form will be shown instead of this form.
     */
    public function showAssignPasswordForm(ilPropertyFormGUI $form = null, string $pwassist_id = '') : void
    {
        // Retrieve form data
        if (!$pwassist_id) {
            if ($this->http->wrapper()->query()->has('key')) {
                $pwassist_id = $this->http->wrapper()->query()->retrieve(
                    'key',
                    $this->refinery->kindlyTo()->string()
                );
            }
        }

        // Retrieve the session, and check if it is valid
        $pwassist_session = db_pwassist_session_read($pwassist_id);
        if (
            !is_array($pwassist_session) ||
            count($pwassist_session) == 0 ||
            $pwassist_session['expires'] < time()
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('pwassist_session_expired'));
            $this->showAssistanceForm(null);
        } else {
            $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assignpassword.html', true);
            $tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
            $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

            $tpl->setVariable(
                'TXT_ENTER_USERNAME_AND_NEW_PASSWORD',
                $this->lng->txt('pwassist_enter_username_and_new_password')
            );

            if (!$form) {
                $form = $this->getAssignPasswordForm($pwassist_id);
            }
            $tpl->setVariable('FORM', $form->getHTML());
            //$this->fillPermanentLink(self::PERMANENT_LINK_TARGET_PW);
            ilStartUpGUI::printToGlobalTemplate($tpl);
        }
    }

    /**
     * Reads the submitted data from the password assistance form.
     * The following form fields are read as HTTP POST parameters:
     * key
     * username
     * password1
     * password2
     * The key is used to retrieve the password assistance session.
     * If the key is missing, or if the password assistance session has expired, the
     * password assistance form will be shown instead of this form.
     * If the password assistance session is valid, and if the username matches the
     * username, for which the password assistance has been requested, and if the
     * new password is valid, ILIAS assigns the password to the user.
     * Note: To prevent replay attacks, the session is deleted when the
     * password has been assigned successfully.
     */
    public function submitAssignPasswordForm() : void
    {
        require_once 'include/inc.pwassist_session_handler.php';

        // We need to fetch this before form instantiation
        $pwassist_id = ilUtil::stripSlashes($_POST['key']);

        $form = $this->getAssignPasswordForm($pwassist_id);
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showAssistanceForm($form);
            return;
        }

        $username = $form->getInput('username');
        $password = $form->getInput('password');
        $pwassist_id = $form->getInput('key');

        // Retrieve the session
        $pwassist_session = db_pwassist_session_read($pwassist_id);

        if (
            !is_array($pwassist_session) ||
            count($pwassist_session) == 0 ||
            $pwassist_session['expires'] < time()
        ) {
            $this->tpl->setOnScreenMessage('failure', str_replace("\\n", '', $this->lng->txt('pwassist_session_expired')));
            $form->setValuesByPost();
            $this->showAssistanceForm($form);
            return;
        } else {
            $is_successful = true;
            $message = '';

            $userObj = \ilObjectFactory::getInstanceByObjId($pwassist_session['user_id'], false);
            if (!$userObj || !($userObj instanceof \ilObjUser)) {
                $message = $this->lng->txt('user_does_not_exist');
                $is_successful = false;
            }

            // check if the username entered by the user matches the
            // one of the user object.
            if ($is_successful && strcasecmp($userObj->getLogin(), $username) != 0) {
                $message = $this->lng->txt('pwassist_login_not_match');
                $is_successful = false;
            }

            $error_lng_var = '';
            if (!ilSecuritySettingsChecker::isPasswordValidForUserContext($password, $userObj, $error_lng_var)) {
                $message = $this->lng->txt($error_lng_var);
                $is_successful = false;
            }

            // End of validation
            // If the validation was successful, we change the password of the
            // user.
            // ------------------
            if ($is_successful) {
                $is_successful = $userObj->resetPassword($password, $password);
                if (!$is_successful) {
                    $message = $this->lng->txt('passwd_invalid');
                }
            }

            // If we are successful so far, we update the user object.
            // ------------------
            if ($is_successful) {
                $userObj->update();
            }

            // If we are successful, we destroy the password assistance
            // session and redirect to the login page.
            // Else we display the form again along with an error message.
            // ------------------
            if ($is_successful) {
                db_pwassist_session_destroy($pwassist_id);
                $this->showMessageForm(sprintf($this->lng->txt('pwassist_password_assigned'), $username));
            } else {
                $this->tpl->setOnScreenMessage('failure', str_replace("\\n", '', $message));
                $form->setValuesByPost();
                $this->showAssignPasswordForm($form, $pwassist_id);
            }
        }
    }
    
    protected function getUsernameAssistanceForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this, 'submitUsernameAssistanceForm'));
        $form->setTarget('_parent');

        $email = new ilTextInputGUI($this->lng->txt('email'), 'email');
        $email->setRequired(true);
        $form->addItem($email);

        $form->addCommandButton('submitUsernameAssistanceForm', $this->lng->txt('submit'));
        return $form;
    }

    /**
     * Shows the password assistance form.
     * This form is used to request a password assistance mail from ILIAS.
     * This form contains the following fields:
     * username
     * email
     * When the user submits the form, then this script is invoked with the cmd
     * 'submitAssistanceForm'.
     */
    public function showUsernameAssistanceForm(ilPropertyFormGUI $form = null) : void
    {
        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_username_assistance.html', true);
        $tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

        $tpl->setVariable(
            'TXT_ENTER_USERNAME_AND_EMAIL',
            str_replace(
                "\\n",
                '<br />',
                sprintf(
                    $this->lng->txt('pwassist_enter_email'),
                    '<a href="mailto:' . ilLegacyFormElementsUtil::prepareFormOutput(
                        $this->settings->get('admin_email')
                    ) . '">' . ilLegacyFormElementsUtil::prepareFormOutput($this->settings->get('admin_email')) . '</a>'
                )
            )
        );

        if (!$form) {
            $form = $this->getUsernameAssistanceForm();
        }
        $tpl->setVariable('FORM', $form->getHTML());
        //$this->fillPermanentLink(self::PERMANENT_LINK_TARGET_NAME);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    /**
     * Reads the submitted data from the password assistance form.
     * The following form fields are read as HTTP POST parameters:
     * username
     * email
     * If the submitted username and email address matches an entry in the user data
     * table, then ILIAS creates a password assistance session for the user, and
     * sends a password assistance mail to the email address.
     * For details about the creation of the session and the e-mail see function
     * sendPasswordAssistanceMail().
     */
    public function submitUsernameAssistanceForm() : void
    {
        $form = $this->getUsernameAssistanceForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showUsernameAssistanceForm($form);

            return;
        }

        $email = $form->getInput('email');
        $logins = ilObjUser::getUserLoginsByEmail($email);

        if (is_array($logins) && count($logins) > 0) {
            $this->sendUsernameAssistanceMail($email, $logins);
        } else {
            \ilLoggerFactory::getLogger('usr')->info(sprintf(
                'Could not sent username assistance emails to (reason: no user found): %s',
                $email
            ));
        }

        $this->showMessageForm($this->lng->txt('pwassist_mail_sent_generic'));
    }

    /**
     * Creates (or reuses) a password assistance session, and sends a password
     * assistance mail to the specified user.
     * Note: To prevent DOS attacks, a new session is created only, if no session
     * exists, or if the existing session has been expired.
     * The password assistance mail contains an URL, which points to this script
     * and contains the following URL parameters:
     * client_id
     * key
     */
    public function sendUsernameAssistanceMail(string $email, array $logins) : void
    {
        global $DIC;

        require_once 'include/inc.pwassist_session_handler.php';

        $login_url = $this->buildUrl(
            'pwassist.php',
            [
                'client_id' => $this->getClientId(),
                'lang' => $this->lng->getLangKey()
            ]
        );

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $DIC["mail.mime.sender.factory"];
        $sender = $senderFactory->system();

        $mm = new ilMimeMail();
        $mm->Subject($this->lng->txt('pwassist_mail_subject'), true);
        $mm->From($sender);
        $mm->To($email);
        $mm->Body(
            str_replace(
                array("\\n", "\\t"),
                array("\n", "\t"),
                sprintf(
                    $this->lng->txt('pwassist_username_mail_body'),
                    join(",\n", $logins),
                    $this->getBaseUrl() . '/',
                    $_SERVER['REMOTE_ADDR'],
                    $email,
                    'mailto:' . $this->settings->get("admin_email"),
                    $login_url
                )
            )
        );
        $mm->Send();
    }

    /**
     * This form is used to show a message to the user.
     */
    public function showMessageForm(string $text) : void
    {
        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_message.html', true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
        $tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));

        $tpl->setVariable('TXT_TEXT', str_replace("\\n", '<br />', $text));
        //$this->fillPermanentLink(self::PERMANENT_LINK_TARGET_NAME);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    protected function fillPermanentLink(string $context) : void
    {
        $this->tpl->setPermanentLink('usr', 0, $context);
    }
}
