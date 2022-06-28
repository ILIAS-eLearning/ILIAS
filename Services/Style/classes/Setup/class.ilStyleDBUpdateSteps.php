<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Style\Content\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if (!$this->db->tableExists('style_char_title')) {
            $fields = [
                'type' => [
                    'type' => 'text',
                    'length' => 30,
                    'notnull' => true
                ],
                'characteristic' => [
                    'type' => 'text',
                    'length' => 30,
                    'notnull' => true
                ],
                'lang' => [
                    'type' => 'text',
                    'length' => 2,
                    'notnull' => true
                ],
                'title' => [
                    'type' => 'text',
                    'length' => 200,
                    'notnull' => false
                ]
            ];

            $this->db->createTable('style_char_title', $fields);
            $this->db->addPrimaryKey('style_char_title', ['type', 'characteristic', 'lang']);
        }
    }

    public function step_2()
    {
        $this->db->dropPrimaryKey('style_char_title');
        if (!$this->db->tableColumnExists('style_char_title', 'style_id')) {
            $this->db->addTableColumn('style_char_title', 'style_id', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4
            ));
        }
        $this->db->addPrimaryKey('style_char_title', ['style_id', 'type', 'characteristic', 'lang']);
    }

    public function step_3()
    {
        if (!$this->db->tableColumnExists('style_char', 'order_nr')) {
            $this->db->addTableColumn('style_char', 'order_nr', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ));
        }
    }

    public function step_4()
    {
        if (!$this->db->tableColumnExists('style_char', 'deprecated')) {
            $this->db->addTableColumn('style_char', 'deprecated', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 1,
                "default" => 0
            ));
        }
    }

    public function step_5()
    {
        $this->db->renameTableColumn('style_char', "deprecated", 'outdated');
    }

    public function step_6()
    {
        if (!$this->db->tableExists('sty_rep_container')) {
            $fields = [
                'ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'reuse' => [
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ];

            $this->db->createTable('sty_rep_container', $fields);
            $this->db->addPrimaryKey('sty_rep_container', ['ref_id']);
        }
    }

    public function step_7()
    {
        $set = $this->db->queryF(
            "SELECT * FROM content_object ",
            [],
            []
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->db->replace(
                "style_usage",
                array(
                    "obj_id" => array("integer", (int) $rec["id"])
                ),
                array(
                    "style_id" => array("integer", (int) $rec["stylesheet"])
                )
            );
        }
    }

    public function step_8()
    {
        $set = $this->db->queryF(
            "SELECT * FROM content_page_data ",
            [],
            []
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->db->replace(
                "style_usage",
                array(
                    "obj_id" => array("integer", (int) $rec["content_page_id"])),
                array(
                    "style_id" => array("integer", (int) $rec["stylesheet"]))
            );
        }
    }

    public function step_9()
    {
        if (!$this->db->tableColumnExists('style_data', 'owner_obj')) {
            $this->db->addTableColumn('style_data', 'owner_obj', array(
                'type' => 'integer',
                'notnull' => false,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_10()
    {
        $set = $this->db->queryF(
            "SELECT * FROM style_data WHERE standard = %s",
            ["integer"],
            [0]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $set2 = $this->db->queryF(
                "SELECT * FROM style_usage " .
                " WHERE style_id = %s ",
                ["integer"],
                [$rec["id"]]
            );
            while ($rec2 = $this->db->fetchAssoc($set2)) {
                $this->db->update(
                    "style_data",
                    [
                    "owner_obj" => ["integer", $rec2["obj_id"]]
                ],
                    [    // where
                        "id" => ["integer", $rec["id"]]
                    ]
                );
            }
        }
    }

    public function step_11()
    {
        // Add new index
        if (!$this->db->indexExistsByFields('style_template', ['style_id'])) {
            $this->db->addIndex('style_template', ['style_id'], 'i1');
        }
    }
}
