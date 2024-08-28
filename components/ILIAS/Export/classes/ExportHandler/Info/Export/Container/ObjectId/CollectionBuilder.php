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

namespace ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\FactoryInterface as ilExportHandlerContainerExportInfoObjectIdFactoryInterface;

class CollectionBuilder implements ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface
{
    protected ilExportHandlerContainerExportInfoObjectIdCollectionInterface $object_id_collection;
    protected ilExportHandlerContainerExportInfoObjectIdFactoryInterface $object_id_factory;

    public function __construct(
        ilExportHandlerContainerExportInfoObjectIdFactoryInterface $object_id_factory
    ) {
        $this->object_id_collection = $object_id_factory->collection();
        $this->object_id_factory = $object_id_factory;
    }

    public function addObjectId(
        ObjectId $object_id,
        bool $create_new_export_for_object
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface {
        $clone = clone $this;
        $clone->object_id_collection = $clone->object_id_collection->withElement(
            $clone->object_id_factory->handler()
                ->withObjectId($object_id)
                ->withReuseExport(!$create_new_export_for_object)
        );
        return $clone;
    }

    public function change(
        ObjectId $object_id,
        bool $create_new_export_for_object
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface {
        $collection = $this->object_id_factory->collection();
        foreach ($this->object_id_collection as $element) {
            if ($element->getObjectId()->toInt() === $object_id->toInt()) {
                $collection = $collection->withElement($element->withReuseExport(!$create_new_export_for_object));
                continue;
            }
            $collection = $collection->withElement($element);
        }
        $clone = clone $this;
        $clone->object_id_collection = $collection;
        return $clone;
    }

    public function removeObjectId(
        ObjectId $object_id
    ): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface {
        $collection = $this->object_id_factory->collection();
        foreach ($this->object_id_collection as $element) {
            if ($element->getObjectId()->toInt() === $object_id->toInt()) {
                continue;
            }
            $collection = $collection->withElement($element);
        }
        $clone = clone $this;
        $clone->object_id_collection = $collection;
        return $clone;
    }

    public function getCollection(): ilExportHandlerContainerExportInfoObjectIdCollectionInterface
    {
        return $this->object_id_collection;
    }
}
