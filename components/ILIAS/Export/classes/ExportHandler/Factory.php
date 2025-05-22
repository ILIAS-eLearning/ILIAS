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

namespace ILIAS\Export\ExportHandler;

use ilAccessHandler;
use ilCtrlInterface;
use ilDBInterface;
use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\Consumer\Factory as ilExportHandlderConsumerFactory;
use ILIAS\Export\ExportHandler\I\Consumer\FactoryInterface as ilExportHandlderConsumerFactoryInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\FactoryInterface as ilExportHandlerInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Manager\FactoryInterface as ilExportHandlerManagerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\FactoryInterface as ilExportHandlerPartFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\FactoryInterface as ilExportHandlerPublicAccessFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\FactoryInterface as ilExportHandlerRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\FactoryInterface as ilExportHandlerTableFactoryInterface;
use ILIAS\Export\ExportHandler\I\Target\FactoryInterface as ilExportHandlerTargetFactoryInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\FactoryInterface as ilExportHandlerWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Info\Factory as ilExportHandlerInfoFactory;
use ILIAS\Export\ExportHandler\Manager\Factory as ilExportHandlerManagerFactory;
use ILIAS\Export\ExportHandler\Part\Factory as ilExportHandlerPartFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Factory as ilExportHandlerPublicAccessFactory;
use ILIAS\Export\ExportHandler\Repository\Factory as ilExportHandlerRepositoryFactory;
use ILIAS\Export\ExportHandler\Table\Factory as ilExportHandlerTableFactory;
use ILIAS\Export\ExportHandler\Target\Factory as ilExportHandlerTargetFactory;
use ILIAS\Export\ExportHandler\Wrapper\Factory as ilExportHandlerWrapperFactory;
use ILIAS\Filesystem\Filesystems;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;
use ILIAS\StaticURL\Services as StaticUrl;
use ilLanguage;
use ilObjectDefinition;
use ilObjUser;
use ilTree;

class Factory implements ilExportHandlerFactoryInterface
{
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ResourcesStorageService $irss;
    protected Filesystems $filesystems;
    protected StaticURL $static_url;
    protected ilObjUser $user;
    protected ilHTTPServices $http_services;
    protected ilRefineryFactory $refinery;
    protected ilUIServices $ui_services;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilDataFactory $data_factory;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
        $this->filesystems = $DIC->filesystem();
        $this->static_url = $DIC['static_url'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->http_services = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_services = $DIC->ui();
        $this->obj_definition = $DIC['objDefinition'];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->data_factory = new ilDataFactory();
    }

    public function part(): ilExportHandlerPartFactoryInterface
    {
        return new ilExportHandlerPartFactory($this);
    }

    public function info(): ilExportHandlerInfoFactoryInterface
    {
        return new ilExportHandlerInfoFactory(
            $this,
            $this->irss
        );
    }

    public function target(): ilExportHandlerTargetFactoryInterface
    {
        return new ilExportHandlerTargetFactory(
            $this
        );
    }

    public function repository(): ilExportHandlerRepositoryFactoryInterface
    {
        return new ilExportHandlerRepositoryFactory(
            $this,
            $this->db,
            $this->irss,
            $this->filesystems
        );
    }

    public function publicAccess(): ilExportHandlerPublicAccessFactoryInterface
    {
        return new ilExportHandlerPublicAccessFactory(
            $this,
            $this->db,
            $this->static_url
        );
    }

    public function manager(): ilExportHandlerManagerFactoryInterface
    {
        return new ilExportHandlerManagerFactory(
            $this,
            $this->obj_definition,
            $this->tree,
            $this->access,
            $this->wrapper()->dataFactory()->handler()
        );
    }

    public function consumer(): ilExportHandlderConsumerFactoryInterface
    {
        return new ilExportHandlderConsumerFactory(
            $this
        );
    }

    public function table(): ilExportHandlerTableFactoryInterface
    {
        return new ilExportHandlerTableFactory(
            $this,
            $this->ui_services,
            $this->http_services,
            $this->refinery,
            $this->user,
            $this->lng,
            $this->ctrl,
            $this->data_factory
        );
    }

    public function wrapper(): ilExportHandlerWrapperFactoryInterface
    {
        return new ilExportHandlerWrapperFactory(
            $this->data_factory
        );
    }
}
