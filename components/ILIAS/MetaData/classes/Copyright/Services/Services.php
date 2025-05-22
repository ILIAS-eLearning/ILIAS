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

namespace ILIAS\MetaData\Copyright\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Search\Services\Services as SearchServices;
use ILIAS\MetaData\Paths\Services\Services as PathsServices;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\Renderer;
use ILIAS\MetaData\Copyright\Database\Wrapper;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface;
use ILIAS\MetaData\Copyright\Identifiers\Handler;
use ILIAS\MetaData\Copyright\Search\Factory;
use ILIAS\MetaData\Copyright\Search\FactoryInterface;

class Services
{
    protected RepositoryInterface $repository;
    protected RendererInterface $renderer;
    protected HandlerInterface $handler;
    protected FactoryInterface $searcher_factory;

    protected GlobalContainer $dic;
    protected SearchServices $search_services;
    protected PathsServices $paths_services;

    public function __construct(
        GlobalContainer $dic,
        SearchServices $repository_services,
        PathsServices $paths_services,
    ) {
        $this->dic = $dic;
        $this->search_services = $repository_services;
        $this->paths_services = $paths_services;
    }

    public function repository(): RepositoryInterface
    {
        if (isset($this->repository)) {
            return $this->repository;
        }
        return $this->repository = new DatabaseRepository(
            new Wrapper($this->dic->database())
        );
    }

    public function renderer(): RendererInterface
    {
        if (isset($this->renderer)) {
            return $this->renderer;
        }
        return $this->renderer = new Renderer(
            $this->dic->ui()->factory(),
            $this->dic->resourceStorage()
        );
    }

    public function identifiersHandler(): HandlerInterface
    {
        if (isset($this->handler)) {
            return $this->handler;
        }
        return $this->handler = new Handler();
    }

    public function searcherFactory(): FactoryInterface
    {
        if (isset($this->searcher_factory)) {
            return $this->searcher_factory;
        }
        return $this->searcher_factory = new Factory(
            $this->search_services->searchFilterFactory(),
            $this->search_services->searchClauseFactory(),
            $this->paths_services->pathFactory(),
            $this->identifiersHandler()
        );
    }
}
