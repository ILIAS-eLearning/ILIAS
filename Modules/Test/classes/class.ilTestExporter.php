<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Used for container export with tests
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestExporter extends ilXmlExporter
{
    private $ds;

    /**
     * Initialisation
     */
    public function init(): void
    {
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        include_once './Modules/Test/classes/class.ilObjTest.php';
        $tst = new ilObjTest($a_id, false);

        require_once 'Modules/Test/classes/class.ilTestExportFactory.php';
        $expFactory = new ilTestExportFactory($tst);
        $testExport = $expFactory->getExporter('xml');
        $zip = $testExport->buildExportFile();

        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $DIC['ilLog']->write(__METHOD__ . ': Created zip file ' . $zip);
        return ''; // Sagt mjansen
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
        if ($a_entity == 'tst') {
            $deps = array();

            $taxIds = $this->getDependingTaxonomyIds($a_ids);

            if (count($taxIds)) {
                $deps[] = array(
                    'component' => 'Services/Taxonomy',
                    'entity' => 'tax',
                    'ids' => $taxIds
                );
            }

            // service settings
            $deps[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            );

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }

    /**
     * @param array $testObjIds
     * @return array $taxIds
     */
    private function getDependingTaxonomyIds($testObjIds): array
    {
        include_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

        $taxIds = array();

        foreach ($testObjIds as $testObjId) {
            foreach (ilObjTaxonomy::getUsageOfObject($testObjId) as $taxId) {
                $taxIds[$taxId] = $taxId;
            }
        }

        return $taxIds;
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @param string $a_entity
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Test/htlm/4_1",
                "xsd_file" => "ilias_tst_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "")
        );
    }
}
