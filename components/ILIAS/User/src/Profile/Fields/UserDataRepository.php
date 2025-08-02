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

namespace ILIAS\User\Profile\Fields;

use ILIAS\User\PropertyAttributes;

class UserDataRepository
{
    private const string TABLE = 'usr_profile_data';

    public function __construct(
        private readonly \ilSetting $settings,
        private readonly \ilDBInterface $db,
        array $available_profile_fields
    ) {
    }

    public function get(): array
    {
        return array_reduce(
            $this->available_profile_fields,
            function (array $c, FieldDefinition $v): array {
                $c[] = $this->buildFieldFromDefinition($v);
                return $c;
            },
            []
        );
    }

    public function getByIdentifier(string $identifier): Field
    {
        foreach ($this->available_profile_fields as $definition) {
            if ($definition->getIdentifier() === $identifier) {
                return $this->buildFieldFromDefinition($definition);
            }
        }
    }

    public function getByClass(string $class): Field
    {
        foreach ($this->available_profile_fields as $definition) {
            if ($definition::class === $class) {
                return $this->buildFieldFromDefinition($definition);
            }
        }
    }

    public function storeConfiguration(Field $field): void
    {
        PropertyAttributes::VisibleInRegistration->store(
            $this->settings,
            $field,
            $field->isVisibleInRegistration()
        );
        PropertyAttributes::VisibleToUser->store(
            $this->settings,
            $field,
            !$field->isVisibleToUser()
        );
        PropertyAttributes::VisibleInLocalUserAdministration->store(
            $this->settings,
            $field,
            $field->isVisibleInLocalUserAdministration()
        );
        PropertyAttributes::VisibleInCourses->store(
            $this->settings,
            $field,
            $field->isVisibleInCourses()
        );
        PropertyAttributes::VisibleInGroups->store(
            $this->settings,
            $field,
            $field->isVisibleInGroups()
        );
        PropertyAttributes::VisibleInStudyProgrammes->store(
            $this->settings,
            $field,
            $field->isVisibleInStudyProgrammes()
        );
        PropertyAttributes::UnchangeableByUser->store(
            $this->settings,
            $field,
            !$field->isChangeableByUser()
        );
        PropertyAttributes::ChangeableInLocalUserAdministration->store(
            $this->settings,
            $field,
            $field->isChangeableInLocalUserAdministration()
        );
        PropertyAttributes::Required->store(
            $this->settings,
            $field,
            $field->isRequired()
        );
        PropertyAttributes::Export->store(
            $this->settings,
            $field,
            $field->export()
        );
        PropertyAttributes::Searchable->store(
            $this->settings,
            $field,
            $field->isSearchable()
        );
        PropertyAttributes::AvailableInCertificates->store(
            $this->settings,
            $field,
            $field->isSearchable()
        );
    }

    public function getCustomFieldTypes(): array {
        return array_map(
            fn (string $v): Custom\Type => new $v(),
            $this->available_custom_field_types
        );
    }

    private function buildCustomFieldDefinitions(
        array $available_custom_field_types
    ): array {
        $query_result = $this->db->query(
            'SELECT * FROM ' . self::UDF_DEFINITIONS_TABLE
        );

        $custom_field_definitions = [];
        while(($field = $this->db->fetchObject($query_result)) !== null) {
            $field_type = match ($field->field_type) {
                3 => 0,
                1 => 1,
                2 => 2
            };
            $values = unserialize($field->field_values, ['allowed_classes' => false]);
            if (!is_array($values)) {
                $values = null;
            }
            $custom_field_definitions[] = new Custom\Custom(
                new $available_custom_field_types[$field_type](),
                (string) $field->field_id,
                $field->field_name,
                \ILIAS\User\Profile\Fields\AvailableSections::Other,
                $values
            );
        }
        return $custom_field_definitions;
    }

    private function buildFieldFromDefinition(
        FieldDefinition $definition
    ): Field {
        return new Field(
            $definition,
            PropertyAttributes::VisibleInRegistration->retrieve($this->settings, $definition),
            !PropertyAttributes::HiddenFromUser->retrieve($this->settings, $definition),
            PropertyAttributes::VisibleInLocalUserAdministration->retrieve($this->settings, $definition),
            PropertyAttributes::VisibleInCourses->retrieve($this->settings, $definition),
            PropertyAttributes::VisibleInGroups->retrieve($this->settings, $definition),
            PropertyAttributes::VisibleInStudyProgrammes->retrieve($this->settings, $definition),
            !PropertyAttributes::UnchangeableByUser->retrieve($this->settings, $definition),
            PropertyAttributes::ChangeableInLocalUserAdministration->retrieve($this->settings, $definition),
            PropertyAttributes::Required->retrieve($this->settings, $definition),
            PropertyAttributes::Export->retrieve($this->settings, $definition),
            PropertyAttributes::Searchable->retrieve($this->settings, $definition),
            PropertyAttributes::AvailableInCertificates->retrieve($this->settings, $definition)
        );
    }
}
