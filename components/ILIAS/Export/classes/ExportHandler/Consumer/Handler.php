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

namespace ILIAS\Export\ExportHandler\Consumer;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfigCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\FactoryInterface as ExportConfigFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ilExportHandlerConsumerExportWriterInterface;
use ILIAS\Export\ExportHandler\I\Consumer\HandlerInterface as ilExportHandlerConsumerInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\HandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ExportRepositoryStakeholderInterface;
use ilObject;

class Handler implements ilExportHandlerConsumerInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function publicAccess(): ilExportHandlerPublicAccessInterface
    {
        return $this->export_handler->publicAccess()->handler();
    }

    public function exportConfig(): ExportConfigFactoryInterface
    {
        return $this->export_handler->consumer()->exportConfig();
    }

    public function createStandardExport(
        int $user_id,
        ObjectId $object_id,
        ExportConfigCollectionInterface $export_configs = null
    ): ilExportHandlerRepositoryElementInterface {
        $manager = $this->export_handler->manager()->handler();
        if (is_null($export_configs)) {
            $export_configs = $this->export_handler->consumer()->exportConfig()->allExportConfigs();
        }
        return $manager->createExport(
            $user_id,
            $manager->getExportInfo($object_id, time(), $export_configs),
            ""
        );
    }

    public function createStandardExportByObject(
        int $user_id,
        ilObject $object,
        ExportConfigCollectionInterface $export_configs = null
    ): ilExportHandlerRepositoryElementInterface {
        $manager = $this->export_handler->manager()->handler();
        if (is_null($export_configs)) {
            $export_configs = $this->export_handler->consumer()->exportConfig()->allExportConfigs();
        }
        return $manager->createExport(
            $user_id,
            $manager->getExportInfoWithObject($object, time(), $export_configs),
            ""
        );
    }

    public function exportWriter(
        ilExportHandlerRepositoryElementInterface $element
    ): ilExportHandlerConsumerExportWriterInterface {
        return $this->export_handler->consumer()->exportWriter()->handler()
            ->withObjectId($element->getKey()->getObjectId())
            ->withResourceIdSerialized($element->getKey()->getResourceIdSerialized());
    }

    public function exportStakeholderHandler(): ExportRepositoryStakeholderInterface
    {
        return $this->export_handler->repository()->stakeholder()->handler();
    }
}
