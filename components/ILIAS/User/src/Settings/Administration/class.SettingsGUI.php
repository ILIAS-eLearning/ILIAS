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

namespace ILIAS\User\Settings\Administration;

use ILIAS\User\RedirectOnMissingWrite;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileConfigurationRepository;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\Authentication\Password\LocalUserPasswordManager;
use Psr\Http\Message\ServerRequestInterface;

class SettingsGUI
{
    use RedirectOnMissingWrite;

    private $modal = null;

    public function __construct(
        private readonly \ILIAS\Language\Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccess $access,
        private readonly \ilSetting $settings,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request,
        private readonly ProfileConfigurationRepository $profile_configuration_repository
    ) {

    }

    public function executeCommand(): void
    {
        $this->redirectOnMissingWrite($this->access, $this->ctrl, $this->tpl, $this->lng);
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    private function showCmd(?StandardForm $form = null): void
    {
        if ($form !== null) {
            $this->tpl->setContent(
                $this->ui_renderer->render($form)
            );
            return;
        }

        $content = [
            $this->buildForm()
        ];

        if ($this->modal !== null) {
            $content[] = $this->modal;
        }

        $this->tpl->setContent(
            $this->ui_renderer->render($content)
        );
    }

    private function saveCmd(): void
    {
        $form = $this->buildForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->showCmd($form);
            return;
        }

        $security = \ilSecuritySettings::_getInstance();

        // account security settings
        $security->setPasswordCharsAndNumbersEnabled(
            $data['password']['password_chars_and_numbers_enabled']
        );
        $security->setPasswordSpecialCharsEnabled(
            $data['password']['password_special_chars_enabled']
        );
        $security->setPasswordMinLength(
            $data['password']['password_min_length']
        );
        $security->setPasswordMaxLength(
            $data['password']['password_max_length']
        );
        $security->setPasswordNumberOfUppercaseChars(
            $data['password']['password_min_uppercase_chars']
        );
        $security->setPasswordNumberOfLowercaseChars(
            $data['password']['password_min_lowercase_chars']
        );
        $security->setPasswordMaxAge(
            $data['password']['password_max_age']
        );
        $security->setLoginMaxAttempts(
            $data['security']['login_max_attempts']
        );
        $security->setPreventionOfSimultaneousLogins(
            $data['security']['prevent_simultaneous_logins']
        );
        $security->setPasswordChangeOnFirstLoginEnabled(
            $data['password']['password_change_on_first_login_enabled']
        );
        $security->setPasswordMustNotContainLoginnameStatus(
            $data['password']['password_must_not_contain_loginame']
        );

        $security->save();

        \ilUserAccountSettings::getInstance()->enableLocalUserAdministration(
            $data['general']['local_user_administration']
        );
        \ilUserAccountSettings::getInstance()->restrictUserAccess(
            $data['general']['restrict_search_in_user_accounts']
        );
        \ilUserAccountSettings::getInstance()->update();

        if ($this->profile_configuration_repository->getByClass(Alias::class)->isChangeableByUser()) {

            $this->settings->set(
                'create_history_loginname',
                $data['login_name']['create_history_loginname'] ? '1' : '0'
            );
            $this->settings->set(
                'reuse_of_loginnames',
                $data['login_name']['allow_reuse_of_loginnames'] ? '1' : '0'
            );
            $this->settings->set(
                'loginname_change_blocking_time',
                $this->refinery->kindlyTo()->string()->transform(
                    $data['login_name']['loginname_change_blocking_time']
                )
            );
        }

        $this->settings->set(
            'user_reactivate_code',
            $data['general']['reactivate_by_code'] ? '1' : '0'
        );

        $this->settings->set(
            'user_delete_own_account',
            $data['general']['allow_account_deletion']['allow_account_deletion'] ? '1' : '0'
        );

        if ($data['general']['allow_account_deletion']['allow_account_deletion']) {
            $this->settings->set(
                'user_delete_own_account_email',
                $data['general']['allow_account_deletion']['notification_email']
            );
        }

        $this->settings->set(
            'tos_withdrawal_usr_deletion',
            $data['general']['tos_withdrawal_usr_deletion'] ? '1' : '0'
        );

        $this->settings->set(
            'dpro_withdrawal_usr_deletion',
            $data['general']['dpro_withdrawal_usr_deletion'] ? '1' : '0'
        );

        $this->settings->set(
            'session_reminder_lead_time',
            $data['general']['session_reminder_lead_time']
        );

        $this->settings->set(
            'password_assistance',
            $data['password']['password_assistance'] ? '1' : '0'
        );

        $this->settings->set(
            'letter_avatars',
            $data['login_name']['letter_avatars'] ? '1' : '0'
        );

        if ($this->needsPasswortResetPrompt($data['password']['password_policy_hash'], $security)) {
            $this->askForPasswordReset();
            return;
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->showCmd();
    }

    private function forcePasswordResetCmd(): void
    {
        LocalUserPasswordManager::getInstance()->resetLastPasswordChangeForLocalUsers();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('ps_passwd_policy_change_force_user_reset_succ'), true);
        $this->showCmd();
    }

