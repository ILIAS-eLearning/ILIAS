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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyQuestionPoolExporter extends ilXmlExporter
{
    public function init(): void
    {
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        $spl = new ilObjSurveyQuestionPool($a_id, false);
        $spl->loadFromDb();
        return $spl->toXmlForExport();
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $dependencies = [];

        // service settings
        $deps[] = [
            "component" => "components/ILIAS/ILIASObject",
            "entity" => "common",
            "ids" => $a_ids
        ];

        $advmd_ids = [];
        foreach ($a_ids as $id) {
            $rec_ids = $this->getActiveAdvMDRecords($id);
            foreach ($rec_ids as $rec_id) {
                $advmd_ids[] = $id . ":" . $rec_id;
            }
        }

        if ($advmd_ids !== []) {
            $dependencies[] = [
                "component" => "components/ILIAS/AdvancedMetaData",
                "entity" => "advmd",
                "ids" => $advmd_ids
            ];
        }

        $md_ids = [];
        foreach ($a_ids as $spl_id) {
            $md_ids[] = $spl_id . ":0:spl";
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

    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/SurveyQuestionPool/htlm/4_1",
                "xsd_file" => "ilias_spl_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }

    protected function getActiveAdvMDRecords(int $a_id): array
    {
        $active = [];
        $component = 'spl';
        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType($component) as $record_obj) {
            foreach ($record_obj->getAssignedObjectTypes() as $obj_info) {
                if ($obj_info['obj_type'] == $component && $obj_info['optional'] == 0) {
                    $active[] = $record_obj->getRecordId();
                }
                // local activation
                if (
                    $obj_info['obj_type'] == $component &&
                    $obj_info['optional'] == 1 &&
                    $a_id == $record_obj->getParentObject()
                ) {
                    $active[] = $record_obj->getRecordId();
                }
            }
        }
        return $active;
    }
}
