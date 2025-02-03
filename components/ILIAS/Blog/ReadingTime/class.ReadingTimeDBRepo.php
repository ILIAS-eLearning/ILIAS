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

namespace ILIAS\Blog\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReadingTimeDBRepo
{
    protected \ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    public function isActivated(int $lm_id): bool
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

    public function activate(int $lm_id, bool $activated): void
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
