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

namespace ILIAS\LearningModule\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class LearningModuleDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('content_object', 'act_est_reading_time')) {
            $this->db->addTableColumn('content_object', 'act_est_reading_time', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 1,
                'default' => 0
            ));
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('content_object', 'est_reading_time')) {
            $this->db->addTableColumn('content_object', 'est_reading_time', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_3(): void
    {
        $this->db->update(
            "ut_lp_settings",
            [
            "u_mode" => ["integer", 0]
        ],
            [    // where
                "obj_type" => ["text", "lm"],
                "u_mode" => ["integer", 3],
            ]
        );
        $this->db->update(
            "ut_lp_settings",
            [
            "u_mode" => ["integer", 0]
        ],
            [    // where
                "obj_type" => ["text", "lm"],
                "u_mode" => ["integer", 16],
            ]
        );
    }

}
