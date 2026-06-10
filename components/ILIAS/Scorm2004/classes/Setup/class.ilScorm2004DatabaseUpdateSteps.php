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

class ilScorm2004DatabaseUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->modifyTableColumn("cmi_interaction", "c_timestamp", array("type" => "text", "length" => 40, "notnull" => false, 'default' => null));
    }

    public function step_2(): void
    {
        $this->db->modifyTableColumn("cp_dependency", "resourceid", array("type" => "text", "length" => 200, "notnull" => false, 'default' => null));
    }

    public function step_3(): void
    {
        if (!$this->db->indexExistsByFields('sahs_user', ['user_id'])) {
            $this->db->addIndex('sahs_user', ['user_id'], 'i1');
        }
    }

    public function step_4(): void
    {
        $this->db->modifyTableColumn("cmi_correct_response", "pattern", array("type" => "text", "length" => 4000, "notnull" => false, 'default' => null));
    }

}
