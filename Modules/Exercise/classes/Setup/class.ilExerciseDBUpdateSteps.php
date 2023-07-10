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
 *********************************************************************/

namespace ILIAS\Exercise\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->indexExistsByFields('exc_assignment', ['exc_id'])) {
            $this->db->addIndex('exc_assignment', ['exc_id'], 'i1');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->indexExistsByFields('exc_members', ['usr_id'])) {
            $this->db->addIndex('exc_members', ['usr_id'], 'i1');
        }
    }

    public function step_3(): void
    {
        if (!$this->db->indexExistsByFields('exc_assignment', ['deadline_mode', 'exc_id'])) {
            $this->db->addIndex('exc_assignment', ['deadline_mode', 'exc_id'], 'i2');
        }
    }

    public function step_4(): void
    {
        if (!$this->db->indexExistsByFields('exc_ass_file_order', ['assignment_id'])) {
            $this->db->addIndex('exc_ass_file_order', ['assignment_id'], 'i1');
        }
    }

    public function step_5(): void
    {
        if (!$this->db->indexExistsByFields('il_exc_team', ['id'])) {
            $this->db->addIndex('il_exc_team', ['id'], 'i1');
        }
    }
}
