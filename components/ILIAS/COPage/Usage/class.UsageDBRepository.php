<?php

declare(strict_types=1);

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

namespace ILIAS\COPage\Usage;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class UsageDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function deleteHistoryUsagesLowerEqualThan(
        string $pc_type,
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr,
        string $a_lang = "-"
    ): void {
        $db = $this->db;

        $and_hist = " AND usage_hist_nr > 0 AND usage_hist_nr <= " . $db->quote($a_usage_hist_nr, "integer");

        $q = "DELETE FROM page_pc_usage WHERE usage_type = " .
            $db->quote($a_type, "text") .
            " AND usage_id = " . $db->quote((int) $a_id, "integer") .
            " AND usage_lang = " . $db->quote($a_lang, "text") .
            $and_hist .
            " AND pc_type = " . $db->quote($pc_type, "text");
        $db->manipulate($q);
    }
}
