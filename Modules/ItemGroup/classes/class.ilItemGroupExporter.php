<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exporter class for item groups
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilItemGroupExporter extends ilXmlExporter
{
    private ilItemGroupDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilItemGroupDataSet();
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
            "5.3.0" => array(
                "namespace" => "http://www.ilias.de/Modules/ItemGroup/itgr/5_3",
                "xsd_file" => "ilias_itgr_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => ""),
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Modules/ItemGroup/itgr/4_3",
                "xsd_file" => "ilias_itgr_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
