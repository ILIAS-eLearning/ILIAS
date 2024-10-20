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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Search\Clauses\NullFactory as NullClauseFactory;
use ILIAS\MetaData\Search\Filters\NullFactory as NullFilterFactory;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Search\Filters\NullFilter;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Clauses\NullClause;
use ILIAS\MetaData\Search\Filters\Placeholder;

class SearcherTest extends TestCase
{
    public function getSearcher(): SearcherInterface
    {
        $clause_factory = new NullClauseFactory();
        $filter_factory = new class () extends NullFilterFactory {
            public function get(
                int|Placeholder $obj_id = Placeholder::ANY,
                int|Placeholder $sub_id = Placeholder::ANY,
                string|Placeholder $type = Placeholder::ANY
            ): FilterInterface {
                return new class ($obj_id, $sub_id, $type) extends NullFilter {
                    public array $data = [];

                    public function __construct(
                        int|Placeholder $obj_id,
                        int|Placeholder $sub_id,
                        string|Placeholder $type
                    ) {
                        $this->data = [
                            'obj_id' => $obj_id,
                            'sub_id' => $sub_id,
                            'type' => $type
                        ];
                    }
                };
            }
        };
        $repository = new class () extends NullRepository {
            public function searchMD(
                ClauseInterface $clause,
                ?int $limit,
                ?int $offset,
                FilterInterface ...$filters
            ): \Generator {
                yield 'clause' => $clause;
                yield 'limit' => $limit;
                yield 'offset' => $offset;
                yield 'filters' => $filters;
            }
        };

        return new Searcher($clause_factory, $filter_factory, $repository);
    }

    public function testGetFilter(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, 98, 'type');
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => 98, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithSubIDZero(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, 0, 'type');
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => Placeholder::OBJ_ID, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithObjIDPlaceholder(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(Placeholder::ANY, 98, 'type');
        $this->assertSame(
            ['obj_id' => Placeholder::ANY, 'sub_id' => 98, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithSubIDPlaceholder(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, Placeholder::ANY, 'type');
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => Placeholder::ANY, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithTypePlaceholder(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, 98, Placeholder::ANY);
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => 98, 'type' => Placeholder::ANY],
            $filter->data
        );
    }

    public function testExecute(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->execute($clause, null, null));
        $this->assertSame(
            ['clause' => $clause, 'limit' => null, 'offset' => null, 'filters' => []],
            $results
        );
    }

    public function testExecuteWithLimit(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->execute($clause, 999, null));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => null, 'filters' => []],
            $results
        );
    }

    public function testExecuteWithLimitAndOffset(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->execute($clause, 999, 333));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => 333, 'filters' => []],
            $results
        );
    }

    public function testExecuteWithFilters(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();
        $filter_1 = new NullFilter();
        $filter_2 = new NullFilter();
        $filter_3 = new NullFilter();

        $results = iterator_to_array($searcher->execute($clause, 999, 333, $filter_1, $filter_2, $filter_3));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => 333, 'filters' => [$filter_1, $filter_2, $filter_3]],
            $results
        );
    }
}
