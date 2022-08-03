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
 * SCORM to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ModulesScormAicc
 */
class ilScormLP extends ilObjectLP
{
    protected ?bool $precondition_cache = null;

    /**
     * @return int[]
     */
    public static function getDefaultModes(bool $a_lp_active) : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SCORM_PACKAGE
        );
    }

    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes() : array
    {
        $subtype = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
        if ($subtype !== 'scorm2004') {
            if ($this->checkSCORMPreconditions()) {
                return [ilLPObjSettings::LP_MODE_SCORM];
            }

            $collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);
            if (count($collection->getPossibleItems()) > 0) {
                return [
                    ilLPObjSettings::LP_MODE_DEACTIVATED,
                    ilLPObjSettings::LP_MODE_SCORM
                ];
            }

            return [ilLPObjSettings::LP_MODE_DEACTIVATED];
        }

        if ($this->checkSCORMPreconditions()) {
            return [
                ilLPObjSettings::LP_MODE_SCORM,
                ilLPObjSettings::LP_MODE_SCORM_PACKAGE
            ];
        }

        $collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);
        if (count($collection->getPossibleItems()) > 0) {
            return [
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_SCORM_PACKAGE,
                ilLPObjSettings::LP_MODE_SCORM
            ];
        }

        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SCORM_PACKAGE
        ];
    }

    /**
     * AK, 14Sep2018: This looks strange, the mode is auto-activated if this object is used
     * as a precondition trigger? This is not implemented for any other object type.
     */
    public function getCurrentMode() : int
    {
//        if ($this->checkSCORMPreconditions()) {
//            return ilLPObjSettings::LP_MODE_SCORM;
//        }
        return parent::getCurrentMode();
    }

    protected function checkSCORMPreconditions() : bool
    {
        if (is_bool($this->precondition_cache)) {
            return $this->precondition_cache;
        }

        $this->precondition_cache = ilConditionHandler::getNumberOfConditionsOfTrigger(
            'sahs',
            $this->obj_id
        ) > 0;

        return $this->precondition_cache;
    }

    protected static function isLPMember(array &$a_res, int $a_usr_id, array $a_obj_ids) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        // subtype
        $types = array();
        $set = $ilDB->query("SELECT id,c_type" .
            " FROM sahs_lm" .
            " WHERE " . $ilDB->in("id", $a_obj_ids, false, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $types[$row["c_type"]][] = $row["id"];
        }

        // 2004
        if (isset($types["scorm2004"])) {
            $set = $ilDB->query("SELECT obj_id" .
                " FROM sahs_user" .
                " WHERE " . $ilDB->in("obj_id", $types["scorm2004"], false, "integer") .
                " AND user_id = " . $ilDB->quote($a_usr_id, "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                return true;
            }
        }

        // 1.2
        if (isset($types["scorm"])) {
            $set = $ilDB->query("SELECT obj_id" .
                " FROM scorm_tracking" .
                " WHERE " . $ilDB->in("obj_id", $types["scorm"], false, "integer") .
                " AND user_id = " . $ilDB->quote($a_usr_id, "integer") .
                " AND lvalue = " . $ilDB->quote("cmi.core.lesson_status", "text"));
            while ($row = $ilDB->fetchAssoc($set)) {
                return true;
            }
        }
        return false;
    }

    public function getMailTemplateId() : string
    {
        return ilScormMailTemplateLPContext::ID;
    }
}
