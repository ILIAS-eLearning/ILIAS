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

class ilMDCopyrightUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a column to il_md_cpr_selections for the full name of the licence.
     */
    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'full_name')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'full_name',
                ['type' => ilDBConstants::T_CLOB]
            );
        }
    }

    /**
     * Add a column to il_md_cpr_selections for the link to the licence.
     */
    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'link')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'link',
                ['type' => ilDBConstants::T_CLOB]
            );
        }
    }

    /**
     * Add a column to il_md_cpr_selections for the link to the licence's image.
     */
    public function step_3(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'image_link')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'image_link',
                ['type' => ilDBConstants::T_CLOB]
            );
        }
    }

    /**
     * Add a column to il_md_cpr_selections for the alt text of the licence's image.
     */
    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'alt_text')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'alt_text',
                ['type' => ilDBConstants::T_CLOB]
            );
        }
    }

    /**
     * Add a column to il_md_cpr_selections to track which licences have been migrated
     */
    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'migrated')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'migrated',
                [
                    'type' => ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'default' => 0
                ]
            );
        }
    }

    /**
     * Add a column to il_md_cpr_selections for the string identifier for the licence's image,
     * if it is saved as a file.
     */
    public function step_6(): void
    {
        if (!$this->db->tableColumnExists('il_md_cpr_selections', 'image_file')) {
            $this->db->addTableColumn(
                'il_md_cpr_selections',
                'image_file',
                ['type' => ilDBConstants::T_CLOB]
            );
        }
    }

    /**
     * Add CC0 to the available copyrights
     */
    public function step_7(): void
    {
        $title = "Public Domain";
        $full_name = "This work is free of known copyright restrictions.";
        $link = "http://creativecommons.org/publicdomain/zero/1.0/";
        $image_link = "https://licensebuttons.net/p/zero/1.0/88x31.png";
        $alt_text = "CC0";

        $next_id = $this->db->nextId('il_md_cpr_selections');

        $res = $this->db->query(
            'SELECT MAX(position) AS max FROM il_md_cpr_selections WHERE is_default = 0'
        );
        $row = $this->db->fetchAssoc($res);
        $position = isset($row['max']) ? $row['max'] + 1 : 0;

        $this->db->insert(
            'il_md_cpr_selections',
            [
                'entry_id' => [\ilDBConstants::T_INTEGER, $next_id],
                'title' => [\ilDBConstants::T_TEXT, $title],
                'description' => [\ilDBConstants::T_TEXT, ''],
                'is_default' => [\ilDBConstants::T_INTEGER, 0],
                'outdated' => [\ilDBConstants::T_INTEGER, 0],
                'position' => [\ilDBConstants::T_INTEGER, $position],
                'full_name' => [\ilDBConstants::T_TEXT, $full_name],
                'link' => [\ilDBConstants::T_TEXT, $link],
                'image_link' => [\ilDBConstants::T_TEXT, $image_link],
                'image_file' => [\ilDBConstants::T_TEXT, ''],
                'alt_text' => [\ilDBConstants::T_TEXT, $alt_text],
                'migrated' => [\ilDBConstants::T_INTEGER, 1]
            ]
        );
    }

    /**
     * Replace CC0 image link by svg
     */
    public function step_8(): void
    {
        $title = "Public Domain";
        $full_name = "This work is free of known copyright restrictions.";
        $old_image_link = "https://licensebuttons.net/p/zero/1.0/88x31.png";
        $new_image_link = "https://mirrors.creativecommons.org/presskit/buttons/88x31/svg/cc-zero.svg";

        $res = $this->db->query(
            'SELECT entry_id FROM il_md_cpr_selections WHERE title = ' .
            $this->db->quote($title, ilDBConstants::T_TEXT) . ' AND full_name = ' .
            $this->db->quote($full_name, ilDBConstants::T_TEXT) . ' AND image_link = ' .
            $this->db->quote($old_image_link, ilDBConstants::T_TEXT)
        );
        if (($row = $this->db->fetchAssoc($res)) && isset($row['entry_id'])) {
            $this->db->update(
                'il_md_cpr_selections',
                ['image_link' => [\ilDBConstants::T_TEXT, $new_image_link]],
                ['entry_id' => [\ilDBConstants::T_INTEGER, $row['entry_id']]]
            );
        }
    }

    /**
     * Change title, description and full name of 'All rights reserved',
     * see https://mantis.ilias.de/view.php?id=41301
     */
    public function step_9(): void
    {
        $title = "All rights reserved";
        $old_full_name = "This work has all rights reserved by the owner.";
        $new_full_name = "All rights reserved";
        $new_description = "The copyright holder reserves, or holds for their own use, all the rights provided by copyright law.";

        $res = $this->db->query(
            'SELECT entry_id FROM il_md_cpr_selections WHERE title = ' .
            $this->db->quote($title, ilDBConstants::T_TEXT) . ' AND full_name = ' .
            $this->db->quote($old_full_name, ilDBConstants::T_TEXT) .
            " AND COALESCE(link, '') = '' AND COALESCE(description, '') = ''"
        );
        if (($row = $this->db->fetchAssoc($res)) && isset($row['entry_id'])) {
            $this->db->update(
                'il_md_cpr_selections',
                [
                    'full_name' => [\ilDBConstants::T_TEXT, $new_full_name],
                    'description' => [\ilDBConstants::T_TEXT, $new_description]
                ],
                ['entry_id' => [\ilDBConstants::T_INTEGER, $row['entry_id']]]
            );
        }
    }

    /**
     * Change title, description and full name of 'Public Domain',
     * see https://mantis.ilias.de/view.php?id=41301
     */
    public function step_10(): void
    {
        $title = "Public Domain";
        $link = "http://creativecommons.org/publicdomain/zero/1.0/";
        $old_full_name = "This work is free of known copyright restrictions.";
        $new_full_name = "Public Domain";
        $new_description = "Creative work to which no exclusive intellectual property rights apply.";

        $res = $this->db->query(
            'SELECT entry_id FROM il_md_cpr_selections WHERE title = ' .
            $this->db->quote($title, ilDBConstants::T_TEXT) . ' AND full_name = ' .
            $this->db->quote($old_full_name, ilDBConstants::T_TEXT) . ' AND link = ' .
            $this->db->quote($link, ilDBConstants::T_TEXT) .
            " AND COALESCE(description, '') = ''"
        );
        if (($row = $this->db->fetchAssoc($res)) && isset($row['entry_id'])) {
            $this->db->update(
                'il_md_cpr_selections',
                [
                    'full_name' => [\ilDBConstants::T_TEXT, $new_full_name],
                    'description' => [\ilDBConstants::T_TEXT, $new_description]
                ],
                ['entry_id' => [\ilDBConstants::T_INTEGER, $row['entry_id']]]
            );
        }
    }

    /**
     * Change title of CC licences, see https://mantis.ilias.de/view.php?id=41898
     */
    public function step_11(): void
    {
        $old_titles_by_link = [
            'http://creativecommons.org/licenses/by-nc-nd/4.0/' => 'Attribution Non-commercial No Derivatives (by-nc-nd)',
            'http://creativecommons.org/licenses/by-nc-sa/4.0/' => 'Attribution Non-commercial Share Alike (by-nc-sa)',
            'http://creativecommons.org/licenses/by-nc/4.0/' => 'Attribution Non-commercial (by-nc)',
            'http://creativecommons.org/licenses/by-nd/4.0/' => 'Attribution No Derivatives (by-nd)',
            'http://creativecommons.org/licenses/by-sa/4.0/' => 'Attribution Share Alike (by-sa)',
            'http://creativecommons.org/licenses/by/4.0/' => 'Attribution (by)'
        ];
        $new_titles_by_link = [
            'http://creativecommons.org/licenses/by-nc-nd/4.0/' => 'Attribution Non-commercial No Derivatives (BY-NC-ND) 4.0',
            'http://creativecommons.org/licenses/by-nc-sa/4.0/' => 'Attribution Non-commercial Share Alike (BY-NC-SA) 4.0',
            'http://creativecommons.org/licenses/by-nc/4.0/' => 'Attribution Non-commercial (BY-NC) 4.0',
            'http://creativecommons.org/licenses/by-nd/4.0/' => 'Attribution No Derivatives (BY-ND) 4.0',
            'http://creativecommons.org/licenses/by-sa/4.0/' => 'Attribution Share Alike (BY-SA) 4.0',
            'http://creativecommons.org/licenses/by/4.0/' => 'Attribution (BY) 4.0'
        ];

        foreach ($old_titles_by_link as $link => $old_title) {
            $res = $this->db->query(
                'SELECT entry_id FROM il_md_cpr_selections WHERE title = ' .
                $this->db->quote($old_title, ilDBConstants::T_TEXT) . ' AND link = ' .
                $this->db->quote($link, ilDBConstants::T_TEXT)
            );
            if (
                ($row = $this->db->fetchAssoc($res)) &&
                isset($row['entry_id']) &&
                isset($new_titles_by_link[$link])
            ) {
                $this->db->update(
                    'il_md_cpr_selections',
                    ['title' => [\ilDBConstants::T_TEXT, $new_titles_by_link[$link]]],
                    ['entry_id' => [\ilDBConstants::T_INTEGER, $row['entry_id']]]
                );
            }
        }
    }
}
