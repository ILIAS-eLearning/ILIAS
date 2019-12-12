<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Survey to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesSurvey
 */
class ilSurveyLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SURVEY_FINISHED
        );
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED; // :TODO:
    }
    
    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SURVEY_FINISHED
        );
    }
    
    public function isAnonymized()
    {
        include_once './Modules/Survey/classes/class.ilObjSurveyAccess.php';
        return (bool) ilObjSurveyAccess::_lookupAnonymize($this->obj_id);
    }

    protected static function isLPMember(array &$a_res, $a_usr_id, $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // if active id
        $set = $ilDB->query("SELECT ss.obj_fi" .
            " FROM svy_finished sf" .
            " JOIN svy_svy ss ON (ss.survey_id = sf.survey_fi)" .
            " WHERE " . $ilDB->in("ss.obj_fi", $a_obj_ids, "", "integer") .
            " AND sf.user_fi = " . $ilDB->quote($a_usr_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $a_res[$row["obj_fi"]] = true;
        }
        
        return true;
    }
}
