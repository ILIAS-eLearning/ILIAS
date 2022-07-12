<?php declare(strict_types=1);

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

namespace ILIAS\Link\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LinkDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        $field = array(
            'type' => 'text',
            'length' => 10,
            'notnull' => true
        );

        $this->db->modifyTableColumn("int_link", "target_type", $field);
    }

    public function step_2() : void
    {
        $this->db->update("int_link", [
            "target_type" => ["text", "wpage"]
        ], [    // where
                "target_type" => ["text", "wpag"]
            ]
        );
    }
}
