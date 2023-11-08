<?php

/* Copyright (c) 1998-2022 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\COPage\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReadingTimeDBRepo
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC->database();
    }

    public function getTime(
        int $page_id,
        string $parent_type,
        string $lang = "-"
    ): int {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT est_reading_time FROM page_object " .
            " WHERE page_id = %s AND parent_type = %s AND lang = %s",
            ["integer", "text", "text"],
            [$page_id, $parent_type, $lang]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return (int) $rec["est_reading_time"];
        }
        return 0;
    }

    public function saveTime(
        int $page_id,
        string $parent_type,
        string $lang = "-",
        int $est_reading_time = 0
    ): void {
        $db = $this->db;
        $db->update(
            "page_object",
            [
            "est_reading_time" => ["integer", $est_reading_time]
        ],
            [    // where
                "page_id" => ["integer", $page_id],
                "parent_type" => ["text", $parent_type],
                "lang" => ["text", $lang],
            ]
        );
    }

    public function getPagesWithMissingReadingTime(
        string $a_parent_type,
        int $a_parent_id
    ) {
        $db = $this->db;
        $q = "SELECT * FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") .
            " AND est_reading_time = " . $db->quote(0, "integer");

        $set = $db->query($q);
        $pages = [];
        while ($rec = $db->fetchAssoc($set)) {
            $pages[] = [
                "parent_type" => $a_parent_type,
                "parent_id" => $a_parent_id,
                "page_id" => (int) $rec["page_id"],
                "lang" => $rec["lang"]
            ];
        }
        return $pages;
    }

    public function getParentReadingTime(
        string $a_parent_type,
        int $a_parent_id
    ): int {
        $db = $this->db;
        $q = "SELECT SUM(est_reading_time) as rt FROM page_object " .
            " WHERE parent_id = " . $db->quote($a_parent_id, "integer") .
            " AND parent_type = " . $db->quote($a_parent_type, "text") .
            " AND lang = " . $db->quote("-", "text");

        $set = $db->query($q);
        $rec = $db->fetchAssoc($set);
        return (int) ($rec["rt"] ?? 0);
    }
}
