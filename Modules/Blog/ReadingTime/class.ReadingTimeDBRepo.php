<?php

/* Copyright (c) 1998-2022 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Blog\ReadingTime;

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

    public function isActivated(int $lm_id) : bool
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT act_est_reading_time FROM il_blog " .
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
            "il_blog",
            [
            "act_est_reading_time" => ["integer", $activated]
        ],
            [    // where
                "id" => ["integer", $lm_id]
            ]
        );
    }
}
