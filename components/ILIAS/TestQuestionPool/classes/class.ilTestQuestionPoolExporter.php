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

use ILIAS\Data\ObjectId;
use ILIAS\TestQuestionPool\ExportImport\Foundation\ExportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\SimpleXMLSerializer;
use ILIAS\TestQuestionPool\ExportImport\QuestionPoolExporter;
use ILIAS\TestQuestionPool\QuestionPoolDIC;

/**
 * Used for container export with tests
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup components\ILIASTest
 */
class ilTestQuestionPoolExporter extends ilXmlExporter
{
    private QuestionPoolExporter $exporter;

    /**
     * @var array<int, ExportContext> $batches
     */
    private array $batches = [];


    public function init(): void
    {
        $this->exporter = QuestionPoolDIC::dic()['exportimport.exporter'];
    }

    /**
     * Returns the final XML content for one question pool.
     *
     * This method is called after `getXmlExportTailDependencies()`. At this point the export writer and export
     * directory are available, so the prepared batch can be written to disk and finalized.
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        if ($a_entity !== 'qpl') {
            throw new InvalidArgumentException("Invalid entity for question pool export: {$a_entity}");
        }

        return $this->finalizeExport((int) $a_id)->getContent();
    }

    /**
     * Collects export tail dependencies for one or more question pools.
     *
     * The export framework calls this method before `getXmlRepresentation()`. Therefore this method only prepares and
     * processes the export batch in memory and caches the context, because writer and export directory are not yet
     * initialized here.
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        if ($a_entity !== 'qpl') {
            throw new InvalidArgumentException("Invalid entity for question pool export: {$a_entity}");
        }

        $dependencies = [];
        foreach ($a_ids as $id) {
            $context = $this->processExport((int) $id);
            $dependencies = array_merge($dependencies, $context->getDependencies());
        }

        return $dependencies;
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/TestQuestionPool/htlm/4_1",
                "xsd_file" => "ilias_qpl_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => ""]
        ];
    }

    /**
     * Prepares and processes a question pool export in memory. The resulting context is cached per pool and reused
     * across calls.
     */
    private function processExport(int $pool_id): ExportContext
    {
        if (isset($this->batches[$pool_id])) {
            return $this->batches[$pool_id];
        }

        $context = $this->exporter->prepare(
            new ObjectId($pool_id),
            $this->exp->getExportConfigs()
        );

        $context = $this->exporter->process(
            $context,
            new SimpleXMLSerializer()->open('memory')
        );

        $this->batches[$pool_id] = $context;
        return $context;
    }

    /**
     * Finalizes a prepared export context and writes it to the export directory.
     */
    private function finalizeExport(int $pool_id): ExportContext
    {
        $context = $this->processExport($pool_id);

        return $this->exporter->write(
            $context,
            $this->exp->getExportWriter(),
            $this->exp->getPathToComponentExpDirInContainer()
        );
    }
}
