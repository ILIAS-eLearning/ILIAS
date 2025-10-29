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

use ILIAS\Data\Password;
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
    private const string NEW_PASSWORD = 'new_password';
    private const string CURRENT_PASSWORD = 'current_password';
    public const string CMD_SHOW_PASSWORD = 'showPassword';
    public const string CMD_SAVE_PASSWORD = 'savePassword';

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
        $this->tpl->setTitle($this->lng->txt('chg_password'));
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            default:
                if (method_exists($this, $cmd . 'Cmd')) {
                    $this->{$cmd . 'Cmd'}();
                } else {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
                }
                break;
        }
    }

    public function showPasswordCmd(
        ?Form $form = null,
        bool $hide_form = false,
        ?MessageBox $message_box = null
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

    private function securePasswordConstraint(): \ILIAS\Refinery\Constraint
    {
        $custom_error = '';

        return $this->refinery->custom()->constraint(function (Password $value) use (&$custom_error): bool {
            return ilSecuritySettingsChecker::isPassword($value->toString(), $custom_error);
        }, function (Closure $txt, Password $value) use (&$custom_error): string {
            if ($custom_error !== '' && $custom_error !== null) {
                return $custom_error;
            }

            return $this->lng->txt('passwd_invalid');
        });
    }

    private function validInUserContextConstraint(): \ILIAS\Refinery\Constraint
    {
        $error_lng_var = null;

        return $this->refinery->custom()->constraint(
            function (Password $value) use (&$error_lng_var): bool {
                return ilSecuritySettingsChecker::isPasswordValidForUserContext(
                    $value->toString(),
                    $this->user,
                    $error_lng_var
                );
            },
            function (Closure $cls, Password $value) use (&$error_lng_var): string {
                return $this->lng->txt($error_lng_var ?? '');
            }
        );
    }

    private function passwordToString(): \ILIAs\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(
            static function (ILIAS\Data\Password $value): string {
                return trim($value->toString());
            }
        );
    }

    public function getPasswordForm(): Form
    {
        $entered_current_passwd = null;

        $items = [];
        if ($this->password_manager->allowPasswordChange($this->user)) {
            $pw_info_set = false;
            if ((int) $this->user->getAuthMode(true) === ilAuthUtils::AUTH_LOCAL) {
                $current_passwd = $this->ui_factory
                    ->input()
                    ->field()
                    ->password(
                        $this->lng->txt(self::CURRENT_PASSWORD),
                        ilSecuritySettingsChecker::getPasswordRequirementsInfo()
                    )
                    ->withRevelation(true)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->constraint(
                            function (Password $value) use (&$entered_current_passwd): bool {
                                $entered_current_passwd = $value;

                                return
                                    ((int) $this->user->getAuthMode(true) !== ilAuthUtils::AUTH_LOCAL) ||
                                    LocalUserPasswordManager::getInstance()->verifyPassword(
                                        $this->user,
                                        $value->toString()
                                    );
                            },
                            $this->lng->txt('passwd_wrong')
                        )
                    )
                    ->withAdditionalTransformation($this->passwordToString());

                $pw_info_set = true;
                if ($this->user->getPasswd()) {
                    $current_passwd = $current_passwd->withRequired(true);
                }

                $items[self::CURRENT_PASSWORD] = $current_passwd;
            }

            $new_passwd = $this->ui_factory
                ->input()
                ->field()
                ->password(
                    $this->lng->txt('desired_password'),
                )
                ->withRevelation(true)
                ->withRequired(true)
                ->withAdditionalTransformation($this->securePasswordConstraint())
                ->withAdditionalTransformation($this->validInUserContextConstraint())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->constraint(
                        function (Password $value) use (&$entered_current_passwd): bool {
                            if ($entered_current_passwd === null) {
                                return true;
                            }

                            $passwords_equal = $entered_current_passwd->toString() === $value->toString();
                            $is_forced_change = $this->user->isPasswordChangeDemanded()
                                || $this->user->isPasswordExpired();

                            return !($passwords_equal && $is_forced_change);
                        },
                        $this->lng->txt('new_pass_equals_old_pass')
                    )
                )
                ->withAdditionalTransformation($this->passwordToString());

            if ($pw_info_set === false) {
                $new_passwd = $new_passwd->withByline(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
            }

            $items[self::NEW_PASSWORD] = $new_passwd;

            switch ($this->user->getAuthMode(true)) {
                case ilAuthUtils::AUTH_LOCAL:
                    $title = $this->lng->txt('chg_password');

                    break;
                case ilAuthUtils::AUTH_SHIBBOLETH:
                default:
                    $title = $this->lng->txt('chg_ilias_password');

                    break;
            }

            $items = [
                $this->ui_factory->input()->field()->section($items, $title)
            ];
        }

        $form = $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getLinkTarget($this, 'savePassword'),
                $items
            )
            ->withSubmitLabel($this->lng->txt('save'))
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(static function (array $values): array {
                    return array_merge(...$values);
                })
            );

        return $form;
    }

    public function savePasswordCmd(): void
    {
        if (!$this->password_manager->allowPasswordChange($this->user)) {
            $this->ctrl->redirect($this, 'showPersonalData');
        }

        $form = $this->getPasswordForm()->withRequest($this->request);
        if (!$form->getError()) {
            $data = $form->getData();
            $entered_current_passwd = $data[self::CURRENT_PASSWORD] ?? '';
            $entered_new_passwd = $data[self::NEW_PASSWORD];

            $this->user->resetPassword($entered_new_passwd, $entered_new_passwd);
            if ($entered_current_passwd !== $entered_new_passwd) {
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
                $this->showPasswordCmd(
                    null,
                    true,
                    $this->ui_factory->messageBox()->success($this->lng->txt('saved_successfully'))
                );

                return;
            }
        }

        $this->showPasswordCmd($form);
    }
}
