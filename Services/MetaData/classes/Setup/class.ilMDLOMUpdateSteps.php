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

class ilMDLOMUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a column to the il_meta_general table to store the
     * 'Aggregation Level' element.
     */
    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('il_meta_general', 'general_aggl')) {
            $this->db->addTableColumn(
                'il_meta_general',
                'general_aggl',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 16,
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_contribute table to store the
     * descrption of the date and its language.
     */
    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('il_meta_contribute', 'c_date_descr')) {
            $this->db->addTableColumn(
                'il_meta_contribute',
                'c_date_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_contribute', 'descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_contribute',
                'descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_annotation table to store the
     * description of the date and its language.
     */
    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('il_meta_annotation', 'a_date_descr')) {
            $this->db->addTableColumn(
                'il_meta_annotation',
                'a_date_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_annotation', 'date_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_annotation',
                'date_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_educational table to store the
     * description of the typical learning time and its language.
     */
    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('il_meta_educational', 'tlt_descr')) {
            $this->db->addTableColumn(
                'il_meta_educational',
                'tlt_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_educational', 'tlt_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_educational',
                'tlt_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add two columns to the il_meta_technical table to store the
     * description of the duration and its language.
     */
    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('il_meta_technical', 'duration_descr')) {
            $this->db->addTableColumn(
                'il_meta_technical',
                'duration_descr',
                [
                    'type' => ilDBConstants::T_CLOB,
                ]
            );
        }
        if (!$this->db->tableColumnExists('il_meta_technical', 'duration_descr_lang')) {
            $this->db->addTableColumn(
                'il_meta_technical',
                'duration_descr_lang',
                [
                    'type' => ilDBConstants::T_TEXT,
                    'length' => 2
                ]
            );
        }
    }

    /**
     * Add a new table for the non-unique coverage.
     */
    public function step_6(): void
    {
        if (!$this->db->tableExists('il_meta_coverage')) {
            $this->db->createTable(
                'il_meta_coverage',
                [
                    'meta_coverage_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'coverage' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 4000
                    ],
                    'coverage_language' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 2,
                        'fixed' => true
                    ]
                ]
            );

            $this->db->createSequence('il_meta_coverage');
            $this->db->addPrimaryKey('il_meta_coverage', ['meta_coverage_id']);
        }
    }

    /**
     * Add a new table for the non-unique metadata schema.
     */
    public function step_7(): void
    {
        if (!$this->db->tableExists('il_meta_meta_schema')) {
            $this->db->createTable(
                'il_meta_meta_schema',
                [
                    'meta_meta_schema_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'meta_data_schema' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ]
                ]
            );

            $this->db->createSequence('il_meta_meta_schema');
            $this->db->addPrimaryKey('il_meta_meta_schema', ['meta_meta_schema_id']);
        }
    }

    /**
     * Add a new table for the non-unique or-composite in requirements.
     */
    public function step_8(): void
    {
        if (!$this->db->tableExists('il_meta_or_composite')) {
            $this->db->createTable(
                'il_meta_or_composite',
                [
                    'meta_or_composite_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'name' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 32
                    ],
                    'min_version' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 255
                    ],
                    'max_version' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 255
                    ]
                ]
            );

            $this->db->createSequence('il_meta_or_composite');
            $this->db->addPrimaryKey('il_meta_or_composite', ['meta_or_composite_id']);
        }
    }

    /**
     * Add a new table for the non-unique learning resource type.
     */
    public function step_9(): void
    {
        if (!$this->db->tableExists('il_meta_lr_type')) {
            $this->db->createTable(
                'il_meta_lr_type',
                [
                    'meta_lr_type_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'learning_resource_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 32
                    ]
                ]
            );

            $this->db->createSequence('il_meta_lr_type');
            $this->db->addPrimaryKey('il_meta_lr_type', ['meta_lr_type_id']);
        }
    }

    /**
     * Add a new table for the non-unique intented end user role.
     */
    public function step_10(): void
    {
        if (!$this->db->tableExists('il_meta_end_usr_role')) {
            $this->db->createTable(
                'il_meta_end_usr_role',
                [
                    'meta_end_usr_role_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'intended_end_user_role' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ]
                ]
            );

            $this->db->createSequence('il_meta_end_usr_role');
            $this->db->addPrimaryKey('il_meta_end_usr_role', ['meta_end_usr_role_id']);
        }
    }

    /**
     * Add a new table for the non-unique context.
     */
    public function step_11(): void
    {
        if (!$this->db->tableExists('il_meta_context')) {
            $this->db->createTable(
                'il_meta_context',
                [
                    'meta_context_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                    ],
                    'rbac_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'obj_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 6
                    ],
                    'parent_type' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ],
                    'parent_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                    ],
                    'context' => [
                        'type' => ilDBConstants::T_TEXT,
                        'length' => 16
                    ]
                ]
            );

            $this->db->createSequence('il_meta_context');
            $this->db->addPrimaryKey('il_meta_context', ['meta_context_id']);
        }
    }
}
