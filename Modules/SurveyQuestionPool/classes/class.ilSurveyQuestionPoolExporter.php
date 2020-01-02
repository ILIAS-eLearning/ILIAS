<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Used for container export with tests
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesSurvey
 */
class ilSurveyQuestionPoolExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init()
    {
    }


    /**
     * Get xml representation
     *
     * @param	string		entity
     * @param	string		target release
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $refs = ilObject::_getAllReferences($a_id);
        $sql_ref_id = current($refs);
        
        include_once './Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php';
        $spl = new ilObjSurveyQuestionPool($a_id, false);
        $spl->loadFromDb();
        
        include_once("./Modules/SurveyQuestionPool/classes/class.ilSurveyQuestionpoolExport.php");
        $spl_exp = new ilSurveyQuestionpoolExport($spl, 'xml');
        $zip = $spl_exp->buildExportFile();
        $GLOBALS['ilLog']->write(__METHOD__ . ': Created zip file ' . $zip);
    }

    /**
     * Get tail dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
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
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
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
