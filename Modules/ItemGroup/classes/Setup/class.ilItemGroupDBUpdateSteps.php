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

namespace ILIAS\ItemGroup\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilItemGroupDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if (!$this->db->tableColumnExists('itgr_data', 'list_presentation')) {
            $this->db->addTableColumn('itgr_data', 'list_presentation', array(
                "type" => "text",
                "length" => 10
            ));
        }
    }

    public function step_2()
    {
        if (!$this->db->tableColumnExists('itgr_data', 'tile_size')) {
            $this->db->addTableColumn('itgr_data', 'tile_size', array(
                "type" => "integer",
                "notnull" => true,
                "default" => 0,
                "length" => 1
            ));
        }
    }
}
