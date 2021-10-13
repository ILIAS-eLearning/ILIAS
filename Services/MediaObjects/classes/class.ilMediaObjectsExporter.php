<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Export2 class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaObjectsExporter extends ilXmlExporter
{
    private ilMediaObjectDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilMediaObjectDataSet();
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
        $md_ids = array();
        foreach ($a_ids as $mob_id) {
            $md_ids[] = "0:" . $mob_id . ":mob";
        }

        return array(
            array(
                "component" => "Services/MetaData",
                "entity" => "md",
                "ids" => $md_ids)
            );
    }

    /**
     * Get xml representation
     * @param	string		entity
     * @param	string		schema version
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
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
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/MediaObjects/mob/5_1",
                "xsd_file" => "ilias_mob_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => ""),
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/MediaObjects/mob/4_3",
                "xsd_file" => "ilias_mob_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Services/MediaObjects/mob/4_1",
                "xsd_file" => "ilias_mob_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
