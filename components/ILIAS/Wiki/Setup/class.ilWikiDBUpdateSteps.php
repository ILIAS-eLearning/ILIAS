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

namespace ILIAS\Wiki\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $db = $this->db;
        foreach (["int_links", "ext_links", "footnotes", "num_ratings", "num_words", "avg_rating", "deleted"] as $field) {
            $db->modifyTableColumn('wiki_stat_page', $field, array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_2(): void
    {
        $db = $this->db;
        foreach (["num_chars"] as $field) {
            $db->modifyTableColumn('wiki_stat_page', $field, array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_3(): void
    {
        $db = $this->db;
        foreach (["num_pages", "del_pages", "avg_rating"] as $field) {
            $db->modifyTableColumn('wiki_stat', $field, array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_4(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('il_wiki_page', 'lang')) {
            $this->db->addTableColumn('il_wiki_page', 'lang', array(
                'type' => 'text',
                'notnull' => true,
                'length' => 10,
                'default' => "-"
            ));
            $this->db->dropPrimaryKey('il_wiki_page');
            $this->db->addPrimaryKey(
                'il_wiki_page',
                ["id", "lang"]
            );
        }
    }

    public function step_5(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists('il_wiki_missing_page', 'lang')) {
            $this->db->addTableColumn('il_wiki_missing_page', 'lang', array(
                'type' => 'text',
                'notnull' => true,
                'length' => 5,
                'default' => "-"
            ));
            $this->db->dropPrimaryKey('il_wiki_missing_page');
            $this->db->addPrimaryKey(
                'il_wiki_missing_page',
                ["wiki_id", "source_id", "target_name", "lang"]
            );
        }
    }

    public function step_6(): void
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM il_wiki_data " .
            " WHERE public_notes = %s ",
            ["integer"],
            [1]
        );
        while ($rec = $db->fetchAssoc($set)) {
            $set2 = $db->queryF(
                "SELECT * FROM note_settings " .
                " WHERE rep_obj_id = %s AND obj_id = %s",
                ["integer", "integer"],
                [$rec["id"], 0]
            );
            if ($rec2 = $db->fetchAssoc($set2)) {
                $db->update(
                    "note_settings",
                    [
                    "activated" => ["integer", 1]
                ],
                    [    // where
                        "rep_obj_id" => ["integer", $rec["id"]],
                        "obj_id" => ["integer", 0]
                    ]
                );
            } else {
                $db->insert("note_settings", [
                    "rep_obj_id" => ["integer", $rec["id"]],
                    "obj_id" => ["integer", 0],
                    "activated" => ["integer", 1],
                    "obj_type" => ["text", "wiki"]
                ]);
            }
        }

    }

}
