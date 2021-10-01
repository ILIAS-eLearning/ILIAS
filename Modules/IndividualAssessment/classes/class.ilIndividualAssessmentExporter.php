<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Export/classes/class.ilXmlExporter.php");
require_once("Modules/IndividualAssessment/classes/class.ilIndividualAssessmentDataSet.php");

/**
 * Manual Assessment exporter class
 *
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilindividualAssessmentExporter extends ilXmlExporter
{
    protected ilIndividualAssessmentDataSet $ds;

    /**
     * initialize the exporter
     */
    public function init() : void
    {
        $this->ds = new ilIndividualAssessmentDataSet();
    }

    /**
     * @inheritdoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);

        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], '', true, true);
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $res = [];

        if ($a_entity == "iass") {
            // service settings
            $res[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            );
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "5.2.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/iass/5_2",
                "xsd_file" => "ilias_iass_5_2.xsd",
                "uses_dataset" => true,
                "min" => "5.2.0",
                "max" => "5.2.99"),
            "5.3.0" => array(
                "namespace" => "http://www.ilias.de/Services/User/iass/5_3",
                "xsd_file" => "ilias_iass_5_3.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "")
        );
    }
}
