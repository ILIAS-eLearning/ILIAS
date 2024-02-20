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

/**
 * General purpose update steps for weblinks
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilWebResourceDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // Add index
        if (!$this->db->indexExistsByFields('webr_items', ['webr_id'])) {
            $this->db->addIndex('webr_items', ['webr_id'], 'i3');
        }
    }

    public function step_2(): void
    {
        // Add combined index
        // 32201
        if (
            $this->db->tableExists('webr_items') &&
            !$this->db->indexExistsByFields('webr_items', ['webr_id', 'active'])
        ) {
            $this->db->addIndex('webr_items', ['webr_id', 'active'], 'i4');
        }
    }
}
