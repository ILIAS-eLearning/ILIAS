<?php

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
 * Survey to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyLP extends ilObjectLP
{
    public static function getDefaultModes(bool $lp_active): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SURVEY_FINISHED
        );
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED; // :TODO:
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_SURVEY_FINISHED
        );
    }

    public function isAnonymized(): bool
    {
        return ilObjSurveyAccess::_lookupAnonymize($this->obj_id);
    }

    protected static function isLPMember(array &$res, int $usr_id, array $obj_ids): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        // if active id
        $set = $ilDB->query("SELECT ss.obj_fi" .
            " FROM svy_finished sf" .
            " JOIN svy_svy ss ON (ss.survey_id = sf.survey_fi)" .
            " WHERE " . $ilDB->in("ss.obj_fi", $obj_ids, "", "integer") .
            " AND sf.user_fi = " . $ilDB->quote($usr_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[(int) $row["obj_fi"]] = true;
        }

        return true;
    }
}
