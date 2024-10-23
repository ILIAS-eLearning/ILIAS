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

namespace ILIAS\Export\ExportHandler\Info\Export\Container;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\CollectionInterface as ilExportHandlerExportInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\HandlerInterface as ilExportHandlerContainerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\CollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ilExportHandlerTargetInterface;
use ilImportExportFactory;
use ilObject;

class Handler implements ilExportHandlerContainerExportInfoInterface
{
    protected ilExportHandlerContainerExportInfoObjectIdCollectionInterface $object_ids;
    protected ObjectId $main_export_entity_object_id;
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilExportHandlerExportInfoInterface $main_entity_export_info;
    protected ilExportHandlerExportInfoCollectionInterface $export_infos;
    protected int $timestamp;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    protected function getExportTarget(
        ObjectId $object_id
    ): ilExportHandlerTargetInterface {
        $obj_id = $object_id->toInt();
        $type = ilObject::_lookupType($obj_id);
        $class = ilImportExportFactory::getExporterClass($type);
        $comp = ilImportExportFactory::getComponentForExport($type);
        $v = explode(".", ILIAS_VERSION_NUMERIC);
        $target_release = $v[0] . "." . $v[1] . ".0";
        return $this->export_handler->target()->handler()
            ->withTargetRelease($target_release)
            ->withType($type)
            ->withObjectIds([$obj_id])
            ->withClassname($class)
            ->withComponent($comp);
    }

    protected function getExportInfo(
        ObjectId $object_id,
        int $time_stamp
    ): ilExportHandlerExportInfoInterface {
        return $this->export_handler->info()->export()->handler()
            ->withTarget($this->getExportTarget($object_id), $time_stamp);
    }

    protected function initExportInfos(): void
    {
        $this->main_entity_export_info = $this->getExportInfo(
            $this->getMainExportEntity(),
            $this->getTimestamp()
        )
            ->withSetNumber(1)
            ->withReuseExport(false)
            ->withContainerExportInfo($this);
        $set_id = 2;
        $this->export_infos = $this->export_handler->info()->export()->collection();
        $repository = $this->export_handler->repository();
        foreach ($this->getObjectIds() as $object_id_handler) {
            $object_id = $object_id_handler->getObjectId();
            if ($object_id->toInt() === $this->main_export_entity_object_id->toInt()) {
                continue;
            }
            $keys = $repository->key()->collection()->withElement($repository->key()->handler()->withObjectId($object_id));
            $timestamp = $object_id_handler->getReuseExport()
                ? $repository->handler()->getElements($keys)->newest()->getValues()->getCreationDate()->getTimestamp()
                : $this->getTimestamp();
            $this->export_infos = $this->export_infos->withElement(
                $this->getExportInfo($object_id, $timestamp)
                ->withSetNumber($set_id++)
                ->withContainerExportInfo($this)
                ->withReuseExport($object_id_handler->getReuseExport())
            );
        }
    }

    public function withObjectIds(
        ilExportHandlerContainerExportInfoObjectIdCollectionInterface $object_ids
    ): ilExportHandlerContainerExportInfoInterface {
        $clone = clone $this;
        $clone->object_ids = $object_ids;
        return $clone;
    }

    public function withMainExportEntity(
        ObjectId $object_id
    ): ilExportHandlerContainerExportInfoInterface {
        $clone = clone $this;
        $clone->main_export_entity_object_id = $object_id;
        return $clone;
    }

    public function withTimestamp(
        int $timestamp
    ): ilExportHandlerContainerExportInfoInterface {
        $clone = clone $this;
        $clone->timestamp = $timestamp;
        return $clone;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getObjectIds(): ilExportHandlerContainerExportInfoObjectIdCollectionInterface
    {
        return $this->object_ids;
    }

    public function getMainEntityExportInfo(): ilExportHandlerExportInfoInterface
    {
        if (!isset($this->export_infos)) {
            $this->initExportInfos();
        }
        return $this->main_entity_export_info;
    }

    public function getExportInfos(): ilExportHandlerExportInfoCollectionInterface
    {
        if (!isset($this->export_infos)) {
            $this->initExportInfos();
        }
        return $this->export_infos;
    }

    public function getMainExportEntity(): ObjectId
    {
        return $this->main_export_entity_object_id;
    }
}
