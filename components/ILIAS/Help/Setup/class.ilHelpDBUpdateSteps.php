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

namespace ILIAS\Help\Setup;

class ilHelpDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('help_module', 'order_nr')) {
            $this->db->addTableColumn('help_module', 'order_nr', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('help_module', 'active')) {
            $this->db->addTableColumn('help_module', 'active', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 1,
                'default' => 0
            ));
        }
    }

    public function step_3(): void
    {
        $set = $this->db->queryF(
            "SELECT value FROM settings " .
            " WHERE module = %s AND keyword = %s",
            ["text", "text"],
            ["common", "help_module"]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            $id = (int) $rec["value"];
            if ($id > 0) {
                $this->db->update(
                    "help_module",
                    [
                    "active" => ["integer", 1]
                ],
                    [    // where
                        "id" => ["integer", $id]
                    ]
                );
            }
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('help_map', 'full_id')) {
            $this->db->addTableColumn('help_map', 'full_id', array(
                'type' => 'text',
                'length' => 1000,
                'default' => ""
            ));
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableExists('help_gt_settings')) {
            $this->db->createTable(
                'help_gt_settings',
                [
                'obj_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'active' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ],
                'screen_ids' => [
                    'type' => 'text',
                    'length' => 1000,
                    'notnull' => true,
                    'default' => ''
                ],
                'permission' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ],
            ]
            );
            $this->db->addPrimaryKey('help_gt_settings', ['obj_id']);
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableExists('help_gt_step')) {
            $this->db->createTable(
                'help_gt_step',
                [
                    'id' => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    'tour_id' => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    'order_nr' => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    'type' => [
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true
                    ],
                    'element_id' => [
                        'type' => 'text',
                        'length' => 1000,
                        'notnull' => true,
                        'default' => ''
                    ]
                ]
            );
            $this->db->addPrimaryKey('help_gt_step', ['id']);
        }
    }

    public function step_7(): void
    {
        $this->db->createSequence('help_gt_step');
    }

    public function step_8(): void
    {
        if (!$this->db->tableExists('help_gt_user_finished')) {
            $this->db->createTable(
                'help_gt_user_finished',
                [
                    'tour_id' => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ],
                    'user_id' => [
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ]
                ]
            );
            $this->db->addPrimaryKey('help_gt_user_finished', ['tour_id', 'user_id']);
        }
    }

    public function step_9(): void
    {
        if (!$this->db->tableColumnExists('help_gt_settings', 'lang')) {
            $this->db->addTableColumn('help_gt_settings', 'lang', array(
                'type' => 'text',
                'notnull' => true,
                'length' => 10,
                'default' => ''
            ));
        }
    }

}
