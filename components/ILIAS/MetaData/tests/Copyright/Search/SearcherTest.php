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

namespace ILIAS\MetaData\Copyright\Search;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Search\Filters\FactoryInterface as FilterFactory;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as ClauseFactory;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\NullBuilder;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Search\Clauses\NullFactory as NullClauseFactory;
use ILIAS\MetaData\Search\Filters\NullFactory as NullFilterFactory;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Clauses\NullClause;
use ILIAS\MetaData\Search\Filters\Placeholder;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Search\Filters\NullFilter;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;

class SearcherTest extends TestCase
{
    protected function getRessourceID(int $idx): RessourceIDInterface
    {
        return new class ($idx) extends NullRessourceID {
            public function __construct(public int $idx)
            {
            }
        };
    }

    protected function assertCorrectSearchResults(RessourceIDInterface ...$ids): void
    {
        $this->assertCount(3, $ids);
        $this->assertSame(1, $ids[0]->idx);
        $this->assertSame(2, $ids[1]->idx);
        $this->assertSame(3, $ids[2]->idx);
    }

    protected function getLOMRepository(): RepositoryInterface
    {
        $returns = [
            $this->getRessourceID(1),
            $this->getRessourceID(2),
            $this->getRessourceID(3)
        ];

        return new class ($returns) extends NullRepository {
            public array $exposed_searches = [];

            public function __construct(protected array $returns)
            {
            }

            public function searchMD(
                ClauseInterface $clause,
                ?int $limit,
                ?int $offset,
                FilterInterface ...$filters
            ): \Generator {
                $filter_data = [];
                foreach ($filters as $filter) {
                    $filter_data[] = $filter->exposed_data;
                }
                $this->exposed_searches[] = [
                    'clause' => $clause->exposed_data,
                    'limit' => $limit,
                    'offset' => $offset,
                    'filters' => $filter_data
                ];

                yield from $this->returns;
            }
        };
    }

    protected function getSearchFilterFactory(): FilterFactory
    {
        return new class () extends NullFilterFactory {
            public function get(
                Placeholder|int $obj_id = Placeholder::ANY,
                Placeholder|int $sub_id = Placeholder::ANY,
                Placeholder|string $type = Placeholder::ANY
            ): FilterInterface {
                $exposed = ['obj_id' => $obj_id, 'sub_id' => $sub_id, 'type' => $type];
                return new class ($exposed) extends NullFilter {
                    public function __construct(public array $exposed_data)
                    {
                    }
                };
            }
        };
    }

    protected function getSearchClauseFactory(): ClauseFactory
    {
        return new class () extends NullClauseFactory {
            public function getBasicClause(
                PathInterface $path,
                Mode $mode,
                string $value,
                bool $is_mode_negated = false
            ): ClauseInterface {
                $exposed = [
                    'path' => $path->toString(),
                    'mode' => $mode,
                    'value' => $value,
                    'is_mode_negated' => $is_mode_negated,
                ];
                return new class ($exposed) extends NullClause {
                    public function __construct(public array $exposed_data)
                    {
                    }
                };
            }

            public function getJoinedClauses(
                Operator $operator,
                ClauseInterface $first_clause,
                ClauseInterface ...$further_clauses
            ): ClauseInterface {
                $clause_data = [];
                foreach ([$first_clause, ...$further_clauses] as $clause) {
                    $clause_data[] = $clause->exposed_data;
                }
                $exposed = [
                    'operator' => $operator,
                    'subclauses' => $clause_data
                ];
                return new class ($exposed) extends NullClause {
                    public function __construct(public array $exposed_data)
                    {
                    }
                };
            }
        };
    }

    protected function getPathFactory(): PathFactory
    {
        return new class () extends NullPathFactory {
            public function custom(): BuilderInterface
            {
                return new class () extends NullBuilder {
                    protected array $path = [];

                    public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
                    {
                        $clone = clone $this;
                        $clone->path[] = $name;
                        return $clone;
                    }

                    public function get(): PathInterface
                    {
                        $string = implode('>', $this->path);
                        return new class ($string) extends NullPath {
                            public function __construct(protected string $string)
                            {
                            }

                            public function toString(): string
                            {
                                return $this->string;
                            }
                        };
                    }
                };
            }
        };
    }

    protected function getIdentifierHandler(): IdentifierHandler
    {
        return new class () extends NullHandler {
            public function buildIdentifierFromEntryID(int $entry_id): string
            {
                return 'identifier_' . $entry_id;
            }
        };
    }

