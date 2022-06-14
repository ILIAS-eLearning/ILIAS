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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UICore\PageContentProvider;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * StartUp GUI class. Handles Login and Registration.
 * @author       Alex Killing <alex.killing@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilStartUpGUI: ilAccountRegistrationGUI, ilPasswordAssistanceGUI, ilLoginPageGUI, ilDashboardGUI
 * @ilCtrl_Calls ilStartUpGUI: ilMembershipOverviewGUI, ilDerivedTasksGUI, ilAccessibilityControlConceptGUI
 * @ingroup      ServicesInit
 */
class ilStartUpGUI implements ilCtrlBaseClassInterface, ilCtrlSecurityInterface
{
    protected const ACCOUNT_MIGRATION_MIGRATE = 1;
    protected const ACCOUNT_MIGRATION_NEW = 2;

    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilLogger $logger;
    protected ilGlobalTemplateInterface $mainTemplate;
    protected ilObjUser $user;
    protected ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation;
    protected ServerRequestInterface $httpRequest;
    protected \ILIAS\DI\Container $dic;
    protected ilAuthSession $authSession;
    protected ilAppEventHandler $eventHandler;
    protected ilSetting $setting;
    protected ilAccessHandler $access;

    protected RefineryFactory $refinery;
    protected HTTPServices $http;

    /**
     * ilStartUpGUI constructor.
     */
    public function __construct(
        ilObjUser $user = null,
        ilTermsOfServiceDocumentEvaluation $termsOfServiceEvaluation = null,
        ilGlobalTemplate $mainTemplate = null,
        ServerRequestInterface $httpRequest = null
    ) {
        global $DIC;

        $this->dic = $DIC;

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
        $this->authSession = $DIC['ilAuthSession'];
        $this->eventHandler = $DIC->event();
        $this->setting = $DIC->settings();
        $this->access = $DIC->access();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ctrl->saveParameter($this, array("rep_ref_id", "lang", "target", "client_id"));
        $this->user->setLanguage($this->lng->getLangKey());
    }

