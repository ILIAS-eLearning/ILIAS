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
class ilCalendarDBUpdateSteps9 implements ilDatabaseUpdateSteps
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
        // Removes is_milestones column from cal_entries
        if($this->db->tableExists('cal_entries')
            && $this->db->tableColumnExists('cal_entries', 'is_milestone')) {
            $this->db->dropTableColumn('cal_entries', 'is_milestone');
        }
        // Removes completion column from cal_entries
        if($this->db->tableExists('cal_entries')
            && $this->db->tableColumnExists('cal_entries', 'completion')) {
            $this->db->dropTableColumn('cal_entries', 'completion');
        }
        // Deletes table cal_entry_responsible
        if($this->db->tableExists('cal_entry_responsible')) {
            $this->db->dropTable('cal_entry_responsible');
        }

        $query = 'delete from settings where ' .
            'keyword = ' . $this->db->quote('enable_grp_milestones', ilDBConstants::T_TEXT) . ' ' .
            'and module = ' . $this->db->quote('calendar', ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }
}