    private function needsPasswortResetPrompt(
        string $password_settings_hash_from_form,
        \ilSecuritySettings $security
    ): bool {
        if ($password_settings_hash_from_form === '') {
            return false;
        }

        return $this->getPasswordPolicySettingsHash($security) !== $password_settings_hash_from_form;
    }

    private function askForPasswordReset(): void
    {
        $this->modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('ps_password_force_user_reset'),
            $this->lng->txt('ps_passwd_policy_changed_force_user_reset'),
            $this->ctrl->getFormActionByClass(self::class, 'forcePasswordReset')
        )->withActionButtonLabel($this->lng->txt('yes'))
        ->withCancelButtonLabel($this->lng->txt('no'));

        $this->modal = $this->modal->withOnLoad($this->modal->getShowSignal());
        $this->showCmd();
    }

    private function buildForm(): StandardForm
    {
        $security_settings = \ilSecuritySettings::_getInstance();

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, self::class],
                'save'
            ),
            [
                'general' => $this->buildGeneralSettings(\ilUserAccountSettings::getInstance()),
                'password' => $this->buildPasswordSettings($security_settings),
                'security' => $this->buildSecuritySettings($security_settings),
                'login_name' => $this->buildLoginNameSettings()
            ]
        );
    }

    private function buildGeneralSettings(
        \ilUserAccountSettings $account_settings
    ): Section {
        $ff = $this->ui_factory->input()->field();

        return $ff->section(
            [
                'local_user_administration' => $ff->checkbox(
                    $this->lng->txt('enable_local_user_administration'),
                    $this->lng->txt('enable_local_user_administration_info')
                )->withValue($account_settings->isLocalUserAdministrationEnabled()),
                'restrict_search_in_user_accounts' => $ff->checkbox(
                    $this->lng->txt('restrict_user_access'),
                    $this->lng->txt('restrict_user_access_info')
                )->withValue($account_settings->isUserAccessRestricted()),
                'reactivate_by_code' => $ff->checkbox(
                    $this->lng->txt('user_account_code_setting'),
                    $this->lng->txt('user_account_code_setting_info')
                )->withValue($this->settings->get('user_reactivate_code') === '1'),
                'allow_account_deletion' => $ff->optionalGroup(
                    [
                        'notification_email' => $ff->text(
                            $this->lng->txt('user_delete_own_account_notification_email')
                        )->withValue($this->settings->get('user_delete_own_account_email', ''))
                    ],
                    $this->lng->txt('user_allow_delete_own_account')
                )->withAdditionalTransformation(
                    $this->buildAllowAccountDeletionTrafo()
                )->withValue(
                    $this->settings->get('user_delete_own_account') === '1'
                        ? [
                            'notification_email' => $this->settings->get('user_delete_own_account_email', '')
                        ] : null
                ),
                'tos_withdrawal_usr_deletion' => $ff->checkbox(
                    $this->lng->txt('tos_withdrawal_usr_deletion'),
                    $this->lng->txt('tos_withdrawal_usr_deletion_desc')
                )->withValue($this->settings->get('tos_withdrawal_usr_deletion') === '1'),
                'dpro_withdrawal_usr_deletion' => $ff->checkbox(
                    $this->lng->txt('dpro_withdrawal_usr_deletion'),
                    $this->lng->txt('dpro_withdrawal_usr_deletion_desc')
                )->withValue($this->settings->get('dpro_withdrawal_usr_deletion') === '1'),
                'session_reminder_lead_time' => $ff->numeric(
                    $this->lng->txt('session_reminder_input'),
                    sprintf(
                        $this->lng->txt('session_reminder_default_lead_time_info'),
                        \ilSessionReminder::LEAD_TIME_DISABLED,
                        \ilSessionReminder::SUGGESTED_LEAD_TIME,
                        \ilDatePresentation::secondsToString(\ilSession::getSessionExpireValue(), true)
                    )
                )->withRequired(true)
                ->withAdditionalTransformation($this->refinery->kindlyTo()->string())
                ->withValue(
                    \ilSessionReminder::byLoggedInUser()->getGlobalSessionReminderLeadTime()
                )
            ],
            $this->lng->txt('general_settings')
        );
    }

    private function buildPasswordSettings(
        \ilSecuritySettings $security_settings
    ): Section {
        $ff = $this->ui_factory->input()->field();

        return $ff->section(
            [
                'password_change_on_first_login_enabled' => $ff->checkbox(
                    $this->lng->txt('ps_password_change_on_first_login_enabled'),
                    $this->lng->txt('ps_password_change_on_first_login_enabled_info')
                )->withValue($security_settings->isPasswordChangeOnFirstLoginEnabled()),
                'password_must_not_contain_loginame' => $ff->checkbox(
                    $this->lng->txt('ps_password_must_not_contain_loginame'),
                    $this->lng->txt('ps_password_must_not_contain_loginame_info')
                )->withValue($security_settings->getPasswordMustNotContainLoginnameStatus()),
                'password_chars_and_numbers_enabled' => $ff->checkbox(
                    $this->lng->txt('ps_password_chars_and_numbers_enabled'),
                    $this->lng->txt('ps_password_chars_and_numbers_enabled_info')
                )->withValue($security_settings->isPasswordCharsAndNumbersEnabled()),
                'password_special_chars_enabled' => $ff->checkbox(
                    $this->lng->txt('ps_password_special_chars_enabled'),
                    $this->lng->txt('ps_password_special_chars_enabled_info')
                )->withValue($security_settings->isPasswordSpecialCharsEnabled()),
                'password_min_length' => $ff->numeric(
                    $this->lng->txt('ps_password_min_length'),
                    $this->lng->txt('ps_password_min_length_info')
                )->withRequired(true)
                ->withAdditionalTransformation($this->refinery->int()->isGreaterThan(0))
                ->withValue($security_settings->getPasswordMinLength()),
                'password_max_length' => $ff->numeric(
                    $this->lng->txt('ps_password_max_length'),
                    $this->lng->txt('ps_password_max_length_info')
                )->withRequired(true)
                ->withValue($security_settings->getPasswordMaxLength()),
                'password_min_uppercase_chars' => $ff->numeric(
                    $this->lng->txt('ps_password_uppercase_chars_num'),
                    $this->lng->txt('ps_password_uppercase_chars_num_info')
                )->withRequired(true)
                ->withValue($security_settings->getPasswordNumberOfUppercaseChars()),
                'password_min_lowercase_chars' => $ff->numeric(
                    $this->lng->txt('ps_password_lowercase_chars_num'),
                    $this->lng->txt('ps_password_lowercase_chars_num_info')
                )->withRequired(true)
                ->withValue($security_settings->getPasswordNumberOfLowercaseChars()),
                'password_max_age' => $ff->numeric(
                    $this->lng->txt('ps_password_max_age'),
                    $this->lng->txt('ps_password_max_age_info')
                )->withRequired(true)
                ->withValue($security_settings->getPasswordMaxAge()),
                'password_assistance' => $ff->checkbox(
                    $this->lng->txt('enable_password_assistance'),
                    $this->lng->txt('password_assistance_info')
                )->withValue($this->settings->get('password_assistance') === '1'),
                'password_policy_hash' => $ff->hidden()->withValue(
                    $this->getPasswordPolicySettingsHash($security_settings)
                )
            ],
            $this->lng->txt('ps_password_settings')
        )->withAdditionalTransformation($this->buildCheckPasswordMinLengthConstraint())
            ->withAdditionalTransformation($this->buildCheckPasswordMaxLengthConstraint());
    }

    private function buildSecuritySettings(
        \ilSecuritySettings $security_settings
    ): Section {
        $ff = $this->ui_factory->input()->field();

        return $ff->section(
            [
                'login_max_attempts' => $ff->numeric(
                    $this->lng->txt('ps_login_max_attempts'),
                    $this->lng->txt('ps_login_max_attempts_info')
                )->withRequired(true)
                ->withAdditionalTransformation(
                    $this->refinery->int()->isLessThan(\ilSecuritySettings::MAX_LOGIN_ATTEMPTS)
                )->withValue($security_settings->getLoginMaxAttempts()),
                'prevent_simultaneous_logins' => $ff->checkbox(
                    $this->lng->txt('ps_prevent_simultaneous_logins'),
                    $this->lng->txt('ps_prevent_simultaneous_logins_info')
                )->withValue($security_settings->isPreventionOfSimultaneousLoginsEnabled())
            ],
            $this->lng->txt('ps_security_protection')
        );
    }

    private function buildLoginNameSettings(): Section
    {
        $ff = $this->ui_factory->input()->field();
        $alias_changeable_by_user = $this->profile_configuration_repository->getByClass(Alias::class)->isChangeableByUser();
        return $ff->section(
            [
                'create_history_loginname' => $ff->checkbox(
                    $this->lng->txt('history_loginname'),
                    $alias_changeable_by_user
                        ? $this->lng->txt('loginname_history_info')
                        : $this->lng->txt('activate_in_profile_fields')
                )->withValue($this->settings->get('create_history_loginname') === '1')
                ->withDisabled(!$alias_changeable_by_user),
                'allow_reuse_of_loginnames' => $ff->checkbox(
                    $this->lng->txt('reuse_of_loginnames_contained_in_history'),
                    $alias_changeable_by_user
                        ? $this->lng->txt('reuse_of_loginnames_contained_in_history_info')
                        : $this->lng->txt('activate_in_profile_fields')
                )->withValue($this->settings->get('reuse_of_loginnames') === '1')
                ->withDisabled(!$alias_changeable_by_user),
                'loginname_change_blocking_time' => $ff->numeric(
                    $this->lng->txt('loginname_change_blocking_time'),
                    $alias_changeable_by_user
                        ? $this->lng->txt('loginname_change_blocking_time_info')
                        : $this->lng->txt('activate_in_profile_fields')
                )->withStepSize(
                    0.00001
                )->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        fn(?float $v): ?float => $v === null ? null : $v * 86400
                    )
                )->withValue(
                    (float) $this->settings->get('loginname_change_blocking_time') / 86400
                )->withDisabled(!$alias_changeable_by_user),
                'letter_avatars' => $ff->checkbox(
                    $this->lng->txt('usr_letter_avatars'),
                    $this->lng->txt('usr_letter_avatars_info')
                )->withValue($this->settings->get('letter_avatars') === '1'),
            ],
            $this->lng->txt('loginname_settings')
        );
    }

    private function buildAllowAccountDeletionTrafo(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function (?array $vs): array {
                if ($vs === null) {
                    return [
                        'allow_account_deletion' => false
                    ];
                }

                $vs['allow_account_deletion'] = true;
                return $vs;
            }
        );
    }

    private function buildCheckPasswordMinLengthConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            static fn(array $vs): bool => $vs['password_min_length'] > 1 + $vs['password_min_uppercase_chars']
                + $vs['password_min_lowercase_chars'] + (int) $vs['password_special_chars_enabled']
                + (int) $vs['password_chars_and_numbers_enabled'],
            $this->lng->txt('ps_min_value_too_small_for_requirements')
        );
    }

    private function buildCheckPasswordMaxLengthConstraint(): Constraint
    {
        return $this->refinery->custom()->constraint(
            static fn(array $vs): bool => $vs['password_max_length'] === 0 ||
                $vs['password_min_length'] < $vs['password_max_length'],
            $this->lng->txt('ps_error_message_password_max_less_min')
        );
    }

    private function getPasswordPolicySettingsHash(\ilSecuritySettings $security): string
    {
        return md5(
            implode(
                '',
                [
                    'password_must_not_contain_loginame' => $security->getPasswordMustNotContainLoginnameStatus() ? 1 : 0,
                    'password_chars_and_numbers_enabled' => $security->isPasswordCharsAndNumbersEnabled() ? 1 : 0,
                    'password_special_chars_enabled' => $security->isPasswordSpecialCharsEnabled() ? 1 : 0,
                    'password_min_length' => $security->getPasswordMinLength(),
                    'password_max_length' => $security->getPasswordMaxLength(),
                    'password_ucase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
                    'password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
                ]
            )
        );
    }
}
