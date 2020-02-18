<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesExercise
 */
class ilExerciseLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_EXERCISE_RETURNED
        );
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_EXERCISE_RETURNED;
    }
    
    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_EXERCISE_RETURNED
        );
    }
    
    protected static function isLPMember(array &$a_res, $a_usr_id, $a_obj_ids)
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
