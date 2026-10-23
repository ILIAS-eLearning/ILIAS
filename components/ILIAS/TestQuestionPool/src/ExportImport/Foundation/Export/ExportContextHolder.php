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

use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ExportTarget;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;

/**
 * Holds the export context for one request. It passes the export target, config and tools between the ILIAS export
 * component and the local exporter classes, as the legacy `ilExport` and `ilXmlExporter` do not provide access to the
 * core export functionality.
 */
class ExportContextHolder
{
    private ?ExportContext $export_context = null;

    public function create(
        ExportTarget $target,
        ExportConfig $config,
        string $option = ''
    ): ExportContext {
        return $this->export_context = new ExportContext($target, $config, $option);
    }

    public function exists(): bool
    {
        return $this->export_context !== null;
    }

    public function get(): ExportContext
    {
        if ($this->export_context === null) {
            throw new \RuntimeException('Export context not found. You need to create the context first.');
        }
        return $this->export_context;
    }

    public function set(ExportContext $export_context): void
    {
        $this->export_context = $export_context;
    }

    public function clear(): void
    {
        $this->export_context = null;
    }
}
