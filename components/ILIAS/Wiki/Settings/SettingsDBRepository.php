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

namespace ILIAS\Wiki\Settings;

use ilDBInterface;

class SettingsDBRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getById(int $id): ?Settings
    {
        $query = "SELECT * FROM il_wiki_data WHERE id = %s";
        $set = $this->db->queryF($query, ["integer"], [$id]);
        $record = $this->db->fetchAssoc($set);

        if ($record) {
            return $this->getSettingsFromRecord($record);
        }

        return null;
    }

    public function update(Settings $settings): void
    {
        $this->db->update("il_wiki_data", [
            "startpage" => ["text", $settings->getStartPage()],
            "short" => ["text", $settings->getShortTitle()],
            "rating_overall" => ["integer", $settings->getRatingOverall()],
            "rating" => ["integer", $settings->getRating()],
            "rating_side" => ["integer", $settings->getRatingAsBlock()],
            "rating_new" => ["integer", $settings->getRatingForNewPages()],
            "rating_ext" => ["integer", $settings->getRatingCategories()],
            "public_notes" => ["integer", (int) $settings->getPublicNotes()],
            "introduction" => ["clob", $settings->getIntroduction()],
            "page_toc" => ["integer", (int) $settings->getPageToc()],
            "link_md_values" => ["integer", (int) $settings->getLinkMetadataValues()],
            "empty_page_templ" => ["integer", (int) $settings->getEmptyPageTemplate()],
        ], [
            "id" => ["integer", $settings->getId()]
        ]);
    }

    public function create(Settings $settings): void
    {
        $this->db->insert("il_wiki_data", [
            "id" => ["integer", $settings->getId()],
            "startpage" => ["text", $settings->getStartPage()],
            "short" => ["text", $settings->getShortTitle()],
            "rating_overall" => ["integer", $settings->getRatingOverall()],
            "rating" => ["integer", $settings->getRating()],
            "rating_side" => ["integer", $settings->getRatingAsBlock()],
            "rating_new" => ["integer", $settings->getRatingForNewPages()],
            "rating_ext" => ["integer", $settings->getRatingCategories()],
            "public_notes" => ["integer", (int) $settings->getPublicNotes()],
            "introduction" => ["clob", $settings->getIntroduction()],
            "page_toc" => ["integer", (int) $settings->getPageToc()],
            "link_md_values" => ["integer", (int) $settings->getLinkMetadataValues()],
            "empty_page_templ" => ["integer", (int) $settings->getEmptyPageTemplate()],
        ]);
    }

    protected function getSettingsFromRecord(array $rec): Settings
    {
        return new Settings(
            (int) $rec['id'],
            (string) $rec['startpage'],
            (string) $rec['short'],
            (bool) $rec['rating_overall'],
            (bool) $rec['rating'],
            (bool) $rec['rating_side'],
            (bool) $rec['rating_new'],
            (bool) $rec['rating_ext'],
            (bool) $rec['public_notes'],
            (string) $rec['introduction'],
            (bool) $rec['page_toc'],
            (bool) $rec['link_md_values'],
            (bool) $rec['empty_page_templ']
        );
    }
}
