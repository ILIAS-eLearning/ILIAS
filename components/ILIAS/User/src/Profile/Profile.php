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

namespace ILIAS\User\Profile;

use ILIAS\User\LocalDIC;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Profile\Fields\AvailableSections as AvailableProfileSections;
use ILIAS\User\Profile\Fields\Repository as ProfileFieldsRepository;
use ILIAS\Language\Language;

class Profile
{
    public const MODE_DESKTOP = 1;
    public const MODE_REGISTRATION = 2;

    private int $mode = self::MODE_DESKTOP;

    private \ilSetting $settings;
    private Language $lng;
    private \ilRbacReview $rbac_review;

    private ProfileFieldsRepository $profile_fields_repository;
    private array $user_fields;
    protected string $ajax_href;
    protected array $skip_fields; // Missing array type.
    protected array $skip_groups; // Missing array type.

    protected \ilUserSettingsConfig $user_settings_config;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->settings = $DIC['ilSetting'];
        $this->lng = $DIC['lng'];
        $this->rbac_review = $DIC['rbacreview'];

        $this->user_settings_config = new \ilUserSettingsConfig();
        $this->profile_fields_repository = LocalDIC::dic()['profile.fields.repository'];
        $this->user_fields = $this->profile_fields_repository->get();

        $this->skip_groups = [];
        $this->skip_fields = [];

