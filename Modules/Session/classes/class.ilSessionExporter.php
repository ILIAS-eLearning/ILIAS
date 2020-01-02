<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Exporter class for sessions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesSession
 */
class ilSessionExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/Session/classes/class.ilSessionDataSet.php");
        $this->ds = new ilSessionDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }
    
    /**
     * Get tail dependencies
     * @param type $a_entity
     * @param type $a_target_release
     * @param type $a_ids
     * @return string
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $deps = [];
        
        $advmd_ids = array();
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
        
        $md_ids = array();
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
        
        return $deps;
    }

    /**
     * get activated adv md records
     * @param type $a_id
     * @return type
     */
    protected function getActiveAdvMDRecords($a_id)
    {
        $active = array();
        
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
    

    /**
     * Get xml representation
     *
     * @param	string		entity
     * @param	string		schema version
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
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
                "max" => ""),
        );
    }
}
