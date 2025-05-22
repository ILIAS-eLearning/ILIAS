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

namespace ILIAS\MetaData\Services\Search;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as ClauseFactory;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Search\Filters\FactoryInterface as FilterFactory;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Search\Filters\Placeholder;

class Searcher implements SearcherInterface
{
    protected ClauseFactory $clause_factory;
    protected FilterFactory $filter_factory;
    protected RepositoryInterface $repository;

    public function __construct(
        ClauseFactory $clause_factory,
        FilterFactory $filter_factory,
        RepositoryInterface $repository
    ) {
        $this->clause_factory = $clause_factory;
        $this->filter_factory = $filter_factory;
        $this->repository = $repository;
    }

    public function getClauseFactory(): ClauseFactory
    {
        return $this->clause_factory;
    }

    public function getFilter(
        int|Placeholder $obj_id = Placeholder::ANY,
        int|Placeholder $sub_id = Placeholder::ANY,
        string|Placeholder $type = Placeholder::ANY
    ): FilterInterface {
        if ($sub_id === 0) {
            $sub_id = Placeholder::OBJ_ID;
        }
        return $this->filter_factory->get($obj_id, $sub_id, $type);
    }

    /**
     * @return RessourceIDInterface[]
     */
    public function execute(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator {
        yield from $this->repository->searchMD(
            $clause,
            $limit,
            $offset,
            ...$filters
        );
    }
}
