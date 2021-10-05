<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exporter class for html learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilHTMLLearningModuleExporter extends ilXmlExporter
{
    private ilHTMLLearningModuleDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilHTMLLearningModuleDataSet();
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
        $deps = [];
        $md_ids = [];
        foreach ($a_ids as $id) {
            $md_ids[] = $id . ":0:htlm";
        }

        $deps[] = [
            "component" => "Services/MetaData",
            "entity" => "md",
            "ids" => $md_ids
        ];

        // service settings
        $deps[] = [
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids
        ];

        return $deps;
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
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/HTMLLearningModule/htlm/4_1",
                "xsd_file" => "ilias_htlm_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
