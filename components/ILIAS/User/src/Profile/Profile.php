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
use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Profile\Fields\AvailableSections as AvailableProfileSections;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileFieldsConfigurationRepository;
use ILIAS\Language\Language;

class Profile
{
    public const MODE_DESKTOP = 1;
    public const MODE_REGISTRATION = 2;

    private int $mode = self::MODE_DESKTOP;

    private \ilSetting $settings;
    private Language $lng;
    private \ilRbacReview $rbac_review;

    private ProfileFieldsConfigurationRepository $profile_fields_repository;
    private array $user_fields;
    /**
     * @var array<string> Identifiers of fields to skip
     */
    protected array $skip_fields = [];
    /**
     * @var array<string> Identifiers of fields to skip
     */
    protected array $skip_groups = [];

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->settings = $DIC['ilSetting'];
        $this->lng = $DIC['lng'];
        $this->rbac_review = $DIC['rbacreview'];

        $this->profile_fields_repository = LocalDIC::dic()[ProfileFieldsConfigurationRepository::class];
        $this->user_fields = $this->profile_fields_repository->get();

        $this->lng->loadLanguageModule('awrn');
        $this->lng->loadLanguageModule('buddysystem');
    }

    /**
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getFields(): array
    {
        return array_reduce(
            $this->user_fields,
            function (array $c, ProfileField $v): array {
                if (!in_array($v->getSection(), $this->skip_groups)
                    && !in_array($v->getIdentifier(), $this->skip_fields)) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<\ILIAS\User\Profile\Fields\Custom\Custom>
     */
    public function getAllUserDefinedFields(): array
    {
        return array_reduce(
            $this->user_fields,
            function (array $c, ProfileField $v): array {
                if ($v->isCustom()) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    /**
     * @deprecated since version 11 will be removed with 13
     * @return array<\ILIAS\User\Profile\Fields\Custom>
     */
    public function getVisibleUserDefinedFields(
        Context $context
    ): array {
        return array_reduce(
            $this->getVisibleFields($context),
            function (array $c, ProfileField $v): array {
                if ($v->isCustom()) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    /**
     * @return array<\ILIAS\User\Profile\Fields\Field>
     */
    public function getVisibleFields(
        Context $context,
        ?\ilObjUser $user = null
    ): array {
        return array_filter(
            $this->user_fields,
            fn(ProfileField $v) => $context->isFieldVisibleInType($v, $user)
                    && !in_array($v->getIdentifier(), $this->skip_fields)
                ? true : false
        );
    }

    public function getVisibleFieldsBySection(
        Context $context,
        ?\ilObjUser $current_user
    ): array {
        return array_filter(
            array_reduce(
                $this->getVisibleFields($context),
                function (array $c, ProfileField $v): array {
                    if (in_array($v->getSection(), $this->skip_groups)) {
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

    public function getFieldByIdentifier(string $identifier): ?ProfileField
    {
        return $this->profile_fields_repository->getByIdentifier($identifier);
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

    public function addFieldsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        bool $do_require,
        ?\ilObjUser $current_user,
    ): \ilPropertyFormGUI {
        $registration_settings = null;
        if ($this->mode === self::MODE_REGISTRATION) {
            $registration_settings = new \ilRegistrationSettings();
            $this->addRegistrationFieldsToFieldArray();
        }

        return array_reduce(
            $this->getVisibleFieldsBySection($context, $current_user),
            function (\ilPropertyFormGUI $c, array $v) use ($context, $current_user, $do_require): \ilPropertyFormGUI {
                $section_header = new \ilFormSectionHeaderGUI();
                $section_header->setTitle($this->lng->txt($v[0]->getSection()->value));
                $c->addItem($section_header);
                return $this->addSectionFieldsToForm($c, $context, $do_require, $current_user, $v);
            },
            $form
        );
    }

    public function addFormValuesToUser(
        \ilPropertyFormGUI $form,
        Context $context,
        \ilObjUser $current_user
    ): \ilObjUser {
        return array_reduce(
            $this->getVisibleFields($context, $current_user),
            static fn(\ilObjUser $c, ProfileField $v): \ilObjUser => $v->addValueToUserObject(
                $current_user,
                $form->getInput($setting->getIdentifier()),
                $form
            ),
            $current_user
        );
    }

    private function addSectionFieldsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        bool $do_require,
        ?\ilObjUser $current_user,
        array $fields
    ): \ilPropertyFormGUI {
        return array_reduce(
            $fields,
            function (\ilPropertyFormGUI $form, ProfileField $v) use ($context, $current_user, $do_require): \ilPropertyFormGUI {
                $input = $v->getInput($this->lng, $current_user);
                $input->setDisabled(!$context->isFieldChangeableInType($v, $current_user));
                $input->setRequired($do_require && $v->isRequired());
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

    public function setMode(int $mode): bool
    {
        if (in_array($mode, [self::MODE_DESKTOP, self::MODE_REGISTRATION])) {
            $this->mode = $mode;
            return true;
        }
        return false;
    }

    public function isProfileIncomplete(\ilObjUser $user): bool
    {
        foreach ($this->user_fields as $field) {
            if (!$field->isVisibleToUser()) {
                continue;
            }

            if ($field->isRequired() && $field->retrieveValueFromUser($user)) {
                return true;
            }
        }

        return false;
    }

    public function userSettingVisibleToUser(string $setting): bool
    {
        if ($this->mode === self::MODE_DESKTOP) {
            return $this->profile_fields_repository->getByIdentifier($setting)
                ?->isVisibleToUser() ?? false;
        }

        return $this->profile_fields_repository->getByIdentifier($setting)
            ?->isVisibleInRegistration() ?? false;
    }

    public function userSettingEditableByUser(string $setting): bool
    {
        $field = $this->profile_fields_repository->getByIdentifier($setting);
        if ($field === null) {
            return false;
        }
        return $field->isVisibleToUser() && $field->isChangeableByUser();
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

            if ($this->userSettingEditableByUser($field)) {
                $ignorableSettings[] = $field;
            }
        }

        return $ignorableSettings;
    }
}
