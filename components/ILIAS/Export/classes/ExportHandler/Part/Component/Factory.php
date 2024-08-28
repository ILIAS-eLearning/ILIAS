<?php

namespace ILIAS\Export\ExportHandler\Part\Component;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\FactoryInterface as ilExportHanlderPartComponentFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\HandlerInterface as ilExportHandlerComponentInterface;
use ILIAS\Export\ExportHandler\Part\Component\Handler as ilExportHandlerComponent;

class Factory implements ilExportHanlderPartComponentFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerComponentInterface
    {
        return new ilExportHandlerComponent(
            $this->export_handler
        );
    }
}
