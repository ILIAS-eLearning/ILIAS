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

use ILIAS\Survey\InternalDomainService;

/**
 * Used for container export with tests
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilSurveyExporter extends ilXmlExporter
{
    private ilSurveyDataSet $ds;
    protected InternalDomainService $domain;

    public function init(): void
    {
        global $DIC;

        $this->domain = $DIC->survey()->internal()->domain();
        $this->ds = new ilSurveyDataSet();
        $this->ds->initByExporter($this);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        if ($a_entity === "svy") {
            $svy = new ilObjSurvey($a_id, false);
            $svy->loadFromDb();

            $svy_exp = new ilSurveyExport($svy, 'xml');
            $zip = $svy_exp->buildExportFile();

            // Unzip, since survey deletes this dir
            $this->domain->resources()->zip()->unzipFile($zip);

            // unzip does not extract the included directory
            // Modules/Survey/set_1 anymore (since 7/2023)
            $missing = $svy_exp->export_dir . "/" . $svy_exp->subdir .
                "/components/ILIAS/Survey/set_1";
            ilFileUtils::makeDirParents($missing);

            // here: svy_data/svy_301/export/1698817474__0__svy_301
            //       svy_301/export/1698817474__0__svy_301/Modules/Survey/set_1
            //       svy_data/svy_301/export/1698817474__0__svy_301.zip
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
    ): array {
        if ($a_entity === "svy") {
            $dependencies = [
                [
                    "component" => "components/ILIAS/Survey",
                    "entity" => "svy_quest_skill",
                    "ids" => $a_ids
                ],
                [
                    "component" => "components/ILIAS/Survey",
                    "entity" => "svy_skill_threshold",
                    "ids" => $a_ids
                ],
                [
                    "component" => "components/ILIAS/Object",
                    "entity" => "common",
                    "ids" => $a_ids
                ]
            ];

            $md_ids = [];
            foreach ($a_ids as $id) {
                $md_ids[] = $id . ":0:svy";
            }
            if ($md_ids !== []) {
                $dependencies[] = [
                    "component" => "components/ILIAS/MetaData",
                    "entity" => "md",
                    "ids" => $md_ids
                ];
            }
            return $dependencies;
        }
        return array();
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
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
