<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesGroup
 */
class ilGroupLP extends ilObjectLP
{
    public static function getDefaultModes(bool $a_lp_active): array
    {
        if (!$a_lp_active) {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED
            );
        } else {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR
            );
        }
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }

    public function getMembers(bool $a_search = true): array
    {
        $member_obj = ilGroupParticipants::_getInstanceByObjId($this->obj_id);
        return $member_obj->getMembers();
    }

    protected static function isLPMember(array &$a_res, int $a_usr_id, array $a_obj_ids): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        // will only find objects with roles for user!
        // see ilParticipants::_getMembershipByType()
        $query = " SELECT DISTINCT obd.obj_id, obd.type, obd2.title" .
            " FROM rbac_ua ua" .
            " JOIN rbac_fa fa ON (ua.rol_id = fa.rol_id)" .
            " JOIN object_reference obr ON (fa.parent = obr.ref_id)" .
            " JOIN object_data obd ON (obr.obj_id = obd.obj_id)" .
            " JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id)" .
            " WHERE obd.type = " . $ilDB->quote("grp", "text") .
            " AND fa.assign = " . $ilDB->quote("y", "text") .
            " AND ua.usr_id = " . $ilDB->quote($a_usr_id, "integer") .
            " AND " . $ilDB->in("obd.obj_id", $a_obj_ids, false, "integer");
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $role = $row["title"];
            if (!stristr($role, "il_" . $row["type"] . "_admin_") &&
                !stristr($role, "il_" . $row["type"] . "_tutor_")) {
                $a_res[$row["obj_id"]] = true;
            }
        }
        return true;
    }
}
