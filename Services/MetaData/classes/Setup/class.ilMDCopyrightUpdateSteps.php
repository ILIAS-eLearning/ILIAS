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
}
