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

namespace ILIAS\Export\ExportHandler\I\Manager;

use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\HandlerInterface as ilExportHandlerContainerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ilObject;

interface HandlerInterface
{
    public function createContainerExport(
        int $user_id,
        ilExportHandlerContainerExportInfoInterface $container_export_info
    ): ilExportHandlerRepositoryElementInterface;

    public function createExport(
        int $user_id,
        ilExportHandlerExportInfoInterface $export_info,
        string $path_in_container
    ): ilExportHandlerRepositoryElementInterface;

    public function getExportInfo(
        ObjectId $object_id,
        int $time_stamp
    ): ilExportHandlerExportInfoInterface;

    public function getExportInfoWithObject(
        ilObject $export_object,
        int $time_stamp
    ): ilExportHandlerExportInfoInterface;

    public function getContainerExportInfo(
        ObjectId $main_entity_object_id,
        ilExportHandlerContainerExportInfoObjectIdCollectionInterface $object_ids
    ): ilExportHandlerContainerExportInfoInterface;

    public function getObjectIdCollectioBuilder(): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;

    public function getObjectIdCollectionBuilderFrom(
        ReferenceId $container_reference_id,
        bool $public_access = false
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
}
