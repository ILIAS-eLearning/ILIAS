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

namespace ILIAS\Export\ExportHandler\I\Consumer;

use ILIAS\Export\ExportHandler\I\Consumer\Context\FactoryInterface as ilExportHandlerConsumerContextFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\FactoryInterface as ilExportHandlerConsumerExportOptionFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\FactoryInterface as ilExportHandlerConsumerExportWriterFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\FactoryInterface as ilExportHandlerConsumerFileFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\HandlerInterface as ilExportHandlerConsumerInterface;

interface FactoryInterface
{
    public function handler(): ilExportHandlerConsumerInterface;

    public function exportOption(): ilExportHandlerConsumerExportOptionFactoryInterface;

    public function context(): ilExportHandlerConsumerContextFactoryInterface;

    public function exportWriter(): ilExportHandlerConsumerExportWriterFactoryInterface;

    public function file(): ilExportHandlerConsumerFileFactoryInterface;
}
