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
 * Used for container export with tests
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilSurveyExporter extends ilXmlExporter
{
    private ilSurveyDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilSurveyDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string {
        if ($a_entity === "svy") {
            $svy = new ilObjSurvey($a_id, false);
            $svy->loadFromDb();

            $svy_exp = new ilSurveyExport($svy, 'xml');
            $zip = $svy_exp->buildExportFile();

            // Unzip, since survey deletes this dir
            ilFileUtils::unzip($zip);

            $GLOBALS['ilLog']->write(__METHOD__ . ': Created zip file ' . $zip);
            return "";
        } else {
            return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
        }
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        if ($a_entity === "svy") {
            return array(
                    array(
                            "component" => "Modules/Survey",
                            "entity" => "svy_quest_skill",
                            "ids" => $a_ids),
                    array(
                            "component" => "Modules/Survey",
                            "entity" => "svy_skill_threshold",
                            "ids" => $a_ids),
                    array(
                            "component" => "Services/Object",
                            "entity" => "common",
                            "ids" => $a_ids)
            );
        }
        return array();
    }

    public function getValidSchemaVersions(
        string $a_entity
    ) : array {
        if ($a_entity === "svy") {
            return array(
                    "4.1.0" => array(
                            "namespace" => "https://www.ilias.de/Modules/Survey/htlm/4_1",
                            "xsd_file" => "ilias_svy_4_1.xsd",
                            "uses_dataset" => false,
                            "min" => "4.1.0",
                            "max" => "")
            );
        } else {
            return array(
                    "5.1.0" => array(
                            "namespace" => "https://www.ilias.de/Modules/Survey/svy/5_1",
                            "xsd_file" => "ilias_svy_5_1.xsd",
                            "uses_dataset" => true,
                            "min" => "5.1.0",
                            "max" => "")
            );
        }
    }
}
