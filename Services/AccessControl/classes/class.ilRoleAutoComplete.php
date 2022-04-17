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
 * Auto completion class for user lists
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilRoleAutoComplete
{
    /**
     * Get completion list
     */
    public static function getList(string $a_str) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->setLimit(20, 0);
        $query = "SELECT o1.title role,o2.title container FROM object_data o1 " .
            "JOIN rbac_fa fa ON o1.obj_id = rol_id " .
//            "JOIN tree t1 ON fa.parent =  t1.child " .
//            "JOIN object_reference obr ON ref_id = t1.parent " .
            "JOIN object_reference obr ON ref_id = fa.parent " .
            "JOIN object_data o2 ON obr.obj_id = o2.obj_id " .
            "WHERE o1.type = 'role' " .
            "AND assign = 'y' " .
            "AND (" . $ilDB->like('o1.title', 'text', '%' . $a_str . '%') . "OR " .
                $ilDB->like('o2.title', 'text', '%' . $a_str . '%') . " )" .
            "AND fa.parent != 8 " .
            "ORDER BY role,container";

        $res = $ilDB->query($query);
        $counter = 0;
        $result = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $result[$counter] = new stdClass();
            $result[$counter]->value = $row->role;
            $result[$counter]->label = $row->role . " (" . $row->container . ")";
            ++$counter;
        }

        if ($counter == 0) {
            return self::getListByObject($a_str);
        }

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    /**
     * Get list of roles assigned to an object
     */
    public static function getListByObject(string $a_str) : string
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $ilDB = $DIC->database();

        $result = array();

        if (strpos($a_str, '@') !== 0) {
            return json_encode($result, JSON_THROW_ON_ERROR);
        }

        $a_str = substr($a_str, 1);

        $ilDB->setLimit(100, 0);
        $query = "SELECT ref_id, title FROM object_data ode " .
            "JOIN object_reference ore ON ode.obj_id = ore.obj_id " .
            "WHERE " . $ilDB->like('title', 'text', $a_str . '%') . ' ' .
            'ORDER BY title';
        $res = $ilDB->query($query);
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            foreach ($rbacreview->getRolesOfRoleFolder($row->ref_id, false) as $rol_id) {
                $role = ilObject::_lookupTitle($rol_id);

                $result[$counter] = new stdClass();
                $result[$counter]->value = $role;
                $result[$counter]->label = $role . " (" . $row->title . ")";
                ++$counter;
            }
        }
        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
