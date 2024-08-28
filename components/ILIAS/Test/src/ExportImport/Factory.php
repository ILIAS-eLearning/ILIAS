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

namespace ILIAS\Test\ExportImport;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\FileDelivery\Services as FileDeliveryServices;
use ILIAS\Data\Factory as DataFactory;

class Factory
{
    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilDBInterface $db,
        private readonly \ilBenchmark $bench,
        private \ilGlobalTemplateInterface $tpl,
        private readonly TestLogger $logger,
        private readonly \ilTree $tree,
        private readonly \ilComponentRepository $component_repository,
        private readonly \ilComponentFactory $component_factory,
        private readonly FileDeliveryServices $file_delivery,
        private readonly DataFactory $data_factory,
        private readonly \ilObjUser $current_user,
        private readonly GeneralQuestionPropertiesRepository $questionrepository
    ) {
    }

    public function getExporter(
        \ilObjTest $test_obj,
        Types $export_type,
        ?string $plugin_type = null
    ): Exporter {
        switch ($export_type) {
            case Types::SCORED_RUN:
                return (new ResultsExportExcel($this->lng, $this->data_factory, $this->current_user, $test_obj, $test_obj->getTitle() . '_results', true))
                    ->withAggregatedResultsPage()
                    ->withResultsPage()
                    ->withUserPages();

            case Types::ALL_RUNS:
                return (new ResultsExportExcel($this->lng, $this->data_factory, $this->current_user, $test_obj, $test_obj->getTitle() . '_results', false))
                    ->withAggregatedResultsPage()
                    ->withResultsPage()
                    ->withUserPages();

            case Types::CERTIFICATE_ARCHIVE:
                return new CertificateExport(
                    $this->lng,
                    $this->db,
                    $this->tpl,
                    $this->file_delivery,
                    $test_obj
                );

            case Types::XML:
            case Types::XML_WITH_RESULTS:
                $export_class = ExportFixedQuestionSet::class;
                if (!$test_obj->isFixedTest()) {
                    $export_class = ExportRandomQuestionSet::class;
                }

                $export = new $export_class(
                    $this->lng,
                    $this->db,
                    $this->bench,
                    $this->logger,
                    $this->tree,
                    $this->component_repository,
                    $this->questionrepository,
                    $this->file_delivery,
                    $test_obj
                );

                if ($export_type === Types::XML_WITH_RESULTS) {
                    return $export->withResultExportingEnabled(true);
                }
                return $export;

            case Types::PLUGIN:
                if ($plugin_type === null) {
                    throw new \Exception('No Plugin Type given!');
                }
                foreach ($this->component_factory->getActivePluginsInSlot('texp') as $plugin) {
                    if ($plugin->getFormat() === $plugin_type) {
                        $plugin->setTest($test_obj);
                        return $plugin;
                    }
                }
        }
    }
}
