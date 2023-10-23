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

namespace ILIAS\Wiki\Navigation;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\Navigation\ImportantPage;

/**
 * Wiki page repo
 */
class ImportantPageDBRepository
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->data = $data;
        $this->db = $db;
    }

    protected function getPageInfoFromRecord(array $rec): ImportantPage
    {
        return $this->data->importantPage(
            (int) $rec["page_id"],
            (int) $rec["ord"],
            (int) $rec["indent"]
        );
    }

    /**
     * @return iterable<ImportantPage>
     */
    public function getList(int $wiki_id): \Iterator
    {
        $set = $this->db->query(
            "SELECT * FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $this->db->quote($wiki_id, "integer") . " ORDER BY ord ASC "
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    public function getListAsArray(int $wiki_id): array
    {
        $ipages = [];
        foreach ($this->getList($wiki_id) as $ip) {
            $ipages[$ip->getId()]["page_id"] = $ip->getId();
            $ipages[$ip->getId()]["ord"] = $ip->getOrder();
            $ipages[$ip->getId()]["indent"] = $ip->getIndent();
        }
        return $ipages;
    }

    protected function getMaxOrdNr(
        int $wiki_id
    ): int {
        $set = $this->db->query(
            "SELECT MAX(ord) as m FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $this->db->quote($wiki_id, "integer")
        );
        $rec = $this->db->fetchAssoc($set);
        return (int) $rec["m"];
    }


    public function add(
        int $wiki_id,
        int $page_id,
        int $nr = 0,
        int $indent = 0
    ): void {
        if (!$this->isImportantPage($wiki_id, $page_id)) {
            if ($nr === 0) {
                $nr = $this->getMaxOrdNr($wiki_id) + 10;
            }

            $this->db->manipulate("INSERT INTO il_wiki_imp_pages " .
                "(wiki_id, ord, indent, page_id) VALUES (" .
                $this->db->quote($wiki_id, "integer") . "," .
                $this->db->quote($nr, "integer") . "," .
                $this->db->quote($indent, "integer") . "," .
                $this->db->quote($page_id, "integer") .
                ")");
        }
    }

    public function isImportantPage(
        int $wiki_id,
        int $page_id
    ): bool {
        $set = $this->db->query(
            "SELECT * FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $this->db->quote($wiki_id, "integer") . " AND " .
            " page_id = " . $this->db->quote($page_id, "integer")
        );
        if ($this->db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function removeImportantPage(
        int $wiki_id,
        int $page_id
    ): void {
        $this->db->manipulate(
            "DELETE FROM il_wiki_imp_pages WHERE "
            . " wiki_id = " . $this->db->quote($wiki_id, "integer")
            . " AND page_id = " . $this->db->quote($page_id, "integer")
        );
        $this->fixImportantPagesNumbering($wiki_id);
    }

    public function saveOrderingAndIndentation(
        int $wiki_id,
        array $a_ord,
        array $a_indent
    ): bool {
        $ipages = $this->getListAsArray($wiki_id);

        foreach ($ipages as $k => $v) {
            if (isset($a_ord[$v["page_id"]])) {
                $ipages[$k]["ord"] = (int) $a_ord[$v["page_id"]];
            }
            if (isset($a_indent[$v["page_id"]])) {
                $ipages[$k]["indent"] = (int) $a_indent[$v["page_id"]];
            }
        }
        $ipages = \ilArrayUtil::sortArray($ipages, "ord", "asc", true);

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        $fixed = false;
        foreach ($ipages as $k => $v) {
            if ($v["indent"] == 2 && $c_indent == 0) {
                $ipages[$k]["indent"] = 1;
                $fixed = true;
            }
            $c_indent = $ipages[$k]["indent"];
        }

        $ord = 10;
        reset($ipages);
        foreach ($ipages as $k => $v) {
            $this->db->manipulate(
                $q = "UPDATE il_wiki_imp_pages SET " .
                    " ord = " . $this->db->quote($ord, "integer") . "," .
                    " indent = " . $this->db->quote($v["indent"], "integer") .
                    " WHERE wiki_id = " . $this->db->quote($wiki_id, "integer") .
                    " AND page_id = " . $this->db->quote($v["page_id"], "integer")
            );
            $ord += 10;
        }

        return $fixed;
    }

    protected function fixImportantPagesNumbering(
        int $wiki_id
    ): void {
        $ipages = $this->getListAsArray($wiki_id);

        // fix indentation: no 2 is allowed after a 0
        $c_indent = 0;
        foreach ($ipages as $k => $v) {
            if ($v["indent"] == 2 && $c_indent == 0) {
                $ipages[$k]["indent"] = 1;
            }
            $c_indent = $ipages[$k]["indent"];
        }

        $ord = 10;
        foreach ($ipages as $k => $v) {
            $this->db->manipulate(
                $q = "UPDATE il_wiki_imp_pages SET " .
                    " ord = " . $this->db->quote($ord, "integer") .
                    ", indent = " . $this->db->quote($v["indent"], "integer") .
                    " WHERE wiki_id = " . $this->db->quote($v["wiki_id"], "integer") .
                    " AND page_id = " . $this->db->quote($v["page_id"], "integer")
            );
            $ord += 10;
        }
    }

    public function getImportantPageIds(int $wiki_id): array
    {
        $set = $this->db->query(
            "SELECT DISTINCT page_id FROM il_wiki_imp_pages WHERE " .
            " wiki_id = " . $this->db->quote($wiki_id, "integer")
        );
        $ids = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $ids[] = (int) $rec["page_id"];
        }
        return $ids;
    }

}
