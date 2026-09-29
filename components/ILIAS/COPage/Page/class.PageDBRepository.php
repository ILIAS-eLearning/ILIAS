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

namespace ILIAS\COPage\Page;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function lookupParentId(int $page_id, string $parent_type): int
    {
        $set = $this->db->queryF(
            "SELECT parent_id FROM page_object WHERE page_id = %s AND parent_type = %s",
            ["integer", "text"],
            [$page_id, $parent_type]
        );
        $record = $this->db->fetchAssoc($set);
        return (int) ($record["parent_id"] ?? 0);
    }

    public function writeParentId(string $parent_type, int $page_id, int $parent_id): void
    {
        $this->db->manipulateF(
            "UPDATE page_object SET parent_id = %s WHERE page_id = %s AND parent_type = %s",
            ["integer", "integer", "text"],
            [$parent_id, $page_id, $parent_type]
        );
    }

    public function writeActive(int $page_id, string $parent_type, bool $active): void
    {
        $this->db->manipulateF(
            "UPDATE page_object SET active = %s, activation_start = %s, " .
            " activation_end = %s WHERE page_id = %s" .
            " AND parent_type = %s AND lang = %s",
            ["int", "timestamp", "timestamp", "integer", "text", "text"],
            [(int) $active, null, null, $page_id, $parent_type, "-"]
        );
    }

    /**
     * @return array{pages: list<array<string, mixed>>, history: list<array<string, mixed>>}
     */
    public function getParentObjectContributorData(
        string $parent_type,
        int $parent_id,
        string $lang
    ): array {
        $and_lang = $lang !== "" ? " AND lang = %s" : "";
        $types = ["integer", "text", "integer"];
        $values = [$parent_id, $parent_type, 0];
        if ($lang !== "") {
            $types[] = "text";
            $values[] = $lang;
        }

        $pages = [];
        $set = $this->db->queryF(
            "SELECT last_change_user, lang, page_id FROM page_object " .
            " WHERE parent_id = %s AND parent_type = %s " .
            " AND last_change_user != %s" . $and_lang,
            $types,
            $values
        );
        while ($page = $this->db->fetchAssoc($set)) {
            $pages[] = $page;
        }

        $history = [];
        $set = $this->db->queryF(
            "SELECT count(*) as cnt, lang, page_id, user_id FROM page_history " .
            " WHERE parent_id = %s AND parent_type = %s AND user_id != %s " . $and_lang .
            " GROUP BY page_id, user_id, lang ",
            $types,
            $values
        );
        while ($page = $this->db->fetchAssoc($set)) {
            $history[] = $page;
        }

        return ["pages" => $pages, "history" => $history];
    }

    /**
     * @return array{pages: list<array<string, mixed>>, history: list<array<string, mixed>>}
     */
    public function getPageContributorData(
        string $parent_type,
        int $page_id,
        string $lang
    ): array {
        $and_lang = $lang !== "" ? " AND lang = %s" : "";
        $types = ["integer", "text", "integer"];
        $values = [$page_id, $parent_type, 0];
        if ($lang !== "") {
            $types[] = "text";
            $values[] = $lang;
        }

        $pages = [];
        $set = $this->db->queryF(
            "SELECT last_change_user, lang FROM page_object " .
            " WHERE page_id = %s AND parent_type = %s " .
            " AND last_change_user != %s" . $and_lang,
            $types,
            $values
        );
        while ($page = $this->db->fetchAssoc($set)) {
            $pages[] = $page;
        }

        $history = [];
        $set = $this->db->queryF(
            "SELECT count(*) as cnt, lang, page_id, user_id FROM page_history " .
            " WHERE page_id = %s AND parent_type = %s AND user_id != %s " . $and_lang .
            " GROUP BY user_id, page_id, lang ",
            $types,
            $values
        );
        while ($page = $this->db->fetchAssoc($set)) {
            $history[] = $page;
        }

        return ["pages" => $pages, "history" => $history];
    }
}
