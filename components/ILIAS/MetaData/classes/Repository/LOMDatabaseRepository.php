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

namespace ILIAS\MetaData\Repository;

use ILIAS\MetaData\Repository\Validation\ProcessorInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Utilities\DatabaseManipulatorInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Utilities\DatabaseReaderInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactoryInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\DatabaseSearcherInterface;
use ILIAS\MetaData\Repository\IdentifierHandler\IdentifierHandlerInterface;

class LOMDatabaseRepository implements RepositoryInterface
{
    protected RessourceIDFactoryInterface $ressource_factory;
    protected DatabaseManipulatorInterface $manipulator;
    protected DatabaseReaderInterface $reader;
    protected DatabaseSearcherInterface $searcher;
    protected ProcessorInterface $processor;
    protected IdentifierHandlerInterface $identifier_handler;

    public function __construct(
        RessourceIDFactoryInterface $ressource_factory,
        DatabaseManipulatorInterface $manipulator,
        DatabaseReaderInterface $reader,
        DatabaseSearcherInterface $searcher,
        ProcessorInterface $processor,
        IdentifierHandlerInterface $identifier_handler
    ) {
        $this->ressource_factory = $ressource_factory;
        $this->manipulator = $manipulator;
        $this->reader = $reader;
        $this->searcher = $searcher;
        $this->processor = $processor;
        $this->identifier_handler = $identifier_handler;
    }

    public function getMD(
        int $obj_id,
        int $sub_id,
        string $type
    ): SetInterface {
        return $this->processor->finishAndCleanData(
            $this->reader->getMD(
                $this->ressource_factory->ressourceID($obj_id, $sub_id, $type)
            )
        );
    }

    public function getMDOnPath(
        PathInterface $path,
        int $obj_id,
        int $sub_id,
        string $type
    ): SetInterface {
        return $this->processor->finishAndCleanData(
            $this->reader->getMDOnPath(
                $path,
                $this->ressource_factory->ressourceID($obj_id, $sub_id, $type)
            )
        );
    }

    /**
     * @return RessourceIDInterface[]
     */
    public function searchMD(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator {
        yield from $this->searcher->search($clause, $limit, $offset, ...$filters);
    }

    public function manipulateMD(SetInterface $set): void
    {
        $this->processor->checkMarkers($set);
        $this->manipulator->manipulateMD($set);
    }

    public function transferMD(
        SetInterface $from_set,
        int $to_obj_id,
        int $to_sub_id,
        string $to_type,
        bool $throw_error_if_invalid
    ): void {
        $to_ressource_id = $this->ressource_factory->ressourceID($to_obj_id, $to_sub_id, $to_type);

        if ($throw_error_if_invalid) {
            $this->processor->checkMarkers($from_set);
        } else {
            $this->processor->cleanMarkers($from_set);
        }
        $from_set = $this->identifier_handler->prepareUpdateOfIdentifier($from_set, $to_ressource_id);
        $this->manipulator->deleteAllMD($to_ressource_id);
        $this->manipulator->transferMD($from_set, $to_ressource_id);
    }

    public function deleteAllMD(
        int $obj_id,
        int $sub_id,
        string $type
    ): void {
        $this->manipulator->deleteAllMD(
            $this->ressource_factory->ressourceID($obj_id, $sub_id, $type)
        );
    }
}