    public function testSearch(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->search($repo = $this->getLOMRepository(), 32);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                'clause' => [
                    'operator' => Operator::OR,
                    'subclauses' => [[
                        'path' => 'rights>description>string',
                        'mode' => Mode::EQUALS,
                        'value' => 'identifier_32',
                        'is_mode_negated' => false
                    ]]
                ],
                'limit' => null,
                'offset' => null,
                'filters' => []
            ]],
            $repo->exposed_searches
        );
    }

    public function testSearchWithMultipleEntries(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->search($repo = $this->getLOMRepository(), 32, 9, 1234);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                 'clause' => [
                     'operator' => Operator::OR,
                     'subclauses' => [
                         [
                             'path' => 'rights>description>string',
                             'mode' => Mode::EQUALS,
                             'value' => 'identifier_32',
                             'is_mode_negated' => false
                         ],
                         [
                             'path' => 'rights>description>string',
                             'mode' => Mode::EQUALS,
                             'value' => 'identifier_9',
                             'is_mode_negated' => false
                         ],
                         [
                             'path' => 'rights>description>string',
                             'mode' => Mode::EQUALS,
                             'value' => 'identifier_1234',
                             'is_mode_negated' => false
                         ]
                     ]
                 ],
                 'limit' => null,
                 'offset' => null,
                 'filters' => []
             ]],
            $repo->exposed_searches
        );
    }

    public function testSearchRestrictedToRepositoryObjects(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->withRestrictionToRepositoryObjects(true)
                            ->search($repo = $this->getLOMRepository(), 32);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                 'clause' => [
                     'operator' => Operator::OR,
                     'subclauses' => [[
                         'path' => 'rights>description>string',
                         'mode' => Mode::EQUALS,
                         'value' => 'identifier_32',
                         'is_mode_negated' => false
                     ]]
                 ],
                 'limit' => null,
                 'offset' => null,
                 'filters' => [
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::OBJ_ID,
                         'type' => Placeholder::ANY
                     ]
                 ]
             ]],
            $repo->exposed_searches
        );
    }

    public function testSearchRestrictedToObjectType(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->withAdditionalTypeFilter('some type')
                            ->search($repo = $this->getLOMRepository(), 32);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                 'clause' => [
                     'operator' => Operator::OR,
                     'subclauses' => [[
                         'path' => 'rights>description>string',
                         'mode' => Mode::EQUALS,
                         'value' => 'identifier_32',
                         'is_mode_negated' => false
                     ]]
                 ],
                 'limit' => null,
                 'offset' => null,
                 'filters' => [
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::ANY,
                         'type' => 'some type'
                     ]
                 ]
             ]],
            $repo->exposed_searches
        );
    }

    public function testSearchRestrictedToMultipleObjectTypes(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->withAdditionalTypeFilter('some type')
                            ->withAdditionalTypeFilter('some other type')
                            ->withAdditionalTypeFilter('a third type')
                            ->search($repo = $this->getLOMRepository(), 32);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                 'clause' => [
                     'operator' => Operator::OR,
                     'subclauses' => [[
                         'path' => 'rights>description>string',
                         'mode' => Mode::EQUALS,
                         'value' => 'identifier_32',
                         'is_mode_negated' => false
                     ]]
                 ],
                 'limit' => null,
                 'offset' => null,
                 'filters' => [
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::ANY,
                         'type' => 'some type'
                     ],
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::ANY,
                         'type' => 'some other type'
                     ],
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::ANY,
                         'type' => 'a third type'
                     ]
                 ]
             ]],
            $repo->exposed_searches
        );
    }

    public function testSearchRestrictedToMultipleObjectTypesAndRepositoryObjects(): void
    {
        $searcher = new Searcher(
            $this->getSearchFilterFactory(),
            $this->getSearchClauseFactory(),
            $this->getPathFactory(),
            $this->getIdentifierHandler()
        );

        $results = $searcher->withAdditionalTypeFilter('some type')
                            ->withAdditionalTypeFilter('some other type')
                            ->withAdditionalTypeFilter('a third type')
                            ->withRestrictionToRepositoryObjects(true)
                            ->search($repo = $this->getLOMRepository(), 32);

        $this->assertCorrectSearchResults(...$results);
        $this->assertEquals(
            [[
                 'clause' => [
                     'operator' => Operator::OR,
                     'subclauses' => [[
                         'path' => 'rights>description>string',
                         'mode' => Mode::EQUALS,
                         'value' => 'identifier_32',
                         'is_mode_negated' => false
                     ]]
                 ],
                 'limit' => null,
                 'offset' => null,
                 'filters' => [
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::OBJ_ID,
                         'type' => 'some type'
                     ],
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::OBJ_ID,
                         'type' => 'some other type'
                     ],
                     [
                         'obj_id' => Placeholder::ANY,
                         'sub_id' => Placeholder::OBJ_ID,
                         'type' => 'a third type'
                     ]
                 ]
             ]],
            $repo->exposed_searches
        );
    }
}
