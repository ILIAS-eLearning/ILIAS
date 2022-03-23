<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\COPage\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) // @TODO: PHP8 Review: Missing return type.
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        $field = array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }

    public function step_2() // @TODO: PHP8 Review: Missing return type.
    {
        $field = array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        );

        $this->db->modifyTableColumn("copg_pc_def", "order_nr", $field);
    }
}
