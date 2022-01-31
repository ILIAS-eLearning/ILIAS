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
 ********************************************************************
 */

/**
 * Exporter class for sessions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesSession
 */
class ilSessionExporter extends ilXmlExporter
{
    private ilSessionDataSet $ds;

    public function init() : void
    {
        $this->ds = new ilSessionDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $deps = [];
        
        $advmd_ids = [];
        foreach ($a_ids as $id) {
            $rec_ids = $this->getActiveAdvMDRecords($id);
            if (sizeof($rec_ids)) {
                foreach ($rec_ids as $rec_id) {
                    $advmd_ids[] = $id . ":" . $rec_id;
                }
            }
        }
        if (sizeof($advmd_ids)) {
            $deps[] = array(
                "component" => "Services/AdvancedMetaData",
                "entity" => "advmd",
                "ids" => $advmd_ids
            );
        }
        
        $md_ids = [];
        foreach ($a_ids as $sess_id) {
            $md_ids[] = $sess_id . ":0:sess";
        }
        if ($md_ids) {
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
            "ids" => $a_ids);

        // tile image
        $deps[] = array(
            "component" => "Services/Object",
            "entity" => "tile",
            "ids" => $a_ids);
        
        return $deps;
    }

    protected function getActiveAdvMDRecords(int $a_id) : array
    {
        $active = [];
        
        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType('sess') as $record_obj) {
            foreach ($record_obj->getAssignedObjectTypes() as $obj_info) {
                if ($obj_info['obj_type'] == 'sess' && $obj_info['optional'] == 0) {
                    $active[] = $record_obj->getRecordId();
                }
                // local activation
                if (
                    $obj_info['obj_type'] == 'sess' &&
                    $obj_info['optional'] == 1 &&
                    $a_id == $record_obj->getParentObject()
                ) {
                    $active[] = $record_obj->getRecordId();
                }
            }
        }
        return $active;
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Session/sess/4_1",
                "xsd_file" => "ilias_sess_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "4.4.999"),
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Session/sess/5_0",
                "xsd_file" => "ilias_sess_5_0.xsd",
                "uses_dataset" => true,
                "min" => "5.0.0",
                "max" => "5.0.999"),
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Session/sess/5_1",
                "xsd_file" => "ilias_sess_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "5.3.999"),
            "5.4.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Session/sess/5_1",
                "xsd_file" => "ilias_sess_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.4.0",
                "max" => "6.999"),
            "7.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Session/sess/7",
                "xsd_file" => "ilias_sess_7.xsd",
                "uses_dataset" => true,
                "min" => "7.0",
                "max" => ""),
        );
    }
}
