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

use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Data\UUID\Factory as UUIDFactory;

class ConfigurationRepository
{
    private const string USER_FIELD_CONFIGURATION_TABLE = 'usr_field_config';
    private const string UDF_DEFINITIONS_TABLE = 'udf_definition';
    private array $available_profile_fields;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly UserDataRepository $data_repository,
        private readonly UUIDFactory $uuid_factory,
        private readonly array $available_custom_field_types,
        private readonly array $available_standard_profile_fields
    ) {
        $this->available_profile_fields = $this->generateAvailableProfielFields();
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
        $this->db->replace(
            self::USER_FIELD_CONFIGURATION_TABLE,
            ['field_id' => [\ilDBConstants::T_TEXT, $field->getIdentifier()]],
            [
                'visible_in_registration' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleInRegistration() ? 1 : 0
                ],
                'visible_to_user' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleToUser() ? 1 : 0
                ],
                'visible_in_lua' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleInLocalUserAdministration() ? 1 : 0
                ],
                'visible_in_crss' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleInCourses() ? 1 : 0
                ],
                'visible_in_grps' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleInGroups() ? 1 : 0
                ],
                'visible_in_prgs' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isVisibleInStudyProgrammes() ? 1 : 0
                ],
                'changeable_by_user' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isChangeableByUser() ? 1 : 0
                ],
                'changeable_in_lua' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isChangeableInLocalUserAdministration() ? 1 : 0
                ],
                'required' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isRequired() ? 1 : 0
                ],
                'export' => [
                    \ilDBConstants::T_INTEGER,
                    $field->export() ? 1 : 0
                ],
                'searchable' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isSearchable() ? 1 : 0
                ],
                'available_in_certs' => [
                    \ilDBConstants::T_INTEGER,
                    $field->isAvailableInCertificates() ? 1 : 0
                ]
            ]
        );

        if ($field->isCustom()) {
            $this->db->replace(
                self::UDF_DEFINITIONS_TABLE,
                ['field_id' => [\ilDBConstants::T_TEXT, $field->getIdentifier()]],
                $field->getDefinition()->toStorage()
            );
        }

        $this->available_profile_fields = $this->generateAvailableProfielFields();
    }

    public function getCustomFieldTypes(): array {
        return array_map(
            fn (string $v): Custom\Type => new $v(),
            $this->available_custom_field_types
        );
    }

    public function getUnspecifiedCustomField(): Field
    {
        return $this->buildFieldFromDefinition(
            new Custom\Custom(
                $this->uuid_factory->uuid4()
            )
        );
    }

    public function deleteCustomField(Field $field): void
    {
        if (!$field->getDefinition() instanceof Custom\Custom) {
            return;
        }
        $this->data_repository->deleteForFieldIdentifier($field->getIdentifier());
        $this->db->manipulate('DELETE FROM ' . self::UDF_DEFINITIONS_TABLE . " WHERE field_id='{$field->getIdentifier()}'" );
        $this->available_profile_fields = $this->generateAvailableProfielFields();
    }

    private function buildCustomFieldDefinitions(
        array $available_custom_field_types
    ): array {
        $query_result = $this->db->query(
            'SELECT * FROM ' . self::UDF_DEFINITIONS_TABLE
        );

        $custom_field_definitions = [];
        while(($field = $this->db->fetchObject($query_result)) !== null) {
            $field_type = array_search($field->field_type, $available_custom_field_types);
            if ($field_type === null) {
                continue;
            }
            $custom_field_definitions[] = new Custom\Custom(
                $this->uuid_factory->fromString($field->field_id),
                new $available_custom_field_types[$field_type](),
                $field->field_name,
                AvailableSections::tryFrom($field->section) ?? AvailableSections::Other,
                $field->field_values
            );
        }
        return $custom_field_definitions;
    }

    private function buildFieldFromDefinition(
        FieldDefinition $definition
    ): Field {
        $values_from_database = $this->db->fetchObject(
            $this->db->query(
                'SELECT * FROM ' . self::USER_FIELD_CONFIGURATION_TABLE . " WHERE field_id ='{$definition->getIdentifier()}'"
            )
        );

        if ($values_from_database === null) {
            return new Field(
                $definition
            );
        }

        return new Field(
            $definition,
            $values_from_database->visible_in_registration === 1,
            $values_from_database->visible_to_user === 1,
            $values_from_database->visible_in_lua === 1,
            $values_from_database->visible_in_crss === 1,
            $values_from_database->visible_in_grps === 1,
            $values_from_database->visible_in_prgs === 1,
            $values_from_database->changeable_by_user === 1,
            $values_from_database->changeable_in_lua === 1,
            $values_from_database->required === 1,
            $values_from_database->export === 1,
            $values_from_database->searchable === 1,
            $values_from_database->available_in_certs === 1
        );
    }

    private function generateAvailableProfielFields(): array
    {
        return array_merge(
            $this->available_standard_profile_fields,
            $this->buildCustomFieldDefinitions($this->available_custom_field_types)
        );
    }
}
