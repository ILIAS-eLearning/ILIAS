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

namespace ILIAS\Export\ExportHandler\I\Info\Export\Component;

use ilExport;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\CollectionInterface as ilExportHandlerExportComponentInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ilExportHandlerTargetInterface;
use ilXmlExporter;

interface HandlerInterface
{
    public function withExportTarget(ilExportHandlerTargetInterface $export_target): HandlerInterface;

    public function withExportFilePathInContainer(string $path_in_container): HandlerInterface;

    public function withComponentExportDirPathInContainer(string $component_export_dir_path_in_container): HandlerInterface;

    public function getTarget(): ilExportHandlerTargetInterface;

    public function getExportFilePathInContainer(): string;

    public function getComponentExportDirPathInContainer(): string;

    public function getXSDSchemaLocation(): string;

    public function getComponentExporter(
        ilExport $il_export
    ): ilXmlExporter;

    public function getHeadComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface;

    public function getTailComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface;

    public function getSchemaVersion(): string;

    public function getNamespace(): string;

    public function getDatasetNamespace(): string;

    public function usesDataset(): bool;

    public function usesCustomNamespace(): bool;
}
