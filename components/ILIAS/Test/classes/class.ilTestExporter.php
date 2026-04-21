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

use ILIAS\Export\ExportHandler\Factory as ExportHandler;
use ILIAS\Test\ExportImport\Types;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\XmlExporterBridge;

class ilTestExporter extends ilXmlExporter
{
    use XmlExporterBridge;

    public function init(): void
    {
        $local_dic = TestDIC::dic();

        $this->export_handler = new ExportHandler();
        $this->state_holder = $local_dic['exportimport.state_holder'];
        $this->exporter = $local_dic['exportimport.exporter'];
        $this->logger = $local_dic['logging.logger'];
    }

    /**
     * Returns the final XML content for the test.
     *
     * This method is called after `getXmlExportTailDependencies()`. At this point the export writer and export
     * directory are available, so the preprocessed export can be written to disk and returned as xml.
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $id): string
    {
        if ($a_entity !== 'tst') {
            throw new InvalidArgumentException("Invalid entity for test export: {$a_entity}");
        }

        return $this->finalizeExport()->getContent();
    }

    /**
     * Collects export tail dependencies for the test.
     *
     * The export framework calls this method before `getXmlRepresentation()`. Therefore this method only prepares and
     * processes the export in memory using the export state. The export state is created if it does not exist yet.
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        if ($a_entity !== 'tst') {
            throw new InvalidArgumentException("Invalid entity for test export: {$a_entity}");
        }

        // If the default export option was used, the state is not initialized yet.
        if ($this->state_holder->exists() === false) {
            $this->initExportState(
                'components/ILIAS/Test',
                $a_target_release,
                $a_entity,
                $a_ids,
                Types::XML->value
            );
        }

        return $this->processExport()->getDependencies();
    }

    /**
    * Returns schema versions that the component can export to. ILIAS chooses the first one, that has min/max
    * constraints which fit to the target release.
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
