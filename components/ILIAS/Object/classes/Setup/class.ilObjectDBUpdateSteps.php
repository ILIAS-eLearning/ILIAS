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

namespace ILIAS\Object\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjectDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $field = [
            "type" => \ilDBConstants::T_TEXT,
            "length" => 255,
            "notnull" => false
        ];

        $this->db->modifyTableColumn("object_translation", "title", $field);
    }

    public function step_2(): void
    {
        if ($this->db->tableExists("il_object_def")) {
            $query = "UPDATE il_object_def SET " . PHP_EOL
                . " component = REPLACE(component, 'Modules', 'components/ILIAS'), " . PHP_EOL
                . " location = REPLACE(location, 'Modules', 'components/ILIAS')" . PHP_EOL
                . " WHERE component LIKE ('Modules/%')";

            $this->db->manipulate($query);
        }
    }
}
