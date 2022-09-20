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

/**
 * These update steps drop the columns 'disable_check', 'last_check'
 * and 'valid' from the table 'webr_items', since the validation of
 * weblinks is abandoned.
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilWebResourceDropValidSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        //Drops the column 'disable_check' from webr_items
        if ($this->db->tableColumnExists('webr_items', 'disable_check')) {
            $this->db->dropTableColumn('webr_items', 'disable_check');
        }
    }

    public function step_2(): void
    {
        //Drops the column 'last_check' from webr_items
        if ($this->db->tableColumnExists('webr_items', 'last_check')) {
            $this->db->dropTableColumn('webr_items', 'last_check');
        }
    }

    public function step_3(): void
    {
        //Drops the column 'valid' from webr_items
        if ($this->db->tableColumnExists('webr_items', 'valid')) {
            $this->db->dropTableColumn('webr_items', 'valid');
        }
    }
}
