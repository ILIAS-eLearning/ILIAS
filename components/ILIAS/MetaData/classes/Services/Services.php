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

namespace ILIAS\MetaData\Services;

use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Services\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Services\DataHelper\DataHelper;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Services\Paths\PathsInterface;
use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Reader\Reader;
use ILIAS\MetaData\Services\Paths\Paths;
use ILIAS\MetaData\Services\Manipulator\Manipulator;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Services\Derivation\SourceSelectorInterface;
use ILIAS\MetaData\Services\Derivation\SourceSelector;
use ILIAS\MetaData\Services\Search\SearcherInterface;
use ILIAS\MetaData\Services\Search\Searcher;
use ILIAS\MetaData\Services\Reader\FactoryInterface as ReaderFactoryInterface;
use ILIAS\MetaData\Services\Reader\Factory as ReaderFactory;
use ILIAS\MetaData\Services\Manipulator\FactoryInterface as ManipulatorFactoryInterface;
use ILIAS\MetaData\Services\Manipulator\Factory as ManipulatorFactory;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Services\Derivation\Creation\Creator;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldFactory;
use ILIAS\MetaData\Elements\Data\DataFactory;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactory;
use ILIAS\MetaData\Services\CopyrightHelper\CopyrightHelperInterface;
use ILIAS\MetaData\Services\CopyrightHelper\CopyrightHelper;

class Services implements ServicesInterface
{
    protected InternalServices $internal_services;

    protected ReaderFactoryInterface $reader_factory;
    protected ManipulatorFactoryInterface $manipulator_factory;
    protected PathsInterface $paths;
    protected DataHelperInterface $data_helper;
    protected SourceSelectorInterface $derivation_source_selector;
    protected SearcherInterface $searcher;
    protected CopyrightHelperInterface $copyright_helper;

    public function __construct(GlobalContainer $dic)
    {
        $this->internal_services = new InternalServices($dic);
    }

    public function read(
        int $obj_id,
        int $sub_id,
        string $type,
        ?PathInterface $limited_to = null
    ): ReaderInterface {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $repo = $this->repository();
        if (isset($limited_to)) {
            $set = $repo->getMDOnPath($limited_to, $obj_id, $sub_id, $type);
        } else {
            $set = $repo->getMD($obj_id, $sub_id, $type);
        }
        return $this->readerFactory()->get($set);
    }

    public function search(): SearcherInterface
    {
        if (isset($this->searcher)) {
            return $this->searcher;
        }
        return $this->searcher = new Searcher(
            $this->internal_services->search()->searchClauseFactory(),
            $this->internal_services->search()->searchFilterFactory(),
            $this->internal_services->repository()->repository()
        );
    }

    public function manipulate(int $obj_id, int $sub_id, string $type): ManipulatorInterface
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $repo = $this->repository();
        $set = $repo->getMD($obj_id, $sub_id, $type);
        return $this->manipulatorFactory()->get($set);
    }

    public function derive(): SourceSelectorInterface
    {
        if (isset($this->derivation_source_selector)) {
            return $this->derivation_source_selector;
        }
        return $this->derivation_source_selector = new SourceSelector(
            $this->internal_services->repository()->repository(),
            new Creator(
                $this->internal_services->manipulator()->manipulator(),
                $this->internal_services->paths()->pathFactory(),
                $this->internal_services->manipulator()->scaffoldProvider()
            )
        );
    }

    public function deleteAll(int $obj_id, int $sub_id, string $type): void
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $repo = $this->repository();
        $repo->deleteAllMD($obj_id, $sub_id, $type);
    }

    public function paths(): PathsInterface
    {
        if (isset($this->paths)) {
            return $this->paths;
        }
        return $this->paths = new Paths(
            $this->internal_services->paths()->pathFactory()
        );
    }

    public function dataHelper(): DataHelperInterface
    {
        if (isset($this->data_helper)) {
            return $this->data_helper;
        }
        return $this->data_helper = new DataHelper(
            $this->internal_services->dataHelper()->dataHelper(),
            $this->internal_services->presentation()->data()
        );
    }

    public function copyrightHelper(): CopyrightHelperInterface
    {
        if (isset($this->copyright_helper)) {
            return $this->copyright_helper;
        }
        return $this->copyright_helper = new CopyrightHelper(
            \ilMDSettings::_getInstance(),
            $this->internal_services->paths()->pathFactory(),
            $this->internal_services->copyright()->repository(),
            $this->internal_services->copyright()->identifiersHandler(),
            $this->internal_services->copyright()->renderer(),
            $this->internal_services->search()->searchClauseFactory()
        );
    }

    protected function readerFactory(): ReaderFactoryInterface
    {
        if (isset($this->reader_factory)) {
            return $this->reader_factory;
        }
        return $this->reader_factory = new ReaderFactory(
            $this->internal_services->paths()->navigatorFactory()
        );
    }

    protected function manipulatorFactory(): ManipulatorFactoryInterface
    {
        if (isset($this->manipulator_factory)) {
            return $this->manipulator_factory;
        }
        return $this->manipulator_factory = new ManipulatorFactory(
            $this->internal_services->manipulator()->manipulator(),
            $this->internal_services->repository()->repository()
        );
    }

    protected function repository(): RepositoryInterface
    {
        return $this->internal_services->repository()->repository();
    }
}
