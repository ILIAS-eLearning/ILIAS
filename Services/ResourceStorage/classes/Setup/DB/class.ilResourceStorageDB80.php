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

/**
 * Class ilResourceStorageDB80
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilResourceStorageDB80 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableExists('il_resource_stkh_u') && $this->db->tableExists('il_resource_stakeh')) {
            $this->db->renameTable('il_resource_stakeh', 'il_resource_stkh_u');
            $this->db->createTable(
                'il_resource_stkh',
                [
                    'id' => ['type' => 'text', 'length' => 32, 'notnull' => true, 'default' => ''],
                    'class_name' => ['type' => 'text', 'length' => 250, 'notnull' => true, 'default' => ''],
                ]
            );
            $this->db->addPrimaryKey('il_resource_stkh', ['id']);
            $this->db->manipulate(
                "INSERT INTO il_resource_stkh (id, class_name) SELECT DISTINCT stakeholder_id, stakeholder_class FROM il_resource_stkh_u;"
            );
        }

        if ($this->db->tableColumnExists('il_resource_stkh_u', 'stakeholder_class')) {
            $this->db->dropTableColumn('il_resource_stkh_u', 'stakeholder_class');
        }
        if ($this->db->tableColumnExists('il_resource_stkh_u', 'internal')) {
            $this->db->dropTableColumn('il_resource_stkh_u', 'internal');
        }
    }

    public function step_2(): void
    {
        //  rename all identification columns to rid
        if (!$this->db->tableColumnExists('il_resource', 'rid')) {
            $this->db->renameTableColumn(
                'il_resource',
                'identification',
                'rid'
            );
        }
        if (!$this->db->tableColumnExists('il_resource_info', 'rid')) {
            $this->db->renameTableColumn(
                'il_resource_info',
                'identification',
                'rid'
            );
        }
        if (!$this->db->tableColumnExists('il_resource_revision', 'rid')) {
            $this->db->renameTableColumn(
                'il_resource_revision',
                'identification',
                'rid'
            );
        }
        if (!$this->db->tableColumnExists('il_resource_stkh_u', 'rid')) {
            $this->db->renameTableColumn(
                'il_resource_stkh_u',
                'identification',
                'rid'
            );
        }
    }

    public function step_3(): void
    {
        // set all rid columns to the same size
        $attributes = [
            'length' => 64,
            'notnull' => true,
            'default' => '',
        ];
        $this->db->modifyTableColumn(
            'il_resource',
            'rid',
            $attributes
        );
        $this->db->modifyTableColumn(
            'il_resource_info',
            'rid',
            $attributes
        );
        $this->db->modifyTableColumn(
            'il_resource_revision',
            'rid',
            $attributes
        );
        $this->db->modifyTableColumn(
            'il_resource_stkh_u',
            'rid',
            $attributes
        );
        try {
            $this->db->modifyTableColumn(
                'file_data',
                'rid',
                $attributes
            );
        } catch (Throwable $t) {
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('il_resource_info', 'version_number')) {
            $this->db->addTableColumn(
                'il_resource_info',
                'version_number',
                [
                    'type' => 'integer',
                    'length' => 8
                ]
            );

            $this->db->manipulate(
                "UPDATE il_resource_info
JOIN il_resource_revision ON il_resource_info.internal = il_resource_revision.internal
SET il_resource_info.version_number = il_resource_revision.version_number
"
            );
        }
    }

    public function step_5(): void
    {
        // remove internal columns and add primaries
        if ($this->db->tableColumnExists('il_resource_revision', 'internal')) {
            $this->db->dropTableColumn('il_resource_revision', 'internal');
            $this->db->addPrimaryKey(
                'il_resource_revision',
                [
                    'rid',
                    'version_number',
                ]
            );
        }
        if ($this->db->tableColumnExists('il_resource_info', 'internal')) {
            $this->db->dropTableColumn('il_resource_info', 'internal');
            $this->db->addPrimaryKey(
                'il_resource_info',
                [
                    'rid',
                    'version_number',
                ]
            );
        }
        if ($this->db->tableColumnExists('il_resource_stkh', 'internal')) {
            $this->db->dropTableColumn('il_resource_stkh', 'internal');
            $this->db->addPrimaryKey(
                'il_resource_stkh',
                [
                    'rid',
                    'stakeholder_id',
                ]
            );
        }
    }

    public function step_6(): void
    {
        // set several fields to notnull
        $attributes = [
            'notnull' => true,
            'default' => '',
        ];
        $table_fields = [
            'il_resource' => ['storage_id'],
            'il_resource_info' => ['title', 'size', 'creation_date'],
            'il_resource_revision' => ['owner_id', 'title'],
        ];
        foreach ($table_fields as $table => $fields) {
            foreach ($fields as $field) {
                $this->db->modifyTableColumn(
                    $table,
                    $field,
                    $attributes
                );
            }
        }
    }

    public function step_7(): void
    {
        // add index to file_data rid
        if (!$this->db->indexExistsByFields('file_data', ['rid'])) {
            $this->db->addIndex('file_data', ['rid'], 'i1');
        }
    }

    public function step_8(): void
    {
        // several changes to irss tables
        $this->db->modifyTableColumn(
            'il_resource_revision',
            'available',
            [
                'default' => 1,
            ]
        );
        $this->db->modifyTableColumn(
            'il_resource_stkh_u',
            'stakeholder_id',
            ['length' => 64]
        );
        $this->db->modifyTableColumn(
            'il_resource_stkh',
            'id',
            ['length' => 64]
        );
        $this->db->modifyTableColumn(
            'il_resource_info',
            'title',
            ['length' => 255]
        );
        $this->db->modifyTableColumn(
            'il_resource_revision',
            'title',
            ['length' => 255]
        );
    }

    public function step_9(): void
    {
        if (!$this->db->tableExists('il_resource_rc')) {
            $this->db->createTable(
                'il_resource_rc',
                [
                    'rcid' => [
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true,
                        'default' => '',
                    ],
                    'title' => [
                        'type' => 'text',
                        'length' => 4000,
                        'notnull' => false,
                        'default' => '',
                    ],
                    'owner' => [
                        'type' => 'integer',
                        'length' => 8,
                        'notnull' => true,
                        'default' => 0,
                    ],
                ]
            );
        }

        if (!$this->db->tableExists('il_resource_rca')) {
            $this->db->createTable(
                'il_resource_rca',
                [
                    'rcid' => [
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true,
                        'default' => '',
                    ],
                    'rid' => [
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true,
                        'default' => '',
                    ],
                    'position' => [
                        'type' => 'integer',
                        'length' => 8,
                        'notnull' => true,
                        'default' => 0,
                    ],
                ]
            );
        }
    }

    public function step_10(): void
    {
        if (!$this->db->addPrimaryKey('il_resource_rca', ['rcid', 'rid'])) {
            $this->db->addPrimaryKey(
                'il_resource_rca',
                [
                    'rcid',
                    'rid',
                ]
            );
        }

        if (!$this->db->indexExistsByFields('il_resource_rc', ['rcid'])) {
            $this->db->addPrimaryKey(
                'il_resource_rc',
                [
                    'rcid'
                ]
            );
        }
    }
}
