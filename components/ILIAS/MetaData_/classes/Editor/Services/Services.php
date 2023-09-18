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

namespace ILIAS\MetaData\Editor\Services;

use ILIAS\MetaData\Editor\Presenter\Presenter;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Presenter\Utilities;
use ILIAS\MetaData\Editor\Presenter\Data;
use ILIAS\MetaData\Editor\Presenter\Elements;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\Editor\Dictionary\LOMDictionaryInitiator;
use ILIAS\MetaData\Editor\Dictionary\TagFactory;
use ILIAS\MetaData\Editor\Http\LinkFactoryInterface;
use ILIAS\MetaData\Editor\Http\RequestParserInterface;
use ILIAS\MetaData\Editor\Http\LinkFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\Editor\Http\RequestParser;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Elements\Markers\MarkerFactory;
use ILIAS\MetaData\Editor\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Editor\Manipulator\Manipulator;
use ILIAS\MetaData\Editor\Observers\ObserverHandler;
use ILIAS\MetaData\Editor\Observers\ObserverHandlerInterface;

class Services
{
    protected PresenterInterface $presenter;
    protected DictionaryInterface $dictionary;
    protected LinkFactoryInterface $link_factory;
    protected RequestParserInterface $request_parser;
    protected ManipulatorInterface $manipulator;
    protected ObserverHandlerInterface $observer_handler;

    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected RepositoryServices $repository_services;

    public function __construct(
        GlobalContainer $dic,
        PathServices $path_services,
        StructureServices $structure_services,
        RepositoryServices $repository_services
    ) {
        $this->dic = $dic;
        $this->path_services = $path_services;
        $this->structure_services = $structure_services;
        $this->repository_services = $repository_services;
    }

    public function presenter(): PresenterInterface
    {
        if (isset($this->presenter)) {
            return $this->presenter;
        }
        $lng = $this->dic->language();
        $lng->loadLanguageModule('meta');
        return $this->presenter = new Presenter(
            $utilities = new Utilities(
                $lng,
                $this->dic->user()
            ),
            $data = new Data($utilities),
            new Elements(
                $utilities,
                $data,
                $this->dictionary(),
                $this->path_services->navigatorFactory()
            ),
        );
    }

    public function dictionary(): DictionaryInterface
    {
        if (isset($this->dictionary)) {
            return $this->dictionary;
        }
        return $this->dictionary = (new LOMDictionaryInitiator(
            new TagFactory($this->path_services->pathFactory()),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
    }

    public function linkFactory(): LinkFactoryInterface
    {
        if (isset($this->link_factory)) {
            return $this->link_factory;
        }
        return $this->link_factory = new LinkFactory(
            $this->dic->ctrl(),
            new DataFactory()
        );
    }

    public function requestParser(): RequestParserInterface
    {
        if (isset($this->request_parser)) {
            return $this->request_parser;
        }
        return $this->request_parser = new RequestParser(
            $this->dic->http(),
            $this->dic->refinery(),
            $this->path_services->pathFactory()
        );
    }

    public function manipulator(): ManipulatorInterface
    {
        if (isset($this->manipulator)) {
            return $this->manipulator;
        }
        return $this->manipulator = new Manipulator(
            $this->repository_services->repository(),
            new MarkerFactory(),
            $this->path_services->navigatorFactory()
        );
    }

    public function observerHandler(): ObserverHandlerInterface
    {
        if (isset($this->observer_handler)) {
            return $this->observer_handler;
        }
        return $this->observer_handler = new ObserverHandler();
    }
}
