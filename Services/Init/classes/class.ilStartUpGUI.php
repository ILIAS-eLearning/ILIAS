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

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UICore\PageContentProvider;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\TermsOfService\Consumer as TermsOfService;
use ILIAS\DataProtection\Consumer as DataProtection;

/**
 * @ilCtrl_Calls ilStartUpGUI: ilAccountRegistrationGUI, ilPasswordAssistanceGUI, ilLoginPageGUI, ilDashboardGUI
 * @ilCtrl_Calls ilStartUpGUI: ilMembershipOverviewGUI, ilDerivedTasksGUI, ilAccessibilityControlConceptGUI
 */
class ilStartUpGUI implements ilCtrlBaseClassInterface, ilCtrlSecurityInterface
{
    private const PROP_USERNAME = 'username';
    private const PROP_PASSWORD = 'password';
    private const PROP_AUTH_MODE = 'auth_mode';
    private const PROP_CODE = 'code';
    private const PROP_ACCOUNT_MIGRATION = 'account_migration';
    private const PROP_ACCOUNT_MIGRATION_NEW = 'account_migration_new';
    private const PROP_ACCOUNT_MIGRATION_MIGRATE = 'account_migration_migrate';

    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilLogger $logger;
    private ilGlobalTemplateInterface $mainTemplate;
    private ilObjUser $user;
    private ServerRequestInterface $httpRequest;
    private ILIAS\DI\Container $dic;
    private ilAuthSession $authSession;
    private ilAppEventHandler $eventHandler;
    private ilSetting $setting;
    private ilAccessHandler $access;

    private RefineryFactory $refinery;
    private HTTPServices $http;
    private ilHelpGUI $help;
    private ILIAS\UI\Factory $ui_factory;
    private ILIAS\UI\Renderer $ui_renderer;

    public function __construct(
        ilObjUser $user = null,
        ilGlobalTemplateInterface $mainTemplate = null,
        ServerRequestInterface $httpRequest = null
    ) {
        global $DIC;

        $this->dic = $DIC;

        $this->user = $user ?? $DIC->user();
        $this->mainTemplate = $mainTemplate ?? $DIC->ui()->mainTemplate();
        $this->httpRequest = $httpRequest ?? $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('auth');
        $this->logger = ilLoggerFactory::getLogger('init');
        $this->authSession = $DIC['ilAuthSession'];
        $this->eventHandler = $DIC->event();
        $this->setting = $DIC->settings();
        $this->access = $DIC->access();
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->ctrl->saveParameter($this, ['rep_ref_id', 'lang', 'target', 'client_id']);
        $this->user->setLanguage($this->lng->getLangKey());
        $this->help->setScreenIdComponent('init');
    }

