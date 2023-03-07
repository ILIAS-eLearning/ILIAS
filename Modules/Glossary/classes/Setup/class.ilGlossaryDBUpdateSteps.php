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
 ********************************************************************
 */

class ilGlossaryDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists("glossary_term", "short_text")) {
            $this->db->addTableColumn("glossary_term", "short_text", [
                "type" => "text",
                "length" => 4000,
                "notnull" => false
            ]);
        }

        if (!$this->db->tableColumnExists("glossary_term", "short_text_dirty")) {
            $this->db->addTableColumn("glossary_term", "short_text_dirty", [
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ]);
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists("glossary_definition", "migration")) {
            $this->db->addTableColumn("glossary_definition", "migration", [
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ]);
        }
    }

    public function step_3(): void
    {
        if (!$this->db->tableExists('glo_flashcard_term')) {
            $fields = [
                'term_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'user_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'glo_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'last_access' => [
                    'type' => 'timestamp',
                    'notnull' => false
                ],
                'box_nr' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ]
            ];
            $this->db->createTable("glo_flashcard_term", $fields);
            $this->db->addPrimaryKey("glo_flashcard_term", ["term_id", "user_id", "glo_id"]);
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableExists('glo_flashcard_box')) {
            $fields = [
                'box_nr' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'user_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'glo_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                ],
                'last_access' => [
                    'type' => 'timestamp',
                    'notnull' => false
                ]
            ];
            $this->db->createTable("glo_flashcard_box", $fields);
            $this->db->addPrimaryKey("glo_flashcard_box", ["box_nr", "user_id", "glo_id"]);
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists("glossary", "flash_active")) {
            $this->db->addTableColumn("glossary", "flash_active", [
                "type" => "text",
                "notnull" => true,
                "length" => 1,
                "default" => "n"
            ]);
        }

        if (!$this->db->tableColumnExists("glossary", "flash_mode")) {
            $this->db->addTableColumn("glossary", "flash_mode", [
                "type" => "text",
                "notnull" => true,
                "length" => 10,
                "default" => "term"
            ]);
        }
    }
}
