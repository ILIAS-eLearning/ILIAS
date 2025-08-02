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

namespace ILIAS\User\Setup;

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\AdminInteraction;

class UserProfileMigrations implements Migration
{
    private \ilDBInterface $db;
    private \ilSetting $settings;
    private AdminInteraction $admin_interaction;

    public function getLabel(): string
    {
        return 'Clean-Up and Consolidate User Profile Fields';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective(),
            new \ilSettingsFactoryExistsObjective(),
            new \ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->settings = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);
        $this->manager = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
    }

    public function step(Environment $environment): void
    {
        $this->migrateCustomFieldAccess();
        $this->migrateStandardFieldAccess();
    }

    public function getRemainingAmountOfSteps(): int
    {
        return $this->db->tableColumnExists('udf_definition', 'prg_export') ? 1 : 0;
    }

    private function migrateCustomFieldAccess(): void
    {
        if (!$this->db->tableColumnExists('udf_definition', 'prg_export')) {
            return;
        }

        $custom_fields_query = $this->db->query('SELECT * FROM udf_definition');

        while (($row = $this->db->fetchObject($custom_fields_query))) {
            $this->insertAccess(
                $row->registration_visible,
                $row->visible,
                $row->visible_lua,
                $row->course_export,
                $row->group_export,
                $row->prg_export,
                $row->changeable,
                $row->changeable_lua,
                $row->required,
                $row->export,
                $row->searchable,
                $row->certificate
            );
        }
        $this->db->dropTableColumn(
            'udf_definition',
            'visible'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'changeable'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'required'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'searchable'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'export'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'course_export'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'registration_visible'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'visible_lua'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'changeable_lua'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'group_export'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'certificate'
        );
        $this->db->dropTableColumn(
            'udf_definition',
            'prg_export'
        );
    }

    private function migrateStandardFieldAccess(): void
    {
        $properties_move = [
            'username',
            'firstname',
            'lastname',
            'title',
            'birthday',
            'gender',
            'upload',
            'roles',
            'org_units',
            'interests_general' .
            'interests_help_offered',
            'interests_help_looking',
            'institution',
            'department',
            'street',
            'zipcode',
            'city',
            'sel_country',
            'phone_office',
            'phone_home',
            'phone_mobile',
            'fax',
            'email',
            'second_email',
            'hobby',
            'referral_comment',
            'matriculation'
        ];

        $property_attributes = [
            'usr_settings_visib_reg',
            'usr_settings_hide',
            'usr_settings_visib_lua',
            'usr_settings_course_export',
            'usr_settings_group_export',
            'usr_settings_prg_export',
            'usr_settings_disable',
            'usr_settings_changeable_lua',
            'require',
            'usr_settings_export',
            'search_enabled',
            'certificate',
        ];

        $this->updateCountryField($property_attributes);



    }

    private function retrievePropertyAttributeValue(string $property, string $attribute): bool
    {
        return $this->settings->get("{$attribute}_{$property}", '0') === '1';
    }

    private function updateCountryField(array $property_attributes): void
    {
        $message = 'ILIAS up to now knows two types of country information: One selectable by a dropdown '
            . 'the other one as a text field. The latter one will be removed. Would you like us to move '
            . 'the current information in the text field to a custom field? If you choose to not move the '
            . 'information it will simply be deleted.';

        if ($this->admin_interaction->confirmOrDeny($message)) {

        } else {
            foreach ($property_attributes as $attribute) {
                $this->settings->delete("{$attribute}_country");
            }
            $this->db->dropTableColumn('usr_data', 'country');
        }
    }

    private function insertAccess(
        int $visible_in_registration,
        int $visible_to_user,
        int $visible_in_lua,
        int $visible_in_crss,
        int $visible_in_grps,
        int $visible_in_prgs,
        int $changeable_by_user,
        int $changeable_in_lua,
        int $required,
        int $export,
        int $searchable,
        int $available_in_certs
    ): void {
        $this->db->insert(
            'usr_field_access',
            [
                'visible_in_registration' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_in_registration
                ],
                'visible_to_user' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_to_user
                ],
                'visible_in_lua' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_in_lua
                ],
                'visible_in_crss' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_in_crss
                ],
                'visible_in_grps' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_in_grps
                ],
                'visible_in_prgs' => [
                    \ilDBConstants::T_INTEGER,
                    $visible_in_prgs
                ],
                'changeable_by_user' => [
                    \ilDBConstants::T_INTEGER,
                    $changeable_by_user
                ],
                'changeable_in_lua' => [
                    \ilDBConstants::T_INTEGER,
                    $changeable_in_lua
                ],
                'required' => [
                    \ilDBConstants::T_INTEGER,
                    $required
                ],
                'export' => [
                    \ilDBConstants::T_INTEGER,
                    $export
                ],
                'searchable' => [
                    \ilDBConstants::T_INTEGER,
                    $searchable
                ],
                'available_in_certs' => [
                    \ilDBConstants::T_INTEGER,
                    $available_in_certs
                ]
            ]
        );
    }
}
