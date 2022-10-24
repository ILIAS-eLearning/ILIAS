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

class ilRadiusDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate("UPDATE usr_data " .
            "SET active=" . $this->db->quote(0, "integer") .
            ",auth_mode=" . $this->db->quote("local", "text") .
            ",passwd_enc_type=" . $this->db->quote("bcryptphp", "text") .
            ",passwd=" . $this->db->quote("dummy", "text") .

            "WHERE auth_mode=" . $this->db->quote("radius", "text"));
    }
}
