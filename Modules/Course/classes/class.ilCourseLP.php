<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Course to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesCourse
 */
class ilCourseLP extends ilObjectLP
{
    public static function getDefaultModes(bool $a_lp_active) : array
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
    
    public function getDefaultMode() : int
    {
        if ($this->checkObjectives()) {
            return ilLPObjSettings::LP_MODE_OBJECTIVES;
        }
        return ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
    }
    
    public function getValidModes() : array
    {
        if ($this->checkObjectives()) {
            return array(ilLPObjSettings::LP_MODE_OBJECTIVES);
        }
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }
    
    public function getCurrentMode() : int
    {
        if ($this->checkObjectives()) {
            return ilLPObjSettings::LP_MODE_OBJECTIVES;
        }
        return parent::getCurrentMode();
    }
    
    protected function checkObjectives()
    {
        if (ilObjCourse::_lookupViewMode($this->obj_id) == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE) {
            return true;
        }
        return false;
    }
    
    public function getSettingsInfo() : string
    {
        global $DIC;

        $lng = $DIC['lng'];
    
        // #9004
        $crs = new ilObjCourse($this->obj_id, false);
        if ($crs->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
            return $lng->txt("crs_status_determination_lp_info");
        }
        return '';
    }
    
    public function getMembers(bool $a_search = true) : array
    {
        $member_obj = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
        return $member_obj->getMembers();
    }
    
    protected static function isLPMember(array &$a_res, int $a_usr_id, array $a_obj_ids) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
            
        // will only find objects with roles for user!
        // see ilParticipants::_getMembershipByType()
        $query = " SELECT DISTINCT obd.obj_id, obd.type, obd2.title" .
            " FROM rbac_ua ua" .
            " JOIN rbac_fa fa ON (ua.rol_id = fa.rol_id)" .
            " JOIN object_reference obr ON (fa.parent = obr.ref_id)" .
            " JOIN object_data obd ON (obr.obj_id = obd.obj_id)" .
            " JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id)" .
            " WHERE obd.type = " . $ilDB->quote("crs", "text") .
            " AND fa.assign = " . $ilDB->quote("y", "text") .
            " AND ua.usr_id = " . $ilDB->quote($a_usr_id, "integer") .
            " AND " . $ilDB->in("obd.obj_id", $a_obj_ids, "", "integer");
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
    
    public function getMailTemplateId() : string
    {
        return ilCourseMailTemplateTutorContext::ID;
    }
}
