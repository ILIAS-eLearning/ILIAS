<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exporter class for media casts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaCastExporter extends ilXmlExporter
{
    private ilMediaCastDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilMediaCastDataSet();
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
        $mc_items_ids = array();

        foreach ($a_ids as $id) {
            $mcst = new ilObjMediaCast($id, false);
            $items = $mcst->readItems(true);
            foreach ($items as $i) {
                $news_ids[] = $i["id"];
            }
        }

        $deps = [];

        $deps[] = [
            "component" => "Services/News",
            "entity" => "news",
            "ids" => $news_ids
        ];

        // common object properties
        $deps[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);

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
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Modules/MediaCast/mcst/5_0",
                "xsd_file" => "ilias_mcst_5_0.xsd",
                "uses_dataset" => true,
                "min" => "5.0.0",
                "max" => ""),
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/MediaCast/mcst/4_1",
                "xsd_file" => "ilias_mcst_4_1.xsd",
                "uses_dataset" => true,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
