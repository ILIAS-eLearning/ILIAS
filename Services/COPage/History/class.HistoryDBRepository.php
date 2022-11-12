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

namespace ILIAS\COPage\History;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class HistoryDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get the maximum history nr, that es older than x days for each page (and all pages)
     * @throws \ilDateTimeException
     */
    public function getMaxHistEntryPerPageOlderThanX(int $xdays): \Iterator
    {
        $db = $this->db;

        $hdate = new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME);
        $hdate->increment(\ilDateTime::DAY, (-1 * $xdays));

        $set = $db->queryF(
            "SELECT MAX(nr) max_nr, parent_type, page_id, lang FROM page_history " .
            " WHERE nr > %s AND hdate < %s GROUP BY parent_type, page_id, lang ",
            ["integer", "timestamp"],
            [0, $hdate]
        );
        while ($rec = $db->fetchAssoc($set)) {
            yield [
                "parent_type" => $rec["parent_type"],
                "page_id" => $rec["page_id"],
                "lang" => $rec["lang"],
                "max_nr" => (int) $rec["max_nr"]
            ];
        }
    }

    /**
     * Get the maximum deletable history nr for a single page,
     * if $keep_entries entries should be kept.
     */
    public function getMaxDeletableNr(
        int $keep_entries,
        string $parent_type,
        int $page_id,
        string $lang
    ): int {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT MAX(nr) mnr FROM page_history " .
            " WHERE parent_type = %s AND page_id = %s AND lang = %s ",
            ["text", "integer", "text"],
            [$parent_type, $page_id, $lang]
        );
        $max_old_nr = 0;
        if ($rec = $db->fetchAssoc($set)) {
            $max_old_nr = (int) $rec["mnr"];
        }
        $max_old_nr -= $keep_entries;
        if ($max_old_nr < 0) {
            $max_old_nr = 0;
        }
        return $max_old_nr;
    }

    public function deleteHistoryEntriesOlderEqualThanNr(
        int $delete_lower_than_nr,
        string $parent_type,
        int $page_id,
        string $lang
    ): void {
        $db = $this->db;

        // main entries in history
        $q = "DELETE FROM page_history " .
            " WHERE parent_type = " . $db->quote($parent_type, "text") .
            " AND page_id = " . $db->quote($page_id, "integer") .
            " AND lang = " . $db->quote($lang, "text") .
            " AND nr <= " . $db->quote($delete_lower_than_nr, "integer");
        $db->manipulate($q);
    }

    public function getHistoryNumbersOlderEqualThanNr(
        int $delete_lower_than_nr,
        string $parent_type,
        int $page_id,
        string $lang
    ): \Iterator {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT nr FROM page_history " .
            " WHERE parent_type = %s  " .
            " AND page_id = %s  " .
            " AND lang = %s  " .
            " AND nr <= %s  ",
            ["text", "integer", "text", "integer"],
            [$parent_type, $page_id, $lang, $delete_lower_than_nr]
        );
        while ($rec = $db->fetchAssoc($set)) {
            yield (int) $rec["nr"];
        }
    }
}
