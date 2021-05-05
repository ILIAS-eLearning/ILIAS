<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UICore\PageContentProvider;

/**
* StartUp GUI class. Handles Login and Registration.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ilCtrl_Calls ilStartUpGUI: ilAccountRegistrationGUI, ilPasswordAssistanceGUI, ilLoginPageGUI
*
* @ingroup ServicesInit
*/
class ilStartUpGUI
{
    const ACCOUNT_MIGRATION_MIGRATE = 1;
    const ACCOUNT_MIGRATION_NEW = 2;

    /** @var \ilCtrl */
    protected $ctrl;
    protected $lng;

    /**
     * @var \ilLogger | null
     */
    protected $logger;

    /** @var \ilGlobalTemplate */
    protected $mainTemplate;

    /** @var \ilObjUser */
    protected $user;

    /** @var \ilTermsOfServiceDocumentEvaluation */
    protected $termsOfServiceEvaluation;

    /** @var ServerRequestInterface*/
    protected $httpRequest;

    /**
     * ilStartUpGUI constructor.
     * @param \ilObjUser|null              $user
     * @param \ilTermsOfServiceDocumentEvaluation|null
     * @param \ilGlobalTemplate|null       $mainTemplate
     * @param ServerRequestInterface|null $httpRequest
     */
    public function __construct(
        \ilObjUser $user = null,
        \ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation = null,
        \ilGlobalTemplate $mainTemplate = null,
        ServerRequestInterface $httpRequest = null
    ) {
        global $DIC;

        if ($user === null) {
            $user = $DIC->user();
        }
        $this->user = $user;

        if ($termsOfServiceEvaluation === null) {
            $termsOfServiceEvaluation = $DIC['tos.document.evaluator'];
        }
        $this->termsOfServiceEvaluation = $termsOfServiceEvaluation;

        if ($mainTemplate === null) {
            $mainTemplate = $DIC->ui()->mainTemplate();
        }
        $this->mainTemplate = $mainTemplate;

        if ($httpRequest === null) {
            $httpRequest = $DIC->http()->request();
        }
        $this->httpRequest = $httpRequest;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('auth');
        $this->logger = ilLoggerFactory::getLogger('init');

        $this->ctrl->saveParameter($this, array("rep_ref_id", "lang", "target", "client_id"));

        $this->user->setLanguage($this->lng->getLangKey());
    }

    /**
     * execute command
     * @see register.php
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("processIndexPHP", array('processIndexPHP','showLoginPage'));
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilLoginPageGUI':
                break;

            case "ilaccountregistrationgui":
                require_once("Services/Registration/classes/class.ilAccountRegistrationGUI.php");
                return $this->ctrl->forwardCommand(new ilAccountRegistrationGUI());

            case "ilpasswordassistancegui":
                require_once("Services/Init/classes/class.ilPasswordAssistanceGUI.php");
                return $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());

            default:
                if (method_exists($this, $cmd)) {
                    return $this->$cmd();
                }
    }
    }

    /**
     * Get logger
     * @return \ilLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * jump to registration gui
     * @see register.php
     */
    public function jumpToRegistration()
    {
        $this->ctrl->setCmdClass("ilaccountregistrationgui");
        $this->ctrl->setCmd("");
        $this->executeCommand();
    }

    /**
     * jump to password assistance
     * @see pwassist.php
     */
    public function jumpToPasswordAssistance()
    {
        $this->ctrl->setCmdClass("ilpasswordassistancegui");
        $this->ctrl->setCmd("");
        $this->executeCommand();
    }

    /**
     * Show login page or redirect to startup page if user is not authenticated.
     */
    protected function showLoginPageOrStartupPage()
    {

        /**
         * @var ilAuthSession
         */
        $auth_session = $GLOBALS['DIC']['ilAuthSession'];
        $ilAppEventHandler = $GLOBALS['DIC']['ilAppEventHandler'];

        $force_login = false;
        if (
            !is_array($_REQUEST['cmd']) &&
            strcmp($_REQUEST['cmd'], 'force_login') === 0
        ) {
            $force_login = true;
        }

        if ($force_login) {
            $this->logger->debug('Force login');
            if ($auth_session->isValid()) {
                $this->logger->debug('Valid session -> logout current user');
                ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
                $auth_session->logout();

                $ilAppEventHandler->raise(
                    'Services/Authentication',
                    'afterLogout',
                    array(
                        'username' => $this->user->getLogin()
                    )
                );
            }
            $this->logger->debug('Show login page');
            return $this->showLoginPage();
        }

        /**
         * @var ilAuthSession
         */
        if ($auth_session->isValid()) {
            $this->logger->debug('Valid session -> redirect to starting page');
            return ilInitialisation::redirectToStartingPage();
        }
        $this->logger->debug('No valid session -> show login');
        $this->showLoginPage();
    }


    /**
     * @todo check for forced authentication like ecs, ...
     * Show login page
     * @param \ilPropertyFormGUI|null $form
     */
    protected function showLoginPage(ilPropertyFormGUI $form = null)
    {
        global $tpl, $ilSetting;


        $this->getLogger()->debug('Showing login page');

        $extUid = '';
        if (isset($_GET['ext_uid']) && is_string($_GET['ext_uid'])) {
            $extUid = $_GET['ext_uid'];
        }
        $soapPw = '';
        if (isset($_GET['soap_pw']) && is_string($_GET['soap_pw'])) {
            $soapPw = $_GET['soap_pw'];
        }

        require_once 'Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentialsSoap.php';
        $credentials = new ilAuthFrontendCredentialsSoap($GLOBALS['DIC']->http()->request(), $this->ctrl, $ilSetting);
        $credentials->setUsername(ilUtil::stripSlashes($extUid));
        $credentials->setPassword(ilUtil::stripSlashes($soapPw));
        $credentials->tryAuthenticationOnLoginPage();
        
        // try apache auth
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentialsApache.php';
        $frontend = new ilAuthFrontendCredentialsApache($this->httpRequest, $this->ctrl);
        $frontend->tryAuthenticationOnLoginPage();

        // Instantiate login template
        $tpl = self::initStartUpTemplate("tpl.login.html");

        $this->mainTemplate->addCss(ilObjStyleSheet::getContentStylePath(0));
        $this->mainTemplate->addCss(ilObjStyleSheet::getSyntaxStylePath());

        $page_editor_html = $this->getLoginPageEditorHTML();
        $page_editor_html = $this->showOpenIdConnectLoginForm($page_editor_html);
        $page_editor_html = $this->showLoginInformation($page_editor_html, $tpl);
        $page_editor_html = $this->showLoginForm($page_editor_html, $form);
        $page_editor_html = $this->showCASLoginForm($page_editor_html);
        $page_editor_html = $this->showShibbolethLoginForm($page_editor_html);
        $page_editor_html = $this->showSamlLoginForm($page_editor_html);
        $page_editor_html = $this->showRegistrationLinks($page_editor_html);
        $page_editor_html = $this->showTermsOfServiceLink($page_editor_html);

        $page_editor_html = $this->purgePlaceholders($page_editor_html);

        // check expired session and send message
        if ($GLOBALS['DIC']['ilAuthSession']->isExpired()) {
            ilUtil::sendFailure($GLOBALS['lng']->txt('auth_err_expired'));
        }

        if (strlen($page_editor_html)) {
            $tpl->setVariable('LPE', $page_editor_html);
        }

        self::printToGlobalTemplate($tpl);
    }


    public static function printToGlobalTemplate($tpl)
    {
        global $DIC;
        $gtpl = $DIC['tpl'];
        $gtpl->setContent($tpl->get());
        $gtpl->printToStdout("DEFAULT", false, true);
    }

    protected function showCodeForm($a_username = null, $a_form = null)
    {
        global $tpl, $lng;

        self::initStartUpTemplate("tpl.login_reactivate_code.html");

        ilUtil::sendFailure($lng->txt("time_limit_reached"));

        if (!$a_form) {
            $a_form = $this->initCodeForm($a_username);
        }

        $tpl->setVariable("FORM", $a_form->getHTML());
        $tpl->printToStdout("DEFAULT", false);
    }

