<?php declare(strict_types=1);

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

namespace ILIAS\MediaObjects\Usage;

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

    public function getHistoryUsagesLowerEqualThan(
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr,
        string $a_lang = "-"
    ) : array {
        $db = $this->db;

        $and_hist = " AND usage_hist_nr > 0 AND usage_hist_nr <= " . $db->quote($a_usage_hist_nr, "integer");

        $mob_ids = array();
        $set = $db->query("SELECT DISTINCT(id) FROM mob_usage" .
            " WHERE usage_type = " . $db->quote($a_type, "text") .
            " AND usage_id = " . $db->quote($a_id, "integer") .
            " AND usage_lang = " . $db->quote($a_lang, "text") .
            $and_hist);

        while ($row = $db->fetchAssoc($set)) {
            $mob_ids[] = $row["id"];
        }
        return $mob_ids;
    }

    public function deleteHistoryUsagesLowerEqualThan(
        string $a_type,
        int $a_id,
        int $a_usage_hist_nr,
        string $a_lang = "-"
    ) : void {
        $db = $this->db;

        $and_hist = " AND usage_hist_nr > 0 AND usage_hist_nr <= " . $db->quote($a_usage_hist_nr, "integer");
        $q = "DELETE FROM mob_usage WHERE usage_type = " .
            $db->quote($a_type, "text") .
            " AND usage_id= " . $db->quote($a_id, "integer") .
            " AND usage_lang = " . $db->quote($a_lang, "text") .
            $and_hist;
        $db->manipulate($q);
    }
}
