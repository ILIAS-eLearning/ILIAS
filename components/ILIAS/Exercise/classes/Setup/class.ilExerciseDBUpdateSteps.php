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

namespace ILIAS\Exercise\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->indexExistsByFields('exc_assignment', ['exc_id'])) {
            $this->db->addIndex('exc_assignment', ['exc_id'], 'i1');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->indexExistsByFields('exc_members', ['usr_id'])) {
            $this->db->addIndex('exc_members', ['usr_id'], 'i1');
        }
    }

    public function step_3(): void
    {
        if (!$this->db->indexExistsByFields('exc_assignment', ['deadline_mode', 'exc_id'])) {
            $this->db->addIndex('exc_assignment', ['deadline_mode', 'exc_id'], 'i2');
        }
    }

    public function step_4(): void
    {
        if (!$this->db->indexExistsByFields('exc_ass_file_order', ['assignment_id'])) {
            $this->db->addIndex('exc_ass_file_order', ['assignment_id'], 'i1');
        }
    }

    public function step_5(): void
    {
        if (!$this->db->indexExistsByFields('il_exc_team', ['id'])) {
            $this->db->addIndex('il_exc_team', ['id'], 'i1');
        }
    }

    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('exc_assignment', 'if_rcid')) {
            $this->db->addTableColumn(
                'exc_assignment',
                'if_rcid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_7(): void
    {
        if (!$this->db->tableColumnExists('exc_assignment_peer', 'id')) {
            $this->db->addTableColumn('exc_assignment_peer', 'id', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
            $this->db->createSequence('exc_assignment_peer');
        }
    }

    public function step_8(): void
    {
        $set = $this->db->queryF(
            "SELECT * FROM exc_assignment_peer ",
            [],
            []
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $next_id = $this->db->nextId("exc_assignment_peer");
            $this->db->update(
                "exc_assignment_peer",
                [
                "id" => ["integer", $next_id]
            ],
                [    // where
                    "ass_id" => ["integer", $rec["ass_id"]],
                    "giver_id" => ["integer", $rec["giver_id"]],
                    "peer_id" => ["integer", $rec["peer_id"]]
                ]
            );
        }
    }

    public function step_9(): void
    {
        $this->db->dropPrimaryKey("exc_assignment_peer");
        $this->db->addPrimaryKey("exc_assignment_peer", ["id"]);
    }

    public function step_10(): void
    {
        $this->db->addUniqueConstraint("exc_assignment_peer", array('ass_id', 'giver_id', 'peer_id'), 'c1');
    }

    public function step_11(): void
    {
        $this->db->addIndex("exc_assignment_peer", ["ass_id"], "i1");
    }

    public function step_12(): void
    {
        if (!$this->db->tableColumnExists('exc_idl', 'requested')) {
            $this->db->addTableColumn('exc_idl', 'requested', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 1,
                'default' => 0
            ));
        }
    }

    public function step_13(): void
    {
        if (!$this->db->tableColumnExists('exc_assignment', 'solution_rid')) {
            $this->db->addTableColumn(
                'exc_assignment',
                'solution_rid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_14(): void
    {
        if (!$this->db->tableColumnExists('exc_mem_ass_status', 'feedback_rcid')) {
            $this->db->addTableColumn(
                'exc_mem_ass_status',
                'feedback_rcid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_15(): void
    {
        if (!$this->db->tableExists('exc_team_data')) {
            $this->db->createTable(
                'exc_team_data',
                [
                    "id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4
                    ],
                    "feedback_rcid" => [
                        'type' => 'text',
                        'notnull' => false,
                        'length' => 64,
                        'default' => ''
                    ]
                ]
            );
        }
        $this->db->addPrimaryKey('exc_team_data', ["id"]);
    }

    public function step_16(): void
    {
        $set = $this->db->queryF(
            "SELECT DISTINCT il_exc_team.id FROM il_exc_team LEFT JOIN exc_team_data ON il_exc_team.id = exc_team_data.id WHERE exc_team_data.id IS NULL",
            [],
            []
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->db->insert("exc_team_data", [
                "id" => ["integer", (int) $rec["id"]],
            ]);
        }
    }

    public function step_17(): void
    {
        if (!$this->db->tableExists('exc_multi_feedback')) {
            $this->db->createTable(
                'exc_multi_feedback',
                [
                    "tutor_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4
                    ],
                    "ass_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4
                    ],
                    "zip_rid" => [
                        'type' => 'text',
                        'notnull' => false,
                        'length' => 64,
                        'default' => ''
                    ]
                ]
            );
        }
        $this->db->addPrimaryKey('exc_multi_feedback', ["tutor_id", "ass_id"]);
    }

    public function step_18(): void
    {
        if (!$this->db->tableColumnExists('exc_returned', 'rid')) {
            $this->db->addTableColumn(
                'exc_returned',
                'rid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_19(): void
    {
        if (!$this->db->tableExists('exc_crit_file')) {
            $this->db->createTable(
                'exc_crit_file',
                [
                    "ass_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4,
                        'default' => 0
                    ],
                    "giver_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4,
                        'default' => 0
                    ],
                    "peer_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4,
                        'default' => 0
                    ],
                    "criteria_id" => [
                        'type' => 'integer',
                        'notnull' => true,
                        'length' => 4,
                        'default' => 0
                    ],
                    "rid" => [
                        'type' => 'text',
                        'notnull' => true,
                        'length' => 64,
                        'default' => ""
                    ],
                ]
            );
            $this->db->addPrimaryKey('exc_crit_file', ['ass_id', 'giver_id','peer_id','criteria_id']);
        }
    }

    public function step_20(): void
    {
        $this->db->manipulate("UPDATE exc_returned SET rid = NULL WHERE rid = " . $this->db->quote("", "text"));
    }

    public function step_21(): void
    {
        if (!$this->db->tableColumnExists('exc_assignment_peer', 'migrated')) {
            $this->db->addTableColumn(
                'exc_assignment_peer',
                'migrated',
                [
                    'type' => 'integer',
                    'notnull' => true,
                    'length' => 1,
                    'default' => 0
                ]
            );
        }
    }

}
