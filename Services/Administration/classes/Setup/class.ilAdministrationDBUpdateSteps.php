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

namespace ILIAS\Administration\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

/**
 * Class ilAdministrationDBUpdateSteps
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAdministrationDBUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        // note: release_8 has step_3 as step_1
    }

    public function step_2(): void
    {
        if ($this->db->sequenceExists("adm_settings_template")) {
            $this->db->dropSequence("adm_settings_template");
        }

        if ($this->db->tableExists("adm_settings_template")) {
            $this->db->dropTable("adm_settings_template");
        }
    }

    public function step_3(): void
    {
        $this->db->addTableColumn(
            'settings',
            'value2',
            array(	"type" => "text",
                      "length" => 4000,
                      "notnull" => false,
                      "default" => null)
        );

        $this->db->query("UPDATE settings SET value2 = SUBSTRING(value, 1, 4000)");
        $this->db->dropTableColumn('settings', 'value');
        $this->db->renameTableColumn('settings', 'value2', 'value');
    }
}
