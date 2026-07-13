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

namespace ILIAS\COPage\History;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class HistoryRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected HistoryDBRepository $history_repo;
    protected \ilDBInterface $db;
    protected int $page_id;
    protected string $parent_type;
    protected string $lang;

    public function __construct(
        HistoryDBRepository $history_repo,
        \ilDBInterface $db,
        int $page_id,
        string $parent_type,
        string $lang
    ) {
        $this->history_repo = $history_repo;
        $this->db = $db;
        $this->page_id = $page_id;
        $this->parent_type = $parent_type;
        $this->lang = $lang;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $rows = [];

        // Current version (nr=0)
        $q = "SELECT page_id, last_change hdate, parent_type, parent_id, last_change_user user_id, content, lang " .
            "FROM page_object WHERE page_id = %s" .
            " AND parent_type = %s" .
            " AND lang = %s";
        $res = $this->db->queryF($q, ["integer", "text", "text"], [$this->page_id, $this->parent_type, $this->lang]);
        if ($row = $this->db->fetchAssoc($res)) {
            $row["nr"] = 0;
            $row["sortkey"] = 999999999;
            $row["user"] = (int) $row["user_id"];
            $row["id"] = "0";
            $rows[] = $row;
        }

        // History entries
        $q = "SELECT * FROM page_history WHERE page_id = %s" .
            " AND parent_type = %s" .
            " AND lang = %s";

        $res = $this->db->queryF($q, ["integer", "text", "text"], [$this->page_id, $this->parent_type, $this->lang]);
        while ($row = $this->db->fetchAssoc($res)) {
            $row["sortkey"] = (int) $row["nr"];
            $row["user"] = (int) $row["user_id"];
            $row["id"] = (string) $row["nr"];
            $rows[] = $row;
        }

        array_multisort(
            array_column($rows, "sortkey"),
            SORT_DESC,
            SORT_NUMERIC,
            $rows
        );

        $rows = $this->applyRange($rows, $range);

        foreach ($rows as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        $q = "SELECT count(*) cnt FROM page_history WHERE page_id = %s" .
            " AND parent_type = %s" .
            " AND lang = %s";
        $res = $this->db->queryF($q, ["integer", "text", "text"], [$this->page_id, $this->parent_type, $this->lang]);
        $row = $this->db->fetchAssoc($res);
        return (int) $row["cnt"] + 1;
    }

    public function isFieldNumeric(
        string $field
    ): bool {
        if ($field === "sortkey" || $field === "nr") {
            return true;
        }
        return false;
    }
}
