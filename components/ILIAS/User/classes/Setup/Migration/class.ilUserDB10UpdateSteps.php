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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\UI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilUserDB10UpdateSteps implements ilDatabaseUpdateSteps
{
    private const USER_DATA_TABLE_NAME = 'usr_data';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }



    public function step_1(): void
    {
        $query = 'DELETE FROM settings WHERE module="common" AND keyword="session_reminder";';
        $this->db->manipulate($query);
    }
}
