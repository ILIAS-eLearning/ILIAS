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

namespace ILIAS\Export\ExportHandler\Table;

use ilCtrl;
use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\HandlerInterface as ilExportHandlerTableDataRetrievalFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\FactoryInterface as ilExportHandlerTableFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\HandlerInterface as ilExportHandlerTableInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\FactoryInterface as ilExportHandlerTableRowIdFactoryInterface;
use ILIAS\Export\ExportHandler\Table\DataRetrieval\Handler as ilExportHandlerTableDataRetrievalFactory;
use ILIAS\Export\ExportHandler\Table\Handler as ilExportHandlerTable;
use ILIAS\Export\ExportHandler\Table\RowId\Factory as ilExportHandlerTableRowIdFactory;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ilLanguage;
use ilObjUser;

class Factory implements ilExportHandlerTableFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilUIServices $ui_services;
    protected ilHTTPServices $http_services;
    protected ilRefineryFactory $refinery;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilDataFactory $data_factory;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilUIServices $ui_services,
        ilHTTPServices $http_services,
        ilRefineryFactory $refinery,
        ilObjUser $user,
        ilLanguage $lng,
        ilCtrl $ctrl,
        ilDataFactory $data_factory
    ) {
        $this->export_handler = $export_handler;
        $this->ui_services = $ui_services;
        $this->http_services = $http_services;
        $this->refinery = $refinery;
        $this->user = $user;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->data_factory = $data_factory;
    }

    public function handler(): ilExportHandlerTableInterface
    {
        return new ilExportHandlerTable(
            $this->ui_services,
            $this->http_services,
            $this->refinery,
            $this->user,
            $this->lng,
            $this->ctrl,
            $this->export_handler,
            $this->data_factory
        );
    }

    public function rowId(): ilExportHandlerTableRowIdFactoryInterface
    {
        return new ilExportHandlerTableRowIdFactory($this->export_handler);
    }

    public function dataRetrieval(): ilExportHandlerTableDataRetrievalFactoryInterface
    {
        return new ilExportHandlerTableDataRetrievalFactory(
            $this->ui_services,
            $this->export_handler
        );
    }
}
