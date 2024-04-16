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

namespace ILIAS\Authentication\Password;

use ilAuthUtils;
use ilCtrlInterface;
use ilDAVActivationChecker;
use ilGlobalTemplateInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Input\Field\Password;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilLanguage;
use ilObjUser;
use ilSecuritySettingsChecker;
use ilSession;
use ilUtil;
use Psr\Http\Message\ServerRequestInterface;

class ilLocalPasswordSettingsGUI
{
    private ServerRequestInterface $request;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilCtrlInterface $ctrl;
    private ilLocalPasswordManager $password_manager;

    public function __construct()
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->password_manager = ilLocalPasswordManager::getInstance();
        $this->lng->loadLanguageModule('user');
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            default:
                $this->$cmd();

                break;
        }
    }

    public function showPassword(
        Form $form = null,
        bool $hide_form = false
    ): void {
        // check whether password of user have to be changed
        // due to first login or password of user is expired
        if ($this->user->isPasswordChangeDemanded()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('password_change_on_first_login_demand'));
        } elseif ($this->user->isPasswordExpired()) {
            $msg = $this->lng->txt('password_expired');
            $password_age = $this->user->getPasswordAge();
            $this->tpl->setOnScreenMessage('info', sprintf($msg, $password_age));
        }

        if (!$form && !$hide_form) {
            $form = $this->getPasswordForm();
        }
        $this->tpl->setContent(!$hide_form ? $this->ui_renderer->render($form) : '');
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
                    $this->lng->txt('current_password'),
                    ilSecuritySettingsChecker::getPasswordRequirementsInfo()
                );

                $pw_info_set = true;
                if ($this->user->getPasswd()) {
                    $cpass = $cpass->withRequired(true);
                }
                $cpass = $cpass->withRevelation(true);
                $cpass_error = $errors['current_password'] ?? [];
                if ($cpass_error !== []) {
                    $cpass = $cpass->withError(implode('<br>', $cpass_error));
                }

                $items['current_password'] = $cpass;
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
            $ipass_error = $errors['new_password'] ?? [];
            if ($ipass_error !== []) {
                $ipass = $ipass->withError(implode('<br>', $ipass_error));
            }
            $items['new_password'] = $ipass;

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
        /** @var Password $cp
         * @var Password $np
         */
        $cp = $section->getInputs()['current_password'];
        $np = $section->getInputs()['new_password'];
        $errors = ['current_password' => [], 'new_password' => []];

        $entered_current_password = $cp->getValue();
        $entered_new_password = $np->getValue();

        if (!$form->getError()) {
            $data = $form->getData();
            $error = false;

            if ((int) $this->user->getAuthMode(true) === ilAuthUtils::AUTH_LOCAL) {
                if (!ilLocalPasswordManager::getInstance()->verifyPassword(
                    $this->user,
                    $entered_current_password
                )) {
                    $error = true;
                    $errors['current_password'][] = $this->lng->txt('passwd_wrong');
                }
            }

            if (!ilSecuritySettingsChecker::isPassword($entered_new_password, $custom_error)) {
                $error = true;
                if ($custom_error !== '') {
                    $errors['new_password'][] = $custom_error;
                } else {
                    $errors['new_password'][] = $this->lng->txt('passwd_invalid');
                }
            }
            $error_lng_var = '';
            if (!ilSecuritySettingsChecker::isPasswordValidForUserContext(
                $entered_new_password,
                $this->user,
                $error_lng_var
            )) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
                $errors['new_password'][] = $this->lng->txt($error_lng_var);
                $error = true;
            }
            if (
                $entered_current_password === $entered_new_password &&
                ($this->user->isPasswordExpired() || $this->user->isPasswordChangeDemanded())
            ) {
                $error = true;
                $errors['new_password'][] = $this->lng->txt('new_pass_equals_old_pass');
            }

            if (!$error) {
                $this->user->resetPassword($entered_new_password, $entered_new_password);
                if ($entered_current_password !== $entered_new_password) {
                    $this->user->setLastPasswordChangeToNow();
                    $this->user->setPasswordPolicyResetStatus(false);
                    $this->user->update();
                }

                if (ilSession::get('orig_request_target')) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                    $target = ilSession::get('orig_request_target');
                    ilSession::set('orig_request_target', '');
                    ilUtil::redirect($target);
                } else {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
                    $this->showPassword(null, true);

                    return;
                }
            }
        }

        $this->showPassword($this->getPasswordForm($this->request, $errors));
    }
}
