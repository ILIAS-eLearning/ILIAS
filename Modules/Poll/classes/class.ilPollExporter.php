<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Poll export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollExporter extends ilXmlExporter
{
    protected $ds;
    
    public function init()
    {
        $this->ds = new ilPollDataSet();
        $this->ds->setDSPrefix("ds");
    }
    
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
    }
    
    public function getValidSchemaVersions($a_entity)
    {
        return array(
                "4.3.0" => array(
                        "namespace" => "http://www.ilias.de/Services/Modules/Poll/4_3",
                        "xsd_file" => "ilias_poll_4_3.xsd",
                        "uses_dataset" => true,
                        "min" => "4.3.0",
                        "max" => "4.4.99"),
                "5.0.0" => array(
                    "namespace" => "http://www.ilias.de/Services/Modules/Poll/5_0",
                    "xsd_file" => "ilias_poll_5_0.xsd",
                    "uses_dataset" => true,
                    "min" => "5.0.0",
                    "max" => "")
        );
    }
}
