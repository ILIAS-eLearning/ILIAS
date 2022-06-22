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
        //Find the obj_id of the digibook type
        $sql =
            "SELECT obj_id FROM object_data " .
            "WHERE type = 'typ' " .
            "AND title = 'dbk'";

        $res = $this->db->query($sql);

        while ($row = $this->db->fetchAssoc($res)) {
            $obj_id = (int) $row['obj_id'];

            //Delete every row with that typ_id from the rbac tables
            $sql =
                "DELETE FROM rbac_ta " .
                "WHERE typ_id = " . $this->db->quote($obj_id, "integer");

            $this->db->manipulate($sql);

            //Delete the original row from object_data
            $sql =
                "DELETE FROM object_data " .
                "WHERE obj_id = " . $this->db->quote($obj_id, "integer");

            $this->db->manipulate($sql);
        }
    }

    public function step_2() : void
    {
        //Find the ops_id of digibook operations
        $sql =
            "SELECT ops_id FROM rbac_operations " .
            "WHERE operation = 'create_dbk' ";

        $res = $this->db->query($sql);

        while ($row = $this->db->fetchAssoc($res)) {
            $ops_id = (int) $row['ops_id'];

            //Delete every row with that ops_id from the rbac tables
            $sql =
                "DELETE FROM rbac_ta " .
                "WHERE ops_id = " . $this->db->quote($ops_id, "integer");

            $this->db->manipulate($sql);

            $sql =
                "DELETE FROM rbac_templates " .
                "WHERE ops_id = " . $this->db->quote($ops_id, "integer");

            $this->db->manipulate($sql);

            //Delete the original row from rbac_operations
            $sql =
                "DELETE FROM rbac_operations " .
                "WHERE ops_id = " . $this->db->quote($ops_id, "integer");

            $this->db->manipulate($sql);
        }
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
