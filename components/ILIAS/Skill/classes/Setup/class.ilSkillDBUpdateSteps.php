<?php

declare(strict_types=1);

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
 ********************************************************************
 */

class ilSkillDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->sequenceExists('skl_self_eval')) {
            $this->db->dropSequence('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval')) {
            $this->db->dropTable('skl_self_eval');
        }

        if ($this->db->tableExists('skl_self_eval_level')) {
            $this->db->dropTable('skl_self_eval_level');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('skl_user_skill_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_skill_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }

    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('skl_user_has_level', 'trigger_user_id')) {
            $this->db->addTableColumn(
                'skl_user_has_level',
                'trigger_user_id',
                array(
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 20,
                    'default' => "-"
                )
            );
        }
    }

    public function step_4(): void
    {
        // moved to ilSkillSetupAgent using Objectives
    }

    public function step_5(): void
    {
        // moved to ilSkillSetupAgent using Objectives
    }

    public function step_6(): void
    {
        $this->db->update(
            "object_data",
            [
                "title" => ["text", "Default"],
                "description" => ["text", ""]
            ],
            [    // where
                 "type" => ["text", "skee"],
                 "title" => ["text", "Skill Tree"]
            ]
        );
    }

    public function step_7(): void
    {
        $set = $this->db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s AND title = %s",
            ["string", "string"],
            ["skee", "Default"]
        );
        $rec = $this->db->fetchAssoc($set);

        $this->db->update(
            "skl_tree",
            [
            "skl_tree_id" => ["integer", $rec["obj_id"]]
        ],
            [    // where
                "skl_tree_id" => ["integer", 1]
            ]
        );
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists("skl_profile", "skill_tree_id")) {
            $this->db->addTableColumn("skl_profile", "skill_tree_id", array(
                "type" => "integer",
                "notnull" => true,
                "default" => 0,
                "length" => 4
            ));
        }
    }

    public function step_9(): void
    {
        $set = $this->db->queryF(
            "SELECT * FROM object_data " .
            " WHERE type = %s AND title = %s",
            ["string", "string"],
            ["skee", "Default"]
        );
        $rec = $this->db->fetchAssoc($set);

        $this->db->update(
            "skl_profile",
            [
            "skill_tree_id" => ["integer", $rec["obj_id"]]
        ],
            [    // where
                "skill_tree_id" => ["integer", 0]
            ]
        );
    }

    public function step_10(): void
    {
        if (!$this->db->tableColumnExists("skl_profile", "image_id")) {
            $this->db->addTableColumn("skl_profile", "image_id", array(
                "type" => "text",
                "notnull" => true,
                "length" => 4000
            ));
        }
    }

    public function step_11(): void
    {
        if (!$this->db->tableExists("skl_profile_completion")) {
            $fields = [
                "profile_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "user_id" => [
                    "type" => "integer",
                    "length" => 4,
                    "notnull" => true
                ],
                "date" => [
                    "type" => "timestamp",
                    "notnull" => true
                ],
                "fulfilled" => [
                    "type" => "integer",
                    "length" => 1,
                    "notnull" => true
                ]
            ];
            $this->db->createTable("skl_profile_completion", $fields);
            $this->db->addPrimaryKey("skl_profile_completion", ["profile_id", "user_id", "date"]);
        }
    }
}
