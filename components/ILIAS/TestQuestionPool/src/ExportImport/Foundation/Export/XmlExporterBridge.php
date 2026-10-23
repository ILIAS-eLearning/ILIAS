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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Export;

use ILIAS\Export\ExportHandler\Factory as ExportHandler;
use ILIAS\Export\ExportHandler\Info\Export\Path\Handler as ExportPath;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Export\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serialize\XmlSerializer;
use Psr\Log\LoggerInterface as Logger;

/**
 * Trait to bridge the export functionality between the ILIAS xml export and the local exporter classes. This bridge is
 * needed because the `ilXmlExporter` does not provide access to the core export functionality.
 *
 * The `getXmlExportTailDependencies` method is called by the core before the XML can be generated, because ExportWriter
 * and ExportPath are not initialized at that point. The export therefore has to be separated into multiple phases. The
 * bridge orchestrates these phases and provides the required dependencies via the `ExportContext` at each phase.
 *
 * @mixin \ilXmlExporter
 */
trait XmlExporterBridge
{
    protected readonly ExportHandler $export_handler;
    protected readonly ExportContextHolder $context_holder;
    protected readonly Exporter $exporter;
    protected readonly Logger $logger;

    /**
     * Processes the export in memory using the request export context. It performs the prepare and process phases of
     * the exporter (if not already done) and returns the context.
     */
    private function processExport(): ExportContext
    {
        $context = $this->context_holder->get();

        if ($context->getPhase()->value < ExportPhase::Prepare->value) {
            $context->setLogger($this->logger);
            $this->exporter->prepare($context);
        }

        if ($context->getPhase()->value < ExportPhase::Process->value) {
            $context->setSerializer(XmlSerializer::inMemory());
            $this->exporter->process($context);
        }

        $this->context_holder->set($context);
        return $context;
    }

    /**
     * Finalizes the export by setting the path info and writer and calling the write phase of the exporter. It
     * performs the prepare and process phases if not already done.
     */
    private function finalizeExport(): ExportContext
    {
        $context = $this->processExport();

        if ($context->getPhase()->value < ExportPhase::Write->value) {
            $context->setPathInfo($this->createPathInfo());
            $context->setWriter($this->exp->getExportWriter());

            $this->exporter->write($context);
        }

        $this->context_holder->clear();
        return $context;
    }

    private function initExportContext(
        string $component,
        string $target_release,
        string $type,
        array $object_ids,
        string $option = ''
    ): ExportContext {
        $object_ids = array_slice($object_ids, 0, 1);
        $target = $this->export_handler->target()->handler()
            ->withType($type)
            ->withTargetRelease($target_release)
            ->withObjectIds($object_ids)
            ->withClassname(static::class)
            ->withComponent($component);

        $context = $this->context_holder->create(
            $target,
            $this->export_handler->consumer()->exportConfig()->collection(),
            $option
        );

        return $context;
    }

    private function createPathInfo(): ExportPath
    {
        return $this->export_handler->info()->export()->path()->handler()
            ->withPathToComponentDirInContainer($this->exp->getExportDirInContainer())
            ->withPathToComponentExpDirInContainer($this->exp->getPathToComponentExpDirInContainer())
            ->withSetNumber($this->exp->getSetNumber())
            ->withIsContainerExport($this->exp->isContainerExport());
    }
}