    protected function initTargetFromQuery() : string
    {
        if ($this->http->wrapper()->query()->has('target')) {
            return $this->http->wrapper()->query()->retrieve(
                'target',
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getUnsafeGetCommands() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSafePostCommands() : array
    {
        return [
            'doStandardAuthentication',
        ];
    }

    /**
     * execute command
     * @return mixed
     * @see register.php
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("processIndexPHP");
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
                    $this->$cmd();
                    return null;
                }
        }

        // because this class now implements ilCtrlSecurityInterface,
        // it may occur that commands are null, therefore I added
        // this as a fallback method.
        $this->showLoginPageOrStartupPage();
        return null;
    }

    /**
     * Get logger
     */
    public function getLogger() : ilLogger
    {
        return $this->logger;
    }

    /**
     * jump to registration gui
     * @see register.php
     */
    public function jumpToRegistration() : void
    {
        $this->ctrl->setCmdClass("ilaccountregistrationgui");
        $this->ctrl->setCmd("");
        $this->executeCommand();
    }

    /**
     * jump to password assistance
     * @see pwassist.php
     */
    public function jumpToPasswordAssistance() : void
    {
        $this->ctrl->setCmdClass("ilpasswordassistancegui");
        $this->ctrl->setCmd("");
        $this->executeCommand();
    }

    /**
     * Show login page or redirect to startup page if user is not authenticated.
     */
    protected function showLoginPageOrStartupPage() : void
    {
        /**
         * @var ilAuthSession
         */
        $auth_session = $this->authSession;
        $ilAppEventHandler = $this->eventHandler;

        $force_login = false;
        if (isset($_REQUEST['cmd']) &&
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
            $this->showLoginPage();
            return;
        }

        if ($auth_session->isValid()) {
            $this->logger->debug('Valid session -> redirect to starting page');
            ilInitialisation::redirectToStartingPage();
            return;
        }
        $this->logger->debug('No valid session -> show login');
        $this->showLoginPage();
    }

    /**
     * @param \ilPropertyFormGUI|null $form
     * @todo check for forced authentication like ecs, ...
     *       Show login page
     */
    protected function showLoginPage(ilPropertyFormGUI $form = null) : void
    {
        global $tpl;

        $this->getLogger()->debug('Showing login page');

        $extUid = '';
        if ($this->http->wrapper()->query()->has('ext_uid')) {
            $extUid = $this->http->wrapper()->query()->retrieve(
                'ext_uid',
                $this->refinery->kindlyTo()->string()
            );
        }
        $soapPw = '';
        if ($this->http->wrapper()->query()->has('soap_pw')) {
            $extUid = $this->http->wrapper()->query()->retrieve(
                'soap_pw',
                $this->refinery->kindlyTo()->string()
            );
        }
        $credentials = new ilAuthFrontendCredentialsSoap(
            $GLOBALS['DIC']->http()->request(),
            $this->ctrl,
            $this->setting
        );
        $credentials->setUsername($extUid);
        $credentials->setPassword($soapPw);
        $credentials->tryAuthenticationOnLoginPage();

        $frontend = new ilAuthFrontendCredentialsApache($this->httpRequest, $this->ctrl);
        $frontend->tryAuthenticationOnLoginPage();

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
        if ($this->authSession->isExpired()) {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('auth_err_expired'));
        }
        if ($page_editor_html !== '') {
            $tpl->setVariable('LPE', $page_editor_html);
        }
        $tosWithdrawalGui = new ilTermsOfServiceWithdrawalGUIHelper($this->user);
        $tosWithdrawalGui->setWithdrawalInfoForLoginScreen($this->httpRequest);
        self::printToGlobalTemplate($tpl);
    }

    /**
     * @param ilTemplate|ilGlobalTemplateInterface $tpl
     */
    public static function printToGlobalTemplate($tpl) : void
    {
        global $DIC;
        $gtpl = $DIC['tpl'];
        $gtpl->setContent($tpl->get());
        $gtpl->printToStdout("DEFAULT", false, true);
    }

    protected function showCodeForm($a_username = null, $a_form = null) : void
    {
        global $tpl;

        self::initStartUpTemplate("tpl.login_reactivate_code.html");
        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt("time_limit_reached"));
        if (!$a_form) {
            $a_form = $this->initCodeForm($a_username);
        }

        $tpl->setVariable("FORM", $a_form->getHTML());
        $tpl->printToStdout("DEFAULT", false);
    }

    protected function initCodeForm(string $a_username) : ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule("auth");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'showcodeform'));
        $form->setTitle($this->lng->txt('auth_account_code_title'));

        $count = new ilTextInputGUI($this->lng->txt('auth_account_code'), 'code');
        $count->setRequired(true);
        $count->setInfo($this->lng->txt('auth_account_code_info'));
        $form->addItem($count);

        // #11658
        $uname = new ilHiddenInputGUI("uname");
        $uname->setValue($a_username);
        $form->addItem($uname);
        $form->addCommandButton('processCode', $this->lng->txt('send'));
        return $form;
    }

    /**
     * @todo needs rafactoring
     */
    protected function processCode() : ?bool
    {
        $uname = $_POST["uname"];
        $form = $this->initCodeForm($uname);
        if ($uname && $form->checkInput()) {
            $code = $form->getInput("code");
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
                    // apply registration code tie limits
                    ilAccountCode::applyAccessLimits($user, $code);

                    $user->update();

                    $this->ctrl->setParameter($this, "cu", 1);
                    $this->lng->loadLanguageModule('auth');
                    $this->mainTemplate->setOnScreenMessage('success', $GLOBALS['DIC']->language()->txt('auth_activation_code_success'), true);
                    $this->ctrl->redirect($this, "showLoginPage");
                }
            }

            $this->lng->loadLanguageModule("user");
            $field = $form->getItemByPostVar("code");
            $field->setAlert($this->lng->txt("user_account_code_not_valid"));
        }

        $form->setValuesByPost();
        $this->showCodeForm($uname, $form);
        return null;
    }

    /**
     * Initialize the standard
     * @return \ilPropertyFormGUI
     */
    protected function initStandardLoginForm() : ilPropertyFormGUI
    {
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'doStandardAuthentication'));
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

            if (count($visible_auth_methods) === 1) {
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

        $form->addCommandButton("doStandardAuthentication", $this->lng->txt("log_in"));

        return $form;
    }

    /**
     * Trying shibboleth authentication
     */
    protected function doShibbolethAuthentication() : void
    {
        $this->getLogger()->debug('Trying shibboleth authentication');

        $credentials = new ilAuthFrontendCredentialsShibboleth();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_SHIBBOLETH);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();
                return;

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');
                return;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
                return;
        }
        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    protected function doCasAuthentication() : void
    {
        $this->getLogger()->debug('Trying cas authentication');
        $credentials = new ilAuthFrontendCredentialsCAS();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_CAS);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
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
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt($status->getReason()));
                $this->showLoginPage();
        }
    }

    /**
     * Handle lti requests
     */
    protected function doLTIAuthentication() : void
    {
        $this->getLogger()->debug('Trying lti authentication');

        $credentials = new ilAuthFrontendCredentialsLTI();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_PROVIDER_LTI);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
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
                $this->ctrl->redirect($this, 'showAccountMigration');
                return;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt($status->getReason()), true);
                $this->ctrl->redirect($this, 'showLoginPage');
                return;
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    /**
     * Try apache auth
     */
    protected function doApacheAuthentication() : void
    {
        $this->getLogger()->debug('Trying apache authentication');

        $credentials = new \ilAuthFrontendCredentialsApache($this->httpRequest, $this->ctrl);
        $credentials->initFromRequest();

        $provider_factory = new \ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_APACHE);

        $status = \ilAuthStatus::getInstance();

        $frontend_factory = new \ilAuthFrontendFactory();
        $frontend_factory->setContext(\ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case \ilAuthStatus::STATUS_AUTHENTICATED:
                if ($credentials->hasValidTargetUrl()) {
                    $this->logger->debug(sprintf(
                        'Authentication successful. Redirecting to starting page: %s',
                        $credentials->getTargetUrl()
                    ));
                    $this->ctrl->redirectToURL($credentials->getTargetUrl());
                } else {
                    $this->logger->debug(
                        'Authentication successful, but no valid target URL given. Redirecting to default starting page.'
                    );
                    \ilInitialisation::redirectToStartingPage();
                }
                break;

            case \ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');
                break;

            case \ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirectToURL(\ilUtil::appendUrlParameterString(
                    $this->ctrl->getLinkTarget($this, 'showLoginPage', '', false, false),
                    'passed_sso=1'
                ));
                break;
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    /**
     * Check form input; authenticate user
     */
    protected function doStandardAuthentication() : void
    {
        $form = $this->initStandardLoginForm();
        if ($form->checkInput()) {
            $this->getLogger()->debug('Trying to authenticate user.');

            $auth_callback = function () use ($form) {
                include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
                $credentials = new ilAuthFrontendCredentials();
                $credentials->setUsername($form->getInput('username'));
                $credentials->setPassword($form->getInput('password'));

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
                    $this->authSession,
                    $status,
                    $credentials,
                    $providers
                );

                $frontend->authenticate();

                return $status;
            };

            if (null !== ($auth_duration = $this->setting->get("auth_duration"))) {
                $duration = $this->http->durations()->callbackDuration((int) $auth_duration);
                $status = $duration->stretch($auth_callback);
            } else {
                $status = $auth_callback();
            }

            switch ($status->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                    ilInitialisation::redirectToStartingPage();
                    return;

                case ilAuthStatus::STATUS_CODE_ACTIVATION_REQUIRED:
                    $uname = ilObjUser::_lookupLogin($status->getAuthenticatedUserId());
                    $this->showLoginPage($this->initCodeForm($uname));
                    return;

                case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                    $this->ctrl->redirect($this, 'showAccountMigration');
                    // no break
                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                    $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason());
                    $this->showLoginPage($form);
                    return;
            }
        }
        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage($form);
    }

    /**
     * Show login form
     */
    protected function showLoginForm(string $page_editor_html, ilPropertyFormGUI $form = null) : string
    {
        global $tpl;

        // @todo move this to auth utils.
        // login via ILIAS (this also includes ldap)
        // If local authentication is enabled for shibboleth users, we
        // display the login form for ILIAS here.
        if (($this->setting->get("auth_mode") != ilAuthUtils::AUTH_SHIBBOLETH ||
                $this->setting->get("shib_auth_allow_local")) &&
            $this->setting->get("auth_mode") != ilAuthUtils::AUTH_CAS) {
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
     */
    protected function showLoginInformation(string $page_editor_html, ilGlobalTemplateInterface $tpl) : string
    {
        if (strlen($page_editor_html)) {
            // page editor active return
            return $page_editor_html;
        }

        $loginSettings = new ilSetting("login_settings");
        $information = $loginSettings->get("login_message_" . $this->lng->getLangKey());

        if (strlen(trim($information))) {
            $tpl->setVariable("TXT_LOGIN_INFORMATION", $information);
        }
        return $page_editor_html;
    }

    /**
     * Show cas login
     */
    protected function showCASLoginForm(string $page_editor_html) : string
    {
        // cas login link
        if ($this->setting->get("cas_active")) {
            $tpl = new ilTemplate('tpl.login_form_cas.html', true, true, 'Services/Init');
            $tpl->setVariable("TXT_CAS_LOGIN", $this->lng->txt("login_to_ilias_via_cas"));
            $tpl->setVariable("TXT_CAS_LOGIN_BUTTON", ilUtil::getImagePath("cas_login_button.png"));
            $tpl->setVariable("TXT_CAS_LOGIN_INSTRUCTIONS", $this->setting->get("cas_login_instructions"));
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
     */
    protected function showShibbolethLoginForm(string $page_editor_html) : string
    {
        $target = $this->initTargetFromQuery();

        // shibboleth login link
        if ($this->setting->get("shib_active")) {
            $tpl = new ilTemplate('tpl.login_form_shibboleth.html', true, true, 'Services/Init');

            $tpl->setVariable(
                'SHIB_FORMACTION',
                './shib_login.php'
            ); // Bugfix http://ilias.de/mantis/view.php?id=10662 {$tpl->setVariable('SHIB_FORMACTION', $this->ctrl->getFormAction($this));}
            $federation_name = $this->setting->get("shib_federation_name");
            $admin_mail = ' <a href="mailto:' . $this->setting->get("admin_email") . '">ILIAS ' . $this->lng->txt(
                "administrator"
            ) . '</a>.';
            if ($this->setting->get("shib_hos_type") == 'external_wayf') {
                $tpl->setCurrentBlock("shibboleth_login");
                $tpl->setVariable("TXT_SHIB_LOGIN", $this->lng->txt("login_to_ilias_via_shibboleth"));
                $tpl->setVariable("IL_TARGET", $target);
                $tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $this->setting->get("shib_federation_name"));
                $tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $this->setting->get("shib_login_button"));
                $tpl->setVariable(
                    "TXT_SHIB_LOGIN_INSTRUCTIONS",
                    sprintf(
                        $this->lng->txt("shib_general_login_instructions"),
                        $federation_name,
                        $admin_mail
                    )
                );
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $this->setting->get("shib_login_instructions"));
                $tpl->parseCurrentBlock();
            } elseif ($this->setting->get("shib_hos_type") == 'embedded_wayf') {
                $tpl->setCurrentBlock("shibboleth_custom_login");
                $customInstructions = stripslashes($this->setting->get("shib_login_instructions"));
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $customInstructions);
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("shibboleth_wayf_login");
                $tpl->setVariable("TXT_SHIB_LOGIN", $this->lng->txt("login_to_ilias_via_shibboleth"));
                $tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $this->setting->get("shib_federation_name"));
                $tpl->setVariable(
                    "TXT_SELECT_HOME_ORGANIZATION",
                    sprintf(
                        $this->lng->txt("shib_select_home_organization"),
                        $this->setting->get("shib_federation_name")
                    )
                );
                $tpl->setVariable("TXT_CONTINUE", $this->lng->txt("btn_next"));
                $tpl->setVariable("TXT_SHIB_HOME_ORGANIZATION", $this->lng->txt("shib_home_organization"));
                $tpl->setVariable(
                    "TXT_SHIB_LOGIN_INSTRUCTIONS",
                    sprintf(
                        $this->lng->txt("shib_general_wayf_login_instructions"),
                        $admin_mail
                    )
                );
                $tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $this->setting->get("shib_login_instructions"));

                $ilShibbolethWAYF = new ilShibbolethWAYF();

                $tpl->setVariable("TXT_SHIB_INVALID_SELECTION", $ilShibbolethWAYF->showNotice());
                $tpl->setVariable("SHIB_IDP_LIST", $ilShibbolethWAYF->generateSelection());
                $tpl->setVariable("ILW_TARGET", $target);
                $tpl->parseCurrentBlock();
            }

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $tpl->get(),
                '[list-shibboleth-login-form]',
                'SHIB_LOGIN_FORM'
            );
        }

        return $page_editor_html;
    }

    /**
     * Substitute login page elements
     * @param ilTemplate|ilGlobalTemplateInterface $tpl
     * @param string                               $page_editor_html
     * @param string                               $element_html
     * @param string                               $placeholder
     * @param string                               $fallback_tplvar
     * return string $page_editor_html
     */
    protected function substituteLoginPageElements(
        $tpl,
        string $page_editor_html,
        string $element_html,
        string $placeholder,
        string $fallback_tplvar
    ) : string {
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
    protected function getLoginPageEditorHTML() : string
    {
        include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorSettings.php';
        $lpe = ilAuthLoginPageEditorSettings::getInstance();
        $active_lang = $lpe->getIliasEditorLanguage($this->lng->getLangKey());

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
        $page_gui->setStyleId(0);

        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $ret = $page_gui->showPage();

        return $ret;
    }

    /**
     * Show registration, password forgotten, client slection links
     */
    protected function showRegistrationLinks(string $page_editor_html) : string
    {
        global $tpl, $ilIliasIniFile;

        $rtpl = new ilTemplate('tpl.login_registration_links.html', true, true, 'Services/Init');

        // allow new registrations?
        include_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
        if (ilRegistrationSettings::_lookupRegistrationType() != ilRegistrationSettings::IL_REG_DISABLED) {
            $rtpl->setCurrentBlock("new_registration");
            $rtpl->setVariable("REGISTER", $this->lng->txt("registration"));
            $rtpl->setVariable(
                "CMD_REGISTER",
                $this->ctrl->getLinkTargetByClass("ilaccountregistrationgui", "")
            );
            $rtpl->parseCurrentBlock();
        }
        // allow password assistance? Surpress option if Authmode is not local database
        if ($this->setting->get("password_assistance")) {
            $rtpl->setCurrentBlock("password_assistance");
            $rtpl->setVariable("FORGOT_PASSWORD", $this->lng->txt("forgot_password"));
            $rtpl->setVariable("FORGOT_USERNAME", $this->lng->txt("forgot_username"));
            $rtpl->setVariable(
                "CMD_FORGOT_PASSWORD",
                $this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "")
            );
            $rtpl->setVariable(
                "CMD_FORGOT_USERNAME",
                $this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "showUsernameAssistanceForm")
            );
            $rtpl->setVariable("LANG_ID", $this->lng->getLangKey());
            $rtpl->parseCurrentBlock();
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME']) &&
            $this->access->checkAccessOfUser(ANONYMOUS_USER_ID, "read", "", ROOT_FOLDER_ID)) {
            $rtpl->setCurrentBlock("homelink");
            $rtpl->setVariable(
                "CLIENT_ID",
                "?client_id=" . $_COOKIE["ilClientId"] . "&lang=" . $this->lng->getLangKey()
            );
            $rtpl->setVariable("TXT_HOME", $this->lng->txt("home"));
            $rtpl->parseCurrentBlock();
        }

        if ($ilIliasIniFile->readVariable("clients", "list")) {
            $rtpl->setCurrentBlock("client_list");
            $rtpl->setVariable("TXT_CLIENT_LIST", $this->lng->txt("to_client_list"));
            $rtpl->setVariable("CMD_CLIENT_LIST", $this->ctrl->getLinkTarget($this, "showClientList"));
            $rtpl->parseCurrentBlock();
        }

        return $this->substituteLoginPageElements(
            $tpl,
            $page_editor_html,
            $rtpl->get(),
            '[list-registration-link]',
            'REG_PWD_CLIENT_LINKS'
        );
    }

    /**
     * Show terms of service link
     */
    protected function showTermsOfServiceLink(string $page_editor_html) : string
    {
        global $tpl;

        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        $helper = new ilTermsOfServiceHelper();
        if ($helper->isGloballyEnabled() && $this->termsOfServiceEvaluation->hasDocument()) {
            $utpl = new ilTemplate('tpl.login_terms_of_service_link.html', true, true, 'Services/Init');
            $utpl->setVariable('TXT_TERMS_OF_SERVICE', $this->lng->txt('usr_agreement'));
            $utpl->setVariable('LINK_TERMS_OF_SERVICE', $this->ctrl->getLinkTarget($this, 'showTermsOfService'));

            return $this->substituteLoginPageElements(
                $tpl,
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
     */
    protected function purgePlaceholders(string $page_editor_html) : string
    {
        return str_replace(
            array(
                '[list-language-selection] ',
                '[list-registration-link]',
                '[list-user-agreement]',
                '[list-login-form]',
                '[list-cas-login-form]',
                '[list-saml-login]',
                '[list-shibboleth-login-form]'
            ),
            array('', '', '', '', '', '', ''),
            $page_editor_html
        );
    }

    /**
     * Show account migration screen
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
        $user->setValue(
            ilLegacyFormElementsUtil::prepareFormOutput(
                (string) ($this->httpRequest->getParsedBody()['mig_username'] ?? '')
            )
        );
        $user->setSize(32);
        $user->setMaxLength(128);
        $keep->addSubItem($user);

        $pass = new ilPasswordInputGUI($this->lng->txt('password'), 'mig_password');
        $pass->setRetype(false);
        $pass->setRequired(true);
        $pass->setValue(
            ilLegacyFormElementsUtil::prepareFormOutput(
                (string) ($this->httpRequest->getParsedBody()['mig_password'] ?? '')
            )
        );
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
            $this->mainTemplate->setOnScreenMessage('failure', $message);
        }

        self::printToGlobalTemplate($tpl);
    }

    protected function migrateAccount() : void
    {
        if (!isset($this->httpRequest->getParsedBody()['account_migration'])) {
            $this->showAccountMigration(
                $this->lng->txt('select_one')
            );
            return;
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
            return;
        }

        if ((int) $this->httpRequest->getParsedBody()['account_migration'] == self::ACCOUNT_MIGRATION_MIGRATE) {
            $this->doMigration();
            return;
        } elseif ((int) $this->httpRequest->getParsedBody()['account_migration'] == static::ACCOUNT_MIGRATION_NEW) {
            $this->doMigrationNewAccount();
            return;
        }
        return;
    }

    protected function doMigrationNewAccount() : bool
    {
        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername(ilSession::get(ilAuthFrontend::MIG_EXTERNAL_ACCOUNT));

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode(
            $credentials,
            ilSession::get(ilAuthFrontend::MIG_TRIGGER_AUTHMODE)
        );

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

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->ctrl->redirect($this, 'showAccountMigration');

        return true;
    }

    protected function doMigration() : bool
    {
        $username = '';
        if ($this->http->wrapper()->post()->has('mig_username')) {
            $username = $this->http->wrapper()->post()->retrieve(
                'mig_username',
                $this->refinery->kindlyTo()->string()
            );
        }
        $password = '';
        if ($this->http->wrapper()->post()->has('mig_password')) {
            $password = $this->http->wrapper()->post()->retrieve(
                'mig_password',
                $this->refinery->kindlyTo()->string()
            );
        }

        $this->logger->debug('Starting account migration for user: ' . ilSession::get('mig_ext_account'));

        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername($username);
        $credentials->setPassword($password);

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_LOCAL);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->getLogger()->debug('Account migration: authentication successful for ' . $username);

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
                    $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'), true);
                    $this->ctrl->redirect($this, 'showAccountMigration');
                }
                break;

            default:
                $this->getLogger()->info('Account migration failed for user ' . $username);
                $this->showAccountMigration($GLOBALS['lng']->txt('err_wrong_login'));
                return false;
        }
    }

    /**
     * Show logout screen
     */
    protected function showLogout() : void
    {
        global $DIC;

        $ilIliasIniFile = $DIC['ilIliasIniFile'];

        $tpl = self::initStartUpTemplate("tpl.logout.html");
        $client_id = '';
        if ($this->http->wrapper()->query()->has('client_id')) {
            $client_id = $this->http->wrapper()->query()->retrieve(
                'client_id',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            $tpl->setCurrentBlock("homelink");
            $tpl->setVariable("CLIENT_ID", "?client_id=" . $client_id . "&lang=" . $this->lng->getLangKey());
            $tpl->setVariable("TXT_HOME", $this->lng->txt("home"));
            $tpl->parseCurrentBlock();
        }

        if ($ilIliasIniFile->readVariable("clients", "list")) {
            $tpl->setCurrentBlock("client_list");
            $tpl->setVariable("TXT_CLIENT_LIST", $this->lng->txt("to_client_list"));
            $this->ctrl->setParameter($this, "client_id", $client_id);
            $tpl->setVariable(
                "CMD_CLIENT_LIST",
                $this->ctrl->getLinkTarget($this, "showClientList")
            );
            $tpl->parseCurrentBlock();
            $this->ctrl->setParameter($this, "client_id", "");
        }

        $tosWithdrawalGui = new ilTermsOfServiceWithdrawalGUIHelper($this->user);

        $tpl->setVariable("TXT_PAGEHEADLINE", $this->lng->txt("logout"));
        $tpl->setVariable(
            "TXT_LOGOUT_TEXT",
            $this->lng->txt("logout_text") . $tosWithdrawalGui->getWithdrawalTextForLogoutScreen($this->httpRequest)
        );
        $tpl->setVariable("TXT_LOGIN", $this->lng->txt("login_to_ilias"));
        $tpl->setVariable(
            "CLIENT_ID",
            "?client_id=" . $client_id . "&cmd=force_login&lang=" . $this->lng->getLangKey()
        );

        self::printToGlobalTemplate($tpl);
    }

    /**
     * show logout screen
     */
    public function doLogout() : void
    {
        global $DIC;

        $ilIliasIniFile = $DIC['ilIliasIniFile'];

        $this->eventHandler->raise(
            'Services/Authentication',
            'beforeLogout',
            [
                'user_id' => $this->user->getId()
            ]
        );

        $user_language = $this->user->getLanguage();

        $tosWithdrawalGui = new ilTermsOfServiceWithdrawalGUIHelper($this->user);
        $tosWithdrawalGui->handleWithdrawalLogoutRequest($this->httpRequest, $this);

        $had_external_authentication = ilSession::get('used_external_auth');

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->authSession->logout();
        $this->eventHandler->raise(
            'Services/Authentication',
            'afterLogout',
            array(
                'username' => $this->user->getLogin()
            )
        );
        if ((int) $this->user->getAuthMode(true) == ilAuthUtils::AUTH_SAML && $had_external_authentication) {
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
     * show help screen, if cookies are disabled
     * to do: link to online help here
     */
    public function showNoCookiesScreen() : void
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
    protected function getAcceptance() : void
    {
        $this->showTermsOfService();
    }

    protected function confirmAcceptance() : void
    {
        $this->showTermsOfService(true);
    }

    protected function confirmWithdrawal() : void
    {
        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }
        $back_to_login = false;
        if ($this->user->getPref('consent_withdrawal_requested') != 1) {
            $back_to_login = true;
        }
        $tpl = self::initStartUpTemplate('tpl.view_terms_of_service.html', $back_to_login, !$back_to_login);

        $helper = new ilTermsOfServiceHelper();
        $handleDocument = $helper->isGloballyEnabled() && $this->termsOfServiceEvaluation->hasDocument();
        if ($handleDocument) {
            $document = $this->termsOfServiceEvaluation->document();
            if ('confirmWithdrawal' === $this->ctrl->getCmd()) {
                if (isset($this->httpRequest->getParsedBody()['status']) && 'withdrawn' === $this->httpRequest->getParsedBody()['status']) {
                    $helper->deleteAcceptanceHistoryByUser($this->user->getId());
                    $this->ctrl->redirectToUrl('logout.php');
                }
            }

            $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, $this->ctrl->getCmd()));
            $tpl->setVariable('ACCEPT_CHECKBOX', ilLegacyFormElementsUtil::formCheckbox(0, 'status', 'accepted'));
            $tpl->setVariable('ACCEPT_TERMS_OF_SERVICE', $this->lng->txt('accept_usr_agreement'));
            $tpl->setVariable('TXT_SUBMIT', $this->lng->txt('submit'));

            $tpl->setPermanentLink('usr', null, 'agreement');
            $tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $document->content());
        } else {
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

        self::printToGlobalTemplate($tpl);
    }

    /**
     * Show terms of service
     * @param bool $accepted
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     * @throws ilTermsOfServiceNoSignableDocumentFoundException
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    protected function showTermsOfService(bool $accepted = false) : void
    {
        $back_to_login = ('getAcceptance' !== $this->ctrl->getCmd());
        $target = $this->initTargetFromQuery();

        if (!$this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        $tpl = self::initStartUpTemplate('tpl.view_terms_of_service.html', $back_to_login, !$back_to_login);

        $helper = new ilTermsOfServiceHelper();
        $handleDocument = $helper->isGloballyEnabled() && $this->termsOfServiceEvaluation->hasDocument();
        if ($handleDocument) {
            $document = $this->termsOfServiceEvaluation->document();
            if (
                'confirmAcceptance' === $this->ctrl->getCmd() ||
                'getAcceptance' === $this->ctrl->getCmd()
            ) {
                if ($accepted) {
                    $helper->trackAcceptance($this->user, $document);

                    if (ilSession::get('orig_request_target')) {
                        $target = ilSession::get('orig_request_target');
                        ilSession::set('orig_request_target', '');
                        $this->ctrl->redirectToURL($target);
                    } else {
                        $this->ctrl->redirectToURL('index.php?target=' . $target . '&client_id=' . CLIENT_ID);
                    }
                }

                $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, $this->ctrl->getCmd()));
                $tpl->setVariable('ACCEPT_TERMS_OF_SERVICE', $this->lng->txt('accept_usr_agreement'));
                $tpl->setVariable('TXT_ACCEPT', $this->lng->txt('accept_usr_agreement_btn'));
                $tpl->setVariable('DENY_TERMS_OF_SERVICE', $this->lng->txt('deny_usr_agreement'));
                $tpl->setVariable(
                    'DENIAL_BUTTON',
                    $this->dic->ui()->renderer()->render(
                        $this->dic->ui()->factory()->button()->standard(
                            $this->dic->language()->txt('deny_usr_agreement_btn'),
                            'logout.php?withdraw_consent'
                        )
                    )
                );
            }

            $tpl->setPermanentLink('usr', 0, 'agreement');
            $tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $document->content());
        } else {
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

        self::printToGlobalTemplate($tpl);
    }

    /**
     * process index.php
     */
    protected function processIndexPHP() : void
    {
        global $ilIliasIniFile, $ilAuth, $ilSetting;

        // In case of an valid session, redirect to starting page
        if ($this->authSession->isValid()) {
            ilInitialisation::redirectToStartingPage();
            return;
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            ilInitialisation::goToPublicSection();
        }

        // otherwise show login page
        $this->showLoginPage();
    }

    /**
     * Return type depends on _checkGoto calls
     * @return bool|mixed
     */
    public static function _checkGoto($a_target)
    {
        global $DIC;
        global $objDefinition, $ilUser;
        $component_factory = $DIC["component.factory"];

        $access = $DIC->access();

        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->checkGotoHook($a_target);
            if ($resp["target"] !== false) {
                $a_target = $resp["target"];
                break;
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
                    $participants = ilCourseParticipant::_getInstanceByObjId($pobj_id, $ilUser->getId());
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

    public function confirmRegistration() : void
    {
        ilUtil::setCookie('iltest', 'cookie', false);
        $regitration_hash = '';
        if ($this->http->wrapper()->query()->has('rh')) {
            $regitration_hash = $this->http->wrapper()->query()->retrieve(
                'rh',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!strlen($regitration_hash) || !strlen(trim($regitration_hash))) {
            $this->ctrl->redirectToURL('./login.php?cmd=force_login&reg_confirmation_msg=reg_confirmation_hash_not_passed');
        }

        try {
            $oRegSettings = new ilRegistrationSettings();

            $usr_id = ilObjUser::_verifyRegistrationHash(trim($regitration_hash));
            /** @var \ilObjUser $user */
            $user = ilObjectFactory::getInstanceByObjId($usr_id);
            $user->setActive(true);
            $password = '';
            if ($oRegSettings->passwordGenerationEnabled()) {
                $passwords = ilSecuritySettingsChecker::generatePasswords(1);
                $password = $passwords[0];
                $user->setPasswd($password, ilObjUser::PASSWD_PLAIN);
                $user->setLastPasswordChangeTS(time());
            }
            $user->update();

            $target = $user->getPref('reg_target');
            if (strlen($target) > 0) {
                // Used for ilAccountMail in ilAccountRegistrationMail, which relies on this super global ...
                // @todo: fixme
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
     * @param mixed $a_tmpl The template file as a string of as an array (index 0: template file, index 1: template directory)
     * @param bool  $a_show_back
     * @param bool  $a_show_logout
     */
    public static function initStartUpTemplate($a_tmpl, bool $a_show_back = false, bool $a_show_logout = false) : ilGlobalTemplateInterface
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

        $header_title = ilObjSystemFolder::_getHeaderTitle();
        PageContentProvider::setTitle($header_title);

        return $tpl;
    }

    protected function showSamlLoginForm(string $page_editor_html) : string
    {
        require_once 'Services/Saml/classes/class.ilSamlIdp.php';
        require_once 'Services/Saml/classes/class.ilSamlSettings.php';

        if (count(ilSamlIdp::getActiveIdpList()) > 0 && ilSamlSettings::getInstance()->isDisplayedOnLoginPage()) {
            $tpl = new ilTemplate('tpl.login_form_saml.html', true, true, 'Services/Saml');

            $return = '';
            $target = $this->initTargetFromQuery();
            if (strlen($target)) {
                $return = '?returnTo=' . urlencode(ilUtil::stripSlashes($target));
            }

            $tpl->setVariable('SAML_SCRIPT_URL', './saml.php' . $return);
            $tpl->setVariable('TXT_LOGIN', $this->lng->txt('saml_log_in'));
            $tpl->setVariable('LOGIN_TO_ILIAS_VIA_SAML', $this->lng->txt('login_to_ilias_via_saml'));
            $tpl->setVariable('TXT_SAML_LOGIN_TXT', $this->lng->txt('saml_login_form_txt'));
            $tpl->setVariable('TXT_SAML_LOGIN_INFO_TXT', $this->lng->txt('saml_login_form_info_txt'));

            return $this->substituteLoginPageElements(
                $GLOBALS['tpl'],
                $page_editor_html,
                $tpl->get(),
                '[list-saml-login]',
                'SAML_LOGIN_FORM'
            );
        }

        return $page_editor_html;
    }

    protected function showOpenIdConnectLoginForm(string $page_editor_html) : string
    {
        $oidc_settings = ilOpenIdConnectSettings::getInstance();
        if ($oidc_settings->getActive()) {
            $tpl = new ilTemplate('tpl.login_element.html', true, true, 'Services/OpenIdConnect');

            $this->lng->loadLanguageModule('auth');
            $tpl->setVariable('TXT_OIDCONNECT_HEADER', $this->lng->txt('auth_oidc_login_element_info'));

            $target = $this->initTargetFromQuery();
            $target_str = empty($target) ? '' : ('?target=' . $target);
            switch ($oidc_settings->getLoginElementType()) {
                case ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_TXT:
                    $tpl->setVariable('SCRIPT_OIDCONNECT_T', ILIAS_HTTP_PATH . '/openidconnect.php' . $target_str);
                    $tpl->setVariable('TXT_OIDC', $oidc_settings->getLoginElemenText());
                    break;

                case ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG:
                    $tpl->setVariable('SCRIPT_OIDCONNECT_I', ILIAS_HTTP_PATH . '/openidconnect.php' . $target_str);
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
    protected function doOpenIdConnectAuthentication() : void
    {
        $this->getLogger()->debug('Trying openid connect authentication');

        $credentials = new ilAuthFrontendCredentialsOpenIdConnect();
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_OPENID_CONNECT);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            array($provider)
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                if ($credentials->getRedirectionTarget()) {
                    ilInitialisation::redirectToStartingPage($credentials->getRedirectionTarget());
                } else {
                    ilInitialisation::redirectToStartingPage();
                }
                return;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
                return;
        }
        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    protected function doSamlAuthentication() : void
    {
        $this->getLogger()->debug('Trying saml authentication');
        $request = $this->httpRequest;
        $params = $request->getQueryParams();

        $factory = new ilSamlAuthFactory();
        $auth = $factory->auth();

        if (isset($params['action']) && $params['action'] === 'logout') {
            $auth->logout($params['logout_url'] ?? '');
        }

        if (isset($params['target']) && !isset($params['returnTo'])) {
            $params['returnTo'] = $params['target'];
        }
        if (isset($params['returnTo'])) {
            $auth->storeParam('target', $params['returnTo']);
        }

        $this->logger->debug('Started SAML authentication request');
        if (!$auth->isAuthenticated()) {
            ilLoggerFactory::getLogger('auth')->debug('User is not authenticated, yet');
            if (!isset($request->getQueryParams()['idpentityid'], $request->getQueryParams()['saml_idp_id'])) {
                $activeIdps = ilSamlIdp::getActiveIdpList();
                if (1 === count($activeIdps)) {
                    $idp = current($activeIdps);

                    ilLoggerFactory::getLogger('auth')->debug(sprintf(
                        'Found exactly one active IDP with id %s: %s',
                        $idp->getIdpId(),
                        $idp->getEntityId()
                    ));

                    $this->ctrl->setParameter($this, 'idpentityid', $idp->getEntityId());
                    $this->ctrl->setParameter($this, 'saml_idp_id', $idp->getIdpId());
                    $this->ctrl->setTargetScript('saml.php');
                    $this->ctrl->redirect($this, 'doSamlAuthentication');
                } elseif ($activeIdps === []) {
                    $this->logger->debug('Did not find any active IDP, skipp authentication process');
                    $this->ctrl->redirect($this, 'showLoginPage');
                } else {
                    $this->logger->debug('Found multiple active IPDs, presenting IDP selection...');
                    $this->showSamlIdpSelection($auth, $activeIdps);
                    return;
                }
            }

            $auth->storeParam('idpId', (int) $request->getQueryParams()['saml_idp_id']);
            $this->logger->debug(sprintf(
                'Stored relevant IDP id in session: %s',
                (string) $auth->getParam('idpId')
            ));
        }

        // re-init
        $auth = $factory->auth();

        $this->logger->debug('Checking SAML authentication status...');
        $auth->protectResource();
        $this->logger->debug(
            'SAML authentication successful, continuing with ILIAS internal authentication process...'
        );

        $idpId = (int) $auth->getParam('idpId');

        $this->logger->debug(sprintf(
            'Internal SAML IDP id fetched from session: %s',
            (string) $idpId
        ));

        if ($idpId < 1) {
            $this->logger->debug(
                'No valid internal IDP id found (most probably due to IDP initiated SSO), trying fallback determination...'
            );
            $authData = $auth->getAuthDataArray();
            if (isset($authData['saml:sp:IdP'])) {
                $idpId = ilSamlIdp::geIdpIdByEntityId($authData['saml:sp:IdP']);
                $this->logger->debug(sprintf(
                    'Searching active ILIAS IDP by entity id "%s" results in: %s',
                    $authData['saml:sp:IdP'],
                    (string) $idpId
                ));
            } else {
                $this->logger->debug(
                    'Could not execute fallback determination, no IDP entity ID found SAML authentication session data'
                );
            }
        }

        $target = $auth->popParam('target');

        $credentials = new ilAuthFrontendCredentialsSaml($auth, $request);
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilUtil::stripSlashes(
            ilAuthUtils::AUTH_SAML . '_' . $idpId
        ));

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            [$provider]
        );

        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage($target);
                return;

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');
                return;

            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
                return;
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    /**
     * @param ilSamlAuth $auth
     * @param ilSamlIdp[] $idps
     */
    protected function showSamlIdpSelection(ilSamlAuth $auth, array $idps) : void
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
                'idp_link' => $renderer->render(
                    $factory->link()->standard(
                        $idp->getEntityId(),
                        $this->ctrl->getLinkTarget($this, 'doSamlAuthentication')
                    )
                )
            ];
        }

        $table->setData($items);
        $this->mainTemplate->setVariable('CONTENT', $table->getHtml());
        $this->mainTemplate->printToStdout('DEFAULT', false);
    }
}
