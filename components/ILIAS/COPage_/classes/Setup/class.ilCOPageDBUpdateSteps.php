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

namespace ILIAS\COPage\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $field = array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }

    public function step_2(): void
    {
        $field = array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }

    public function step_3(): void
    {
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media"]
        ],
            [    // where
                "title" => ["text", "1A Simple text page with accompanying media"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media and test"]
        ],
            [    // where
                "title" => ["text", "1C Text page with accompanying media and test"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Text page with accompanying media followed by test and text"]
        ],
            [    // where
                "title" => ["text", "1E Text page with accompanying media followed by test and text"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Media page with accompanying text and test"]
        ],
            [    // where
                "title" => ["text", "2C Simple media page with accompanying text and test"]
            ]
        );
        $this->db->update(
            "page_layout",
            [
            "title" => ["text", "Vertical component navigation page with media and text	"]
        ],
            [    // where
                "title" => ["text", "7C Vertical component navigation page with media and text"]
            ]
        );
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('page_object', 'est_reading_time')) {
            $this->db->addTableColumn('page_object', 'est_reading_time', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_5(): void
    {
        $set = $this->db->queryF(
            "SELECT content FROM page_object " .
            " WHERE page_id = %s AND parent_type = %s AND lang = %s",
            ["integer", "text", "text"],
            [5, "stys", "-"]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $content = $rec["content"];

            $replacements = [
                ["a4e417c08feebeafb1487e60a2e245a4", "a4e417c08feebeafb1487e60a2e245a5"],
                ["a4e417c08feebeafb1487e60a2e245a4", "a4e417c08feebeafb1487e60a2e245a6"],
                ["a4e417c08feebeafb1487e60a2e245a5", "a4e417c08feebeafb1487e60a2e245a7"],
                ["a4e417c08feebeafb1487e60a2e245a5", "a4e417c08feebeafb1487e60a2e245a8"]
            ];

            foreach ($replacements as $r) {
                $content = preg_replace('/' . $r[0] . '/', $r[1], $content, 1);
            }

            $this->db->update(
                "page_object",
                [
                "content" => ["clob", $content]
            ],
                [    // where
                    "page_id" => ["integer", 5],
                    "parent_type" => ["text", "stys"],
                    "lang" => ["text", '-'],
                ]
            );
        }
    }
}
