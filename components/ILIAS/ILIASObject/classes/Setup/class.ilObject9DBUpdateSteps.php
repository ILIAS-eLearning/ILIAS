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

namespace ILIAS\Object\Setup;

class ilObject9DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('object_data', 'tile_image_rid')) {
            $this->db->addTableColumn(
                'object_data',
                'tile_image_rid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]
            );
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableColumnExists('object_data', 'type')) {
            $this->db->manipulate(
                'UPDATE object_data SET type = "" WHERE type IS NULL'
            );
            $this->db->modifyTableColumn(
                'object_data',
                'type',
                [
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 4,
                    'default' => 'none'
                ]
            );
        }

        if ($this->db->tableColumnExists('object_data', 'title')) {
            $this->db->manipulate(
                'UPDATE object_data SET title = "" WHERE title IS NULL'
            );
            $this->db->modifyTableColumn(
                'object_data',
                'title',
                [
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 255,
                    'default' => ''
                ]
            );
        }

        if ($this->db->tableColumnExists('object_data', 'description')) {
            $this->db->manipulate(
                'UPDATE object_data SET description = "" WHERE description IS NULL'
            );
            $this->db->modifyTableColumn(
                'object_data',
                'description',
                [
                    'type' => 'text',
                    'notnull' => true,
                    'length' => 128,
                    'default' => ''
                ]
            );
        }
    }
}
