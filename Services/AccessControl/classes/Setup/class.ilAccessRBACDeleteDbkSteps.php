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

/**
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilAccessRBACDeleteDbkSteps implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        //Delete all entries with type digibook from rbac_ta
        $sql =
            "DELETE FROM rbac_ta " .
            "WHERE typ_id IN (" .
                "SELECT obj_id FROM object_data " .
                "WHERE type = 'typ' " .
                "AND title = 'dbk'" .
            ")";

        $this->db->manipulate($sql);

        //Delete the entry of the digibook type from object_data
        $sql =
            "DELETE FROM object_data " .
            "WHERE type = 'typ' " .
            "AND title = 'dbk'";

        $this->db->manipulate($sql);
    }

    public function step_2() : void
    {
        //Delete every row with the ops_id of the digibook create operation from the rbac tables
        $sql =
            "DELETE FROM rbac_ta " .
            "WHERE ops_id IN (" .
                "SELECT ops_id FROM rbac_operations " .
                "WHERE operation = 'create_dbk'" .
            ")";

        $this->db->manipulate($sql);

        $sql =
            "DELETE FROM rbac_templates " .
            "WHERE ops_id IN (" .
                "SELECT ops_id FROM rbac_operations " .
                "WHERE operation = 'create_dbk'" .
            ")";

        $this->db->manipulate($sql);

        //Delete the operation from rbac_operations
        $sql =
            "DELETE FROM rbac_operations " .
            "WHERE operation = 'create_dbk'";

        $this->db->manipulate($sql);
    }

    public function step_3() : void
    {
        //Delete all other rows in the rbac tables which refer to the type dbk
        $sql =
            "DELETE FROM rbac_templates " .
            "WHERE type = 'dbk' "
        ;

        $this->db->manipulate($sql);
    }
}
