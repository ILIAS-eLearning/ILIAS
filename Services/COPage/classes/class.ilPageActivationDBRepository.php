<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageActivationDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(\ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;
    }

    /**
     * Get activation data for a set of page ids
     * @param string $parent_type
     * @param array  $ids
     * @param bool   $check_scheduled_activation
     * @param string $lang
     * @return array
     */
    public function get(string $parent_type, array $ids, bool $check_scheduled_activation = false, string $lang = "")
    {
        $db = $this->db;

        // language must be set at least to "-"
        if ($lang == "") {
            $lang = "-";
        }
        $set = $db->queryF(
            "SELECT page_id, active, activation_start, activation_end, show_activation_info FROM page_object WHERE " .
            $db->in("page_id", $ids, false, "integer").
            " AND parent_type = %s AND lang = %s",
            ["text", "text"],
            [$parent_type, $lang]
            );
        $active = [];
        $now = ilUtil::now();
        while($rec = $db->fetchAssoc($set)) {
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