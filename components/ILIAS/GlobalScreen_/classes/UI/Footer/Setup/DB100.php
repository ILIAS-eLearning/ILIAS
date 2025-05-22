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

namespace ILIAS\GlobalScreen_\UI\Footer\Setup;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DB100 implements \ilDatabaseUpdateSteps
{
    private ?\ilDBInterface $db = null;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableExists('gs_footer_items')) {
            $this->db->dropTable('gs_footer_items'); //
        }
    }

    public function step_2(): void
    {
        if ($this->db->tableExists('gs_footer_items')) {
            return;
        }
        $this->db->createTable(
            'gs_footer_items',
            [
                'id' => ['type' => 'text', 'length' => 255, 'notnull' => true],
                'type' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
                'title' => ['type' => 'text', 'length' => 4000, 'notnull' => true],
                'position' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
                'is_active' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
                'parent' => ['type' => 'text', 'length' => 255, 'notnull' => false],
                'action' => ['type' => 'text', 'length' => 4000, 'notnull' => false],
                'external' => ['type' => 'integer', 'length' => 1, 'notnull' => false],
                'core' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
            ]
        );
    }

    public function step_3(): void
    {
        if ($this->db->tableExists('gs_item_translation')) {
            return;
        }
        $this->db->createTable(
            'gs_item_translation',
            [
                'id' => ['type' => 'text', 'length' => 255, 'notnull' => true],
                'language_code' => ['type' => 'text', 'length' => 4, 'notnull' => true],
                'translation' => ['type' => 'text', 'length' => 4000, 'notnull' => true],
                'status' => ['type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0],
            ]
        );
        $this->db->addPrimaryKey('gs_item_translation', ['id', 'language_code']);
    }

}
