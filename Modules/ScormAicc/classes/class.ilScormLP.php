<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * SCORM to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesScormAicc
 */
class ilScormLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SCORM_PACKAGE
        );
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }
    
    public function getValidModes()
    {
        include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
        $subtype = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
        if ($subtype != "scorm2004") {
            if ($this->checkSCORMPreconditions()) {
                return array(ilLPObjSettings::LP_MODE_SCORM);
            }
            
            include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfSCOs.php";
            $collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);
            if (sizeof($collection->getPossibleItems())) {
                return array(ilLPObjSettings::LP_MODE_DEACTIVATED,
                    ilLPObjSettings::LP_MODE_SCORM);
            }
            return array(ilLPObjSettings::LP_MODE_DEACTIVATED);
        } else {
            if ($this->checkSCORMPreconditions()) {
                return array(ilLPObjSettings::LP_MODE_SCORM,
                    ilLPObjSettings::LP_MODE_SCORM_PACKAGE);
            }
            
            include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfSCOs.php";
            $collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);
            if (sizeof($collection->getPossibleItems())) {
                return array(ilLPObjSettings::LP_MODE_DEACTIVATED,
                    ilLPObjSettings::LP_MODE_SCORM_PACKAGE,
                    ilLPObjSettings::LP_MODE_SCORM);
            }
            
            return array(ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_SCORM_PACKAGE);
        }
    }

    /**
     * AK, 14Sep2018: This looks strange, the mode is auto-activated if this object is used
     * as a precondition trigger? This is not implemented for any other object type.
     *
     * @return int
     */
    public function getCurrentMode()
    {
        if ($this->checkSCORMPreconditions()) {
            return ilLPObjSettings::LP_MODE_SCORM;
        }
        return parent::getCurrentMode();
    }
    
    protected function checkSCORMPreconditions()
    {
        include_once('./Services/Conditions/classes/class.ilConditionHandler.php');
        if (count(ilConditionHandler::_getPersistedConditionsOfTrigger('sahs', $this->obj_id))) {
            return true;
        }
        return false;
    }
    
    protected static function isLPMember(array &$a_res, $a_usr_id, $a_obj_ids)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        // subtype
        $types = array();
        $set = $ilDB->query("SELECT id,c_type" .
            " FROM sahs_lm" .
            " WHERE " . $ilDB->in("id", $a_obj_ids, "", "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $types[$row["c_type"]][] = $row["id"];
        }
        
        // 2004
        if (isset($types["scorm2004"])) {
            $set = $ilDB->query("SELECT obj_id" .
                " FROM sahs_user" .
                " WHERE " . $ilDB->in("obj_id", $types["scorm2004"], "", "integer") .
                " AND user_id = " . $ilDB->quote($a_usr_id, "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $a_res[$row["obj_id"]] = true;
            }
        }
        
        // 1.2
        if (isset($types["scorm"])) {
            $set = $ilDB->query("SELECT obj_id" .
                " FROM scorm_tracking" .
                " WHERE " . $ilDB->in("obj_id", $types["scorm"], "", "integer") .
                " AND user_id = " . $ilDB->quote($a_usr_id, "integer") .
                " AND lvalue = " . $ilDB->quote("cmi.core.lesson_status", "text"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $a_res[$row["obj_id"]] = true;
            }
        }
    }
    
    public function getMailTemplateId()
    {
        include_once './Modules/ScormAicc/classes/class.ilScormMailTemplateLPContext.php';
        return ilScormMailTemplateLPContext::ID;
    }
}
