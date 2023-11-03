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

namespace ILIAS\Wiki\Links;

use ILIAS\Wiki\InternalDataService;

/**
 * Wiki page repo
 */
class MissingPageDBRepository
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

    public function save(int $wiki_id, int $source_id, string $title, string $lang): void
    {
        $this->db->replace(
            "il_wiki_missing_page",
            [
            "wiki_id" => ["integer", $wiki_id],
            "source_id" => ["integer", $source_id],
            "target_name" => ["text", \ilWikiUtil::makeDbTitle($title)],
            "lang" => ["text", $lang]
        ],
            []
        );
    }

    public function deleteForTarget(int $wiki_id, string $target_title, string $lang = "-"): void
    {
        $this->db->manipulateF(
            "DELETE FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND target_name = %s AND lang = %s",
            array("integer", "text", "text"),
            array($wiki_id, \ilWikiUtil::makeDbTitle($target_title), $lang)
        );
    }

    public function deleteForSourceId(int $wiki_id, int $source_id, string $lang = "-"): void
    {
        $this->db->manipulateF(
            "DELETE FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND source_id = %s AND lang = %s",
            array("integer", "integer", "text"),
            array($wiki_id, $source_id, $lang)
        );
    }

    /**
     * @return int[]
     */
    public function getSourcesOfMissingTarget(int $wiki_id, string $target_title, string $lang = "-"): \Iterator
    {
        $set = $this->db->queryF(
            "SELECT source_id FROM il_wiki_missing_page WHERE " .
            " wiki_id = %s AND target_name = %s AND lang = %s",
            array("integer", "text", "text"),
            array($wiki_id, \ilWikiUtil::makeDbTitle($target_title), $lang)
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield (int) $rec["source_id"];
        }
    }

}
