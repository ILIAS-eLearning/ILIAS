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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge;

use ILIAS\Export\ExportHandler\Factory as ExportHandler;
use ILIAS\Export\ExportHandler\Info\Export\Path\Handler as ExportPath;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Exporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\SimpleXMLSerializer;
use Psr\Log\LoggerInterface as Logger;

/**
 * Trait to bridge the export functionality between the ILIAS xml export and the local exporter classes. This bridge is
 * needed because the `ilXmlExporter` does not provide access to the core export functionality.
 *
 * The `getXmlExportTailDependencies` method is called by the core before the XML can be generated, because ExportWriter
 * and ExportPath are not initialized at that point. The export therefore has to be separated into multiple steps. The
 * bridge orchestrates these steps and provides the required dependencies via the `ExportState` at each step.
 *
 * @mixin \ilXmlExporter
 */
trait XmlExporterBridge
{
    protected readonly ExportHandler $export_handler;
    protected readonly StateHolder $state_holder;
    protected readonly Exporter $exporter;
    protected readonly Logger $logger;

    /**
     * Processes the export in memory using the global export state. It performs the prepare and process steps of the
     * exporter (if not already done) and returns the state.
     */
    private function processExport(): ExportState
    {
        $state = $this->state_holder->get();

        if ($state->getStep()->value < ExportStep::PREPARE->value) {
            $state->setLogger($this->logger);
            $this->exporter->prepare($state);
        }

        if ($state->getStep()->value < ExportStep::PROCESS->value) {
            $state->setSerializer(new SimpleXMLSerializer()->open('memory'));
            $this->exporter->process($state);
        }

        $this->state_holder->set($state);
        return $state;
    }

    /**
     * Finalizes the export by setting the path info and writer and calling the write step of the exporter. It performs
     * the prepare and process steps if not already done.
     */
    private function finalizeExport(): ExportState
    {
        $state = $this->processExport();

        if ($state->getStep()->value < ExportStep::WRITE->value) {
            $state->setPathInfo($this->createPathInfo());
            $state->setWriter($this->exp->getExportWriter());

            $this->exporter->write($state);
        }

        $this->state_holder->set($state);
        return $state;
    }

    private function initExportState(
        string $component,
        string $target_release,
        string $type,
        array $object_ids,
        string $option = ''
    ): ExportState {
        $target = $this->export_handler->target()->handler()
            ->withType($type)
            ->withTargetRelease($target_release)
            ->withObjectIds($object_ids)
            ->withClassname(static::class)
            ->withComponent($component);

        return $this->state_holder->create(
            $target,
            $this->export_handler->consumer()->exportConfig()->collection(),
            $option
        );
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
