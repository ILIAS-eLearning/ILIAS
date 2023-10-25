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

namespace ILIAS\Help\Map;

class MapDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function saveScreenIdsForChapter(
        int $a_chap,
        array $a_ids
    ): void {
        $this->removeScreenIdsOfChapter($a_chap);
        foreach ($a_ids as $id) {
            $id = trim($id);
            $id = explode("/", $id);
            if ($id[0] != "") {
                if (($id[1] ?? "") == "") {
                    $id[1] = "-";
                }
                $id2 = explode("#", ($id[2] ?? ""));
                if ($id2[0] == "") {
                    $id2[0] = "-";
                }
                if (($id2[1] ?? "") == "") {
                    $id2[1] = "-";
                }
                $this->db->replace(
                    "help_map",
                    array("chap" => array("integer", $a_chap),
                          "component" => array("text", $id[0]),
                          "screen_id" => array("text", $id[1]),
                          "screen_sub_id" => array("text", $id2[0]),
                          "perm" => array("text", $id2[1]),
                          "module_id" => array("integer", 0)
                    ),
                    array()
                );
            }
        }
    }

    public function saveMappingEntry(
        int $a_chap,
        string $a_comp,
        string $a_screen_id,
        string $a_screen_sub_id,
        string $a_perm,
        int $a_module_id = 0
    ): void {
        $this->db->replace(
            "help_map",
            array("chap" => array("integer", $a_chap),
                  "component" => array("text", $a_comp),
                  "screen_id" => array("text", $a_screen_id),
                  "screen_sub_id" => array("text", $a_screen_sub_id),
                  "perm" => array("text", $a_perm),
                  "module_id" => array("integer", $a_module_id)
            ),
            array()
        );
    }

    public function removeScreenIdsOfChapter(
        int $a_chap,
        int $a_module_id = 0
    ): void {
        $this->db->manipulate(
            "DELETE FROM help_map WHERE " .
            " chap = " . $this->db->quote($a_chap, "integer") .
            " AND module_id = " . $this->db->quote($a_module_id, "integer")
        );
    }

    public function getScreenIdsOfChapter(
        int $a_chap,
        int $a_module_id = 0
    ): array {
        $set = $this->db->query(
            "SELECT * FROM help_map " .
            " WHERE chap = " . $this->db->quote($a_chap, "integer") .
            " AND module_id = " . $this->db->quote($a_module_id, "integer") .
            " ORDER BY component, screen_id, screen_sub_id"
        );
        $screen_ids = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            if ($rec["screen_id"] == "-") {
                $rec["screen_id"] = "";
            }
            if ($rec["screen_sub_id"] == "-") {
                $rec["screen_sub_id"] = "";
            }
            $id = $rec["component"] . "/" . $rec["screen_id"] . "/" . $rec["screen_sub_id"];
            if ($rec["perm"] != "" && $rec["perm"] != "-") {
                $id .= "#" . $rec["perm"];
            }
            $screen_ids[] = $id;
        }
        return $screen_ids;
    }

    public function getChaptersForScreenId(
        string $a_screen_id,
        array $module_ids
    ): \Generator {
        $sc_id = explode("/", $a_screen_id);
        $chaps = array();
        foreach ($module_ids as $module_id) {
            if ($sc_id[0] != "") {
                if ($sc_id[1] == "") {
                    $sc_id[1] = "-";
                }
                if ($sc_id[2] == "") {
                    $sc_id[2] = "-";
                }
                $set = $this->db->query(
                    "SELECT chap, perm FROM help_map JOIN lm_tree" .
                    " ON (help_map.chap = lm_tree.child) " .
                    " WHERE (component = " . $this->db->quote($sc_id[0], "text") .
                    " OR component = " . $this->db->quote("*", "text") . ")" .
                    " AND screen_id = " . $this->db->quote($sc_id[1], "text") .
                    " AND screen_sub_id = " . $this->db->quote($sc_id[2], "text") .
                    " AND module_id = " . $this->db->quote($module_id, "integer") .
                    " ORDER BY lm_tree.lft"
                );
                while ($rec = $this->db->fetchAssoc($set)) {
                    yield $rec;
                }
            }
        }
    }

    public function deleteEntriesOfModule(
        int $a_id
    ): void {
        $this->db->manipulate("DELETE FROM help_map WHERE " .
            " module_id = " . $this->db->quote($a_id, "integer"));
    }

}