        $this->lng->loadLanguageModule('awrn');
        $this->lng->loadLanguageModule('buddysystem');
    }

    /**
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getStandardFields(

    ): array {
        return array_reduce(
            $this->user_fields,
            function (array $c, ProfileField $v): array {
                if (!in_array($v->getSection(), $this->skip_groups)
                    && !in_array($v->getIdentifier(), $this->skip_fields)) {
                    $c[] = $v;
                }
                return $c;
            },
            []
        );
    }

    public function getVisibleFieldsBySection(
        FormTypes $form_type
    ): array {
        return array_filter(
            array_reduce(
                $this->user_fields,
                function (array $c, ProfileField $v) use ($form_type): array {
                    if (in_array($v->getSection(), $this->skip_groups)
                        || in_array($v->getIdentifier(), $this->skip_fields)
                        || !$form_type->isFieldVisibleInType($v)) {
                        return $c;
                    }
                    $c[$v->getSection()->value][] = $v;
                    return $c;
                },
                array_reduce(
                    AvailableProfileSections::cases(),
                    static function (array $c, AvailableProfileSections $v): array {
                        $c[$v->value] = [];
                        return $c;
                    },
                    []
                )
            )
        );
    }

    public function getLocalUserAdministrationFields(): array
    {
        $fields = [];
        foreach ($this->getStandardFields() as $field => $info) {
            if ($this->settings->get('usr_settings_visib_lua_' . $field, '1')) {
                $fields[$field] = $info;
            } elseif ($info['visib_lua_fix_value'] ?? false) {
                $fields[$field] = $info;
            }
        }
        return $fields;
    }

    public function skipGroup(string $group): void
    {
        $this->skip_groups[] = $group;
    }

    public function skipField(string $field_definition_class): void
    {
        $this->skip_fields[] = $this->profile_fields_repository->getByClass(
            $field_definition_class
        )->getIdentifier();
    }

    public function addStandardFieldsToForm(
        \ilPropertyFormGUI $form,
        FormTypes $form_type,
        \ilObjUser $current_user,
        array $custom_fields = []
    ): \ilPropertyFormGUI {
        $registration_settings = null;
        if ($this->mode === self::MODE_REGISTRATION) {
            $registration_settings = new \ilRegistrationSettings();
            $this->addRegistrationFieldsToFieldArray();
        }

        return array_reduce(
            $this->getVisibleFieldsBySection($form_type),
            function (\ilPropertyFormGUI $c, array $v) use ($current_user): \ilPropertyFormGUI {
                $section_header = new \ilFormSectionHeaderGUI();
                $section_header->setTitle($this->lng->txt($v[0]->getSection()->value));
                $c->addItem($section_header);
                return $this->addSectionFieldsToForm($current_user, $c, $v);
            },
            $form
        );

        // append custom fields as 'other'
        if ($custom_fields !== [] && !$custom_fields_done) {
            $form = $this->addCustomFieldsToForm(
                $form,
                $custom_fields,
                $current_group
            );
        }
    }

    private function addSectionFieldsToForm(
        \ilObjUser $current_user,
        \ilPropertyFormGUI $form,
        array $fields
    ): \ilPropertyFormGUI {
        return array_reduce(
            $fields,
            function (\ilPropertyFormGUI $form, ProfileField $v) use ($current_user): \ilPropertyFormGUI {
                $input = $v->getInput($this->lng, $current_user);
                $input->setDisabled(!$v->isChangeableByUser());
                $form->addItem($input);
                return $form;
            },
            $form
        );
    }

    private function addRegistrationFieldsToFieldArray(): void
    {
        $this->user_fields['username']['group'] = 'login_data';
        $this->user_fields['password']['group'] = 'login_data';
        $this->user_fields['language']['default'] = $this->lng->lang_key;

        // different position for role
        $roles = $this->user_fields['roles'];
        unset($this->user_fields['roles']);
        $this->user_fields['roles'] = $roles;
        $this->user_fields['roles']['group'] = 'settings';
    }

    private function addCustomFieldsToForm(
        \ilPropertyFormGUI $form,
        array $custom_fields,
        string $current_group
    ): \ilPropertyFormGUI {
        if ($current_group !== 'other') {
            $section_header = new \ilFormSectionHeaderGUI();
            $section_header->setTitle($this->lng->txt('other'));
            $form->addItem($section_header);
        }
        foreach ($custom_fields as $custom_field) {
            $form->addItem($custom_field);
        }
        return $form;
    }

    public function setAjaxCallback(string $href): void
    {
        $this->ajax_href = $href;
    }

    public function userSettingVisible(string $setting): bool
    {
        if ($this->mode === self::MODE_DESKTOP) {
            return ($this->user_settings_config->isVisible($setting));
        }

        if (isset($this->user_fields[$setting]['visib_reg_hide'])
            && $this->user_fields[$setting]['visib_reg_hide'] === true) {
            return true;
        }

        return ($this->settings->get('usr_settings_visib_reg_' . $setting, '1')
            || $this->settings->get('require_' . $setting, '0'));
    }

    public function setMode(int $mode): bool
    {
        if (in_array($mode, [self::MODE_DESKTOP, self::MODE_REGISTRATION])) {
            $this->mode = $mode;
            return true;
        }
        return false;
    }

    public function isProfileIncomplete(
        \ilObjUser $user,
        bool $include_udf = true,
        bool $personal_data_only = true
    ): bool {
        // standard fields
        foreach ($this->user_fields as $field) {
            // only if visible in personal data
            if ($personal_data_only && !$this->user_settings_config->isVisible($field->getIdentifier())) {
                continue;
            }

            if ($field->isRequired() && $field->getValueForUser($user)) {
                return true;
            }
        }

        // custom fields
        if (!$include_udf) {
            return false;
        }

        $user_defined_data = $user->getUserDefinedData();
        $user_defined_fields = \ilUserDefinedFields::_getInstance();
        foreach ($user_defined_fields->getRequiredDefinitions() as $field) {
            // only if visible in personal data
            if ($personal_data_only && !$field->isVisibleToUser()) {
                continue;
            }

            if (!($user_defined_data['f_' . $field->getIdentifier()] ?? false)) {
                \ilLoggerFactory::getLogger('user')->info('Profile is incomplete due to missing required udf.');
                return true;
            }
        }

        return false;
    }

    protected function isEditableByUser(string $setting): bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    public function getIgnorableRequiredSettings(): array // Missing array type.
    {
        $ignorableSettings = [];

        foreach (array_keys($this->user_fields) as $field) {
            // !!!username and password must not be ignored!!!
            if ('username' == $field ||
                'password' == $field) {
                continue;
            }

            // Field is not required -> continue
            if (!$this->settings->get('require_' . $field)) {
                continue;
            }

            if ($this->isEditableByUser($field)) {
                $ignorableSettings[] = $field;
            }
        }

        return $ignorableSettings;
    }
}
