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

namespace ILIAS\Wiki\Page;

use ILIAS\Wiki\InternalDataService;

/**
 * Wiki page repo
 */
class PageDBRepository
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

    public function delete($id, $lang = "-"): void
    {
        $and = in_array($lang, ["", "-"])
            ? ""
            : " AND lang = " . $this->db->quote($lang, "text");
        $query = "DELETE FROM il_wiki_page" .
            " WHERE id = " . $this->db->quote($id, "integer") .
            $and;
        $this->db->manipulate($query);
    }

    protected function getPageFromRecord(array $rec): Page
    {
        return $this->data->page(
            (int) $rec["id"],
            (int) $rec["wiki_id"],
            $rec["title"],
            $rec["lang"],
            (bool) $rec["blocked"],
            (bool) $rec["rating"],
            (bool) $rec["hide_adv_md"]
        );
    }

    protected function getPageInfoFromRecord(array $rec): PageInfo
    {
        return $this->data->pageInfo(
            (int) $rec["id"],
            $rec["lang"] ?? "",
            $rec["title"],
            (int) ($rec["last_change_user"] ?? 0),
            $rec["last_change"] ?? "",
            (int) ($rec["create_user"] ?? 0),
            $rec["created"] ?? "",
            (int) ($rec["cnt"] ?? 0),
            (int) ($rec["nr"] ?? 0)
        );
    }

    /**
     * @return iterable<Page>
     */
    public function getWikiPages(int $wiki_id, string $lang = "-"): \Iterator
    {
        $set = $this->db->queryF(
            "SELECT * FROM il_wiki_page  " .
            " WHERE lang = %s AND wiki_id = %s ORDER BY title",
            ["string", "integer"],
            [$lang, $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageFromRecord($rec);
        }
    }

    /**
     * @return iterable<Page>
     */
    public function getMasterPagesWithoutTranslation(int $wiki_id, string $trans): \Iterator
    {
        $set = $this->db->queryF(
            "SELECT w1.* FROM il_wiki_page w1 LEFT JOIN il_wiki_page w2 " .
            " ON w1.id = w2.id AND w2.lang = %s " .
            " WHERE w1.lang = %s AND w1.wiki_id = %s AND w2.id IS NULL ORDER BY w1.title",
            ["string", "string", "integer"],
            [$trans, "-", $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageFromRecord($rec);
        }
    }

    /**
     * Queries last change and user per page regardless of language
     * @return iterable<PageInfo>
     */
    public function getAllPagesInfo(int $wiki_id): \Iterator
    {
        $set = $this->db->queryF(
            "SELECT w.id, p.last_change_user, p.last_change, w.title " . "FROM page_object p JOIN il_wiki_page w " .
            " ON (w.wiki_id = %s AND p.parent_type = %s AND p.page_id = w.id AND w.lang = %s) " .
            " JOIN ( select page_id, max(last_change) mlc FROM page_object " .
            " WHERE parent_type='wpg' group by page_id) mp " .
            " ON (mp.page_id = p.page_id AND mp.mlc = p.last_change)",
            ["integer", "string", "string"],
            [$wiki_id, "wpg", "-"]
        );
        $ids = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            // note: the query may get multiple entries for a page id, if multiple
            // languages share the same max(last_change). We only return one.
            if (isset($ids[(int) $rec["id"]])) {
                continue;
            }
            $ids[(int) $rec["id"]] = (int) $rec["id"];
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    public function getInfoOfSelected($wiki_id, array $ids, $lang = "-"): \Iterator
    {
        $query = "SELECT wp.id, p.last_change_user, p.last_change, wp.title, wp.lang " .
            " FROM il_wiki_page wp JOIN page_object p " .
            " ON (wp.id = p.page_id AND wp.lang = p.lang) " .
            " WHERE " . $this->db->in("wp.id", $ids, false, "integer") .
            " AND p.parent_type = " . $this->db->quote("wpg", "text") .
            " AND wp.wiki_id = " . $this->db->quote($wiki_id, "integer") .
            " AND wp.lang = " . $this->db->quote($lang, "text") .
            " ORDER BY title";
        $set = $this->db->query($query);

        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    /**
     * Queries last change and user per page regardless of language
     * @return iterable<PageInfo>
     */
    public function getRecentChanges(
        int $wiki_id,
        int $period = 30
    ): \Iterator {
        $limit_ts = date('Y-m-d H:i:s', time() - ($period * 24 * 60 * 60));
        $q1 = "SELECT w.id, p.last_change_user, p.last_change, w.title, w.lang, 0 nr FROM page_object p " .
            " JOIN il_wiki_page w ON (w.id = p.page_id AND p.parent_type = %s AND w.lang = p.lang AND w.wiki_id = %s) " .
            " WHERE p.last_change >= " . $this->db->quote($limit_ts, "timestamp");
        $q2 = "SELECT w.id, p.user_id last_change_user, p.hdate last_change, w.title, w.lang, p.nr FROM page_history p " .
            " JOIN il_wiki_page w ON (w.id = p.page_id AND p.parent_type = %s AND w.lang = p.lang AND w.wiki_id = %s) " .
            " WHERE p.hdate >= " . $this->db->quote($limit_ts, "timestamp");
        $q = $q1 . " UNION " . $q2 . " ORDER BY last_change DESC ";
        $set = $this->db->queryF(
            $q,
            ["string", "integer", "string", "integer"],
            ["wpg", $wiki_id,"wpg", $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getNewPages(int $wiki_id): \Iterator
    {
        $set = $this->db->queryF(
            "SELECT w.id, p.created, p.create_user, w.title, w.lang FROM page_object p " .
            " JOIN il_wiki_page w " .
            " ON w.id = p.page_id AND p.parent_type = %s AND w.lang = p.lang AND w.wiki_id = %s " .
            " ORDER BY created DESC",
            ["string", "integer"],
            ["wpg", $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getPopularPages(
        int $a_wiki_id
    ): \Iterator {

        $query = "SELECT wp.id, wp.title, wp.lang, po.view_cnt as cnt FROM il_wiki_page wp JOIN page_object po" .
            " ON (wp.id = po.page_id AND wp.lang = po.lang) " .
            " WHERE wp.wiki_id = " . $this->db->quote($a_wiki_id, "integer") .
            " AND po.parent_type = " . $this->db->quote("wpg", "text") . " " .
            " ORDER BY po.view_cnt";
        $set = $this->db->query($query);

        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageInfoFromRecord($rec);
        }
    }

    /**
     * @return string[]
     */
    public function getLanguages(int $wpg_id): array
    {
        $set = $this->db->queryF(
            "SELECT DISTINCT lang FROM  " .
            " il_wiki_page WHERE id = %s AND lang <> '-' ",
            ["integer"],
            [$wpg_id]
        );
        $langs = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $langs[] = $rec["lang"];
        }
        return $langs;
    }

    public function doesAtLeastOnePageExist(int $wiki_id, array $ids): bool
    {
        // cross check existence of sources in il_wiki_page
        $query = "SELECT count(*) cnt FROM il_wiki_page" .
            " WHERE " . $this->db->in("id", $ids, false, "integer") .
            " AND wiki_id = " . $this->db->quote($wiki_id, "integer") .
            " GROUP BY wiki_id";
        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);
        return ((int) $rec["cnt"]) > 0;
    }

    public function getPageIdForTitle(
        int $wiki_id,
        string $title,
        string $lang = "-"
    ): ?int {
        if ($lang === "") {
            $lang = "-";
        }
        $title = \ilWikiUtil::makeDbTitle($title);

        $query = "SELECT w.id  FROM il_wiki_page w " .
            " JOIN page_object p ON (w.id = p.page_id AND w.lang = p.lang) " .
            " WHERE w.wiki_id = " . $this->db->quote($wiki_id, "integer") .
            " AND w.title = " . $this->db->quote($title, "text") .
            " AND w.lang = " . $this->db->quote($lang, "text");
        $set = $this->db->query($query);
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["id"];
        }

        return null;
    }

    public function existsByTitle(
        int $wiki_id,
        string $title,
        string $lang = "-"
    ): bool {
        $id = $this->getPageIdForTitle($wiki_id, $title, $lang);
        if (is_null($id)) {
            return false;
        }
        return $this->exists($id, $lang);
    }

    public function exists(
        int $id,
        string $lang = "-"
    ): bool {
        if ($lang === "") {
            $lang = "-";
        }
        $query = "SELECT w.id FROM il_wiki_page w " .
            " JOIN page_object p ON (w.id = p.page_id AND w.lang = p.lang) " .
            " WHERE w.id = " . $this->db->quote($id, "integer") .
            " AND w.lang = " . $this->db->quote($lang, "text");
        $set = $this->db->query($query);
        if ($rec = $this->db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function getTitle(
        int $id,
        string $lang = "-"
    ): string {
        if ($lang === "") {
            $lang = "-";
        }
        $query = "SELECT title FROM il_wiki_page " .
            " WHERE id = " . $this->db->quote($id, "integer") .
            " AND lang = " . $this->db->quote($lang, "text");
        $set = $this->db->query($query);
        if ($rec = $this->db->fetchAssoc($set)) {
            return $rec["title"];
        }
        return "";
    }

    public function getWikiIdByPageId(
        int $id
    ): ?int {
        $query = "SELECT wiki_id FROM il_wiki_page" .
            " WHERE id = " . $this->db->quote($id, "integer") .
            " AND lang = " . $this->db->quote('-', "text");
        $set = $this->db->query($query);
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["wiki_id"];
        }
        return null;
    }

}
