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

namespace ILIAS\Authentication\Form;

use ilAuthUtils;
use ilCtrlInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;
use ilLDAPServer;
use ilObject;
use ilRbacReview;
use ilRbacSystem;

readonly class ApacheAuthSettingsForm
{
    private UIFactory $ui_factory;
    private ilLanguage $lng;
    private ilRbacSystem $rbac_system;
    private ilRbacReview $rbac_review;
    private ilCtrlInterface $ctrl;
    private FieldFactory $ui_field;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private int $ref_id,
        private object $parentObject,
        private string $show_command,
        private string $save_command,
        private array $values = [],
        ?UIFactory $ui_factory = null,
        ?ilLanguage $lng = null,
        ?ilRbacSystem $rbac_system = null,
        ?ilRbacReview $rbac_review = null,
        ?ilCtrlInterface $ctrl = null
    ) {
        global $DIC;

        $this->ui_factory = $ui_factory ?? $DIC->ui()->factory();
        $this->lng = $lng ?? $DIC->language();
        $this->rbac_system = $rbac_system ?? $DIC->rbac()->system();
        $this->rbac_review = $rbac_review ?? $DIC->rbac()->review();
        $this->ctrl = $ctrl ?? $DIC->ctrl();

        $this->ui_field = $this->ui_factory->input()->field();

        $this->lng->loadLanguageModule('auth');
    }

    public function buildForm(): StandardForm
    {
        $access = $this->rbac_system->checkAccess('write', $this->ref_id);
        $inputs = [
            'apache_enable_auth' => $this->buildEnableAuthInput(),
            'apache_enable_local' => $this->ui_field->checkbox($this->lng->txt('apache_enable_local'))
                                                    ->withValue((bool) ($this->values['apache_enable_local'] ?? true)),
            'apache_enable_ldap' => $this->buildLdapEnableInput(),
            'apache_auth_indicator_name' => $this->ui_field->text($this->lng->txt('apache_auth_indicator_name'))
                                                           ->withRequired(true)
                                                           ->withValue($this->values['apache_auth_indicator_name'] ?? ''),
            'apache_auth_indicator_value' => $this->ui_field->text($this->lng->txt('apache_auth_indicator_value'))
                                                            ->withRequired(true)
                                                            ->withValue($this->values['apache_auth_indicator_value'] ?? ''),
            'apache_auth_enable_override_login_page' => $this->buildAuthEnableOverrideLoginPageInput(),
            'apache_auth_authenticate_on_login_page' => $this->ui_field->checkbox($this->lng->txt('apache_auth_authenticate_on_login_page'))
                                                                       ->withValue((bool) ($this->values['apache_auth_authenticate_on_login_page'] ?? true)),
            'apache_auth_username_config' => $this->ui_field->section([
                'apache_auth_username_config_type' => $this->buildAuthUsernameConfigTypeInput()
            ], $this->lng->txt('apache_auth_username_config')),
            'apache_auth_security' => $this->ui_field->section([
                'apache_auth_domains' => $this->ui_field->textarea(
                    $this->lng->txt('apache_auth_domains'),
                    $this->lng->txt('apache_auth_domains_description')
                )->withValue($this->values['apache_auth_domains'] ?? '')
            ], $this->lng->txt('apache_auth_security'))
        ];

        if (!$access) {
            foreach ($inputs as $key => $input) {
                $inputs[$key] = $input->withDisabled(true);
            }
        }


        $form = $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this->parentObject, $access ? $this->save_command : $this->show_command),
            $inputs
        );

        if (!$access) {
            $form = $form->withSubmitLabel($this->lng->txt('refresh'));
        }

        return $form;
    }

    private function buildEnableAuthInput(): OptionalGroup
    {
        $roleOptions = [];
        foreach ($this->rbac_review->getGlobalRolesArray() as $role) {
            $roleOptions[$role['obj_id']] = ilObject::_lookupTitle($role['obj_id']);
        }

        $apache_default_role = $this->ui_field->select(
            $this->lng->txt('apache_default_role'),
            $roleOptions
        );

        $apache_local_autocreate = $this->ui_field->optionalGroup([
            'apache_default_role' => $apache_default_role
        ], $this->lng->txt('apache_autocreate'));

        return $this->ui_field->optionalGroup([
            'apache_local_autocreate' => $apache_local_autocreate
        ], $this->lng->txt('apache_enable_auth'))
            ->withValue(
                $this->checkGroupEnabled('apache_enable_auth')
                ? [
                    'apache_local_autocreate' => $this->checkGroupEnabled('apache_local_autocreate')
                        ? [
                            'apache_default_role' => $this->values['apache_default_role'] ?? 4
                        ]
                        : null
                ]
                : null
            );
    }

    private function buildLdapEnableInput(): Checkbox|OptionalGroup
    {
        $servers = ilLDAPServer::getServerIds();

        if ($servers !== []) {
            $options[0] = $this->lng->txt('select_one');
            foreach ($servers as $server_id) {
                $ldap_server = new ilLDAPServer($server_id);
                $options[$server_id] = $ldap_server->getName();
            }

            $apache_enable_ldap = $this->ui_field->optionalGroup([
                'apache_ldap_sid' => $this->ui_field->select($this->lng->txt('auth_ldap_server_ds'), $options)
                    ->withRequired(true)
            ], $this->lng->txt('apache_enable_ldap'), $this->lng->txt('apache_ldap_hint_ldap_must_be_configured'))
                ->withValue(
                    $this->checkGroupEnabled('apache_enable_ldap')
                    ? [
                        'apache_ldap_sid' => $this->values['apache_ldap_sid'] ?? ilLDAPServer::getDataSource(ilAuthUtils::AUTH_APACHE)
                    ]
                    : null
                );
        } else {
            $apache_enable_ldap = $this->ui_field->checkbox(
                $this->lng->txt('apache_enable_ldap'),
                $this->lng->txt('apache_ldap_hint_ldap_must_be_configured')
            )->withValue((bool) ($this->values['apache_enable_ldap'] ?? true));
        }

        return $apache_enable_ldap;
    }

    private function buildAuthEnableOverrideLoginPageInput(): OptionalGroup
    {
        return $this->ui_field->optionalGroup([
            'apache_auth_target_override_login_page' => $this->ui_field->text($this->lng->txt('apache_auth_target_override_login'))
                ->withRequired(true)
        ], $this->lng->txt('apache_auth_enable_override_login'))
            ->withValue(
                $this->checkGroupEnabled('apache_auth_enable_override_login_page')
                ? [
                    'apache_auth_target_override_login_page' => $this->values['apache_auth_target_override_login_page'] ?? ''
                ]
                : null
            );
    }

    private function buildAuthUsernameConfigTypeInput(): SwitchableGroup
    {
        return $this->ui_field->switchableGroup([
            '1' => $this->ui_field->group([
                'apache_auth_username_direct_mapping_fieldname' => $this->ui_field->text(
                    $this->lng->txt('apache_auth_username_direct_mapping_fieldname')
                )->withValue($this->values['apache_auth_username_direct_mapping_fieldname'] ?? '')
            ], $this->lng->txt('apache_auth_username_direct_mapping')),
            '2' => $this->ui_field->group([], $this->lng->txt('apache_auth_username_extended_mapping'))->withDisabled(true),
            '3' => $this->ui_field->group([], $this->lng->txt('apache_auth_username_by_function')),
        ], $this->lng->txt('apache_auth_username_config_type'))
            ->withValue(
                isset($this->values['apache_auth_username_config_type']) &&
                $this->values['apache_auth_username_config_type'] !== '' ?
                    $this->values['apache_auth_username_config_type'] :
                    '1'
            );
    }

    private function checkGroupEnabled(string $post_var): bool
    {
        return isset($this->values[$post_var]) && $this->values[$post_var];
    }
}
