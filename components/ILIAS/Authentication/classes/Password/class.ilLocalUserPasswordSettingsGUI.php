<?php

/*
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

use Closure;
use ilObjUser;
use ilSession;
use ilLanguage;
use ilAuthUtils;
use ilCtrlInterface;
use ilErrorHandling;
use ILIAS\Data\Password;
use ilDAVActivationChecker;
use ilGlobalTemplateInterface;
use ilSecuritySettingsChecker;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\Authentication\Password\LocalUserPasswordManager;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Input\Field\Password as PasswordInput;

class ilLocalUserPasswordSettingsGUI
{
    private const NEW_PASSWORD = 'new_password';
    private const CURRENT_PASSWORD = 'current_password';
    public const CMD_SHOW_PASSWORD = 'showPassword';
    public const CMD_SAVE_PASSWORD = 'savePassword';
    private readonly ServerRequestInterface $request;
    private readonly ilErrorHandling $error;
    private readonly Refinery $refinery;
    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilObjUser $user;
    private readonly ilCtrlInterface $ctrl;
    private readonly LocalUserPasswordManager $password_manager;

    public function __construct()
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->error = $DIC['ilErr'];
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->password_manager = LocalUserPasswordManager::getInstance();
        $this->lng->loadLanguageModule('user');
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            default:
                if (method_exists($this, $cmd)) {
                    $this->$cmd();
                } else {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }

                break;
        }
    }

    public function showPassword(
        Form $form = null,
        bool $hide_form = false,
        MessageBox $message_box = null
    ): void {
        // check whether password of user have to be changed
        // due to first login or password of user is expired
        if ($this->user->isPasswordChangeDemanded()) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_INFO,
                $this->lng->txt('password_change_on_first_login_demand')
            );
        } elseif ($this->user->isPasswordExpired()) {
            $msg = $this->lng->txt('password_expired');
            $password_age = $this->user->getPasswordAge();
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_INFO, sprintf($msg, $password_age));
        }

        if (!$form && !$hide_form) {
            $form = $this->getPasswordForm();
        }
        $this->tpl->setContent(
            !$hide_form ? $this->ui_renderer->render($form) : $this->ui_renderer->render($message_box)
        );
        $this->tpl->printToStdout();
    }

    public function getPasswordForm(
        ServerRequestInterface $request = null,
        array $errors = []
    ): Form {
        $items = [];
        if ($this->password_manager->allowPasswordChange($this->user)) {
            $pw_info_set = false;
            if ((int) $this->user->getAuthMode(true) === ilAuthUtils::AUTH_LOCAL) {
                $cpass = $this->ui_factory->input()->field()->password(
                    $this->lng->txt(self::CURRENT_PASSWORD),
                    ilSecuritySettingsChecker::getPasswordRequirementsInfo()
                );

                $pw_info_set = true;
                if ($this->user->getPasswd()) {
                    $cpass = $cpass->withRequired(true);
                }
                $cpass = $cpass->withRevelation(true);
                $cpass_error = $errors[self::CURRENT_PASSWORD] ?? [];
                if ($cpass_error !== []) {
                    $cpass = $cpass->withError(implode('<br>', $cpass_error));
                }
                $cpass = $cpass->withAdditionalTransformation(
                    $this->refinery->custom()->constraint(function (Password $value): bool {
                        return
                            ((int) $this->user->getAuthMode(true) !== ilAuthUtils::AUTH_LOCAL) ||
                            LocalUserPasswordManager::getInstance()->verifyPassword(
                                $this->user,
                                $value->toString()
                            );
                    }, $this->lng->txt('passwd_wrong'))
                );

                $items[self::CURRENT_PASSWORD] = $cpass;
            }

            // new password
            $ipass = $this->ui_factory->input()->field()->password(
                $this->lng->txt('desired_password'),
            );
            if ($pw_info_set === false) {
                $ipass = $ipass->withByline(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
            }
            $ipass = $ipass->withRequired(true);
            $ipass = $ipass->withRevelation(true);
            $ipass_error = $errors[self::NEW_PASSWORD] ?? [];
            if ($ipass_error !== []) {
                $ipass = $ipass->withError(implode('<br>', $ipass_error));
            }
            $ipass = $ipass->withAdditionalTransformation(
                $this->refinery->custom()->constraint(function (Password $value): bool {
                    return ilSecuritySettingsChecker::isPassword($value->toString(), $custom_error);
                }, function (Closure $txt, Password $value): string {
                    $custom_error = '';
                    !ilSecuritySettingsChecker::isPassword($value->toString(), $custom_error);
                    if ($custom_error !== '' && $custom_error !== null) {
                        return $custom_error;
                    }

                    return $this->lng->txt('passwd_invalid');
                })
            );
            $ipass = $ipass->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    function (Password $value): bool {
                        return ilSecuritySettingsChecker::isPasswordValidForUserContext(
                            $value->toString(),
                            $this->user,
                            $error_lng_var
                        );
                    },
                    function (Closure $cls, Password $value): string {
                        ilSecuritySettingsChecker::isPasswordValidForUserContext(
                            $value->toString(),
                            $this->user,
                            $error_lng_var
                        );

                        return $this->lng->txt($error_lng_var ?? '');
                    }
                )
            );
            $items[self::NEW_PASSWORD] = $ipass;

            switch ($this->user->getAuthMode(true)) {
                case ilAuthUtils::AUTH_LOCAL:
                    $title = $this->lng->txt('chg_password');

                    break;
                case ilAuthUtils::AUTH_SHIBBOLETH:
                case ilAuthUtils::AUTH_CAS:
                    if (ilDAVActivationChecker::_isActive()) {
                        $title = $this->lng->txt('chg_ilias_and_webfolder_password');
                    } else {
                        $title = $this->lng->txt('chg_ilias_password');
                    }

                    break;
                default:
                    $title = $this->lng->txt('chg_ilias_password');

                    break;
            }
            $section = $this->ui_factory->input()->field()->section($items, $title);
            $items = ['password' => $section];
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, 'savePassword'),
            $items
        )->withSubmitLabel($this->lng->txt('save'));
    }

    public function savePassword(): void
    {
        if (!$this->password_manager->allowPasswordChange($this->user)) {
            $this->ctrl->redirect($this, 'showPersonalData');

            return;
        }

        $form = $this->getPasswordForm()->withRequest($this->request);
        $section = $form->getInputs()['password'];
        /**
         * @var PasswordInput $cp
         * @var PasswordInput $np
         */
        $cp = $section->getInputs()[self::CURRENT_PASSWORD];
        $np = $section->getInputs()[self::NEW_PASSWORD];
        $errors = [self::CURRENT_PASSWORD => [], self::NEW_PASSWORD => []];

        if (!$form->getError()) {
            $data = $form->getData();
            $error = false;
            if ($cp->getError()) {
                $error = true;
                $errors[self::CURRENT_PASSWORD][] = $cp->getError();
            }
            if ($np->getError()) {
                $error = true;
                $errors[self::NEW_PASSWORD][] = $np->getError();
            }

            $entered_current_password = $cp->getValue();
            $entered_new_password = $np->getValue();

            if (
                $entered_current_password === $entered_new_password &&
                ($this->user->isPasswordExpired() || $this->user->isPasswordChangeDemanded())
            ) {
                $error = true;
                $errors[self::NEW_PASSWORD][] = $this->lng->txt('new_pass_equals_old_pass');
            }

            if (!$error) {
                $this->user->resetPassword($entered_new_password, $entered_new_password);
                if ($entered_current_password !== $entered_new_password) {
                    $this->user->setLastPasswordChangeToNow();
                    $this->user->setPasswordPolicyResetStatus(false);
                    $this->user->update();
                }

                if (ilSession::get('orig_request_target')) {
                    $this->tpl->setOnScreenMessage(
                        $this->tpl::MESSAGE_TYPE_SUCCESS,
                        $this->lng->txt('saved_successfully'),
                        true
                    );
                    $target = ilSession::get('orig_request_target');
                    ilSession::set('orig_request_target', '');
                    $this->ctrl->redirectToURL($target);
                } else {
                    $this->showPassword(
                        null,
                        true,
                        $this->ui_factory->messageBox()->success($this->lng->txt('saved_successfully'))
                    );

                    return;
                }
            }
        }

        $this->showPassword($this->getPasswordForm($this->request, $errors));
    }
}