    private function mergeValuesTrafo(): ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static function (array $values): array {
            return array_merge(...$values);
        });
    }

    private function saniziteArrayElementsTrafo(): ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static function (array $values): array {
            $processed_values = array_merge(
                ilArrayUtil::stripSlashesRecursive($values),
                isset($values[self::PROP_PASSWORD]) ? [self::PROP_PASSWORD => $values[self::PROP_PASSWORD]] : []
            );

            return $processed_values;
        });
    }

    private function initTargetFromQuery(): string
    {
        return $this->http->wrapper()->query()->retrieve(
            'target',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            'doLogout'
        ];
    }

    public function getSafePostCommands(): array
    {
        return [
            'doStandardAuthentication',
        ];
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('processIndexPHP');
        $next_class = $this->ctrl->getNextClass($this) ?? '';

        switch (strtolower($next_class)) {
            case strtolower(ilLoginPageGUI::class):
                break;

            case strtolower(ilAccountRegistrationGUI::class):
                $this->ctrl->forwardCommand(new ilAccountRegistrationGUI());
                return;

            case strtolower(ilPasswordAssistanceGUI::class):
                $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());
                return;

            case strtolower(ilAccessibilityControlConceptGUI::class):
                $this->ctrl->forwardCommand(new ilAccessibilityControlConceptGUI());
                return;

            default:
                if (method_exists($this, $cmd)) {
                    $this->$cmd();
                    return;
                }
        }

        // because this class now implements ilCtrlSecurityInterface,
        // it may occur that commands are null, therefore I added
        // this as a fallback method.
        $this->showLoginPageOrStartupPage();
    }

    private function getLogger(): ilLogger
    {
        return $this->logger;
    }

    private function jumpToRegistration(): void
    {
        $this->ctrl->setCmdClass(ilAccountRegistrationGUI::class);
        $this->ctrl->setCmd('');
        $this->executeCommand();
    }

    private function jumpToPasswordAssistance(): void
    {
        $this->ctrl->setCmdClass(ilPasswordAssistanceGUI::class);
        $this->ctrl->setCmd('');
        $this->executeCommand();
    }

    private function showLoginPageOrStartupPage(): void
    {
        $auth_session = $this->authSession;
        $ilAppEventHandler = $this->eventHandler;

        $force_login = false;
        $request_cmd = $this->http->wrapper()->query()->retrieve(
            'cmd',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        if ($request_cmd === 'force_login') {
            $force_login = true;
        }

        if ($force_login) {
            $this->logger->debug('Force login');
            if ($auth_session->isValid()) {
                $messages = $this->retrieveMessagesFromSession();
                $this->logger->debug('Valid session -> logout current user');
                ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
                $auth_session->logout();

                $ilAppEventHandler->raise(
                    'Services/Authentication',
                    'afterLogout',
                    [
                        'username' => $this->user->getLogin(),
                        'is_explicit_logout' => false,
                    ]
                );
            }
            $this->logger->debug('Show login page');
            if (isset($messages) && count($messages) > 0) {
                foreach ($messages as $type => $content) {
                    $this->mainTemplate->setOnScreenMessage($type, $content);
                }
            }

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

    private function showLoginPage(ILIAS\UI\Component\Input\Container\Form\Form $form = null): void
    {
        global $tpl; // Don't remove this, the global variables will be replaced with a ilGlobalTemplate instnace

        $this->help->setSubScreenId('login');

        $this->getLogger()->debug('Showing login page');

        $extUid = $this->http->wrapper()->query()->retrieve(
            'ext_uid',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );
        $soapPw = $this->http->wrapper()->query()->retrieve(
            'soap_pw',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );
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

        $tpl = self::initStartUpTemplate('tpl.login.html');
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
        $page_editor_html = $this->showLegalDocumentsLinks($page_editor_html);
        $page_editor_html = $this->purgePlaceholders($page_editor_html);

        // check expired session and send message
        if ($this->authSession->isExpired() || $this->http->wrapper()->query()->has('session_expired')) {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('auth_err_expired'));
        } elseif ($this->http->wrapper()->query()->has('reg_confirmation_msg')) {
            $this->lng->loadLanguageModule('registration');
            $message_key = $this->http->wrapper()->query()->retrieve(
                'reg_confirmation_msg',
                $this->refinery->kindlyTo()->string()
            );
            $message_type = $message_key === 'reg_account_confirmation_successful' ?
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS : ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE;
            $this->mainTemplate->setOnScreenMessage(
                $message_type,
                $this->lng->txt($message_key)
            );
        }
        if ($page_editor_html !== '') {
            $tpl->setVariable('LPE', $page_editor_html);
        }

        self::printToGlobalTemplate($tpl);
    }

    /**
     * @param ilTemplate|ilGlobalTemplateInterface $tpl
     */
    public static function printToGlobalTemplate($tpl): void
    {
        global $DIC;
        $gtpl = $DIC['tpl'];
        $gtpl->setContent($tpl->get());
        $gtpl->printToStdout('DEFAULT', false, true);
    }

    /**
     * @return array<string, string>
     */
    private function retrieveMessagesFromSession(): array
    {
        $messages = [];
        $message_types = [
            ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
            ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
            ilGlobalTemplateInterface::MESSAGE_TYPE_INFO,
            ilGlobalTemplateInterface::MESSAGE_TYPE_QUESTION
        ];

        foreach ($message_types as $message_type) {
            if (ilSession::get($message_type)) {
                $messages[$message_type] = ilSession::get($message_type);
            }
        }

        return $messages;
    }

    private function showCodeForm(
        string $username = null,
        ILIAS\UI\Component\Input\Container\Form\Form $form = null
    ): void {
        $this->help->setSubScreenId('code_input');

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('time_limit_reached'));

        $tpl = self::initStartUpTemplate('tpl.login_reactivate_code.html');
        $tpl->setVariable('FORM', $this->ui_renderer->render($form ?? $this->buildCodeForm($username)));
        self::printToGlobalTemplate($tpl);
    }

    private function buildCodeForm(string $username = null): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $this->lng->loadLanguageModule('auth');

        $field_factory = $this->ui_factory->input()->field();

        $username_field = $field_factory
            ->hidden()
            ->withRequired(true);
        if ($username !== null) {
            $username_field = $username_field->withValue($username);
        }

        return $this->ui_factory->input()
                                ->container()
                                ->form()
                                ->standard(
                                    $this->ctrl->getFormAction($this, 'processCode'),
                                    [
                                        $field_factory->section(
                                            [
                                                self::PROP_CODE => $field_factory
                                                    ->text(
                                                        $this->lng->txt('auth_account_code'),
                                                        $this->lng->txt('auth_account_code_info')
                                                    )
                                                    ->withRequired(true),
                                                // #11658
                                                self::PROP_USERNAME => $username_field,
                                            ],
                                            $this->lng->txt('auth_account_code_title'),
                                        ),
                                    ]
                                )
                                ->withSubmitLabel($this->lng->txt('send'))
                                ->withAdditionalTransformation($this->mergeValuesTrafo())
                                ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    private function processCode(): void
    {
        $form = $this->buildCodeForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        $uname = null;
        if ($form_valid) {
            $code = $form_data[self::PROP_CODE];
            $uname = $form_data[self::PROP_USERNAME];
            if (ilRegistrationCode::isUnusedCode($code)) {
                $valid_until = ilRegistrationCode::getCodeValidUntil($code);
                if (!$user_id = ilObjUser::_lookupId($uname)) {
                    $this->showLoginPage();
                    return;
                }
                $invalid_code = false;
                $user = new ilObjUser($user_id);
                if ($valid_until === '0') {
                    $user->setTimeLimitUnlimited(true);
                } else {
                    if (is_numeric($valid_until)) {
                        $valid_until = strtotime('+' . $valid_until . 'days');
                    } else {
                        $valid_until = explode('-', $valid_until);
                        $valid_until = mktime(
                            23,
                            59,
                            59,
                            (int) $valid_until[1],
                            (int) $valid_until[2],
                            (int) $valid_until[0]
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
                    ilRegistrationCode::useCode($code);
                    // apply registration code role assignments
                    ilRegistrationCode::applyRoleAssignments($user, $code);
                    // apply registration code tie limits
                    ilRegistrationCode::applyAccessLimits($user, $code);

                    $user->update();

                    $this->ctrl->setParameter($this, 'cu', 1);
                    $this->lng->loadLanguageModule('auth');
                    $this->mainTemplate->setOnScreenMessage(
                        'success',
                        $GLOBALS['DIC']->language()->txt('auth_activation_code_success'),
                        true
                    );
                    $this->ctrl->redirect($this, 'showLoginPage');
                }
            }

            $this->lng->loadLanguageModule('user');
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('user_account_code_not_valid'));
        } else {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        }

        $this->showCodeForm($uname, $form);
    }

    private function buildStandardLoginForm(): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $field_factory = $this->ui_factory->input()->field();

        $fields = [];
        $det = ilAuthModeDetermination::_getInstance();
        if (ilAuthUtils::_hasMultipleAuthenticationMethods() && $det->isManualSelection()) {
            $auth_mode = $field_factory->radio($this->lng->txt('auth_selection'))->withRequired(true);
            $visible_auth_methods = [];
            foreach (ilAuthUtils::_getMultipleAuthModeOptions($this->lng) as $key => $option) {
                if (isset($option['hide_in_ui']) && $option['hide_in_ui']) {
                    continue;
                }

                $auth_mode = $auth_mode->withOption((string) $key, $option['txt']);

                if (isset($option['checked'])) {
                    $auth_mode = $auth_mode->withValue($key);
                }
                $visible_auth_methods[] = $key;
            }

            if (count($visible_auth_methods) === 1) {
                $auth_mode = $field_factory->hidden()->withRequired(true)->withValue(current($visible_auth_methods));
            }

            $fields[self::PROP_AUTH_MODE] = $auth_mode;
        }

        $fields = $fields + [
            self::PROP_USERNAME => $field_factory
                ->text($this->lng->txt('username'))
                ->withRequired(true),
            self::PROP_PASSWORD => $field_factory
                ->password($this->lng->txt('password'))
                ->withRevelation(true)
                ->withRequired(true)
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        static function (ILIAS\Data\Password $value): string {
                            return $value->toString();
                        }
                    )
                ),
        ];

        $sections = [$field_factory->section($fields, $this->lng->txt('login_to_ilias'))];

        return $this->ui_factory->input()
                                ->container()
                                ->form()
                                ->standard($this->ctrl->getFormAction($this, 'doStandardAuthentication'), $sections)
                                ->withDedicatedName('login_form')
                                ->withSubmitLabel($this->lng->txt('log_in'))
                                ->withAdditionalTransformation($this->mergeValuesTrafo())
                                ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    private function doShibbolethAuthentication(): void
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
            [$provider]
        );
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    private function doCasAuthentication(): void
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
            [$provider]
        );
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->getLogger()->debug('Authentication successful.');
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
            default:
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt($status->getReason()));
                $this->showLoginPage();
        }
    }

    private function doLTIAuthentication(): void
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
            [$provider]
        );
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt($status->getReason()), true);
                $this->ctrl->redirect($this, 'showLoginPage');
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    private function doApacheAuthentication(): void
    {
        $this->getLogger()->debug('Trying apache authentication');

        $credentials = new ilAuthFrontendCredentialsApache($this->httpRequest, $this->ctrl);
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode($credentials, ilAuthUtils::AUTH_APACHE);

        $status = ilAuthStatus::getInstance();

        $frontend_factory = new \ilAuthFrontendFactory();
        $frontend_factory->setContext(\ilAuthFrontendFactory::CONTEXT_STANDARD_FORM);
        $frontend = $frontend_factory->getFrontend(
            $this->authSession,
            $status,
            $credentials,
            [$provider]
        );
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                if ($credentials->hasValidTargetUrl()) {
                    $this->logger->debug(
                        sprintf(
                            'Authentication successful. Redirecting to starting page: %s',
                            $credentials->getTargetUrl()
                        )
                    );
                    $this->ctrl->redirectToURL($credentials->getTargetUrl());
                }
                $this->logger->debug(
                    'Authentication successful, but no valid target URL given. Redirecting to default starting page.'
                );
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirectToURL(
                    ilUtil::appendUrlParameterString(
                        $this->ctrl->getLinkTarget($this, 'showLoginPage', '', false, false),
                        'passed_sso=1'
                    )
                );
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    private function doStandardAuthentication(): void
    {
        $form = $this->buildStandardLoginForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        if (!$form_valid) {
            $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
            $this->showLoginPage($form);
            return;
        }

        $this->getLogger()->debug('Trying to authenticate user.');

        $auth_callback = function () use ($form_data) {
            $credentials = new ilAuthFrontendCredentials();
            $credentials->setUsername($form_data[self::PROP_USERNAME]);
            $credentials->setPassword($form_data[self::PROP_PASSWORD]);

            $det = ilAuthModeDetermination::_getInstance();
            if (ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection()) {
                $credentials->setAuthMode($form_data[self::PROP_AUTH_MODE]);
            }

            $provider_factory = new ilAuthProviderFactory();
            $providers = $provider_factory->getProviders($credentials);

            $status = ilAuthStatus::getInstance();

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

        if (($auth_duration = $this->setting->get('auth_duration')) !== null) {
            $duration = $this->http->durations()->callbackDuration((int) $auth_duration);
            $status = $duration->stretch($auth_callback);
        } else {
            $status = $auth_callback();
        }

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug(
                    'Authentication successful; Redirecting to starting page.'
                );
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_CODE_ACTIVATION_REQUIRED:
                $uname = ilObjUser::_lookupLogin($status->getAuthenticatedUserId());
                $this->showLoginPage($this->buildCodeForm($uname));
                return;

            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason());
                $this->showLoginPage($form);
        }
    }

    private function showLoginForm(
        string $page_editor_html,
        ILIAS\UI\Component\Input\Container\Form\Form $form = null
    ): string {
        global $tpl;

        // @todo move this to auth utils.
        // login via ILIAS (this also includes ldap)
        // If local authentication is enabled for shibboleth users, we
        // display the login form for ILIAS here.
        if ((
            $this->setting->get('auth_mode') != ilAuthUtils::AUTH_SHIBBOLETH ||
            $this->setting->get('shib_auth_allow_local')
        ) && $this->setting->get('auth_mode') != ilAuthUtils::AUTH_CAS) {
            return $this->substituteLoginPageElements(
                $tpl,
                $page_editor_html,
                $this->ui_renderer->render($form ?? $this->buildStandardLoginForm()),
                '[list-login-form]',
                'LOGIN_FORM'
            );
        }

        return $page_editor_html;
    }

    private function showLoginInformation(string $page_editor_html, ilGlobalTemplateInterface $tpl): string
    {
        if ($page_editor_html !== '') {
            return $page_editor_html;
        }

        $loginSettings = new ilSetting('login_settings');
        $information = trim($loginSettings->get('login_message_' . $this->lng->getLangKey()) ?? '');

        if ($information !== '') {
            $tpl->setVariable('TXT_LOGIN_INFORMATION', $information);
        }

        return $page_editor_html;
    }

    private function showCASLoginForm(string $page_editor_html): string
    {
        if ($this->setting->get('cas_active')) {
            $tpl = new ilTemplate('tpl.login_form_cas.html', true, true, 'Services/Init');
            $tpl->setVariable('TXT_CAS_LOGIN', $this->lng->txt('login_to_ilias_via_cas'));
            $tpl->setVariable('TXT_CAS_LOGIN_BUTTON', ilUtil::getImagePath('auth/cas_login_button.png'));
            $tpl->setVariable('TXT_CAS_LOGIN_INSTRUCTIONS', $this->setting->get('cas_login_instructions'));
            $this->ctrl->setParameter($this, 'forceCASLogin', '1');
            $tpl->setVariable('TARGET_CAS_LOGIN', $this->ctrl->getLinkTarget($this, 'doCasAuthentication'));
            $this->ctrl->setParameter($this, 'forceCASLogin', '');

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

    private function showShibbolethLoginForm(string $page_editor_html): string
    {
        $target = $this->initTargetFromQuery();

        if ($this->setting->get('shib_active')) {
            $tpl = new ilTemplate('tpl.login_form_shibboleth.html', true, true, 'Services/Init');

            $tpl->setVariable(
                'SHIB_FORMACTION',
                './shib_login.php'
            ); // Bugfix http://ilias.de/mantis/view.php?id=10662 {$tpl->setVariable('SHIB_FORMACTION', $this->ctrl->getFormAction($this));}
            $federation_name = $this->setting->get('shib_federation_name');
            $admin_mail = ' <a href="mailto:' . $this->setting->get('admin_email') . '">ILIAS ' . $this->lng->txt(
                'administrator'
            ) . '</a>.';
            if ($this->setting->get('shib_hos_type') === 'external_wayf') {
                $tpl->setCurrentBlock('shibboleth_login');
                $tpl->setVariable('TXT_SHIB_LOGIN', $this->lng->txt('login_to_ilias_via_shibboleth'));
                $tpl->setVariable('IL_TARGET', $target);
                $tpl->setVariable('TXT_SHIB_FEDERATION_NAME', $this->setting->get('shib_federation_name'));
                $tpl->setVariable('TXT_SHIB_LOGIN_BUTTON', $this->setting->get('shib_login_button'));
                $tpl->setVariable(
                    'TXT_SHIB_LOGIN_INSTRUCTIONS',
                    sprintf(
                        $this->lng->txt('shib_general_login_instructions'),
                        $federation_name,
                        $admin_mail
                    )
                );
                $tpl->setVariable('TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS', $this->setting->get('shib_login_instructions'));
                $tpl->parseCurrentBlock();
            } elseif ($this->setting->get('shib_hos_type') == 'embedded_wayf') {
                $tpl->setCurrentBlock('shibboleth_custom_login');
                $customInstructions = stripslashes($this->setting->get('shib_login_instructions'));
                $tpl->setVariable('TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS', $customInstructions);
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock('shibboleth_wayf_login');
                $tpl->setVariable('TXT_SHIB_LOGIN', $this->lng->txt('login_to_ilias_via_shibboleth'));
                $tpl->setVariable('TXT_SHIB_FEDERATION_NAME', $this->setting->get('shib_federation_name'));
                $tpl->setVariable(
                    'TXT_SELECT_HOME_ORGANIZATION',
                    sprintf(
                        $this->lng->txt('shib_select_home_organization'),
                        $this->setting->get('shib_federation_name')
                    )
                );
                $tpl->setVariable('TXT_CONTINUE', $this->lng->txt('btn_next'));
                $tpl->setVariable('TXT_SHIB_HOME_ORGANIZATION', $this->lng->txt('shib_home_organization'));
                $tpl->setVariable(
                    'TXT_SHIB_LOGIN_INSTRUCTIONS',
                    sprintf(
                        $this->lng->txt('shib_general_wayf_login_instructions'),
                        $admin_mail
                    )
                );
                $tpl->setVariable('TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS', $this->setting->get('shib_login_instructions'));

                $ilShibbolethWAYF = new ilShibbolethWAYF();

                $tpl->setVariable('TXT_SHIB_INVALID_SELECTION', $ilShibbolethWAYF->showNotice());
                $tpl->setVariable('SHIB_IDP_LIST', $ilShibbolethWAYF->generateSelection());
                $tpl->setVariable('ILW_TARGET', $target);
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
     * @param ilTemplate|ilGlobalTemplateInterface $tpl
     */
    private function substituteLoginPageElements(
        $tpl,
        string $page_editor_html,
        string $element_html,
        string $placeholder,
        string $fallback_tplvar
    ): string {
        if ($page_editor_html === '') {
            $tpl->setVariable($fallback_tplvar, $element_html);
            return $page_editor_html;
        }

        if (stripos($page_editor_html, $placeholder) === false) {
            $tpl->setVariable($fallback_tplvar, $element_html);
            return $page_editor_html;
        }

        return str_replace($placeholder, $element_html, $page_editor_html);
    }

    private function getLoginPageEditorHTML(): string
    {
        $lpe = ilAuthLoginPageEditorSettings::getInstance();
        $active_lang = $lpe->getIliasEditorLanguage($this->lng->getLangKey());

        if (!$active_lang) {
            return '';
        }

        // if page does not exist, return nothing
        if (!ilPageUtil::_existsAndNotEmpty('auth', ilLanguage::lookupId($active_lang))) {
            return '';
        }

        // get page object
        $page_gui = new ilLoginPageGUI(ilLanguage::lookupId($active_lang));

        $page_gui->setStyleId(0);

        $page_gui->setPresentationTitle('');
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader('');
        $ret = $page_gui->showPage();

        return $ret;
    }

    private function showRegistrationLinks(string $page_editor_html): string
    {
        global $tpl;

        $rtpl = new ilTemplate('tpl.login_registration_links.html', true, true, 'Services/Init');

        // allow new registrations?
        if (ilRegistrationSettings::_lookupRegistrationType() !== ilRegistrationSettings::IL_REG_DISABLED) {
            $rtpl->setCurrentBlock('new_registration');
            $rtpl->setVariable('REGISTER', $this->lng->txt('registration'));
            $rtpl->setVariable(
                'CMD_REGISTER',
                $this->ctrl->getLinkTargetByClass(ilAccountRegistrationGUI::class)
            );
            $rtpl->parseCurrentBlock();
        }
        // allow password assistance? Surpress option if Authmode is not local database
        if ($this->setting->get('password_assistance')) {
            $rtpl->setCurrentBlock('password_assistance');
            $rtpl->setVariable('FORGOT_PASSWORD', $this->lng->txt('forgot_password'));
            $rtpl->setVariable('FORGOT_USERNAME', $this->lng->txt('forgot_username'));
            $rtpl->setVariable(
                'CMD_FORGOT_PASSWORD',
                $this->ctrl->getLinkTargetByClass(ilPasswordAssistanceGUI::class)
            );
            $rtpl->setVariable(
                'CMD_FORGOT_USERNAME',
                $this->ctrl->getLinkTargetByClass(ilPasswordAssistanceGUI::class, 'showUsernameAssistanceForm')
            );
            $rtpl->setVariable('LANG_ID', $this->lng->getLangKey());
            $rtpl->parseCurrentBlock();
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME']) &&
            $this->access->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', ROOT_FOLDER_ID)) {
            $rtpl->setCurrentBlock('homelink');
            $rtpl->setVariable(
                'CLIENT_ID',
                '?client_id=' . CLIENT_ID . '&lang=' . $this->lng->getLangKey()
            );
            $rtpl->setVariable('TXT_HOME', $this->lng->txt('home'));
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

    private function showLegalDocumentsLinks(string $page_editor_html): string
    {
        global $tpl;
        global $DIC;

        if (0 === $this->user->getId()) {
            $this->user->setId(ANONYMOUS_USER_ID);
        }

        $page_editor_html = $this->substituteLoginPageElements(
            $tpl,
            $page_editor_html,
            $DIC['legalDocuments']->loginPageHTML(TermsOfService::ID),
            '[list-user-agreement]',
            'USER_AGREEMENT'
        );
        $page_editor_html = $this->substituteLoginPageElements(
            $tpl,
            $page_editor_html,
            $DIC['legalDocuments']->loginPageHTML(DataProtection::ID),
            '[list-dpro-agreement]',
            'DPRO_AGREEMENT'
        );

        return $page_editor_html;
    }

    private function purgePlaceholders(string $page_editor_html): string
    {
        return str_replace(
            [
                '[list-language-selection]',
                '[list-registration-link]',
                '[list-user-agreement]',
                '[list-dpro-agreement]',
                '[list-login-form]',
                '[list-cas-login-form]',
                '[list-saml-login]',
                '[list-shibboleth-login-form]',
                '[list-openid-connect-login]'
            ],
            '',
            $page_editor_html
        );
    }

    private function buildAccountMigrationForm(): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $field_factory = $this->ui_factory->input()->field();

        $keep = $field_factory->group(
            [
                self::PROP_USERNAME => $field_factory->text($this->lng->txt('login'))->withRequired(true),
                self::PROP_PASSWORD => $field_factory
                    ->password($this->lng->txt('password'))
                    ->withRequired(true)
                    ->withRevelation(true)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(
                            static function (ILIAS\Data\Password $value): string {
                                return $value->toString();
                            }
                        )
                    ),
            ],
            $this->lng->txt('auth_account_migration_keep'),
            $this->lng->txt('auth_info_migrate')
        );

        $new = $field_factory->group(
            [],
            $this->lng->txt('auth_account_migration_new'),
            $this->lng->txt('auth_info_add')
        );

        $fields = [
            self::PROP_ACCOUNT_MIGRATION => $field_factory->switchableGroup(
                [
                    self::PROP_ACCOUNT_MIGRATION_MIGRATE => $keep,
                    self::PROP_ACCOUNT_MIGRATION_NEW => $new,
                ],
                $this->lng->txt('auth_account_migration_name')
            )->withRequired(true)->withValue(self::PROP_ACCOUNT_MIGRATION_MIGRATE)
        ];

        $sections = [$field_factory->section($fields, $this->lng->txt('auth_account_migration'))];

        return $this->ui_factory->input()
                                ->container()
                                ->form()
                                ->standard($this->ctrl->getFormAction($this, 'migrateAccount'), $sections)
                                ->withDedicatedName('login_form')
                                ->withSubmitLabel($this->lng->txt('save'))
                                ->withAdditionalTransformation($this->mergeValuesTrafo())
                                ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    private function showAccountMigration(
        ILIAS\UI\Component\Input\Container\Form\Form $form = null,
        string $message = ''
    ): void {
        $this->help->setSubScreenId('account_migration');

        $tpl = self::initStartUpTemplate('tpl.login_account_migration.html');
        $tpl->setVariable('MIG_FORM', $this->ui_renderer->render($form ?? $this->buildAccountMigrationForm()));

        if ($message !== '') {
            $this->mainTemplate->setOnScreenMessage('failure', $message);
        }

        self::printToGlobalTemplate($tpl);
    }

    private function migrateAccount(): void
    {
        $form = $this->buildAccountMigrationForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        if (!$form_valid) {
            $this->showAccountMigration($form, $this->lng->txt('form_input_not_valid'));
            return;
        }

        $account_migration = $form_data[self::PROP_ACCOUNT_MIGRATION];
        $account_migration_mode = $account_migration[0];
        if ($account_migration_mode === self::PROP_ACCOUNT_MIGRATION_MIGRATE) {
            $this->doMigration($account_migration[1]);
        } elseif ($account_migration_mode === self::PROP_ACCOUNT_MIGRATION_NEW) {
            $this->doMigrationNewAccount();
        } else {
            $this->showAccountMigration(
                $form,
                $this->lng->txt('form_input_not_valid')
            );
        }
    }

    private function doMigrationNewAccount(): void
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
    }

    /**
     * @param array<string, string> $migration_request_data
     */
    private function doMigration(array $migration_request_data): void
    {
        $username = $migration_request_data[self::PROP_USERNAME];
        $password = $migration_request_data[self::PROP_PASSWORD];

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
            [$provider]
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
                if ($frontend->migrateAccount($GLOBALS['DIC']['ilAuthSession'])) {
                    ilInitialisation::redirectToStartingPage();
                }

                $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'), true);
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            default:
                $this->getLogger()->info('Account migration failed for user ' . $username);
                $this->showAccountMigration(null, $GLOBALS['lng']->txt('err_wrong_login'));
        }
    }

    private function showLogout(): void
    {
        $this->help->setSubScreenId('logout');

        $tpl = self::initStartUpTemplate('tpl.logout.html');
        $client_id = $this->http->wrapper()->query()->retrieve(
            'client_id',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );
        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            $tpl->setCurrentBlock('homelink');
            $tpl->setVariable('CLIENT_ID', '?client_id=' . $client_id . '&lang=' . $this->lng->getLangKey());
            $tpl->setVariable('TXT_HOME', $this->lng->txt('home'));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('logout'));
        $tpl->setVariable(
            'TXT_LOGOUT_TEXT',
            $this->lng->txt('logout_text') . $this->dic['legalDocuments']->logoutText()
        );
        $tpl->setVariable('TXT_LOGIN', $this->lng->txt('login_to_ilias'));
        $tpl->setVariable(
            'CLIENT_ID',
            '?client_id=' . $client_id . '&cmd=force_login&lang=' . $this->lng->getLangKey()
        );

        self::printToGlobalTemplate($tpl);
    }

    private function doLogout(): void
    {
        $this->eventHandler->raise(
            'Services/Authentication',
            'beforeLogout',
            [
                'user_id' => $this->user->getId()
            ]
        );

        $user_language = $this->user->getLanguage();

        $used_external_auth_mode = ilSession::get('used_external_auth_mode');

        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $this->authSession->logout();
        $this->eventHandler->raise(
            'Services/Authentication',
            'afterLogout',
            [
                'username' => $this->user->getLogin(),
                'is_explicit_logout' => true,
                'used_external_auth_mode' => $used_external_auth_mode,
            ]
        );
        if ($used_external_auth_mode && (int) $this->user->getAuthMode(true) === ilAuthUtils::AUTH_SAML) {
            $this->logger->info('Redirecting user to SAML logout script');
            $this->ctrl->redirectToURL(
                'saml.php?action=logout&logout_url=' . urlencode(ilUtil::_getHttpPath() . '/login.php')
            );
        }

        // reset cookie
        ilUtil::setCookie("ilClientId", "");

        // redirect and show logout information
        $this->ctrl->setParameter($this, 'client_id', CLIENT_ID);
        $this->ctrl->setParameter($this, 'lang', $user_language);
        $this->ctrl->redirect($this, 'showLogout');
    }

    protected function showLegalDocuments(): void
    {
        global $DIC;
        $tpl = self::initStartUpTemplate(['agreement.html', 'Services/LegalDocuments'], true, false);
        $tpl->setVariable('CONTENT', $DIC['legalDocuments']->agreeContent(self::class, __FUNCTION__));
        self::printToGlobalTemplate($tpl);
    }

    private function processIndexPHP(): void
    {
        if ($this->authSession->isValid()) {
            if (!$this->user->isAnonymous() || ilPublicSectionSettings::getInstance()->isEnabledForDomain(
                $this->httpRequest->getServerParams()['SERVER_NAME']
            )) {
                ilInitialisation::redirectToStartingPage();
                return;
            }
        }

        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            ilInitialisation::goToPublicSection();
        }

        $this->showLoginPage();
    }

    /**
     * @return bool|mixed
     */
    public static function _checkGoto(string $a_target)
    {
        global $DIC;

        $component_factory = $DIC['component.factory'];

        $access = $DIC->access();

        foreach ($component_factory->getActivePluginsInSlot('uihk') as $ui_plugin) {
            /** @var ilUIHookPluginGUI $gui_class */
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->checkGotoHook($a_target);
            if (isset($resp['target']) && is_string($resp['target']) && $resp['target'] !== '') {
                $a_target = $resp['target'];
                break;
            }
        }

        if ($a_target === '') {
            return false;
        }

        $t_arr = explode('_', $a_target);
        $type = $t_arr[0];

        if ($type === 'git') {
            $type = 'glo';
        }

        if ($type === 'pg' | $type === 'st') {
            $type = 'lm';
        }

        $class = $DIC['objDefinition']->getClassName($type);
        if ($class === '') {
            return false;
        }

        $location = $DIC['objDefinition']->getLocation($type);
        $full_class = 'ilObj' . $class . 'Access';
        include_once($location . '/class.' . $full_class . '.php');

        $ret = call_user_func([$full_class, '_checkGoto'], $a_target);

        // if no access and repository object => check for parent course/group
        if (!$ret &&
            isset($t_arr[1]) &&
            !str_contains($a_target, '_wsp') &&
            !$DIC->user()->isAnonymous() && // #10637
            !$DIC['objDefinition']->isAdministrationObject($type) &&
            $DIC['objDefinition']->isRBACObject($type)) {
            $ref_id = 0;
            // original type 'pg' => pg_<page_id>[_<ref_id>]
            if ($t_arr[0] === 'pg') {
                if (isset($t_arr[2])) {
                    $ref_id = (int) $t_arr[2];
                } else {
                    $lm_id = ilLMObject::_lookupContObjID((int) $t_arr[1]);
                    $ref_ids = ilObject::_getAllReferences($lm_id);
                    if ($ref_ids) {
                        $ref_id = array_shift($ref_ids);
                    }
                }
            } else {
                $ref_id = (int) $t_arr[1];
            }

            if ($ref_id < 1) {
                return false;
            }

            $block_obj = [];

            // walk path to find parent container
            $path = $DIC->repositoryTree()->getPathId($ref_id);
            array_pop($path);
            foreach ($path as $path_ref_id) {
                $redirect_infopage = false;
                $add_member_role = false;

                $ptype = ilObject::_lookupType($path_ref_id, true);
                $pobj_id = ilObject::_lookupObjId($path_ref_id);

                // core checks: timings/object-specific
                if (!$access->doActivationCheck('read', '', $path_ref_id, $DIC->user()->getId(), $pobj_id, $ptype) ||
                    !$access->doStatusCheck('read', '', $path_ref_id, $DIC->user()->getId(), $pobj_id, $ptype)) {
                    // object in path is inaccessible - aborting
                    return false;
                } elseif ($ptype === 'crs') {
                    // check if already participant
                    $participants = ilCourseParticipant::_getInstanceByObjId($pobj_id, $DIC->user()->getId());
                    if (!$participants->isAssigned()) {
                        // subscription currently possible?
                        if (ilObjCourse::_isActivated($pobj_id) && ilObjCourse::_registrationEnabled($pobj_id)) {
                            $block_obj[] = $path_ref_id;
                            $add_member_role = true;
                        } else {
                            $redirect_infopage = true;
                        }
                    }
                } elseif ($ptype === 'grp') {
                    // check if already participant
                    if (!ilGroupParticipants::_isParticipant($path_ref_id, $DIC->user()->getId())) {
                        // subscription currently possible?
                        $group_obj = new ilObjGroup($path_ref_id);
                        if ($group_obj->isRegistrationEnabled()) {
                            $block_obj[] = $path_ref_id;
                            $add_member_role = true;
                        } else {
                            $redirect_infopage = true;
                        }
                    }
                }

                // add members roles for all 'blocking' objects
                if ($add_member_role) {
                    // cannot join? goto will never work, so redirect to current object
                    $DIC->rbac()->system()->resetPACache($DIC->user()->getId(), $path_ref_id);
                    if (!$DIC->rbac()->system()->checkAccess('join', $path_ref_id)) {
                        $redirect_infopage = true;
                    } else {
                        $DIC->rbac()->system()->addTemporaryRole(
                            $DIC->user()->getId(),
                            ilParticipants::getDefaultMemberRole($path_ref_id)
                        );
                    }
                }

                // redirect to infopage of 1st blocking object in path
                if ($redirect_infopage) {
                    if ($DIC->rbac()->system()->checkAccess('visible', $path_ref_id)) {
                        ilUtil::redirect(
                            'ilias.php?baseClass=ilRepositoryGUI&ref_id=' . $path_ref_id . '&cmd=infoScreen'
                        );
                    } else {
                        return false;
                    }
                }
            }

            // check if access will be possible with all (possible) member roles added
            $DIC->rbac()->system()->resetPACache($DIC->user()->getId(), $ref_id);
            if ($block_obj !== [] && $DIC->rbac()->system()->checkAccess('read', $ref_id)) { // #12128
                // this won't work with lm-pages (see above)
                // keep original target
                ilSession::set('pending_goto', 'goto.php?target=' . $a_target);

                // redirect to 1st non-member object in path
                ilUtil::redirect(
                    'ilias.php?baseClass=ilRepositoryGUI&ref_id=' . array_shift($block_obj)
                );
            }
        }

        return $ret;
    }

    private function confirmRegistration(): void
    {
        $this->lng->loadLanguageModule('registration');

        ilUtil::setCookie('iltest', 'cookie', false);
        $regitration_hash = trim($this->http->wrapper()->query()->retrieve(
            'rh',
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        ));
        if ($regitration_hash === '') {
            $this->mainTemplate->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('reg_confirmation_hash_not_passed'),
                true
            );
            $this->ctrl->redirectToURL(sprintf('./login.php?cmd=force_login&lang=%s', $this->lng->getLangKey()));
        }

        try {
            $oRegSettings = new ilRegistrationSettings();

            $usr_id = ilObjUser::_verifyRegistrationHash(trim($regitration_hash));
            /** @var ilObjUser $user */
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

            $target = $user->getPref('reg_target') ?? '';
            if ($target !== '') {
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

            $this->mainTemplate->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('reg_account_confirmation_successful'),
                true
            );
            $this->ctrl->redirectToURL(sprintf('./login.php?cmd=force_login&lang=%s', $user->getLanguage()));
        } catch (ilRegConfirmationLinkExpiredException $exception) {
            $soap_client = new ilSoapClient();
            $soap_client->setResponseTimeout(1);
            $soap_client->enableWSDL(true);
            $soap_client->init();

            $this->logger->info(
                'Triggered soap call (background process) for deletion of inactive user objects with expired confirmation hash values (dual opt in) ...'
            );

            $soap_client->call(
                'deleteExpiredDualOptInUserObjects',
                [
                    $_COOKIE[session_name()] . '::' . CLIENT_ID,
                    $exception->getCode() // user id
                ]
            );

            $this->mainTemplate->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt($exception->getMessage()),
                true
            );
            $this->ctrl->redirectToURL(sprintf('./login.php?cmd=force_login&lang=%s', $this->lng->getLangKey()));
        } catch (ilRegistrationHashNotFoundException $exception) {
            $this->mainTemplate->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->lng->txt($exception->getMessage()),
                true
            );
            $this->ctrl->redirectToURL(sprintf('./login.php?cmd=force_login&lang=%s', $this->lng->getLangKey()));
        }
    }

    /**
     * This method enriches the global template with some user interface elements (language selection, headlines, back buttons, ...) for public service views
     * @param string|array{0: string, 1: string} $a_tmpl The template file as a string of as an array (index 0: template file, index 1: template directory)
     */
    public static function initStartUpTemplate(
        $a_tmpl,
        bool $a_show_back = false,
        bool $a_show_logout = false
    ): ilGlobalTemplateInterface {
        global $DIC;

        $tpl = new ilGlobalTemplate('tpl.main.html', true, true);

        $tpl->addBlockfile('CONTENT', 'content', 'tpl.startup_screen.html', 'Services/Init');

        $view_title = $DIC->language()->txt('login_to_ilias');
        if ($a_show_back) {
            // #13400
            $param = 'client_id=' . CLIENT_ID . '&lang=' . $DIC->language()->getLangKey();

            $tpl->setCurrentBlock('link_item_bl');
            $tpl->setVariable('LINK_TXT', $view_title);
            $tpl->setVariable('LINK_URL', 'login.php?cmd=force_login&' . $param);
            $tpl->parseCurrentBlock();

            if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME']) &&
                $DIC->access()->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', ROOT_FOLDER_ID)) {
                $tpl->setVariable('LINK_URL', 'index.php?' . $param);
                $tpl->setVariable('LINK_TXT', $DIC->language()->txt('home'));
                $tpl->parseCurrentBlock();
            }
        } elseif ($a_show_logout) {
            $view_title = $DIC->language()->txt('logout');
            $tpl->setCurrentBlock('link_item_bl');
            $tpl->setVariable('LINK_TXT', $view_title);
            $tpl->setVariable('LINK_URL', self::logoutUrl());
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
        $short_title = trim($DIC->settings()->get('short_inst_name') ?? '');
        if ($short_title === '') {
            $short_title = 'ILIAS';
        }
        PageContentProvider::setShortTitle($short_title);

        $header_title = ilObjSystemFolder::_getHeaderTitle();
        PageContentProvider::setTitle($header_title);

        return $tpl;
    }

    private function showSamlLoginForm(string $page_editor_html): string
    {
        if (count(ilSamlIdp::getActiveIdpList()) > 0 && ilSamlSettings::getInstance()->isDisplayedOnLoginPage()) {
            $tpl = new ilTemplate('tpl.login_form_saml.html', true, true, 'Services/Saml');

            $return = '';
            $target = $this->initTargetFromQuery();
            if ($target !== '') {
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

    private function showOpenIdConnectLoginForm(string $page_editor_html): string
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
                    $tpl->setVariable('SCRIPT_OIDCONNECT_T', './openidconnect.php' . $target_str);
                    $tpl->setVariable('TXT_OIDC', $oidc_settings->getLoginElemenText());
                    break;

                case ilOpenIdConnectSettings::LOGIN_ELEMENT_TYPE_IMG:
                    $tpl->setVariable('SCRIPT_OIDCONNECT_I', './openidconnect.php' . $target_str);
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

    private function doOpenIdConnectAuthentication(): void
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
            [$provider]
        );
        $frontend->authenticate();

        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                $this->logger->debug('Authentication successful; Redirecting to starting page.');
                if ($credentials->getRedirectionTarget()) {
                    ilInitialisation::redirectToStartingPage($credentials->getRedirectionTarget());
                }
                ilInitialisation::redirectToStartingPage();

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    private function doSamlAuthentication(): void
    {
        $this->getLogger()->debug('Trying saml authentication');
        $request = $this->httpRequest;
        $params = $request->getQueryParams();

        $factory = new ilSamlAuthFactory();
        $auth = $factory->auth();

        if (isset($params['action']) && $params['action'] === 'logout') {
            $logout_url = $params['logout_url'] ?? '';
            $this->logger->info(sprintf('Requested SAML logout: %s', $logout_url));
            $auth->logout($logout_url);
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
                if (count($activeIdps) === 1) {
                    $idp = current($activeIdps);

                    ilLoggerFactory::getLogger('auth')->debug(
                        sprintf(
                            'Found exactly one active IDP with id %s: %s',
                            $idp->getIdpId(),
                            $idp->getEntityId()
                        )
                    );

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
            $this->logger->debug(sprintf('Stored relevant IDP id in session: %s', $auth->getParam('idpId')));
        }

        $auth = $factory->auth();

        $this->logger->debug('Checking SAML authentication status...');
        $auth->protectResource();
        $this->logger->debug(
            'SAML authentication successful, continuing with ILIAS internal authentication process...'
        );

        $idpId = (int) $auth->getParam('idpId');

        $this->logger->debug(
            sprintf(
                'Internal SAML IDP id fetched from session: %s',
                $idpId
            )
        );

        if ($idpId < 1) {
            $this->logger->debug(
                'No valid internal IDP id found (most probably due to IDP initiated SSO), trying fallback determination...'
            );
            $authData = $auth->getAuthDataArray();
            if (isset($authData['saml:sp:IdP'])) {
                $idpId = ilSamlIdp::geIdpIdByEntityId($authData['saml:sp:IdP']);
                $this->logger->debug(
                    sprintf(
                        'Searching active ILIAS IDP by entity id "%s" results in: %s',
                        $authData['saml:sp:IdP'],
                        $idpId
                    )
                );
            } else {
                $this->logger->debug(
                    'Could not execute fallback determination, no IDP entity ID found SAML authentication session data'
                );
            }
        }

        $target = $auth->popParam('target');

        $this->logger->debug(sprintf('Retrieved "target" parameter: %s', print_r($target, true)));

        $credentials = new ilAuthFrontendCredentialsSaml($auth, $request);
        $credentials->initFromRequest();

        $provider_factory = new ilAuthProviderFactory();
        $provider = $provider_factory->getProviderByAuthMode(
            $credentials,
            ilUtil::stripSlashes(
                ilAuthUtils::AUTH_SAML . '_' . $idpId
            )
        );

        if ($target) {
            $credentials->setReturnTo($target);
        } else {
            $target = $credentials->getReturnTo();
        }

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
                ilInitialisation::redirectToStartingPage($target ?? '');

                // no break
            case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                $this->ctrl->redirect($this, 'showAccountMigration');

                // no break
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                $this->mainTemplate->setOnScreenMessage('failure', $status->getTranslatedReason(), true);
                $this->ctrl->redirect($this, 'showLoginPage');
        }

        $this->mainTemplate->setOnScreenMessage('failure', $this->lng->txt('err_wrong_login'));
        $this->showLoginPage();
    }

    /**
     * @param list<ilSamlIdp> $idps
     */
    private function showSamlIdpSelection(ilSamlAuth $auth, array $idps): void
    {
        $this->help->setSubScreenId('saml_idp_selection');

        self::initStartUpTemplate(['tpl.saml_idp_selection.html', 'Services/Saml']);

        $this->ctrl->setTargetScript('saml.php');
        $items = [];
        $table = new ilSamlIdpSelectionTableGUI($this, 'doSamlAuthentication');
        foreach ($idps as $idp) {
            $this->ctrl->setParameter($this, 'saml_idp_id', $idp->getIdpId());
            $this->ctrl->setParameter($this, 'idpentityid', urlencode($idp->getEntityId()));

            $items[] = [
                'idp_link' => $this->ui_renderer->render(
                    $this->ui_factory->link()->standard(
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

    /**
     * Return the logout URL with a valid CSRF token. Without the token the logout won't be successful.
     *
     * @param array<string, string> $parameters
     */
    public static function logoutUrl(array $parameters = []): string
    {
        global $DIC;

        $defaults = ['lang' => $DIC->user()->getCurrentLanguage()];
        $parameters = '&' . http_build_query(array_merge($defaults, $parameters));

        $DIC->ctrl()->setTargetScript('logout.php');
        $url = $DIC->ctrl()->getLinkTargetByClass([self::class], 'doLogout') . $parameters;
        $DIC->ctrl()->setTargetScript('ilias.php');

        return $url;
    }
}
