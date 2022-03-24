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
 * Exporter class for exercise
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseExporter extends ilXmlExporter
{
    private ilExerciseDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilExerciseDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Get xml representation
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/4_1",
                "xsd_file" => "ilias_exc_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "4.3.99"),
            "4.4.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/4_4",
                "xsd_file" => "ilias_exc_4_4.xsd",
                "uses_dataset" => true,
                "min" => "4.4.0",
                "max" => "4.4.99"),
            "5.0.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/5_0",
                "xsd_file" => "ilias_exc_5_0.xsd",
                "uses_dataset" => true,
                "min" => "5.0.0",
                "max" => "5.0.99"),
            "5.1.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/5_1",
                "xsd_file" => "ilias_exc_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.1.99"),
            "5.2.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/5_2",
                "xsd_file" => "ilias_exc_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"),
            "5.3.0" => array(
                "namespace" => "https://www.ilias.de/Modules/Exercise/exc/5_3",
                "xsd_file" => "ilias_exc_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "")
        );
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $deps = [];

        if ($a_entity == "exc") {
            // service settings
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            );

            $advmd_ids = array();
            foreach ($a_ids as $id) {
                $rec_ids = $this->getActiveAdvMDRecords($id);
                foreach ($rec_ids as $rec_id) {
                    $advmd_ids[] = $id . ":" . $rec_id;
                }
            }

            if ($advmd_ids !== []) {
                $deps[] = array(
                    "component" => "Services/AdvancedMetaData",
                    "entity" => "advmd",
                    "ids" => $advmd_ids
                );
            }

            $md_ids = array();
            foreach ($a_ids as $exc_id) {
                $md_ids[] = $exc_id . ":0:exc";
            }
            if ($md_ids !== []) {
                $deps[] =
                    array(
                        "component" => "Services/MetaData",
                        "entity" => "md",
                        "ids" => $md_ids
                    );
            }

            // service settings
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "service_settings",
                "ids" => $a_ids
            );
        }
        
        return $deps;
    }

    /**
     * @return int[]
     */
    protected function getActiveAdvMDRecords($a_id) : array
    {
        $active = array();

        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType("exc") as $record_obj) {
            foreach ($record_obj->getAssignedObjectTypes() as $obj_info) {
                if ($obj_info['obj_type'] == 'exc' && $obj_info['optional'] == 0) {
                    $active[] = $record_obj->getRecordId();
                }
                // local activation
                if (
                    $obj_info['obj_type'] == 'exc' &&
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
