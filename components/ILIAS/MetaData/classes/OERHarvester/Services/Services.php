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

namespace ILIAS\MetaData\OERHarvester\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Presentation\Services\Services as PresentationServices;
use ILIAS\MetaData\Copyright\Services\Services as CopyrightServices;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\XML\Services\Services as XMLServices;
use ILIAS\MetaData\OERHarvester\Settings\Settings;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as StatusRepository;
use ILIAS\MetaData\OERHarvester\ResourceStatus\DatabaseRepository;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;
use ILIAS\MetaData\OERHarvester\Publisher\Publisher;
use ILIAS\MetaData\OERHarvester\ExposedRecords\DatabaseRepository as ExposedRecordsRepository;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\Handler as ObjectHandler;
use ILIAS\MetaData\OERHarvester\Export\Handler as ExportHandler;
use ILIAS\Export\ExportHandler\Factory as ExportService;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\OERHarvester\XML\Writer;
use ILIAS\MetaData\OERHarvester\ControlCenter\ControlCenterGUI;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\RequestParser;
use ILIAS\MetaData\OERHarvester\ControlCenter\Content\ContentFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcherInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcher;
use ILIAS\MetaData\OERHarvester\ControlCenter\ComponentFactoryInterface as ControlCenterComponentFactoryInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\ComponentFactory as ControlCenterComponentFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactoryInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactory;
use ILIAS\MetaData\OERHarvester\CronJob\AutomaticPublisher;

class Services
{
    protected SettingsInterface $settings;
    protected ExposedRecordsRepository $exposed_records_repository;
    protected StateInfoFetcherInterface $state_info_fetcher;
    protected ControlCenterComponentFactoryInterface $control_center_component_factory;
    protected ControlCenterGUI $control_center_gui;

    protected LinkFactoryInterface $link_factory;
    protected ObjectHandler $repository_object_handler;
    protected Writer $xml_writer;
    protected StatusRepository $status_repository;
    protected PublisherInterface $publisher;

    public function __construct(
        protected GlobalContainer $dic,
        protected PresentationServices $presentation_services,
        protected CopyrightServices $copyright_services,
        protected PathServices $path_services,
        protected RepositoryServices $repository_services,
        protected XMLServices $xml_services
    ) {
    }

    public function settings(): SettingsInterface
    {
        return $this->settings ??= new Settings();
    }

    public function exposedRecordsRepository(): ExposedRecordsRepository
    {
        return $this->exposed_records_repository ??= new ExposedRecordsRepository($this->dic->database());
    }

    public function controlCenterGUI(string $link_to_parent): ControlCenterGUI
    {
        $data_factory = new DataFactory();
        $link_to_parent = $data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            ltrim($link_to_parent, '/')
        );

        return $this->control_center_gui ??= new ControlCenterGUI(
            $link_to_parent,
            $this->dic->ctrl(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ui()->factory(),
            $this->dic->ui()->renderer(),
            new RequestParser(
                $this->dic->http(),
                $this->dic->refinery()
            ),
            new ContentFactory(
                $this->dic->ui()->factory(),
                $this->presentation_services->utilities(),
                $this->linkFactory(),
                $this->copyright_services->repository()
            ),
            $this->presentation_services->utilities(),
            $this->stateInfoFetcher(),
            $this->publisher(),
            $this->settings(),
            $this->dic['static_url'],
            $data_factory,
            $this->repositoryObjectHandler()
        );
    }

    public function stateInfoFetcher(): StateInfoFetcherInterface
    {
        return $this->state_info_fetcher ??= new StateInfoFetcher(
            $this->dic->access(),
            $this->exposedRecordsRepository(),
            $this->statusRepository(),
            $this->settings(),
            $this->repositoryObjectHandler(),
            $this->copyright_services->identifiersHandler(),
            $this->publisher(),
            $this->repository_services->repository(),
            $this->path_services->navigatorFactory(),
            $this->path_services->pathFactory()
        );
    }

    public function controlCenterComponentFactory(): ControlCenterComponentFactoryInterface
    {
        return $this->control_center_component_factory ??= new ControlCenterComponentFactory(
            $this->dic->ui()->factory(),
            new DataFactory(),
            $this->linkFactory(),
            $this->presentation_services->utilities()
        );
    }

    public function automaticPublisher(): AutomaticPublisher
    {
        return new AutomaticPublisher(
            $this->publisher(),
            $this->settings(),
            $this->repositoryObjectHandler(),
            $this->statusRepository(),
            $this->exposedRecordsRepository(),
            $this->copyright_services->searcherFactory(),
            $this->repository_services->repository(),
            $this->XMLWriter(),
            $this->dic->logger()->meta()
        );
    }

    protected function linkFactory(): LinkFactoryInterface
    {
        return $this->link_factory ??= new LinkFactory(
            $this->dic->ctrl()
        );
    }

    protected function repositoryObjectHandler(): ObjectHandler
    {
        return $this->repository_object_handler ??= new ObjectHandler($this->dic->repositoryTree());
    }

    protected function XMLWriter(): Writer
    {
        return $this->xml_writer ??= new Writer(
            $this->repository_services->repository(),
            $this->xml_services->simpleDCWriter()
        );
    }

    protected function statusRepository(): StatusRepository
    {
        return $this->status_repository ??= new DatabaseRepository($this->dic->database());
    }

    protected function publisher(): PublisherInterface
    {
        return $this->publisher ??= new Publisher(
            $this->exposedRecordsRepository(),
            $this->statusRepository(),
            $this->repositoryObjectHandler(),
            new ExportHandler(
                $this->dic->user(),
                new ExportService(), // should be replaced by proper API
                new DataFactory()
            ),
            $this->settings(),
            $this->XMLWriter(),
            $this->dic->access()
        );
    }
}
