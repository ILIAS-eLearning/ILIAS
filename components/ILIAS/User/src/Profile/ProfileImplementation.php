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

use ILIAS\User\Context;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Profile\Fields\AvailableSections as AvailableProfileSections;
use ILIAS\User\Profile\Fields\ConfigurationRepository as FieldsConfigurationRepository;
use ILIAS\Language\Language;
use ILIAS\User\Profile\Fields\AvailableSections;

class ProfileImplementation implements Profile
{
    /**
     * @var list<ProfileField>
     */
    private array $user_fields;

    public function __construct(
        private readonly Language $lng,
        private readonly FieldsConfigurationRepository $profile_fields_repository,
        private readonly DataRepository $profile_data_repository
    ) {
        $this->user_fields = $this->profile_fields_repository->get();
    }

    public function getFields(
        array $sections_to_skip = [],
        array $fields_to_skip = []
    ): array {
        return array_reduce(
            $this->user_fields,
            static function (array $c, ProfileField $v) use ($sections_to_skip, $fields_to_skip): array {
                if (!in_array($v->getSection(), $sections_to_skip)
                    && !in_array(get_class($v->getDefinition()), $fields_to_skip)) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    public function getVisibleFields(
        Context $context,
        ?\ilObjUser $user = null,
        array $sections_to_skip = [],
        array $fields_to_skip = []
    ): array {
        return array_filter(
            $this->user_fields,
            static fn(ProfileField $v) => !in_array($v->getSection(), $sections_to_skip)
                    && !in_array($v->getDefinition()::class, $fields_to_skip)
                    && $context->isFieldVisible($v, $user)
                ? true : false
        );
    }

    public function getFieldByIdentifier(string $identifier): ?ProfileField
    {
        return $this->profile_fields_repository->getByIdentifier($identifier);
    }

    public function getFieldByClass(string $class): ?ProfileField
    {
        return $this->profile_fields_repository->getByClass($class);
    }

    public function addFieldsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        bool $do_require,
        ?\ilObjUser $user,
        array $fields_to_skip = []
    ): \ilPropertyFormGUI {
        return array_reduce(
            $this->getVisibleFieldsBySection($context, $user, $fields_to_skip),
            function (\ilPropertyFormGUI $c, array $v) use ($context, $user, $do_require): \ilPropertyFormGUI {
                $section_header = new \ilFormSectionHeaderGUI();
                $section_header->setTitle($this->lng->txt($v[0]->getSection()->value));
                $c->addItem($section_header);
                return $this->addSectionFieldsToForm($c, $context, $do_require, $user, $v);
            },
            $form
        );
    }

    public function addFormValuesToUser(
        \ilPropertyFormGUI $form,
        Context $context,
        \ilObjUser $user,
        array $skip_fields = []
    ): \ilObjUser {
        return array_reduce(
            $this->getVisibleFields($context, $user, [], $skip_fields),
            static function (\ilObjUser $c, ProfileField $v) use ($form, $context, $user): \ilObjUser {
                if ($form->getItemByPostVar($v->getIdentifier())->getDisabled()) {
                    return $c;
                }
                return $v->addValueToUserObject(
                    $c,
                    $context,
                    $form->getInput($v->getIdentifier()),
                    $form
                );
            },
            $user
        );
    }

    public function getDataFor(int $usr_id): Data
    {
        return $this->profile_data_repository->getSingle($usr_id);
    }

    public function getDataForMultiple(
        array $usr_ids
    ): \Generator {
        return $this->profile_data_repository->getMultiple($usr_ids);
    }

    public function isProfileIncomplete(\ilObjUser $user): bool
    {
        foreach ($this->user_fields as $field) {
            if (!$field->isVisibleToUser()) {
                continue;
            }

            if ($field->isRequired() && empty($field->retrieveValueFromUser($user))) {
                return true;
            }
        }

        return false;
    }

    public function userFieldVisibleToUser(
        string $setting_identifier
    ): bool {
        $field = $this->profile_fields_repository->getByIdentifier($setting_identifier);
        if ($field === null) {
            return false;
        }

        return $field->isVisibleToUser();
    }

    public function userFieldEditableByUser(string $setting): bool
    {
        $field = $this->profile_fields_repository->getByIdentifier($setting);
        if ($field === null) {
            return false;
        }
        return $field->isVisibleToUser() && $field->isChangeableByUser();
    }

    public function getIgnorableRequiredFields(): array
    {
        return array_reduce(
            $this->user_fields,
            static function (array $c, ProfileField $v): array {
                if ($v->getIdentifier() === 'username'
                    || $v->getIdentifier() === 'password'
                    || $v->isRequired()
                    || !$v->isChangeableByUser()) {
                    return $c;
                }
                $c[] = $v;
                return $c;
            },
            []
        );
    }

    public function getAllUserDefinedFields(): array
    {
        return array_reduce(
            $this->user_fields,
            static function (array $c, ProfileField $v): array {
                if ($v->isCustom()) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    public function getVisibleUserDefinedFields(
        Context $context
    ): array {
        return array_reduce(
            $this->getVisibleFields($context),
            static function (array $c, ProfileField $v): array {
                if ($v->isCustom()) {
                    $c[$v->getIdentifier()] = $v;
                }
                return $c;
            },
            []
        );
    }

    /**
     * @deprecated since version 11 will be removed asap
     */
    public function tempStorePicture(
        \ilPropertyFormGUI $form
    ): \ilPropertyFormGUI {
        return $this->profile_fields_repository->getByClass(Fields\Standard\Avatar::class)
            ->getDefinition()->tempStorePicture($form);
    }

    /**
     * @return array<value-of<AvailableSections>, array<int, ProfileField>>
     */
    private function getVisibleFieldsBySection(
        Context $context,
        ?\ilObjUser $user,
        array $fields_to_skip = []
    ): array {
        return array_filter(
            array_reduce(
                $this->getVisibleFields($context, $user, [], $fields_to_skip),
                static function (array $c, ProfileField $v): array {
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

    private function addSectionFieldsToForm(
        \ilPropertyFormGUI $form,
        Context $context,
        bool $do_require,
        ?\ilObjUser $user,
        array $fields
    ): \ilPropertyFormGUI {
        return array_reduce(
            $fields,
            function (\ilPropertyFormGUI $form, ProfileField $v) use ($context, $user, $do_require): \ilPropertyFormGUI {
                $input = $v->getLegacyInput($this->lng, $context, $user);
                $input->setDisabled(!$context->isFieldChangeable($v, $user));
                $input->setRequired($do_require && $input->getRequired());
                $form->addItem($input);
                return $form;
            },
            $form
        );
    }
}
