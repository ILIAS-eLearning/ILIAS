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

namespace ILIAS\MetaData\Vocabularies\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\Copyright\Services\Services as CopyrightServices;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\PresentationInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\Presentation;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;
use ILIAS\MetaData\Vocabularies\Copyright\Bridge;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface;
use ILIAS\MetaData\Vocabularies\Factory\Factory;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepositoryInterface;
use ILIAS\MetaData\Vocabularies\Controlled\Repository as ControlledRepository;
use ILIAS\MetaData\Vocabularies\Controlled\Database\Wrapper;
use ILIAS\MetaData\Vocabularies\Slots\Handler as SlotHandler;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepositoryInterface;
use ILIAS\MetaData\Vocabularies\Standard\Repository as StandardRepository;
use ILIAS\MetaData\Vocabularies\Standard\DatabaseGateway;
use ILIAS\MetaData\Vocabularies\Standard\Assignment\Assignments;
use ILIAS\MetaData\Vocabularies\Manager\ManagerInterface;
use ILIAS\MetaData\Vocabularies\Manager\Manager;
use ILIAS\MetaData\Vocabularies\Dispatch\ReaderInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Reader;
use ILIAS\MetaData\Vocabularies\Dispatch\Actions;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\Infos;
use ILIAS\MetaData\Vocabularies\ElementHelper\ElementHelperInterface;
use ILIAS\MetaData\Vocabularies\ElementHelper\ElementHelper;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\Checker;

class Services
{
    protected PresentationInterface $presentation;
    protected ManagerInterface $manager;
    protected ElementHelperInterface $element_helper;
    protected SlotHandler $slot_handler;

    protected ReaderInterface $reader;
    protected CopyrightBridge $copyright_bridge;
    protected FactoryInterface $factory;
    protected ControlledRepositoryInterface $controlled_repository;
    protected StandardRepositoryInterface $standard_repository;

    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected CopyrightServices $copyright_services;

    public function __construct(
        GlobalContainer $dic,
        PathServices $path_services,
        StructureServices $structure_services,
        CopyrightServices $copyright_services
    ) {
        $this->dic = $dic;
        $this->path_services = $path_services;
        $this->structure_services = $structure_services;
        $this->copyright_services = $copyright_services;
    }

    public function presentation(): PresentationInterface
    {
        if (isset($this->presentation)) {
            return $this->presentation;
        }
        return $this->presentation = new Presentation(
            $this->copyrightBridge(),
            $this->controlledRepository(),
            $this->standardRepository()
        );
    }

    public function elementHelper(): ElementHelperInterface
    {
        if (isset($this->element_helper)) {
            return $this->element_helper;
        }
        return $this->element_helper = new ElementHelper(
            $this->slotHandler(),
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory(),
            new Checker(
                $this->slotHandler(),
                $this->path_services->pathFactory(),
                $this->path_services->navigatorFactory()
            ),
            $this->reader()
        );
    }

    public function manager(): ManagerInterface
    {
        if (isset($this->manager)) {
            return $this->manager;
        }
        return $this->manager = new Manager(
            $this->controlledRepository(),
            $this->reader(),
            $infos = new Infos(
                $this->controlledRepository(),
                $this->standardRepository()
            ),
            new Actions(
                $infos,
                $this->controlledRepository(),
                $this->standardRepository()
            )
        );
    }

    public function slotHandler(): SlotHandler
    {
        if (isset($this->slot_handler)) {
            return $this->slot_handler;
        }
        return $this->slot_handler = new SlotHandler(
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory(),
            $this->structure_services->structure(),
        );
    }

    protected function reader(): ReaderInterface
    {
        if (isset($this->reader)) {
            return $this->reader;
        }
        return $this->reader = new Reader(
            $this->copyrightBridge(),
            $this->controlledRepository(),
            $this->standardRepository()
        );
    }

    protected function copyrightBridge(): CopyrightBridge
    {
        if (isset($this->copyright_bridge)) {
            return $this->copyright_bridge;
        }
        return $this->copyright_bridge = new Bridge(
            $this->factory(),
            \ilMDSettings::_getInstance(),
            $this->copyright_services->repository(),
            $this->copyright_services->identifiersHandler()
        );
    }

    protected function controlledRepository(): ControlledRepositoryInterface
    {
        if (isset($this->controlled_repository)) {
            return $this->controlled_repository;
        }
        return $this->controlled_repository = new ControlledRepository(
            new Wrapper($this->dic->database()),
            $this->factory(),
            $this->slotHandler()
        );
    }

    protected function standardRepository(): StandardRepositoryInterface
    {
        if (isset($this->standard_repository)) {
            return $this->standard_repository;
        }
        return $this->standard_repository = new StandardRepository(
            new DatabaseGateway($this->dic->database()),
            $this->factory(),
            new Assignments()
        );
    }

    protected function factory(): FactoryInterface
    {
        if (isset($this->factory)) {
            return $this->factory;
        }
        return $this->factory = new Factory();
    }
}
