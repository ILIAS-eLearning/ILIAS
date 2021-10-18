<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exporter class for help system information
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilHelpExporter extends ilXmlExporter
{
    private ilHelpDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilHelpDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Get tail dependencies
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return        array        array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        if ($a_entity == "help") {
            $lm_node_ids = array();
            foreach ($a_ids as $lm_id) {
                $chaps = ilLMObject::getObjectList($lm_id, "st");
                foreach ($chaps as $chap) {
                    $lm_node_ids[] = $chap["obj_id"];
                }
            }

            return array(
                array(
                    "component" => "Services/Help",
                    "entity" => "help_map",
                    "ids" => $lm_node_ids),
                array(
                    "component" => "Services/Help",
                    "entity" => "help_tooltip",
                    "ids" => $a_ids)
                );
        }

        return array();
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
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/Help/help/4_3",
                "xsd_file" => "ilias_help_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
