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
use ILIAS\MetaData\Search\Clauses\FactoryInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Search\Filters\Placeholder;

interface SearcherInterface
{
    /**
     * Get a factory where you can assemble your search clause.
     */
    public function getClauseFactory(): FactoryInterface;

    /**
     * Get a filter with which the results of a search can be
     * restricted. You can either give specific values for
     * the three identifying parameters of objects (see
     * {@see \ILIAS\MetaData\Services\ServicesInterface::read()}
     * for a description of what those are), or placeholders
     * to accept either any value or to force two parameters
     * to match.
     */
    public function getFilter(
        int|Placeholder $obj_id = Placeholder::ANY,
        int|Placeholder $sub_id = Placeholder::ANY,
        string|Placeholder $type = Placeholder::ANY
    ): FilterInterface;

    /**
     * Results are always ordered first by obj_id, then sub_id, then type.
     * Multiple filters are joined with a logical OR, values within the
     * same filter with AND.
     * @return RessourceIDInterface[]
     */
    public function execute(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator;
}
