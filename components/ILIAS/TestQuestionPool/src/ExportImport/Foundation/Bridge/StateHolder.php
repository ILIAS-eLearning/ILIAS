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

use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ExportTarget;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfig;
use RuntimeException;

/**
 * State management for the export process. It is used to pass the export target, config and tools between the ILIAS
 * export component and the local exporter classes, as the legacy `ilExport` and `ilXmlExporter` do not provide access
 * to the core export functionality.
 */
class StateHolder
{
    private ?ExportState $export_state = null;

    public function create(ExportTarget $target, ExportConfig $config): ExportState
    {
        $this->export_state = new ExportState($target, $config);
        return $this->export_state;
    }

    public function exists(): bool
    {
        return $this->export_state !== null;
    }

    public function get(): ExportState
    {
        if ($this->export_state === null) {
            throw new RuntimeException('Export state not found. You need to create the state first.');
        }
        return $this->export_state;
    }

    public function set(ExportState $export_state): void
    {
        $this->export_state = $export_state;
    }
}
