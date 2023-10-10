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

namespace ILIAS\Help\Tooltips;

class TooltipsDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function getTooltipPresentationText(
        string $a_tt_id,
        array $module_ids
    ): string {
        $set = $this->db->query(
            $q =
            "SELECT tt.tt_text FROM help_tooltip tt LEFT JOIN help_module hmod " .
            " ON (tt.module_id = hmod.id) " .
            " WHERE tt.tt_id = " . $this->db->quote($a_tt_id, "text") .
            " AND " . $this->db->in("tt.module_id", $module_ids, false, "integer") .
            " ORDER BY hmod.order_nr "
        );
        $rec = $this->db->fetchAssoc($set);
        if (is_array($rec) && $rec["tt_text"] != "") {
            $t = $rec["tt_text"];
            if (count($module_ids) === 1 && current($module_ids) === 0) {
                $t .= "<br/><i>(" . $a_tt_id . ")</i>";
            }
            return $t;
        } else { // try to get general version
            $fu = (int) strpos($a_tt_id, "_");
            $gen_tt_id = "*" . substr($a_tt_id, $fu);
            $set = $this->db->query(
                "SELECT tt.tt_text FROM help_tooltip tt LEFT JOIN help_module hmod " .
                " ON (tt.module_id = hmod.id) " .
                " WHERE tt.tt_id = " . $this->db->quote($gen_tt_id, "text") .
                " AND " . $this->db->in("tt.module_id", $module_ids, false, "integer") .
                " ORDER BY hmod.order_nr "
            );
            $rec = $this->db->fetchAssoc($set);
            if (is_array($rec) && $rec["tt_text"] != "") {
                $t = $rec["tt_text"];
                if (count($module_ids) === 1 && current($module_ids) === 0) {
                    $t .= "<br/><i>(" . $a_tt_id . ")</i>";
                }
                return $t;
            }
        }
        if (count($module_ids) === 1 && current($module_ids) === 0) {
            return "<i>" . $a_tt_id . "</i>";
        }
        return "";
    }

    public function getAllTooltips(
        string $a_comp = "",
        int $a_module_id = 0
    ): array {
        $q = "SELECT * FROM help_tooltip";
        $q .= " WHERE module_id = " . $this->db->quote($a_module_id, "integer");
        if ($a_comp !== "") {
            $q .= " AND comp = " . $this->db->quote($a_comp, "text");
        }
        $set = $this->db->query($q);
        $tts = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $tts[$rec["id"]] = array("id" => $rec["id"], "text" => $rec["tt_text"],
                                     "tt_id" => $rec["tt_id"]);
        }
        return $tts;
    }

    public function addTooltip(
        string $a_tt_id,
        string $a_text,
        int $a_module_id = 0
    ): void {
        $fu = strpos($a_tt_id, "_");
        $comp = substr($a_tt_id, 0, $fu);

        $nid = $this->db->nextId("help_tooltip");
        $this->db->manipulate("INSERT INTO help_tooltip " .
            "(id, tt_text, tt_id, comp,module_id) VALUES (" .
            $this->db->quote($nid, "integer") . "," .
            $this->db->quote($a_text, "text") . "," .
            $this->db->quote($a_tt_id, "text") . "," .
            $this->db->quote($comp, "text") . "," .
            $this->db->quote($a_module_id, "integer") .
            ")");
    }

    public function updateTooltip(
        int $a_id,
        string $a_text,
        string $a_tt_id
    ): void {
        $fu = strpos($a_tt_id, "_");
        $comp = substr($a_tt_id, 0, $fu);

        $this->db->manipulate(
            "UPDATE help_tooltip SET " .
            " tt_text = " . $this->db->quote($a_text, "text") . ", " .
            " tt_id = " . $this->db->quote($a_tt_id, "text") . ", " .
            " comp = " . $this->db->quote($comp, "text") .
            " WHERE id = " . $this->db->quote($a_id, "integer")
        );
    }


    /**
     * Get all tooltip components
     */
    public function getTooltipComponents(
        int $a_module_id = 0
    ): array {
        $set = $this->db->query("SELECT DISTINCT comp FROM help_tooltip " .
            " WHERE module_id = " . $this->db->quote($a_module_id, "integer") .
            " ORDER BY comp ");
        $comps = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $comps[] = $rec["comp"];
        }
        return $comps;
    }

    public function deleteTooltip(
        int $a_id
    ): void {
        $this->db->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " id = " . $this->db->quote($a_id, "integer")
        );
    }

    public function deleteTooltipsOfModule(
        int $a_id
    ): void {
        $this->db->manipulate(
            "DELETE FROM help_tooltip WHERE " .
            " module_id = " . $this->db->quote($a_id, "integer")
        );
    }

}
