<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseLP extends ilObjectLP
{
    public static function getDefaultModes(bool $a_lp_active) : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_EXERCISE_RETURNED
        );
    }
    
    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_EXERCISE_RETURNED;
    }
    
    public function getValidModes() : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_EXERCISE_RETURNED
        );
    }
    
    protected static function isLPMember(array &$a_res, int $a_usr_id, array $a_obj_ids) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT obj_id" .
            " FROM exc_members" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer") .
            " AND usr_id = " . $ilDB->quote($a_usr_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $a_res[$row["obj_id"]] = true;
        }
        
        return true;
    }
}
