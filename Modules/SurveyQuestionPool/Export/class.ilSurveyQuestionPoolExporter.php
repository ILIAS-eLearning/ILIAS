<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Used for container export with tests
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyQuestionPoolExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init() : void
    {
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
        $refs = ilObject::_getAllReferences($a_id);
        $sql_ref_id = current($refs);
        
        $spl = new ilObjSurveyQuestionPool($a_id, false);
        $spl->loadFromDb();
        
        $spl_exp = new ilSurveyQuestionpoolExport($spl, 'xml');
        $zip = $spl_exp->buildExportFile();
        $GLOBALS['ilLog']->write(__METHOD__ . ': Created zip file ' . $zip);
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

        // service settings
        $deps[] = [
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids
        ];

        return $deps;
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
                "namespace" => "http://www.ilias.de/Modules/SurveyQuestionPool/htlm/4_1",
                "xsd_file" => "ilias_spl_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
