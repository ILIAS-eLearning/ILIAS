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

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HTTPServices;

class ilPasswordAssistanceGUI implements ilCtrlSecurityInterface
{
    private const PERMANENT_LINK_TARGET_PW = 'pwassist';
    private const PERMANENT_LINK_TARGET_NAME = 'nameassist';

    private const PROP_USERNAME = 'username';
    private const PROP_EMAIL = 'email';
    private const PROP_PASSWORD = 'password';
    private const PROP_KEY = 'key';

    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilRbacReview $rbacreview;
    private ilGlobalTemplateInterface $tpl;
    private ilSetting $settings;
    private ilErrorHandling $ilErr;
    private RefineryFactory $refinery;
    private HTTPServices $http;
    private ilHelpGUI $help;
    private ILIAS\UI\Factory $ui_factory;
    private ILIAS\UI\Renderer $ui_renderer;
    private ilObjUser $actor;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->rbacreview = $DIC->rbac()->review();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
        $this->ilErr = $DIC['ilErr'];
        $this->help = $DIC->help();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->actor = $DIC->user();

        $this->help->setScreenIdComponent('init');
    }

    private function retrieveRequestedKey(): string
    {
        $key = $this->http->wrapper()->query()->retrieve(
            'key',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(
                    $this->http->wrapper()->post()->retrieve(
                        'key',
                        $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
                    )
                )
            ])
        );

        return $key;
    }

    private function getClientId(): string
    {
        return CLIENT_ID;
    }

    public function executeCommand(): void
    {
        // check correct setup
        if (!$this->settings->get('setup_ok')) {
            $this->ilErr->raiseError('Setup is not completed. Please run setup routine again.', $this->ilErr->FATAL);
        }

        // check hack attempts
        if (!$this->settings->get('password_assistance')) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
        }

        if ($this->actor->getId() > 0 && !$this->actor->isAnonymous()) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
        }

        $this->lng->loadLanguageModule('pwassist');
        $cmd = $this->ctrl->getCmd() ?? '';
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                if ($cmd !== '' && method_exists($this, $cmd)) {
                    $this->$cmd();
                    return;
                }

                if ($this->retrieveRequestedKey() !== '') {
                    $this->showAssignPasswordForm(null, $this->retrieveRequestedKey());
                } else {
                    $this->showAssistanceForm();
                }
                break;
        }
    }

    public function getUnsafeGetCommands(): array
    {
        return [];
    }

    public function getSafePostCommands(): array
    {
        return ['submitAssignPasswordForm'];
    }

    private function getBaseUrl(): string
    {
        return rtrim(ilUtil::_getHttpPath(), '/');
    }

    /**
     * @param array<string, string> $query_parameters
     */
    private function buildUrl(string $script, array $query_parameters): string
    {
        $url = implode('/', [
            $this->getBaseUrl(),
            ltrim($script, '/')
        ]);

        $url = ilUtil::appendUrlParameterString(
            $url,
            http_build_query($query_parameters, '', '&')
        );

        return $url;
    }

    private function emailTrafo(): \ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->constraint(
            static function ($value): bool {
                return is_string($value) && ilUtil::is_email($value);
            },
            $this->lng->txt('email_not_valid')
        );
    }

    private function mergeValuesTrafo(): \ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static function (array $values): array {
            return array_merge(...$values);
        });
    }

    private function saniziteArrayElementsTrafo(): \ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static function (array $values): array {
            return ilArrayUtil::stripSlashesRecursive($values);
        });
    }

    private function trimIfStringTrafo(): \ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(static function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return $value;
        });
    }

    private function getAssistanceForm(): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $field_factory = $this->ui_factory->input()->field();

        return $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'submitAssistanceForm'),
                [
                    $field_factory->section(
                        [
                            self::PROP_USERNAME => $field_factory
                                ->text($this->lng->txt('username'))
                                ->withAdditionalTransformation($this->trimIfStringTrafo())
                                ->withRequired(true),
                            self::PROP_EMAIL => $field_factory
                                ->text($this->lng->txt('email'))
                                ->withRequired(true)
                                ->withAdditionalTransformation($this->trimIfStringTrafo())
                                ->withAdditionalTransformation($this->emailTrafo()),
                        ],
                        $this->lng->txt('password_assistance'),
                        ''
                    ),
                ]
            )
            ->withAdditionalTransformation($this->mergeValuesTrafo())
            ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    private function showAssistanceForm(ILIAS\UI\Component\Input\Container\Form\Form $form = null): void
    {
        $this->help->setSubScreenId('password_assistance');

        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assistance.html', true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
        $tpl->setVariable(
            'IMG_PAGEHEADLINE',
            $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_auth.svg'),
                $this->lng->txt('password_assistance')
            ))
        );

        $tpl->setVariable(
            'TXT_ENTER_USERNAME_AND_EMAIL',
            $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info(
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
                )
            )
        );

        $tpl->setVariable('FORM', $this->ui_renderer->render($form ?? $this->getAssistanceForm()));
        $this->fillPermanentLink(self::PERMANENT_LINK_TARGET_PW);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    /**
     * If the submitted username and email address matches an entry in the user data
     * table, then ILIAS creates a password assistance session for the user, and
     * sends a password assistance mail to the email address.
     * For details about the creation of the session and the e-mail see function
     * sendPasswordAssistanceMail().
     */
    private function submitAssistanceForm(): void
    {
        $form = $this->getAssistanceForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        if (!$form_valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showAssistanceForm($form);
            return;
        }

        $defaultAuth = ilAuthUtils::AUTH_LOCAL;
        if ($GLOBALS['DIC']['ilSetting']->get('auth_mode')) {
            $defaultAuth = $GLOBALS['DIC']['ilSetting']->get('auth_mode');
        }

        $username = $form_data[self::PROP_USERNAME];
        $email = $form_data[self::PROP_EMAIL];

        $assistance_callback = function () use ($defaultAuth, $username, $email): void {
            $usr_id = ilObjUser::getUserIdByLogin($username);
            if (!is_numeric($usr_id) || !($usr_id > 0)) {
                ilLoggerFactory::getLogger('usr')->info(
                    sprintf(
                        'Could not process password assistance form (reason: no user found) %s / %s',
                        $username,
                        $email
                    )
                );
                return;
            }

            $user = new ilObjUser($usr_id);
            $email_addresses = array_map('strtolower', [$user->getEmail(), $user->getSecondEmail()]);

            if (!in_array(strtolower($email), $email_addresses, true)) {
                if (implode('', $email_addresses) === '') {
                    ilLoggerFactory::getLogger('usr')->info(
                        sprintf(
                            'Could not process password assistance form (reason: account without email addresses): %s / %s',
                            $username,
                            $email
                        )
                    );
                } else {
                    ilLoggerFactory::getLogger('usr')->info(
                        sprintf(
                            'Could not process password assistance form (reason: account email addresses differ from input): %s / %s',
                            $username,
                            $email
                        )
                    );
                }
            } elseif (
                (
                    $user->getAuthMode(true) != ilAuthUtils::AUTH_LOCAL ||
                    ($user->getAuthMode(true) == $defaultAuth && $defaultAuth != ilAuthUtils::AUTH_LOCAL)
                ) && !(
                    (int) $user->getAuthMode(true) === ilAuthUtils::AUTH_SAML &&
                    \ilAuthUtils::isLocalPasswordEnabledForAuthMode($user->getAuthMode(true))
                )
            ) {
                ilLoggerFactory::getLogger('usr')->info(
                    sprintf(
                        'Could not process password assistance form (reason: not permitted for accounts using external authentication sources): %s / %s',
                        $username,
                        $email
                    )
                );
            } elseif ($this->rbacreview->isAssigned($user->getId(), ANONYMOUS_ROLE_ID) ||
                $this->rbacreview->isAssigned($user->getId(), SYSTEM_ROLE_ID)) {
                ilLoggerFactory::getLogger('usr')->info(
                    sprintf(
                        'Could not process password assistance form (reason: not permitted for system user or anonymous): %s / %s',
                        $username,
                        $email
                    )
                );
            } else {
                $this->sendPasswordAssistanceMail($user);
            }
        };

        if (($assistance_duration = $this->settings->get('account_assistance_duration')) !== null) {
            $duration = $this->http->durations()->callbackDuration((int) $assistance_duration);
            $status = $duration->stretch($assistance_callback);
        } else {
            $status = $assistance_callback();
        }

        $this->showMessageForm(sprintf($this->lng->txt('pwassist_mail_sent'), $email), self::PERMANENT_LINK_TARGET_PW);
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
    private function sendPasswordAssistanceMail(ilObjUser $userObj): void
    {
        global $DIC;

        require_once 'include/inc.pwassist_session_handler.php';
        $pwassist_session['pwassist_id'] = db_pwassist_create_id();
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
        $senderFactory = $DIC->mail()->mime()->senderFactory();
        $sender = $senderFactory->system();

        $mm = new ilMimeMail();
        $mm->Subject($this->lng->txt('pwassist_mail_subject'), true);
        $mm->From($sender);
        $mm->To($userObj->getEmail());
        $mm->Body(
            str_replace(
                ["\\n", "\\t"],
                ["\n", "\t"],
                sprintf(
                    $this->lng->txt('pwassist_mail_body'),
                    $pwassist_url,
                    $this->getBaseUrl() . '/',
                    $_SERVER['REMOTE_ADDR'],
                    $userObj->getLogin(),
                    'mailto:' . $DIC->settings()->get('admin_email'),
                    $alternative_pwassist_url
                )
            )
        );
        $mm->Send();
    }

    private function getAssignPasswordForm(string $pwassist_id = null): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $field_factory = $this->ui_factory->input()->field();

        $key = $field_factory
            ->hidden()
            ->withRequired(true)
            ->withDedicatedName(self::PROP_KEY);
        if ($pwassist_id !== null) {
            $key = $key->withValue($pwassist_id);
        }

        return $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'submitAssignPasswordForm'),
                [
                    $field_factory->section(
                        [
                            self::PROP_KEY => $key,
                            self::PROP_USERNAME => $field_factory
                                ->text($this->lng->txt('username'))
                                ->withAdditionalTransformation($this->trimIfStringTrafo())
                                ->withRequired(true),
                            self::PROP_PASSWORD => $field_factory
                                ->password(
                                    $this->lng->txt('password'),
                                    ilSecuritySettingsChecker::getPasswordRequirementsInfo()
                                )
                                ->withRequired(true)
                                ->withRevelation(true)
                                ->withAdditionalTransformation(
                                    $this->refinery->custom()->constraint(
                                        static function (ILIAS\Data\Password $value): bool {
                                            return ilSecuritySettingsChecker::isPassword(
                                                trim($value->toString())
                                            );
                                        },
                                        static function (Closure $lng, ILIAS\Data\Password $value): string {
                                            $problem = $lng('passwd_invalid');
                                            $custom_problem = null;
                                            if (!ilSecuritySettingsChecker::isPassword(
                                                trim($value->toString()),
                                                $custom_problem
                                            )) {
                                                $problem = $custom_problem;
                                            }

                                            return $problem;
                                        }
                                    )
                                )
                                ->withAdditionalTransformation(
                                    $this->refinery->custom()->transformation(
                                        static function (ILIAS\Data\Password $value): string {
                                            return trim($value->toString());
                                        }
                                    )
                                ),
                        ],
                        $this->lng->txt('password_assistance'),
                        ''
                    ),
                ]
            )
            ->withAdditionalTransformation($this->mergeValuesTrafo())
            ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
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
    private function showAssignPasswordForm(
        ILIAS\UI\Component\Input\Container\Form\Form $form = null,
        string $pwassist_id = ''
    ): void {
        $this->help->setSubScreenId('password_input');

        if ($pwassist_id === '') {
            $pwassist_id = $this->retrieveRequestedKey();
        }

        require_once 'include/inc.pwassist_session_handler.php';
        $pwassist_session = db_pwassist_session_read($pwassist_id);
        if (!is_array($pwassist_session) || $pwassist_session['expires'] < time()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('pwassist_session_expired'));
            $this->showAssistanceForm(null);
            return;
        }

        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assignpassword.html', true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
        $tpl->setVariable(
            'IMG_PAGEHEADLINE',
            $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_auth.svg'),
                $this->lng->txt('password_assistance')
            ))
        );

        $tpl->setVariable(
            'TXT_ENTER_USERNAME_AND_NEW_PASSWORD',
            $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info($this->lng->txt('pwassist_enter_username_and_new_password'))
            )
        );

        $tpl->setVariable('FORM', $this->ui_renderer->render($form ?? $this->getAssignPasswordForm($pwassist_id)));
        $this->fillPermanentLink(self::PERMANENT_LINK_TARGET_PW);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    /**
     * The key is used to retrieve the password assistance session.
     * If the key is missing, or if the password assistance session has expired, the
     * password assistance form will be shown instead of this form.
     * If the password assistance session is valid, and if the username matches the
     * username, for which the password assistance has been requested, and if the
     * new password is valid, ILIAS assigns the password to the user.
     * Note: To prevent replay attacks, the session is deleted when the
     * password has been assigned successfully.
     */
    private function submitAssignPasswordForm(): void
    {
        $form = $this->getAssignPasswordForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        if (!$form_valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showAssistanceForm($form);
            return;
        }

        $username = $form_data[self::PROP_USERNAME];
        $password = $form_data[self::PROP_PASSWORD];
        $pwassist_id = $form_data[self::PROP_KEY];

        require_once 'include/inc.pwassist_session_handler.php';
        $pwassist_session = db_pwassist_session_read($pwassist_id);
        if (!is_array($pwassist_session) || $pwassist_session['expires'] < time()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                str_replace("\\n", '', $this->lng->txt('pwassist_session_expired'))
            );
            $this->showAssistanceForm($form);
        } else {
            $is_successful = true;
            $message = '';

            $userObj = ilObjectFactory::getInstanceByObjId((int) $pwassist_session['user_id'], false);
            if (!($userObj instanceof ilObjUser)) {
                $message = $this->lng->txt('user_does_not_exist');
                $is_successful = false;
            }

            // check if the username entered by the user matches the
            // one of the user object.
            if ($is_successful && strcasecmp($userObj->getLogin(), $username) !== 0) {
                $message = $this->lng->txt('pwassist_login_not_match');
                $is_successful = false;
            }

            $error_lng_var = '';
            if ($is_successful &&
                !ilSecuritySettingsChecker::isPasswordValidForUserContext($password, $userObj, $error_lng_var)) {
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
                $userObj->setLastPasswordChangeToNow();
                $userObj->update();
            }

            // If we are successful, we destroy the password assistance
            // session and redirect to the login page.
            // Else we display the form again along with an error message.
            // ------------------
            if ($is_successful) {
                db_pwassist_session_destroy($pwassist_id);
                $this->showMessageForm(
                    $this->ui_renderer->render(
                        $this->ui_factory->messageBox()->info(
                            sprintf($this->lng->txt('pwassist_password_assigned'), $username)
                        )
                    ),
                    self::PERMANENT_LINK_TARGET_PW
                );
            } else {
                $this->tpl->setOnScreenMessage('failure', str_replace("\\n", '', $message));
                $this->showAssignPasswordForm($form, $pwassist_id);
            }
        }
    }

    private function getUsernameAssistanceForm(): ILIAS\UI\Component\Input\Container\Form\Form
    {
        $field_factory = $this->ui_factory->input()->field();

        return $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'submitUsernameAssistanceForm'),
                [
                    $field_factory->section(
                        [
                            self::PROP_EMAIL => $field_factory
                                ->text($this->lng->txt('email'))
                                ->withRequired(true)
                                ->withAdditionalTransformation($this->trimIfStringTrafo())
                                ->withAdditionalTransformation($this->emailTrafo()),
                        ],
                        $this->lng->txt('username_assistance'),
                        ''
                    ),
                ]
            )
            ->withAdditionalTransformation($this->mergeValuesTrafo())
            ->withAdditionalTransformation($this->saniziteArrayElementsTrafo());
    }

    private function showUsernameAssistanceForm(ILIAS\UI\Component\Input\Container\Form\Form $form = null): void
    {
        $this->help->setSubScreenId('username_assistance');

        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_username_assistance.html', true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
        $tpl->setVariable(
            'IMG_PAGEHEADLINE',
            $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_auth.svg'),
                $this->lng->txt('password_assistance')
            ))
        );

        $tpl->setVariable(
            'TXT_ENTER_USERNAME_AND_EMAIL',
            $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info(
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
                )
            )
        );

        $tpl->setVariable('FORM', $this->ui_renderer->render($form ?? $this->getUsernameAssistanceForm()));
        $this->fillPermanentLink(self::PERMANENT_LINK_TARGET_NAME);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    private function submitUsernameAssistanceForm(): void
    {
        $form = $this->getUsernameAssistanceForm();
        $form_valid = false;
        $form_data = null;
        if ($this->http->request()->getMethod() === 'POST') {
            $form = $form->withRequest($this->http->request());
            $form_data = $form->getData();
            $form_valid = $form_data !== null;
        }

        if (!$form_valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showUsernameAssistanceForm($form);
            return;
        }

        $email = trim($form_data[self::PROP_EMAIL]);

        $assistance_callback = function () use ($email): void {
            $logins = ilObjUser::getUserLoginsByEmail($email);

            if (is_array($logins) && count($logins) > 0) {
                $this->sendUsernameAssistanceMail($email, $logins);
            } else {
                ilLoggerFactory::getLogger('usr')->info(
                    sprintf(
                        'Could not sent username assistance emails to (reason: no user found): %s',
                        $email
                    )
                );
            }
        };

        if (($assistance_duration = $this->settings->get('account_assistance_duration')) !== null) {
            $duration = $this->http->durations()->callbackDuration((int) $assistance_duration);
            $status = $duration->stretch($assistance_callback);
        } else {
            $status = $assistance_callback();
        }

        $this->showMessageForm($this->lng->txt('pwassist_mail_sent_generic'), self::PERMANENT_LINK_TARGET_NAME);
    }

    /**
     * @param list<string> $logins
     */
    private function sendUsernameAssistanceMail(string $email, array $logins): void
    {
        global $DIC;

        $login_url = $this->buildUrl(
            'pwassist.php',
            [
                'client_id' => $this->getClientId(),
                'lang' => $this->lng->getLangKey()
            ]
        );

        $senderFactory = $DIC->mail()->mime()->senderFactory();
        $sender = $senderFactory->system();

        $mm = new ilMimeMail();
        $mm->Subject($this->lng->txt('pwassist_mail_subject'), true);
        $mm->From($sender);
        $mm->To($email);
        $mm->Body(
            str_replace(
                ["\\n", "\\t"],
                ["\n", "\t"],
                sprintf(
                    $this->lng->txt('pwassist_username_mail_body'),
                    implode(",\n", $logins),
                    $this->getBaseUrl() . '/',
                    $_SERVER['REMOTE_ADDR'],
                    $email,
                    'mailto:' . $this->settings->get('admin_email'),
                    $login_url
                )
            )
        );
        $mm->Send();
    }

    private function showMessageForm(string $text, string $permanent_link_context): void
    {
        $tpl = ilStartUpGUI::initStartUpTemplate('tpl.pwassist_message.html', true);
        $tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
        $tpl->setVariable(
            'IMG_PAGEHEADLINE',
            $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_auth.svg'),
                $this->lng->txt('password_assistance')
            ))
        );

        $tpl->setVariable('TXT_TEXT', str_replace("\\n", '<br />', $text));
        $this->fillPermanentLink($permanent_link_context);
        ilStartUpGUI::printToGlobalTemplate($tpl);
    }

    private function fillPermanentLink(string $context): void
    {
        $this->tpl->setPermanentLink('usr', null, $context);
    }
}
