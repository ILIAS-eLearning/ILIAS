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

declare(strict_types=1);

use ILIAS\TestQuestionPool\QuestionInfoService;

/**
 * Used for container export with tests
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup components\ILIASTest
 */
class ilTestExporter extends ilXmlExporter
{
    private ilLanguage $lng;
    private ilLogger $log;
    private ilTree $tree;
    private ilComponentRepository $component_repository;
    private QuestionInfoService $questioninfo;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->log = $DIC['ilLog'];
        $this->tree = $DIC['tree'];
        $this->component_repository = $DIC['component.repository'];
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();

        parent::__construct();
    }

    /**
     * Initialisation
     */
    public function init(): void
    {
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $id): string
    {
        $tst = new ilObjTest((int) $id, false);
        $tst->read();
        $test_export_factory = new ilTestExportFactory($tst, $this->lng, $this->log, $this->tree, $this->component_repository, $this->questioninfo);
        $test_export = $test_export_factory->getExporter('xml');
        $zip = $test_export->buildExportFile();

        $this->log->write(__METHOD__ . ': Created zip file ' . $zip);
        return ''; // Sagt mjansen
    }

    /**
     * @param array<int> ids
     * @return array<array> array of array with keys 'component', 'entity', 'ids'
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        if ($a_entity == 'tst') {
            $deps = [];

            $tax_ids = $this->getDependingTaxonomyIds($a_ids);

            if (count($tax_ids)) {
                $deps[] = [
                    'component' => 'components/ILIAS/Taxonomy',
                    'entity' => 'tax',
                    'ids' => $tax_ids
                ];
            }

            $deps[] = [
                'component' => 'components/ILIAS/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];

            return $deps;
        }

        return parent::getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids);
    }

    /**
     * @param array<int> $testObjIds
     * @return array<int> $taxIds
     */
    private function getDependingTaxonomyIds(array $test_obj_ids): array
    {
        $tax_ids = [];

        foreach ($test_obj_ids as $test_obj_id) {
            foreach (ilObjTaxonomy::getUsageOfObject($test_obj_id) as $tax_id) {
                $tax_ids[$tax_id] = $tax_id;
            }
        }

        return $tax_ids;
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
        return [
            '4.1.0' => [
                'namespace' => 'http://www.ilias.de/Modules/Test/htlm/4_1',
                'xsd_file' => 'ilias_tst_4_1.xsd',
                'uses_dataset' => false,
                'min' => '4.1.0',
                'max' => ''
            ]
        ];
    }
}
