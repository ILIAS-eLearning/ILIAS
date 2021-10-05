<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exporter class for rating (categories)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingExporter extends ilXmlExporter
{
    private ilRatingDataSet $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilRatingDataSet();
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        $this->ds->setDSPrefix("ds");
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/Rating/rating_category/4_3",
                "xsd_file" => "ilias_rating_4_3.xsd",
                "uses_dataset" => true,
                "min" => "4.3.0",
                "max" => "")
        );
    }
}
