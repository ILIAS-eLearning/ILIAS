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

namespace ILIAS\User\Setup;

use ILIAS\User\Profile\Fields\AvailableSections;
use ILIAS\Data\UUID\Factory as UUIDFactory;

class DBUpdateSteps11 implements \ilDatabaseUpdateSteps
{
    private \ilDBInterface $db;
    private UUIDFactory $uuid_factory;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
        $this->uuid_factory = new UUIDFactory();
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('mail_template', 'att_rid')) {
            $this->db->addTableColumn(
                'mail_template',
                'att_rid',
                [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64
                ]
            );
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableExists('usr_data_multi')
            && !$this->db->tableExists('usr_profile_data')) {
            $this->db->renameTable(
                'usr_data_multi',
                'usr_profile_data'
            );
        }
        if ($this->db->tableExists('usr_profile_data')
            && !$this->db->indexExistsByFields('usr_profile_data', ['usr_id', 'field_id'])) {
            $this->db->addIndex('usr_profile_data', ['usr_id', 'field_id'], 'uf');
        }
        if ($this->db->tableExists('usr_profile_data')) {
            $this->db->modifyTableColumn('usr_profile_data', 'value', ['type' => \ilDBConstants::T_CLOB]);
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableExists('udf_data')) {
            $this->db->dropTable('udf_data');
        }

        if (!$this->db->sequenceExists('udf_definition')) {
            /*
             * 2025-07-17, sk: This needs to be done here, as we absolutely need
             * the change to be ready for the next steps
             */
            $this->db->modifyTableColumn('ldap_attribute_mapping', 'keyword', ['length' => 68]);
            $this->db->modifyTableColumn('settings', 'keyword', ['length' => 74]);

            $this->db->renameTableColumn('udf_definition', 'field_id', 'old_field_id');
            $this->db->manipulate('ALTER TABLE udf_definition ADD COLUMN field_id VARCHAR(64) NOT NULL FIRST');
            $this->db->modifyTableColumn('udf_clob', 'field_id', ['type' => 'text', 'length' => 64]);
            $this->db->modifyTableColumn('udf_text', 'field_id', ['type' => 'text', 'length' => 64]);
            $fields_query = $this->db->query('SELECT old_field_id FROM udf_definition');
            while (($row = $this->db->fetchObject($fields_query))) {
                $uuid = $this->uuid_factory->uuid4AsString();
                $this->db->manipulate(
                    "UPDATE udf_definition SET field_id = '{$uuid}' WHERE old_field_id = '{$row->old_field_id}'"
                );
                $this->db->manipulate(
                    "UPDATE udf_clob SET field_id = '{$uuid}' WHERE field_id = '{$row->old_field_id}'"
                );
                $this->db->manipulate(
                    "UPDATE udf_text SET field_id = '{$uuid}' WHERE field_id = '{$row->old_field_id}'"
                );
                $this->db->manipulate(
                    "UPDATE ldap_attribute_mapping SET keyword = 'udf_{$uuid}' WHERE keyword = 'udf_{$row->old_field_id}'"
                );
                $this->db->manipulate(
                    "UPDATE settings SET keyword = 'pmap_udf_{$uuid}' WHERE keyword = 'pmap_udf_{$row->old_field_id}'"
                );
                $this->db->manipulate(
                    "UPDATE settings SET keyword = 'pumap_udf_{$uuid}' WHERE keyword = 'pumap_udf_{$row->old_field_id}'"
                );
            }
            $this->db->dropTableColumn('udf_definition', 'old_field_id');
            $this->db->addPrimaryKey('udf_definition', ['field_id']);
            $this->db->dropSequence('udf_definition');
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableExists('usr_field_config')) {
            $this->db->createTable(
                'usr_field_config',
                [
                    'field_id' => [
                        'type' => \ilDBConstants::T_TEXT,
                        'length' => 64,
                        'notnull' => true
                    ],
                    'visible_in_registration' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'visible_to_user' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'visible_in_lua' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'visible_in_crss' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'visible_in_grps' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'visible_in_prgs' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'changeable_by_user' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'changeable_in_lua' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'required' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'export' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'searchable' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                    'available_in_certs' => [
                        'type' => \ilDBConstants::T_INTEGER,
                        'length' => 1,
                        'notnull' => true
                    ],
                ]
            );
            $this->db->addPrimaryKey('usr_field_config', ['field_id']);
            $this->db->insert(
                'usr_field_config',
                [
                    'field_id' => [\ilDBConstants::T_TEXT, 'location'],
                    'visible_in_registration' => [\ilDBConstants::T_INTEGER, 0],
                    'visible_to_user' => [\ilDBConstants::T_INTEGER, 1],
                    'visible_in_lua' => [\ilDBConstants::T_INTEGER, 0],
                    'visible_in_crss' => [\ilDBConstants::T_INTEGER, 0],
                    'visible_in_grps' => [\ilDBConstants::T_INTEGER, 0],
                    'visible_in_prgs' => [\ilDBConstants::T_INTEGER, 0],
                    'changeable_by_user' => [\ilDBConstants::T_INTEGER, 1],
                    'changeable_in_lua' => [\ilDBConstants::T_INTEGER, 0],
                    'required' => [\ilDBConstants::T_INTEGER, 0],
                    'export' => [\ilDBConstants::T_INTEGER, 0],
                    'searchable' => [\ilDBConstants::T_INTEGER, 0],
                    'available_is_certs' => [\ilDBConstants::T_INTEGER, 0]
                ]
            );
        }
    }

    public function step_5(): void
    {
        $this->db->modifyTableColumn(
            'udf_definition',
            'field_type',
            [
                'type' => \ilDBConstants::T_TEXT,
                'length' => 4000
            ]
        );
        $this->db->update(
            'udf_definition',
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    \ILIAS\User\Profile\Fields\Custom\Text::class
                ]
            ],
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    '1'
                ]
            ]
        );
        $this->db->update(
            'udf_definition',
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    \ILIAS\User\Profile\Fields\Custom\Text::class
                ]
            ],
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    '1'
                ]
            ]
        );
        $this->db->update(
            'udf_definition',
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    \ILIAS\User\Profile\Fields\Custom\Select::class
                ]
            ],
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    '2'
                ]
            ]
        );
        $this->db->update(
            'udf_definition',
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    \ILIAS\User\Profile\Fields\Custom\TextArea::class
                ]
            ],
            [
                'field_type' => [
                    \ilDBConstants::T_TEXT,
                    '3'
                ]
            ]
        );
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('udf_definition', 'section')) {
            $this->db->addTableColumn(
                'udf_definition',
                'section',
                [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true,
                    'default' => AvailableSections::Other->value
                ]
            );
        }
    }
}
