<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Used for container export with tests
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestQuestionPoolExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init(): void
    {
    }

    /**
     * Overwritten for qpl
     * @param string $a_obj_type
     * @param int    $a_obj_id
     * @param string $a_export_type
     */
    public static function lookupExportDirectory(string $a_obj_type, int $a_obj_id, string $a_export_type = 'xml', string $a_entity = ""): string
    {
        if ($a_export_type == 'xml') {
            return ilFileUtils::getDataDir() . "/qpl_data" . "/qpl_" . $a_obj_id . "/export_zip";
        }
        return ilFileUtils::getDataDir() . "/qpl_data" . "/qpl_" . $a_obj_id . "/export_" . $a_export_type;
    }


    /**
     * Get xml representation
     * @param	string		entity
     * @param	string		schema version
     * @param	string		id
     * @return	string		xml string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        include_once './Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
        $qpl = new ilObjQuestionPool($a_id, false);
        $qpl->loadFromDb();

        include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
        $qpl_exp = new ilQuestionpoolExport($qpl, 'xml');
        $qpl_exp->buildExportFile();

        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['ilLog']->write(__METHOD__ . ': Created zip file');
        return ''; // sagt mjansen
    }

    /**
     * Get tail dependencies
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return        array        array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        if ($a_entity == 'qpl') {
            $deps = array();

            $taxIds = $this->getDependingTaxonomyIds($a_ids);

            if (count($taxIds)) {
                $deps[] = array(
                    'component' => 'Services/Taxonomy',
                    'entity' => 'tax',
                    'ids' => $taxIds
                );
            }

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }

    /**
     * @param array $testObjIds
     * @return array $taxIds
     */
    private function getDependingTaxonomyIds($poolObjIds): array
    {
        include_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

        $taxIds = array();

        foreach ($poolObjIds as $poolObjId) {
            foreach (ilObjTaxonomy::getUsageOfObject($poolObjId) as $taxId) {
                $taxIds[$taxId] = $taxId;
            }
        }

        return $taxIds;
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/TestQuestionPool/htlm/4_1",
                "xsd_file" => "ilias_qpl_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
