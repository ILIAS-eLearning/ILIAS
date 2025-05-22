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

namespace ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;

interface CollectionBuilderInterface
{
    public function addObjectId(
        ObjectId $object_id,
        bool $create_new_export_for_object
    ): CollectionBuilderInterface;

    public function change(
        ObjectId $object_id,
        bool $create_new_export_for_object
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;

    public function removeObjectId(
        ObjectId $object_id
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;

    public function getCollection(): ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
}
