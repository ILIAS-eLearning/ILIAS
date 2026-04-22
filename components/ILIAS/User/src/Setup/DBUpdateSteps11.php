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

    private function insertSetting(
        string $keyword,
        string $value
    ): void {
        if ($this->db->fetchObject(
            $this->db->query(
                "SELECT COUNT(*) cnt FROM settings WHERE module = 'common' AND keyword='{$keyword}'"
            )
        )?->cnt > 0
        ) {
            return;
        }

        $this->db->insert(
            'settings',
            [
                'module' => [
                    \ilDBConstants::T_TEXT,
                    'common'
                ],
                'keyword' => [
                    \ilDBConstants::T_TEXT,
                    $keyword
                ],
                'value' => [
                    \ilDBConstants::T_TEXT,
                    $value
                ],
            ]
        );
    }

    private function migrateBadges(
        string $old_value,
        string $new_value
    ): void {
        $query = $this->db->query("SELECT id, conf FROM badge_badge WHERE type_id='user/profile' AND conf LIKE '%$old_value%'");
        while (($badge = $this->db->fetchObject($query)) !== null) {
            $config_array = unserialize($badge->conf, ['allowed_classes' => false]);
            if (!array_key_exists('profile', $config_array)) {
                continue;
            }
            $config_array['profile'] = array_map(
                static function (string $v) use ($old_value, $new_value): string {
                    if ($v !== "chk_{$old_value}") {
                        return $v;
                    }

                    return "chk_{$new_value}";
                },
                $config_array['profile']
            );
            $this->db->update(
                'badge_badge',
                [
                    'conf' => [\ilDBConstants::T_TEXT, serialize($config_array)]
                ],
                [
                    'id' => [\ilDBConstants::T_INTEGER, $badge->id]
                ]
            );
        }
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
        if ($this->db->tableExists('usr_profile_data')) {
            $this->db->modifyTableColumn('usr_profile_data', 'value', ['type' => \ilDBConstants::T_CLOB]);
        }
        if ($this->db->sequenceExists('usr_profile_data')) {
            $this->db->dropSequence('usr_profile_data');
        }
        if ($this->db->tableExists('usr_profile_data')
            && $this->db->tableColumnExists('usr_profile_data', 'id')) {
            $this->db->dropTableColumn('usr_profile_data', 'id');
        }
        if ($this->db->tableExists('usr_profile_data')
            && !$this->db->indexExistsByFields('usr_profile_data', ['usr_id', 'field_id'])) {
            $this->db->addIndex('usr_profile_data', ['usr_id', 'field_id'], 'uf');
        }
    }

    public function step_3(): void
    {
        if ($this->db->tableExists('udf_data')) {
            $this->db->dropTable('udf_data');
        }

        if ($this->db->sequenceExists('udf_definition')) {
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

                $this->migrateBadges("udf_{$row->old_field_id}", $uuid);
            }
            $this->db->dropTableColumn('udf_definition', 'old_field_id');
            $this->db->addPrimaryKey('udf_definition', ['field_id']);
            $this->db->dropSequence('udf_definition');
        }
    }

    public function step_4(): void
    {
        if ($this->db->tableExists('udf_text')) {
            $this->db->manipulate('INSERT INTO usr_profile_data SELECT * FROM udf_text');
            $this->db->dropTable('udf_text');
        }

        if ($this->db->tableExists('udf_clob')) {
            $this->db->manipulate('INSERT INTO usr_profile_data SELECT * FROM udf_clob');
            $this->db->dropTable('udf_clob');
        }
    }

    public function step_5(): void
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
                    'available_in_certs' => [\ilDBConstants::T_INTEGER, 0]
                ]
            );
        }
    }

    public function step_6(): void
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

    public function step_7(): void
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

    public function step_8(): void
    {
        if ($this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(*) cnt FROM settings WHERE module = "common"' . PHP_EOL
                    . 'AND keyword="usr_settings_changeable_by_user_new_mail_notification"'
            )
        )?->cnt <= 0
        ) {
            $this->db->manipulate(
                'INSERT INTO settings (module, keyword, value) VALUES '
                    . '("common", "usr_settings_changeable_by_user_new_mail_notification", "1"), '
                . '("common", "usr_settings_changeable_lua_new_mail_notification", "1"), '
                . '("common", "usr_settings_export_new_mail_notification", "1")'
            );
        }

        $renamed_fields = [
            'hide_own_online_status' => 'awrn_user_show',
            'bs_allow_to_contact_me' => 'allow_contact_request',
            'mail_incoming_mail' => 'incoming_mail',
            'skin_style' => 'style',
            'upload' => 'avatar',
            'sel_country' => 'selcountry',
            'country' => 'old_country',
            'selcountry' => 'country'
        ];

        foreach ($renamed_fields as $old_id => $new_id) {
            $settings_query = $this->db->query(
                "SELECT keyword FROM settings WHERE {$this->db->like('keyword', \ilDBConstants::T_TEXT, "%_{$old_id}")}"
            );
            while (($row = $this->db->fetchObject($settings_query)) !== null) {
                if ($row->keyword === 'admin_country') {
                    continue;
                }
                $this->db->update(
                    'settings',
                    [
                        'keyword' => [
                            \ilDBConstants::T_TEXT,
                            str_replace("_{$old_id}", "_{$new_id}", $row->keyword)
                        ]
                    ],
                    [
                        'module' => [
                            \ilDBConstants::T_TEXT,
                            'common'
                        ],
                        'keyword' => [
                            \ilDBConstants::T_TEXT,
                            $row->keyword
                        ]
                    ]
                );
            }

            $user_query = $this->db->query(
                "SELECT DISTINCT keyword FROM usr_pref WHERE {$this->db->like('keyword', \ilDBConstants::T_TEXT, "%_{$old_id}")}"
            );
            while (($row = $this->db->fetchObject($user_query)) !== null) {
                $this->db->update(
                    'usr_pref',
                    [
                        'keyword' => [
                            \ilDBConstants::T_TEXT,
                            str_replace("_{$old_id}", "_{$new_id}", $row->keyword)
                        ]
                    ],
                    [
                        'keyword' => [
                            \ilDBConstants::T_TEXT,
                            $row->keyword
                        ]
                    ]
                );
            }
        }

        $this->migrateBadges('upload', 'avatar');
        $this->migrateBadges('selcountry', 'country');
    }

    public function step_9(): void
    {
        $query = $this->db->query(
            "SELECT usr_id, keyword FROM usr_pref WHERE {$this->db->like('keyword', \ilDBConstants::T_TEXT, 'public_udf_%')}"
        );
        while (($row = $this->db->fetchObject($query)) !== null) {
            $this->db->update(
                'usr_pref',
                [
                    'keyword' => [
                        \ilDBConstants::T_TEXT,
                            str_replace('public_udf_', 'public_', $row->keyword)
                    ]
                ],
                [
                    'usr_id' => [
                        \ilDBConstants::T_INTEGER,
                        $row->usr_id
                    ],
                    'keyword' => [
                        \ilDBConstants::T_TEXT,
                        $row->keyword
                    ]
                ]
            );
        }
    }

    public function step_10(): void
    {
        if ($this->db->tableColumnExists('usr_data', 'sel_country')) {
            $this->db->renameTableColumn('usr_data', 'country', 'old_country');
            $this->db->renameTableColumn('usr_data', 'sel_country', 'country');
        }
    }

    public function step_11(): void
    {
        $this->db->modifyTableColumn('usr_pref', 'keyword', ['length' => 74]);
    }

    public function step_12(): void
    {
        if ($this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(*) cnt FROM settings WHERE module = "common"' . PHP_EOL
                    . 'AND keyword="usr_settings_changeable_by_user_starting_point"'
            )
        )?->cnt <= 0
        ) {
            $this->db->update(
                'settings',
                [
                    'keyword' => [
                        \ilDBConstants::T_TEXT,
                        'usr_settings_changeable_by_user_starting_point'
                    ]
                ],
                [
                    'module' => [
                        \ilDBConstants::T_TEXT,
                        'common'
                    ],
                    'keyword' => [
                        \ilDBConstants::T_TEXT,
                        'usr_starting_point_personal'
                    ]
                ]
            );
        } else {
            $this->db->manipulate(
                'DELETE FROM settings WHERE module = "common" AND keyword= "usr_starting_point_personal"'
            );
        }

        $this->insertSetting('usr_settings_changeable_lua_starting_point', '1');
        $this->insertSetting('usr_settings_export_starting_point', '1');

        foreach ([
            'last_visited',
            'timezone',
            'date_format',
            'time_format'
        ] as $setting) {
            $this->insertSetting("usr_settings_changeable_by_user_{$setting}", '1');
            $this->insertSetting("usr_settings_changeable_lua_{$setting}", '1');
            $this->insertSetting("usr_settings_export_{$setting}", '1');
        }
    }

    public function step_13(): void
    {
        $this->db->update(
            'usr_pref',
            ['keyword' => [\ilDBConstants::T_TEXT, 'public_avatar']],
            ['keyword' => [\ilDBConstants::T_TEXT, 'public_upload']]
        );
    }

    public function step_14(): void
    {
        if (!$this->db->tableColumnExists('usr_data', 'expiration_reminder_sent')) {
            $this->db->addTableColumn(
                'usr_data',
                'expiration_reminder_sent',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            );
        }

        if ($this->db->tableColumnExists('usr_data', 'time_limit_message')) {
            $this->db->update(
                'usr_data',
                [
                    'expiration_reminder_sent' => [
                        \ilDBConstants::T_INTEGER,
                        1
                    ]
                ],
                [
                    'time_limit_message' => [
                        \ilDBConstants::T_TEXT,
                        1
                    ]
                ]
            );
            $this->db->dropTableColumn('usr_data', 'time_limit_message');
        }

    }
}