    protected function initCodeForm($a_username)
    {
        global $lng, $ilCtrl;

        $lng->loadLanguageModule("auth");

        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, 'showcodeform'));
        $form->setTitle($lng->txt('auth_account_code_title'));

        $count = new ilTextInputGUI($lng->txt('auth_account_code'), 'code');
        $count->setRequired(true);
        $count->setInfo($lng->txt('auth_account_code_info'));
        $form->addItem($count);

        // #11658
        $uname = new ilHiddenInputGUI("uname");
        $uname->setValue($a_username);
        $form->addItem($uname);

        $form->addCommandButton('processCode', $lng->txt('send'));

        return $form;
    }

    /**
     * @todo has to be refactored.
     * @global ilLanguage $lng
     * @global type $ilAuth
     * @global type $ilCtrl
     * @return boolean
     */
    protected function processCode()
    {
        global $lng, $ilAuth, $ilCtrl;

        $uname = $_POST["uname"];

        $form = $this->initCodeForm($uname);
        if ($uname && $form->checkInput()) {
            $code = $form->getInput("code");

            include_once "Services/User/classes/class.ilAccountCode.php";
            if (ilAccountCode::isUnusedCode($code)) {
                $valid_until = ilAccountCode::getCodeValidUntil($code);

                if (!$user_id = ilObjUser::_lookupId($uname)) {
                    $this->showLoginPage();
                    return false;
                }

                $invalid_code = false;
                $user = new ilObjUser($user_id);

                if ($valid_until === "0") {
                    $user->setTimeLimitUnlimited(true);
                } else {
                    if (is_numeric($valid_until)) {
                        $valid_until = strtotime("+" . $valid_until . "days");
                    } else {
                        $valid_until = explode("-", $valid_until);
                        $valid_until = mktime(
                            23,
                            59,
                            59,
                            $valid_until[1],
                            $valid_until[2],
                            $valid_until[0]
                        );
                        if ($valid_until < time()) {
                            $invalid_code = true;
                        }
                    }

                    if (!$invalid_code) {
                        $user->setTimeLimitUnlimited(false);
                        $user->setTimeLimitUntil($valid_until);
                    }
                }

                if (!$invalid_code) {
                    $user->setActive(true);

                    ilAccountCode::useCode($code);

                    // apply registration code role assignments
                    ilAccountCode::applyRoleAssignments($user, $code);

                    // apply registration code time limits
                    ilAccountCode::applyAccessLimits($user, $code);

                    $user->update();

                    $ilCtrl->setParameter($this, "cu", 1);
                    $GLOBALS['DIC']->language()->loadLanguageModule('auth');
                    ilUtil::sendSuccess($GLOBALS['DIC']->language()->txt('auth_activation_code_success'), true);
                    $ilCtrl->redirect($this, "showLoginPage");
                }
            }

            $lng->loadLanguageModule("user");
            $field = $form->getItemByPostVar("code");
            $field->setAlert($lng->txt("user_account_code_not_valid"));
        }

        $form->setValuesByPost();
        $this->showCodeForm($uname, $form);
    }


    /**
     * Initialize the standard
     * @return \ilPropertyFormGUI
     */
    protected function initStandardLoginForm()
    {
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, ''));
        $form->setName("formlogin");
        $form->setShowTopButtons(false);
        $form->setTitle($this->lng->txt("login_to_ilias"));

        include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';
        $det = ilAuthModeDetermination::_getInstance();

        if (ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection()) {
            $visible_auth_methods = array();
            $radg = new ilRadioGroupInputGUI($this->lng->txt("auth_selection"), "auth_mode");
            foreach (ilAuthUtils::_getMultipleAuthModeOptions($this->lng) as $key => $option) {
                if (isset($option['hide_in_ui']) && $option['hide_in_ui']) {
                    continue;
                }

                $op1 = new ilRadioOption($option['txt'], $key);
                $radg->addOption($op1);
                if (isset($option['checked'])) {
                    $radg->setValue($key);
                }
                $visible_auth_methods[] = $op1;
            }

            if (count($visible_auth_methods) == 1) {
                $first_auth_method = current($visible_auth_methods);
                $hidden_auth_method = new ilHiddenInputGUI("auth_mode");
                $hidden_auth_method->setValue($first_auth_method->getValue());
                $form->addItem($hidden_auth_method);
            } else {
                $form->addItem($radg);
            }
        }

        $ti = new ilTextInputGUI($this->lng->txt("username"), "username");
        $ti->setSize(20);
        $ti->setRequired(true);
        $form->addItem($ti);

        $pi = new ilPasswordInputGUI($this->lng->txt("password"), "password");
        $pi->setUseStripSlashes(false);
        $pi->setRetype(false);
        $pi->setSkipSyntaxCheck(true);
        $pi->setSize(20);
        $pi->setDisableHtmlAutoComplete(false);
        $pi->setRequired(true);
        $form->addItem($pi);

        require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
        if (ilCaptchaUtil::isActiveForLogin()) {
            require_once 'Services/Captcha/classes/class.ilCaptchaInputGUI.php';
            $captcha = new ilCaptchaInputGUI($this->lng->txt('captcha_code'), 'captcha_code');
            $captcha->setRequired(true);
            $form->addItem($captcha);
        }

        $form->addCommandButton("doStandardAuthentication", $this->lng->txt("log_in"));

        return $form;
    }

    /**
     * Trying shibboleth authentication
     */
    protected function doShibbolethAuthentication()
    {
        $this->getLogger()->debug('Trying shibboleth authentication');

        include_once './Services/AuthShibboleth/classes/class.ilAuthFrontendCredentialsShibboleth.php';
        $credentials = new ilAuthFrontendCredentialsShibboleth();
        $credentials->initFromRequest();

        include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_SHIBBOLETH);

        include_once './Services/Authentication/classes/class.ilAuthStatus.php';
        $status = ilAuthStatus::getInstance();

        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                include_once './Services/Init/classes/class.ilInitialisation.php';
                ilInitialisation::redirectToStartingPage();
                return;

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                return $GLOBALS['ilCtrl']->redirect($this, 'showAccountMigration');

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilUtil::sendFailure($status->getTranslatedReason(), true);
                $GLOBALS['ilCtrl']->redirect($this, 'showLoginPage');
                return false;
        }

        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
        return false;
    }

    /**
     * Try CAS auth
     */
    protected function doCasAuthentication()
    {
        global $DIC;

        $this->getLogger()->debug('Trying cas authentication');

        include_once './Services/CAS/classes/class.ilAuthFrontendCredentialsCAS.php';
        $credentials = new ilAuthFrontendCredentialsCAS();

        include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_CAS);

        include_once './Services/Authentication/classes/class.ilAuthStatus.php';
        $status = ilAuthStatus::getInstance();

        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();
        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->getLogger()->debug('Authentication successful.');
                ilInitialisation::redirectToStartingPage();
                break;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
            default:
                ilUtil::sendFailure($DIC->language()->txt($status->getReason()));
                $this->showLoginPage();
                return false;
        }
    }

    /**
     * Handle lti requests
     */
    protected function doLTIAuthentication()
    {
        $this->getLogger()->debug('Trying lti authentication');

        $credentials = new ilAuthFrontendCredentialsLTI();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_PROVIDER_LTI);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();
                return;

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                return $GLOBALS['ilCtrl']->redirect($this, 'showAccountMigration');

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilUtil::sendFailure($GLOBALS['lng']->txt($status->getReason()), true);
                $GLOBALS['ilCtrl']->redirect($this, 'showLoginPage');
                return false;
        }

        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
        return false;
    }


    /**
     * Try apache auth
     */
    protected function doApacheAuthentication()
    {
        $this->getLogger()->debug('Trying apache authentication');

        $credentials = new \ilAuthFrontendCredentialsApache($this->httpRequest, $this->ctrl);
        $credentials->initFromRequest();

        $provider_factory = new \ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_APACHE);

        $status = \ilAuthStatus::getInstance();

        $frontend_factory = new \ilAuthFrontendFactory();
        $frontend_factory->setContext(\ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case \ilAuthStatus::STATUS_AUTHENTICATED:
                if ($credentials->hasValidTargetUrl()) {
                    \ilLoggerFactory::getLogger('auth')->debug(sprintf(
                        'Authentication successful. Redirecting to starting page: %s',
                        $credentials->getTargetUrl()
                    ));
                    $this->ctrl->redirectToURL($credentials->getTargetUrl());
                } else {
                    \ilLoggerFactory::getLogger('auth')->debug(
                        'Authentication successful, but no valid target URL given. Redirecting to default starting page.'
                    );
                    \ilInitialisation::redirectToStartingPage();
                }
                break;

            case \ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');
                break;

            case \ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                \ilUtil::sendFailure($status->getTranslatedReason(), true);
                $this->ctrl->redirectToURL(\ilUtil::appendUrlParameterString(
                    $this->ctrl->getLinkTarget($this, 'showLoginPage', '', false, false),
                    'passed_sso=1'
                ));
                break;
        }

        \ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
        return false;
    }

    /**
     * Check form input; authenticate user
     */
    protected function doStandardAuthentication()
    {
        $form = $this->initStandardLoginForm();
        if ($form->checkInput()) {
            $this->getLogger()->debug('Trying to authenticate user.');

            include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
            $credentials = new ilAuthFrontendCredentials();
            $credentials->setUsername($form->getInput('username'));
            $credentials->setPassword($form->getInput('password'));
            $credentials->setCaptchaCode($form->getInput('captcha_code'));

            // set chosen auth mode
            include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';
            $det = ilAuthModeDetermination::_getInstance();
            if (ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection()) {
                $credentials->setAuthMode($form->getInput('auth_mode'));
            }

            include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
            $provider_factory = new ilAuthProviderFactory();
            $providers = $provider_factory->getProviders($credentials);

            include_once './Services/Authentication/classes/class.ilAuthStatus.php';
            $status = ilAuthStatus::getInstance();

            include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
            $frontend_factory = new ilAuthFrontendFactory();
            $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
            $frontend = $frontend_factory->getFrontend(
                $GLOBALS['DIC']['ilAuthSession'],
                $status,
                $credentials,
                $providers
            );

            $frontend->authenticate();

            switch ($status->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                    include_once './Services/Init/classes/class.ilInitialisation.php';
                    ilInitialisation::redirectToStartingPage();
                    return;

                case ilAuthStatus::STATUS_CODE_ACTIVATION_REQUIRED:
                    $uname = ilObjUser::_lookupLogin($status->getAuthenticatedUserId());
                    return $this->showLoginPage($this->initCodeForm($uname));

                case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                    return $GLOBALS['ilCtrl']->redirect($this, 'showAccountMigration');

                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                    ilUtil::sendFailure($status->getTranslatedReason());
                    return $this->showLoginPage($form);
            }
        }
        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage($form);
        return false;
    }





    /**
     * Show login form
     * @global ilSetting $ilSetting
     * @param string $page_editor_html
     */
    protected function showLoginForm($page_editor_html, ilPropertyFormGUI $form = null)
    {
        global $ilSetting,$lng,$tpl;

        // @todo move this to auth utils.
        // login via ILIAS (this also includes radius and ldap)
        // If local authentication is enabled for shibboleth users, we
        // display the login form for ILIAS here.
        if (($ilSetting->get("auth_mode") != AUTH_SHIBBOLETH ||
            $ilSetting->get("shib_auth_allow_local")) &&
            $ilSetting->get("auth_mode") != AUTH_CAS) {
            if (!$form instanceof ilPropertyFormGUI) {
                $form = $this->initStandardLoginForm();
            }

            return $this->substituteLoginPageElements(
                $tpl,
                $page_editor_html,
                $form->getHTML(),
                '[list-login-form]',
                'LOGIN_FORM'
            );
        }
        return $page_editor_html;
    }

    /**
     * Show login information
     * @param string $page_editor_html
     * @return string $page_editor_html
     */
    protected function showLoginInformation($page_editor_html, $tpl)
    {
        global $lng;

        if (strlen($page_editor_html)) {
            // page editor active return
            return $page_editor_html;
        }

        $loginSettings = new ilSetting("login_settings");
        $information = $loginSettings->get("login_message_" . $lng->getLangKey());

        if (strlen(trim($information))) {
            $tpl->setVariable("TXT_LOGIN_INFORMATION", $information);
        }
        return $page_editor_html;
    }

    /**
     * Show cas login
     * @global ilSetting $ilSetting
     * @param string $page_editor_html
     * @return string $page_editor_html
     */
    protected function showCASLoginForm($page_editor_html)
    {
        global $ilSetting, $lng;


        // cas login link
        if ($ilSetting->get("cas_active")) {
            $tpl = new ilTemplate('tpl.login_form_cas.html', true, true, 'Services/Init');
            $tpl->setVariable("TXT_CAS_LOGIN", $lng->txt("login_to_ilias_via_cas"));
            $tpl->setVariable("TXT_CAS_LOGIN_BUTTON", ilUtil::getImagePath("cas_login_button.png"));
            $tpl->setVariable("TXT_CAS_LOGIN_INSTRUCTIONS", $ilSetting->get("cas_login_instructions"));
            $this->ctrl->setParameter($this, "forceCASLogin", "1");
            $tpl->setVariable("TARGET_CAS_LOGIN", $this->ctrl->getLinkTarget($this, "doCasAuthentication"));
            $this->ctrl->setParameter($this, "forceCASLogin", "");

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $tpl->get(),
                '[list-cas-login-form]',
                'CAS_LOGIN_FORM'
            );
        }
        return $page_editor_html;
    }

    /**
     * Show shibboleth login form
     * @param string $page_editor_html
     * @return string $page_editor_html
     */
    protected function showShibbolethLoginForm($page_editor_html)
    {
        global $ilSetting, $lng;

        // Refactoring with ilFormPropertyGUI
        // [...]

        // shibboleth login link
        if ($ilSetting->get("shib_active")) {
            $tpl = new ilTemplate('tpl.login_form_shibboleth.html', true, true, 'Services/Init');

            $tpl->setVariable('SHIB_FORMACTION', './shib_login.php'); // Bugfix http://ilias.de/mantis/view.php?id=10662 {$tpl->setVariable('SHIB_FORMACTION', $this->ctrl->getFormAction($this));}

            if ($ilSetting->get("shib_hos_type") == 'external_wayf') {
                $tpl->setCurrentBlock("shibboleth_login");
                $tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
                $tpl->setVariable("IL_TARGET", $_GET["target"]);
                $tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
                $tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $ilSetting->get("shib_login_button"));
                $tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", sprintf($lng->txt("shib_general_login_instructions"), $ilSetting->get("shib_federation_name")) . ' <a href="mailto:' . $ilSetting->get("admin_email") . '">ILIAS ' . $lng->txt("administrator") . '</a>.');
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));
                $tpl->parseCurrentBlock();
            } elseif ($ilSetting->get("shib_hos_type") == 'embedded_wayf') {
                $tpl->setCurrentBlock("shibboleth_custom_login");
                $customInstructions = stripslashes($ilSetting->get("shib_login_instructions"));
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $customInstructions);
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("shibboleth_wayf_login");
                $tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
                $tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
                $tpl->setVariable("TXT_SELECT_HOME_ORGANIZATION", sprintf($lng->txt("shib_select_home_organization"), $ilSetting->get("shib_federation_name")));
                $tpl->setVariable("TXT_CONTINUE", $lng->txt("btn_next"));
                $tpl->setVariable("TXT_SHIB_HOME_ORGANIZATION", $lng->txt("shib_home_organization"));
                $tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", $lng->txt("shib_general_wayf_login_instructions") . ' <a href="mailto:' . $ilSetting->get("admin_email") . '">ILIAS ' . $lng->txt("administrator") . '</a>.');
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));

                require_once "./Services/AuthShibboleth/classes/class.ilShibbolethWAYF.php";
                $WAYF = new ShibWAYF();

                $tpl->setVariable("TXT_SHIB_INVALID_SELECTION", $WAYF->showNotice());
                $tpl->setVariable("SHIB_IDP_LIST", $WAYF->generateSelection());
                $tpl->setVariable("ILW_TARGET", $_GET["target"]);
                $tpl->parseCurrentBlock();
            }

            return $this->substituteLoginPageElements($GLOBALS['tpl'], $page_editor_html, $tpl->get(), '[list-shibboleth-login-form]', 'SHIB_LOGIN_FORM');
        }

        return $page_editor_html;
    }


    /**
     * Substitute login page elements
     * @param ilTemplate $tpl
     * @param string $page_editor_html
     * @param string $element_html
     * @param string $placeholder
     * @param string $fallback_tplvar
     * return string $page_editor_html
     */
    protected function substituteLoginPageElements($tpl, $page_editor_html, $element_html, $placeholder, $fallback_tplvar)
    {
        if (!strlen($page_editor_html)) {
            $tpl->setVariable($fallback_tplvar, $element_html);
            return $page_editor_html;
        }
        // Try to replace placeholders
        if (!stristr($page_editor_html, $placeholder)) {
            $tpl->setVariable($fallback_tplvar, $element_html);
            return $page_editor_html;
        }
        return str_replace($placeholder, $element_html, $page_editor_html);
    }

    /**
     * Get HTML of ILIAS login page editor
     * @return string html
     */
    protected function getLoginPageEditorHTML()
    {
        global $lng, $tpl;

        include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorSettings.php';
        $lpe = ilAuthLoginPageEditorSettings::getInstance();
        $active_lang = $lpe->getIliasEditorLanguage($lng->getLangKey());

        if (!$active_lang) {
            return '';
        }

        // if page does not exist, return nothing
        include_once './Services/COPage/classes/class.ilPageUtil.php';
        if (!ilPageUtil::_existsAndNotEmpty('auth', ilLanguage::lookupId($active_lang))) {
            return '';
        }

        // get page object
        $page_gui = new ilLoginPageGUI(ilLanguage::lookupId($active_lang));

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $page_gui->setStyleId(0, 'auth');

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $ret = $page_gui->showPage();

        return $ret;
    }

    /**
     * Show registration, password forgotten, client slection links
     * @global ilLanguage $lng
     * @global ilSetting $ilSetting
     * @global <type> $ilIliasIniFile
     * @param string $page_editor_html
     * @return string
     */
    protected function showRegistrationLinks($page_editor_html)
    {
        global $lng, $ilSetting, $ilIliasIniFile, $ilAccess;

        $rtpl = new ilTemplate('tpl.login_registration_links.html', true, true, 'Services/Init');

        // allow new registrations?
        include_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
        if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED) {
            $rtpl->setCurrentBlock("new_registration");
            $rtpl->setVariable("REGISTER", $lng->txt("registration"));
            $rtpl->setVariable(
                "CMD_REGISTER",
                $this->ctrl->getLinkTargetByClass("ilaccountregistrationgui", "")
            );
            $rtpl->parseCurrentBlock();
        }
        // allow password assistance? Surpress option if Authmode is not local database
        if ($ilSetting->get("password_assistance")) {
            $rtpl->setCurrentBlock("password_assistance");
            $rtpl->setVariable("FORGOT_PASSWORD", $lng->txt("forgot_password"));
            $rtpl->setVariable("FORGOT_USERNAME", $lng->txt("forgot_username"));
            $rtpl->setVariable(
                "CMD_FORGOT_PASSWORD",
                $this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "")
            );
            $rtpl->setVariable(
                "CMD_FORGOT_USERNAME",
                $this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "showUsernameAssistanceForm")
            );
            $rtpl->setVariable("LANG_ID", $lng->getLangKey());
            $rtpl->parseCurrentBlock();
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME']) &&
            $ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, "read", "", ROOT_FOLDER_ID)) {
            $rtpl->setCurrentBlock("homelink");
            $rtpl->setVariable("CLIENT_ID", "?client_id=" . $_COOKIE["ilClientId"] . "&lang=" . $lng->getLangKey());
            $rtpl->setVariable("TXT_HOME", $lng->txt("home"));
            $rtpl->parseCurrentBlock();
        }

        if ($ilIliasIniFile->readVariable("clients", "list")) {
            $rtpl->setCurrentBlock("client_list");
            $rtpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
            $rtpl->setVariable("CMD_CLIENT_LIST", $this->ctrl->getLinkTarget($this, "showClientList"));
            $rtpl->parseCurrentBlock();
        }

        return $this->substituteLoginPageElements(
            $GLOBALS['tpl'],
            $page_editor_html,
            $rtpl->get(),
            '[list-registration-link]',
            'REG_PWD_CLIENT_LINKS'
        );
    }

    /**
     * Show terms of service link
     * @param string $page_editor_html
     * @return string
     */
    protected function showTermsOfServiceLink(string $page_editor_html) : string
    {
        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        if (\ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument()) {
            $utpl = new ilTemplate('tpl.login_terms_of_service_link.html', true, true, 'Services/Init');
            $utpl->setVariable('TXT_TERMS_OF_SERVICE', $this->lng->txt('usr_agreement'));
            $utpl->setVariable('LINK_TERMS_OF_SERVICE', $this->ctrl->getLinkTarget($this, 'showTermsOfService'));

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $utpl->get(),
                '[list-user-agreement]',
                'USER_AGREEMENT'
            );
        }

        return $this->substituteLoginPageElements(
            $GLOBALS['tpl'],
            $page_editor_html,
            '',
            '[list-user-agreement]',
            'USER_AGREEMENT'
        );
    }

    /**
     * Purge page editor html from unused placeholders
     * @param string $page_editor_html
     * @return string
     */
    protected function purgePlaceholders($page_editor_html)
    {
        return str_replace(
            array(
                '[list-language-selection] ',
                '[list-registration-link]',
                '[list-user-agreement]',
                '[list-login-form]',
                '[list-cas-login-form]',
                '[list-shibboleth-login-form]'
            ),
            array('','','','','','',''),
            $page_editor_html
        );
    }

    /**
     * Show account migration screen
     * @param string $message
     */
    public function showAccountMigration(string $message = '') : void
    {
        $tpl = self::initStartUpTemplate('tpl.login_account_migration.html');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'migrateAccount'));

        $form->setTitle($this->lng->txt('auth_account_migration'));
        $form->addCommandButton('migrateAccount', $this->lng->txt('save'));
        $form->addCommandButton('showLogin', $this->lng->txt('cancel'));

        $rad = new ilRadioGroupInputGUI($this->lng->txt('auth_account_migration_name'), 'account_migration');
        $rad->setValue(1);

        $keep = new ilRadioOption(
            $this->lng->txt('auth_account_migration_keep'),
            static::ACCOUNT_MIGRATION_MIGRATE,
            $this->lng->txt('auth_info_migrate')
        );
        $user = new ilTextInputGUI($this->lng->txt('login'), 'mig_username');
        $user->setRequired(true);
        $user->setValue(ilUtil::prepareFormOutput(
            (string) ($this->httpRequest->getParsedBody()['mig_username'] ?? '')
        ));
        $user->setSize(32);
        $user->setMaxLength(128);
        $keep->addSubItem($user);

        $pass = new ilPasswordInputGUI($this->lng->txt('password'), 'mig_password');
        $pass->setRetype(false);
        $pass->setRequired(true);
        $pass->setValue(ilUtil::prepareFormOutput(
            (string) ($this->httpRequest->getParsedBody()['mig_password'] ?? '')
        ));
        $pass->setSize(12);
        $pass->setMaxLength(128);
        $keep->addSubItem($pass);
        $rad->addOption($keep);

        $new = new ilRadioOption(
            $this->lng->txt('auth_account_migration_new'),
            static::ACCOUNT_MIGRATION_NEW,
            $this->lng->txt('auth_info_add')
        );
        $rad->addOption($new);

        $form->addItem($rad);

        $tpl->setVariable('MIG_FORM', $form->getHTML());

        if (strlen($message)) {
            ilUtil::sendFailure($message);
        }

        self::printToGlobalTemplate($tpl);
    }

    /**
     * Migrate Account
     * @return bool
     */
    protected function migrateAccount() : bool
    {
        if (!isset($this->httpRequest->getParsedBody()['account_migration'])) {
            $this->showAccountMigration(
                $this->lng->txt('select_one')
            );
            return false;
        }

        if (
            ((int) $this->httpRequest->getParsedBody()['account_migration'] === self::ACCOUNT_MIGRATION_MIGRATE) &&
            (
                !isset($this->httpRequest->getParsedBody()['mig_username']) ||
                !is_string($this->httpRequest->getParsedBody()['mig_username']) ||
                0 === strlen($this->httpRequest->getParsedBody()['mig_username']) ||
                !isset($this->httpRequest->getParsedBody()['mig_password']) ||
                !is_string($this->httpRequest->getParsedBody()['mig_password'])
            )
        ) {
            $this->showAccountMigration(
                $this->lng->txt('err_wrong_login')
            );
            return false;
        }

        if ((int) $this->httpRequest->getParsedBody()['account_migration'] == self::ACCOUNT_MIGRATION_MIGRATE) {
            return $this->doMigration();
        } elseif ((int) $this->httpRequest->getParsedBody()['account_migration'] == static::ACCOUNT_MIGRATION_NEW) {
            return $this->doMigrationNewAccount();
        }
    }

    /**
     * @return bool
     */
    protected function doMigrationNewAccount() : bool
    {
        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername(ilSession::get(ilAuthFrontend::MIG_EXTERNAL_ACCOUNT));

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilSession::get(ilAuthFrontend::MIG_TRIGGER_AUTHMODE));

        $this->logger->debug('Using provider: ' . get_class($provider) . ' for further processing.');

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            [$provider]
        );

        if ($frontend->migrateAccountNew()) {
            ilInitialisation::redirectToStartingPage();
        }

        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->ctrl->redirect($this, 'showAccountMigration');

        return true;
    }

    /**
     * @return bool
     */
    protected function doMigration() : bool
    {
        $this->logger->debug('Starting account migration for user: ' . (string) ilSession::get('mig_ext_account'));

        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername((string) $_POST['mig_username']);
        $credentials->setPassword((string) $_POST['mig_password']);

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_LOCAL);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->getLogger()->debug('Account migration: authentication successful for ' . (string) $_POST['mig_username']);

                $provider = $provider_factory->getProviderByAuthMode(
                    $credentials,
                    ilSession::get(ilAuthFrontend::MIG_TRIGGER_AUTHMODE)
                );
                $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
                $frontend = $frontend_factory->getFrontend(
                    $GLOBALS['DIC']['ilAuthSession'],
                    $status,
                    $credentials,
                    [$provider]
                );
                if (
                    $frontend->migrateAccount($GLOBALS['DIC']['ilAuthSession'])
                ) {
                    ilInitialisation::redirectToStartingPage();
                } else {
                    ilUtil::sendFailure($this->lng->txt('err_wrong_login'), true);
                    $this->ctrl->redirect($this, 'showAccountMigration');
                }
                break;

            default:
                $this->getLogger()->info('Account migration failed for user ' . (string) $_POST['mig_username']);
                $this->showAccountMigration($GLOBALS['lng']->txt('err_wrong_login'));
                return false;
        }
    }

    /**
     * Show logout screen
     */
    protected function showLogout()
    {
        global $DIC;

        $lng = $DIC->language();
        $ilIliasIniFile = $DIC['ilIliasIniFile'];

        $tpl = self::initStartUpTemplate("tpl.logout.html");

        $client_id = $_GET['client_id'];

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            $tpl->setCurrentBlock("homelink");
            $tpl->setVariable("CLIENT_ID", "?client_id=" . $client_id . "&lang=" . $lng->getLangKey());
            $tpl->setVariable("TXT_HOME", $lng->txt("home"));
            $tpl->parseCurrentBlock();
        }

        if ($ilIliasIniFile->readVariable("clients", "list")) {
            $tpl->setCurrentBlock("client_list");
            $tpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
            $this->ctrl->setParameter($this, "client_id", $client_id);
            $tpl->setVariable(
                "CMD_CLIENT_LIST",
                $this->ctrl->getLinkTarget($this, "showClientList")
            );
            $tpl->parseCurrentBlock();
            $this->ctrl->setParameter($this, "client_id", "");
        }

        $tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("logout"));
        $tpl->setVariable("TXT_LOGOUT_TEXT", $lng->txt("logout_text"));
        $tpl->setVariable("TXT_LOGIN", $lng->txt("login_to_ilias"));
        $tpl->setVariable("CLIENT_ID", "?client_id=" . $client_id . "&cmd=force_login&lang=" . $lng->getLangKey());

        self::printToGlobalTemplate($tpl);
    }



    /**
    * show logout screen
    */
    public function doLogout()
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $user = $DIC->user();
        $lng = $DIC->language();
        $ilIliasIniFile = $DIC['ilIliasIniFile'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $ilAppEventHandler->raise(
            'Services/Authentication',
            'beforeLogout',
            [
                'user_id' => $this->user->getId()
            ]
        );

        $user_language = $user->getLanguage();

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $GLOBALS['DIC']['ilAuthSession']->logout();

        $GLOBALS['ilAppEventHandler']->raise(
            'Services/Authentication',
            'afterLogout',
            array(
                'username' => $this->user->getLogin()
            )
        );

        if ((int) $this->user->getAuthMode(true) == AUTH_SAML && ilSession::get('used_external_auth')) {
            $this->ctrl->redirectToURL('saml.php?action=logout&logout_url=' . urlencode(ILIAS_HTTP_PATH . '/login.php'));
        }

        // reset cookie
        $client_id = $_COOKIE["ilClientId"];
        ilUtil::setCookie("ilClientId", "");

        // redirect and show logout information
        $this->ctrl->setParameter($this, 'client_id', $client_id);
        $this->ctrl->setParameter($this, 'lang', $user_language);
        $this->ctrl->redirect($this, 'showLogout');
    }

    /**
     * show client list
     */
    public function showClientList()
    {
        global $tpl, $ilIliasIniFile, $lng;

        if (!$ilIliasIniFile->readVariable("clients", "list")) {
            $this->processIndexPHP();
            return;
        }

        // fix #21612
        $tpl->hideFooter(); // no client yet

        $tpl->setVariable("PAGETITLE", $lng->txt("clientlist_clientlist"));

        // load client list template
        $tpl = self::initStartUpTemplate("tpl.client_list.html");

        // load template for table
        $tpl->addBlockfile("CLIENT_LIST", "client_list", "tpl.table.html");

        // load template for table content data
        $tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        // load table content data
        require_once("setup/classes/class.ilClientList.php");
        require_once("setup/classes/class.ilClient.php");
        require_once("./Services/Table/classes/class.ilTableGUI.php");
        $clientlist = new \ilClientList();
        $list = $clientlist->getClients();

        if (count($list) == 0) {
            header("Location: ./setup/setup.php");
            exit();
        }

        $hasPublicSection = false;
        foreach ($list as $key => $client) {
            $client->setDSN();
            if ($client->checkDatabaseExists(true)) {
                $client->connect();
                if ($client->ini->readVariable("client", "access") and $client->getSetting("setup_ok")) {
                    $this->ctrl->setParameter($this, "client_id", $key);
                    $tmp = array();
                    $tmp[] = $client->getName();
                    $tmp[] = "<a href=\"" . "login.php?cmd=force_login&client_id=" . urlencode($key) . "\">" . $lng->txt("clientlist_login_page") . "</a>";

                    if ($client->getSetting('pub_section')) {
                        $hasPublicSection = true;
                        $tmp[] = "<a href=\"" . "ilias.php?baseClass=ilRepositoryGUI&client_id=" . urlencode($key) . "\">" . $lng->txt("clientlist_start_page") . "</a>";
                    } else {
                        $tmp[] = '';
                    }

                    $data[] = $tmp;
                }
            }
        }

        // create table
        $tbl = new ilTableGUI('', false);

        // title & header columns
        if ($hasPublicSection) {
            $tbl->setTitle($lng->txt("clientlist_available_clients"));
            $tbl->setHeaderNames(array($lng->txt("clientlist_installation_name"), $lng->txt("clientlist_login"), $lng->txt("clientlist_public_access")));
            $tbl->setHeaderVars(array("name","index","login"));
            $tbl->setColumnWidth(array("50%","25%","25%"));
        } else {
            $tbl->setTitle($lng->txt("clientlist_available_clients"));
            $tbl->setHeaderNames(array($lng->txt("clientlist_installation_name"), $lng->txt("clientlist_login"), ''));
            $tbl->setHeaderVars(array("name","login",''));
            $tbl->setColumnWidth(array("70%","25%",'1px'));
        }

        // control
        $tbl->setOrderColumn($_GET["sort_by"], "name");
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setLimit($_GET["limit"]);
        $tbl->setOffset($_GET["offset"]);

        // content
        $tbl->setData($data);

        $tbl->disable("icon");
        $tbl->disable("numinfo");
        $tbl->disable("sort");
        $tbl->disable("footer");

        // render table
        $html_for_nothing = $tbl->render();
        self::printToGlobalTemplate($tbl->getTemplateObject());
    }

    /**
    * show help screen, if cookies are disabled
    *
    * to do: link to online help here
    */
    public function showNoCookiesScreen()
    {
        global $tpl;

        $str = "<p style=\"margin:15px;\">
			You need to enable Session Cookies in your Browser to use ILIAS.
			<br/>
			<br/><b>Firefox</b>
			<br/>Tools -> Options -> Privacy -> Cookies
			<br/>Enable 'Allow sites to set cookies' and activate option 'Keep
			<br/>cookies' auf 'until I close Firefox'
			<br/>
			<br/><b>Mozilla/Netscape</b>
			<br/>Edit -> Preferences -> Privacy&Security -> Cookies
			<br/>Go to 'Cookie Lifetime Policy' and check option 'Accept for current
			<br/>session only'.
			<br/>
			<br/><b>Internet Explorer</b>
			<br/>Tools -> Internet Options -> Privacy -> Advanced
			<br/>- Check 'Override automatic cookie handling'
			<br/>- Check 'Always allow session cookies'
			</p>";
        $tpl->setVariable("CONTENT", $str);
        $tpl->printToStdout();
    }

    /**
     * Get terms of service
     */
    protected function getAcceptance()
    {
        $this->showTermsOfService();
    }

    /**
     * Show terms of service
     */
    protected function showTermsOfService()
    {
        $back_to_login = ('getAcceptance' != $this->ctrl->getCmd());

        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        $tpl = self::initStartUpTemplate('tpl.view_terms_of_service.html', $back_to_login, !$back_to_login);

        $handleDocument = \ilTermsOfServiceHelper::isEnabled() && $this->termsOfServiceEvaluation->hasDocument();
        if ($handleDocument) {
            $document = $this->termsOfServiceEvaluation->document();
            if ('getAcceptance' == $this->ctrl->getCmd()) {
                if (isset($_POST['status']) && 'accepted' == $_POST['status']) {
                    $helper = new \ilTermsOfServiceHelper();

                    $helper->trackAcceptance($this->user, $document);

                    if (ilSession::get('orig_request_target')) {
                        $target = ilSession::get('orig_request_target');
                        ilSession::set('orig_request_target', '');
                        ilUtil::redirect($target);
                    } else {
                        ilUtil::redirect('index.php?target=' . $_GET['target'] . '&client_id=' . CLIENT_ID);
                    }
                }

                $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, $this->ctrl->getCmd()));
                $tpl->setVariable('ACCEPT_CHECKBOX', ilUtil::formCheckbox(0, 'status', 'accepted'));
                $tpl->setVariable('ACCEPT_TERMS_OF_SERVICE', $this->lng->txt('accept_usr_agreement'));
                $tpl->setVariable('TXT_SUBMIT', $this->lng->txt('submit'));
            }

            $tpl->setPermanentLink('usr', null, 'agreement');
            $tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $document->content());
        } else {
            $tpl->setVariable(
                'TERMS_OF_SERVICE_CONTENT',
                sprintf(
                    $this->lng->txt('no_agreement_description'),
                    'mailto:' . ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress())
                )
            );
        }

        self::printToGlobalTemplate($tpl);
    }

    /**
    * process index.php
    */
    protected function processIndexPHP()
    {
        global $ilIliasIniFile, $ilAuth, $ilSetting;

        // In case of an valid session, redirect to starting page
        if ($GLOBALS['DIC']['ilAuthSession']->isValid()) {
            include_once './Services/Init/classes/class.ilInitialisation.php';
            ilInitialisation::redirectToStartingPage();
            return;
        }

        // no valid session => show client list, if no client info is given
        if (
            !isset($_GET["client_id"]) &&
            ($_GET["cmd"] == "") &&
            $ilIliasIniFile->readVariable("clients", "list")) {
            return $this->showClientList();
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            return ilInitialisation::goToPublicSection();
        }

        // otherwise show login page
        return $this->showLoginPage();
    }


    public static function _checkGoto($a_target)
    {
        global $DIC;
        global $objDefinition, $ilPluginAdmin, $ilUser;

        $access = $DIC->access();


        if (is_object($ilPluginAdmin)) {
            // get user interface plugins
            $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

            // search
            foreach ($pl_names as $pl) {
                $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
                $gui_class = $ui_plugin->getUIClassInstance();
                $resp = $gui_class->checkGotoHook($a_target);
                if ($resp["target"] !== false) {
                    $a_target = $resp["target"];
                    break;
                }
            }
        }

        if ($a_target == "") {
            return false;
        }

        $t_arr = explode("_", $a_target);
        $type = $t_arr[0];

        if ($type == "git") {
            $type = "glo";
        }

        if ($type == "pg" | $type == "st") {
            $type = "lm";
        }

        $class = $objDefinition->getClassName($type);
        if ($class == "") {
            return false;
        }

        $location = $objDefinition->getLocation($type);
        $full_class = "ilObj" . $class . "Access";
        include_once($location . "/class." . $full_class . ".php");

        $ret = call_user_func(array($full_class, "_checkGoto"), $a_target);

        // if no access and repository object => check for parent course/group
        if (!$ret &&
            !stristr($a_target, "_wsp") &&
            $ilUser->getId() != ANONYMOUS_USER_ID && // #10637
            !$objDefinition->isAdministrationObject($type) &&
            $objDefinition->isRBACObject($type) &&
            $t_arr[1]) {
            global $tree, $rbacsystem, $ilAccess;

            // original type "pg" => pg_<page_id>[_<ref_id>]
            if ($t_arr[0] == "pg") {
                if (isset($t_arr[2])) {
                    $ref_id = $t_arr[2];
                } else {
                    $lm_id = ilLMObject::_lookupContObjID($t_arr[1]);
                    $ref_id = ilObject::_getAllReferences($lm_id);
                    if ($ref_id) {
                        $ref_id = array_shift($ref_id);
                    }
                }
            } else {
                $ref_id = $t_arr[1];
            }

            include_once "Services/Membership/classes/class.ilParticipants.php";
            $block_obj = array();

            // walk path to find parent container
            $path = $tree->getPathId($ref_id);
            array_pop($path);
            foreach ($path as $path_ref_id) {
                $redirect_infopage = false;
                $add_member_role = false;

                $ptype = ilObject::_lookupType($path_ref_id, true);
                $pobj_id = ilObject::_lookupObjId($path_ref_id);

                // core checks: timings/object-specific
                if (
                    !$access->doActivationCheck('read', '', $path_ref_id, $ilUser->getId(), $pobj_id, $ptype) ||
                    !$access->doStatusCheck('read', '', $path_ref_id, $ilUser->getId(), $pobj_id, $ptype)
                ) {
                    // object in path is inaccessible - aborting
                    return false;
                } elseif ($ptype == "crs") {
                    // check if already participant
                    include_once "Modules/Course/classes/class.ilCourseParticipant.php";
                    $participants = new ilCourseParticipant($pobj_id, $ilUser->getId());
                    if (!$participants->isAssigned()) {
                        // subscription currently possible?
                        include_once "Modules/Course/classes/class.ilObjCourse.php";
                        if (ilObjCourse::_isActivated($pobj_id) &&
                            ilObjCourse::_registrationEnabled($pobj_id)) {
                            $block_obj[] = $path_ref_id;
                            $add_member_role = true;
                        } else {
                            $redirect_infopage = true;
                        }
                    }
                } elseif ($ptype == "grp") {
                    // check if already participant
                    include_once "Modules/Group/classes/class.ilGroupParticipants.php";
                    if (!ilGroupParticipants::_isParticipant($path_ref_id, $ilUser->getId())) {
                        // subscription currently possible?
                        include_once "Modules/Group/classes/class.ilObjGroup.php";
                        $group_obj = new ilObjGroup($path_ref_id);
                        if ($group_obj->isRegistrationEnabled()) {
                            $block_obj[] = $path_ref_id;
                            $add_member_role = true;
                        } else {
                            $redirect_infopage = true;
                        }
                    }
                }

                // add members roles for all "blocking" objects
                if ($add_member_role) {
                    // cannot join? goto will never work, so redirect to current object
                    $rbacsystem->resetPACache($ilUser->getId(), $path_ref_id);
                    if (!$rbacsystem->checkAccess("join", $path_ref_id)) {
                        $redirect_infopage = true;
                    } else {
                        $rbacsystem->addTemporaryRole(
                            $ilUser->getId(),
                            ilParticipants::getDefaultMemberRole($path_ref_id)
                        );
                    }
                }

                // redirect to infopage of 1st blocking object in path
                if ($redirect_infopage) {
                    if ($rbacsystem->checkAccess("visible", $path_ref_id)) {
                        ilUtil::redirect("ilias.php?baseClass=ilRepositoryGUI" .
                            "&ref_id=" . $path_ref_id . "&cmd=infoScreen");
                    } else {
                        return false;
                    }
                }
            }


            // check if access will be possible with all (possible) member roles added
            $rbacsystem->resetPACache($ilUser->getId(), $ref_id);
            if ($rbacsystem->checkAccess("read", $ref_id) && sizeof($block_obj)) { // #12128
                // this won't work with lm-pages (see above)
                // include_once "Services/Link/classes/class.ilLink.php";
                // $_SESSION["pending_goto"] = ilLink::_getStaticLink($ref_id, $type);

                // keep original target
                $_SESSION["pending_goto"] = "goto.php?target=" . $a_target;

                // redirect to 1st non-member object in path
                ilUtil::redirect("ilias.php?baseClass=ilRepositoryGUI" .
                    "&ref_id=" . array_shift($block_obj));
            }
        }

        return $ret;
    }

    public function confirmRegistration()
    {
        ilUtil::setCookie('iltest', 'cookie', false);

        if (!isset($_GET['rh']) || !strlen(trim($_GET['rh']))) {
            $this->ctrl->redirectToURL('./login.php?cmd=force_login&reg_confirmation_msg=reg_confirmation_hash_not_passed');
        }

        try {
            $oRegSettings = new ilRegistrationSettings();

            $usr_id = ilObjUser::_verifyRegistrationHash(trim($_GET['rh']));
            /** @var \ilObjUser $user */
            $user = ilObjectFactory::getInstanceByObjId($usr_id);
            $user->setActive(true);
            $password = '';
            if ($oRegSettings->passwordGenerationEnabled()) {
                $passwords = ilUtil::generatePasswords(1);
                $password = $passwords[0];
                $user->setPasswd($password, IL_PASSWD_PLAIN);
                $user->setLastPasswordChangeTS(time());
            }
            $user->update();

            $target = $user->getPref('reg_target');
            if (strlen($target) > 0) {
                // Used for ilAccountMail in ilAccountRegistrationMail, which relies on this super global ...
                $_GET['target'] = $target;
            }

            $accountMail = new ilAccountRegistrationMail(
                $oRegSettings,
                $this->lng,
                ilLoggerFactory::getLogger('user')
            );
            $accountMail->withEmailConfirmationRegistrationMode()->send($user, $password);

            $this->ctrl->redirectToURL(sprintf(
                './login.php?cmd=force_login&reg_confirmation_msg=reg_account_confirmation_successful&lang=%s',
                $user->getLanguage()
            ));
        } catch (ilRegConfirmationLinkExpiredException $exception) {
            $soap_client = new ilSoapClient();
            $soap_client->setResponseTimeout(1);
            $soap_client->enableWSDL(true);
            $soap_client->init();

            $this->logger->info('Triggered soap call (background process) for deletion of inactive user objects with expired confirmation hash values (dual opt in) ...');

            $soap_client->call(
                'deleteExpiredDualOptInUserObjects',
                [
                    $_COOKIE[session_name()] . '::' . $_COOKIE['ilClientId'],
                    $exception->getCode() // user id
                ]
            );

            $this->ctrl->redirectToURL(sprintf(
                './login.php?cmd=force_login&reg_confirmation_msg=%s',
                $exception->getMessage()
            ));
        } catch (ilRegistrationHashNotFoundException $exception) {
            $this->ctrl->redirectToURL(sprintf(
                './login.php?cmd=force_login&reg_confirmation_msg=%s',
                $exception->getMessage()
            ));
        }
    }

    /**
     * This method enriches the global template with some user interface elements (language selection, headlines, back buttons, ...) for public service views
     * @param mixed   $a_tmpl The template file as a string of as an array (index 0: template file, index 1: template directory)
     * @param bool    $a_show_back
     * @param bool    $a_show_logout
     */
    public static function initStartUpTemplate($a_tmpl, $a_show_back = false, $a_show_logout = false)
    {
        /**
         * @var $tpl       ilTemplate
         * @var $lng       ilLanguage
         * @var $ilCtrl    ilCtrl
         * @var $ilSetting ilSetting
         * @var $ilAccess  ilAccessHandler
         */
        global $lng, $ilAccess, $ilSetting;
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);

        $tpl->addBlockfile('CONTENT', 'content', 'tpl.startup_screen.html', 'Services/Init');

        $view_title = $lng->txt('login_to_ilias');
        if ($a_show_back) {
            // #13400
            $param = 'client_id=' . $_COOKIE['ilClientId'] . '&lang=' . $lng->getLangKey();

            $tpl->setCurrentBlock('link_item_bl');
            $tpl->setVariable('LINK_TXT', $view_title);
            $tpl->setVariable('LINK_URL', 'login.php?cmd=force_login&' . $param);
            $tpl->parseCurrentBlock();

            include_once './Services/Init/classes/class.ilPublicSectionSettings.php';
            if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME']) &&
                $ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', ROOT_FOLDER_ID)) {
                $tpl->setVariable('LINK_URL', 'index.php?' . $param);
                $tpl->setVariable('LINK_TXT', $lng->txt('home'));
                $tpl->parseCurrentBlock();
            }
        } elseif ($a_show_logout) {
            $view_title = $lng->txt('logout');
            $tpl->setCurrentBlock('link_item_bl');
            $tpl->setVariable('LINK_TXT', $view_title);
            $tpl->setVariable('LINK_URL', ILIAS_HTTP_PATH . '/logout.php');
            $tpl->parseCurrentBlock();
        }

        if (is_array($a_tmpl)) {
            $template_file = $a_tmpl[0];
            $template_dir = $a_tmpl[1];
        } else {
            $template_file = $a_tmpl;
            $template_dir = 'Services/Init';
        }

        $tpl->addBlockFile('STARTUP_CONTENT', 'startup_content', $template_file, $template_dir);

        PageContentProvider::setViewTitle($view_title);
        $short_title = $ilSetting->get('short_inst_name');
        if (trim($short_title) === "") {
            $short_title = 'ILIAS';
        }
        PageContentProvider::setShortTitle($short_title);

        $header_title = (string) ilObjSystemFolder::_getHeaderTitle();
        PageContentProvider::setTitle($header_title);

        return $tpl;
    }

    /**
     * @param string $page_editor_html
     * @return string
     */
    protected function showSamlLoginForm($page_editor_html)
    {
        require_once 'Services/Saml/classes/class.ilSamlIdp.php';
        require_once 'Services/Saml/classes/class.ilSamlSettings.php';

        if (count(ilSamlIdp::getActiveIdpList()) > 0 && ilSamlSettings::getInstance()->isDisplayedOnLoginPage()) {
            $tpl = new ilTemplate('tpl.login_form_saml.html', true, true, 'Services/Saml');

            $return = '';
            if (isset($_GET['target'])) {
                $return = '?returnTo=' . urlencode(ilUtil::stripSlashes($_GET['target']));
            }

            $tpl->setVariable('SAML_SCRIPT_URL', './saml.php' . $return);
            $tpl->setVariable('TXT_LOGIN', $GLOBALS['DIC']->language()->txt('saml_log_in'));
            $tpl->setVariable('LOGIN_TO_ILIAS_VIA_SAML', $GLOBALS['DIC']->language()->txt('login_to_ilias_via_saml'));
            $tpl->setVariable('TXT_SAML_LOGIN_TXT', $GLOBALS['DIC']->language()->txt('saml_login_form_txt'));
            $tpl->setVariable('TXT_SAML_LOGIN_INFO_TXT', $GLOBALS['DIC']->language()->txt('saml_login_form_info_txt'));

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $tpl->get(),
                '[list-saml-login-form]',
                'SAML_LOGIN_FORM'
            );
        }

        return $page_editor_html;
    }

    /**
     * @param $page_editor_html
     * @return string
     */
    protected function showOpenIdConnectLoginForm($page_editor_html)
    {
        global $DIC;

        $lang = $DIC->language();

        $oidc_settings = ilOpenIdConnectSettings::getInstance();
        if ($oidc_settings->getActive()) {
            $tpl = new ilTemplate('tpl.login_element.html', true, true, 'Services/OpenIdConnect');

            $lang->loadLanguageModule('auth');
            $tpl->setVariable('TXT_OIDCONNECT_HEADER', $lang->txt('auth_oidc_login_element_info'));

            $target = empty($_GET['target']) ? '' : ('?target=' . (string) $_GET['target']);
            switch ($oidc_settings->getLoginElementType()) {
                case ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_TXT:


                    $tpl->setVariable('SCRIPT_OIDCONNECT_T', ILIAS_HTTP_PATH . '/openidconnect.php' . $target);
                    $tpl->setVariable('TXT_OIDC', $oidc_settings->getLoginElemenText());
                    break;

                case ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG:
                    $tpl->setVariable('SCRIPT_OIDCONNECT_I', ILIAS_HTTP_PATH . '/openidconnect.php' . $target);
                    $tpl->setVariable('IMG_SOURCE', $oidc_settings->getImageFilePath());
                    break;
            }

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $tpl->get(),
                '[list-openid-connect-login]',
                'OPEN_ID_CONNECT_LOGIN_FORM'
            );
        }

        return $page_editor_html;
    }

    /**
     * do open id connect authentication
     */
    protected function doOpenIdConnectAuthentication()
    {
        global $DIC;

        $this->getLogger()->debug('Trying openid connect authentication');

        $credentials = new ilAuthFrontendCredentialsOpenIdConnect();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, AUTH_OPENID_CONNECT);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                include_once './Services/Init/classes/class.ilInitialisation.php';
                ilInitialisation::redirectToStartingPage();
                return;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilUtil::sendFailure($status->getTranslatedReason(), true);
                $GLOBALS['ilCtrl']->redirect($this, 'showLoginPage');
                return false;
        }

        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
        return false;
    }


    /**
     * @return bool
     */
    protected function doSamlAuthentication()
    {
        global $DIC;

        $this->getLogger()->debug('Trying saml authentication');

        $request = $DIC->http()->request();
        $params = $request->getQueryParams();

        $factory = new ilSamlAuthFactory();
        $auth = $factory->auth();

        if (isset($params['action']) && $params['action'] == 'logout') {
            $auth->logout(isset($params['logout_url']) ? $params['logout_url'] : '');
        }

        if (isset($params['target']) && !isset($params['returnTo'])) {
            $params['returnTo'] = $params['target'];
        }
        if (isset($params['returnTo'])) {
            $auth->storeParam('target', $params['returnTo']);
        }

        ilLoggerFactory::getLogger('auth')->debug('Started SAML authentication request');

        if (!$auth->isAuthenticated()) {
            ilLoggerFactory::getLogger('auth')->debug('User is not authenticated, yet');
            if (!isset($_GET['idpentityid']) || !isset($_GET['saml_idp_id'])) {
                $activeIdps = ilSamlIdp::getActiveIdpList();
                if (1 == count($activeIdps)) {
                    $idp = current($activeIdps);
                    $_GET['idpentityid'] = $idp->getEntityId();
                    $_GET['saml_idp_id'] = $idp->getIdpId();

                    ilLoggerFactory::getLogger('auth')->debug(sprintf(
                        'Found exactly one active IDP with id %s: %s',
                        $idp->getIdpId(),
                        $idp->getEntityId()
                    ));
                } elseif (0 == count($activeIdps)) {
                    ilLoggerFactory::getLogger('auth')->debug('Did not find any active IDP, skipp authentication process');
                    $GLOBALS['DIC']->ctrl()->redirect($this, 'showLoginPage');
                } else {
                    ilLoggerFactory::getLogger('auth')->debug('Found multiple active IPDs, presenting IDP selection...');
                    $this->showSamlIdpSelection($auth, $activeIdps);
                    return;
                }
            }

            $auth->storeParam('idpId', (int) $_GET['saml_idp_id']);
            ilLoggerFactory::getLogger('auth')->debug(sprintf(
                'Stored relevant IDP id in session: %s',
                (string) $auth->getParam('idpId')
            ));
        }

        // re-init
        $auth = $factory->auth();

        ilLoggerFactory::getLogger('auth')->debug('Checking SAML authentication status...');

        $auth->protectResource();

        ilLoggerFactory::getLogger('auth')->debug(
            'SAML authentication successful, continuing with ILIAS internal authentication process...'
        );

        $idpId = (int) $auth->getParam('idpId');

        ilLoggerFactory::getLogger('auth')->debug(sprintf(
            'Internal SAML IDP id fetched from session: %s',
            (string) $idpId
        ));

        if ($idpId < 1) {
            ilLoggerFactory::getLogger('auth')->debug(
                'No valid internal IDP id found (most probably due to IDP initiated SSO), trying fallback determination...'
            );
            $authData = $auth->getAuthDataArray();
            if (isset($authData['saml:sp:IdP'])) {
                $idpId = ilSamlIdp::geIdpIdByEntityId($authData['saml:sp:IdP']);
                ilLoggerFactory::getLogger('auth')->debug(sprintf(
                    'Searching active ILIAS IDP by entity id "%s" results in: %s',
                    $authData['saml:sp:IdP'],
                    (string) $idpId
                ));
            } else {
                ilLoggerFactory::getLogger('auth')->debug(
                    'Could not execute fallback determination, no IDP entity ID found SAML authentication session data'
                );
            }
        }
        $_GET['target'] = $auth->popParam('target');

        $_POST['auth_mode'] = AUTH_SAML . '_' . $idpId;

        $credentials = new ilAuthFrontendCredentialsSaml($auth);
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilUtil::stripSlashes($_POST['auth_mode']));

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                return ilInitialisation::redirectToStartingPage();

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                return $GLOBALS['DIC']->ctrl()->redirect($this, 'showAccountMigration');

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                ilUtil::sendFailure($status->getTranslatedReason(), true);
                $GLOBALS['DIC']->ctrl()->redirect($this, 'showLoginPage');
                return false;
        }

        ilUtil::sendFailure($this->lng->txt('err_wrong_login'));
        $this->showLoginPage();

        return false;
    }

    /**
     * @param \ilSamlAuth  $auth
     * @param \ilSamlIdp[] $idps
     */
    protected function showSamlIdpSelection(\ilSamlAuth $auth, array $idps)
    {
        global $DIC;

        self::initStartUpTemplate(array('tpl.saml_idp_selection.html', 'Services/Saml'));

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $DIC->ctrl()->setTargetScript('saml.php');

        $items = [];

        $table = new ilSamlIdpSelectionTableGUI($this, 'doSamlAuthentication');

        foreach ($idps as $idp) {
            $DIC->ctrl()->setParameter($this, 'saml_idp_id', $idp->getIdpId());
            $DIC->ctrl()->setParameter($this, 'idpentityid', urlencode($idp->getEntityId()));

            $items[] = [
                'idp_link' => $renderer->render($factory->link()->standard($idp->getEntityId(), $DIC->ctrl()->getLinkTarget($this, 'doSamlAuthentication')))
            ];
        }

        $table->setData($items);
        $this->mainTemplate->setVariable('CONTENT', $table->getHtml());

        $this->mainTemplate->printToStdout('DEFAULT', false);
    }
}
