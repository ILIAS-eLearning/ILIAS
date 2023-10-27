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
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilCalendarDBUpdateSteps8 implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // Add indices
        if (!$this->db->indexExistsByFields('cal_entries', ['starta'])) {
            $this->db->addIndex('cal_entries', ['starta'], 'i3');
        }
        if (!$this->db->indexExistsByFields('cal_entries', ['enda'])) {
            $this->db->addIndex('cal_entries', ['enda'], 'i4');
        }
    }

    public function step_2(): void
    {
        $this->db->modifyTableColumn(
            'cal_entries',
            'title',
            [
                'length' => 255
            ]
        );
    }

    public function step_3(): void
    {
        $this->db->modifyTableColumn(
            'cal_categories',
            'title',
            [
                'length' => 255
            ]
        );
    }
}
