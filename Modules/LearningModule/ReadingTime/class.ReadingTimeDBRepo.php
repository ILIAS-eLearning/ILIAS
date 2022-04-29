<?php

/* Copyright (c) 1998-2022 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\LearningModule\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReadingTimeDBRepo
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected static $times = [];

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->db = $DIC->database();
    }

    public function isActivated(int $lm_id) : bool
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT act_est_reading_time FROM content_object " .
            " WHERE id = %s ",
            ["integer"],
            [$lm_id]
        );
        if ($rec = $db->fetchAssoc($set)) {
            return (bool) $rec["act_est_reading_time"];
        }
        return false;
    }

    public function activate(int $lm_id, bool $activated) : void
    {
        $db = $this->db;
        $db->update(
            "content_object",
            [
            "act_est_reading_time" => ["integer", $activated]
        ],
            [    // where
                "id" => ["integer", $lm_id]
            ]
        );
    }

    public function saveReadingTime(int $lm_id, int $reading_time) : void
    {
        $db = $this->db;
        $db->update(
            "content_object",
            [
            "est_reading_time" => ["integer", $reading_time]
        ],
            [    // where
                "id" => ["integer", $lm_id]
            ]
        );
    }

    public function getReadingTime(int $lm_id) : ?int
    {
        if (!isset(self::$times[$lm_id])) {
            $this->loadData([$lm_id]);
        }
        return self::$times[$lm_id];
    }

    public function loadData(array $lm_ids) : void
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT id, act_est_reading_time, est_reading_time FROM content_object " .
            " WHERE " . $db->in("id", $lm_ids, false, "integer"),
            [],
            []
        );
        foreach ($lm_ids as $lm_id) {
            self::$times[(int) $lm_id] = null;
        }
        while ($rec = $db->fetchAssoc($set)) {
            if ($rec["act_est_reading_time"]) {
                self::$times[(int) $rec["id"]] = (int) $rec["est_reading_time"];
            }
        }
    }
}
