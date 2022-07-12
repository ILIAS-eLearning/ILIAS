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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageActivationDBRepository
{
    protected ilDBInterface $db;

    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    /**
     * Get activation data for a set of page ids
     */
    public function get(
        string $parent_type,
        array $ids,
        bool $check_scheduled_activation = false,
        string $lang = ""
    ) : array {


        // language must be set at least to "-"
        if ($lang == "") {
            $lang = "-";
        }

        $active = [];

        // for special languages initialize with master language
        if ($lang != "-") {
            foreach ($this->getData($parent_type, $ids, $check_scheduled_activation, "-") as $k => $v) {
                $active[$k] = $v;
            }
        }

        foreach ($this->getData($parent_type, $ids, $check_scheduled_activation, $lang) as $k => $v) {
            $active[$k] = $v;
        }

        return $active;
    }

    protected function getData(
        string $parent_type,
        array $ids,
        bool $check_scheduled_activation = false,
        string $lang = ""
    ) : array {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT page_id, active, activation_start, activation_end, show_activation_info FROM page_object WHERE " .
            $db->in("page_id", $ids, false, "integer") .
            " AND parent_type = %s AND lang = %s",
            ["text", "text"],
            [$parent_type, $lang]
        );
        $active = [];
        $now = ilUtil::now();
        while ($rec = $db->fetchAssoc($set)) {
            if (!$rec["active"] && $check_scheduled_activation) {
                if ($now >= $rec["activation_start"] &&
                    $now <= $rec["activation_end"]) {
                    $active[$rec["page_id"]] = [
                        "active" => true,
                        "start" => $rec["activation_start"],
                        "end" => $rec["activation_end"],
                        "show_info" => (bool) $rec["show_activation_info"]
                    ];
                } else {
                    $active[$rec["page_id"]] = [
                        "active" => false,
                        "start" => $rec["activation_start"],
                        "end" => $rec["activation_end"],
                        "show_info" => (bool) $rec["show_activation_info"]
                    ];
                }
            } else {
                $active[$rec["page_id"]] = [
                    "active" => (bool) $rec["active"],
                    "start" => null,
                    "end" => null,
                    "show_info" => false
                ];
            }
        }
        return $active;
    }
}
