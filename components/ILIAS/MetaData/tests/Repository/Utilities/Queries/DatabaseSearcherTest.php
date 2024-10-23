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

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserFactoryInterface;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceIDFactory;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserFactory;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\NullDatabasePathsParserFactory;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserInterface;
use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\NullDatabasePathsParser;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Search\Clauses\NullClause;
use ILIAS\MetaData\Search\Clauses\Properties\BasicPropertiesInterface;
use ILIAS\MetaData\Search\Clauses\Properties\JoinPropertiesInterface;
use ILIAS\MetaData\Search\Clauses\Properties\NullBasicProperties;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Clauses\Properties\NullJoinProperties;
use ILIAS\MetaData\Search\Filters\FilterInterface;
use ILIAS\MetaData\Search\Filters\NullFilter;
use ILIAS\MetaData\Search\Filters\Placeholder;

class DatabaseSearcherTest extends TestCase
{
    protected const RESULT = [
        ['rbac_id' => 37, 'obj_id' => 55, 'obj_type' => 'type1'],
        ['rbac_id' => 123, 'obj_id' => 85, 'obj_type' => 'type2'],
        ['rbac_id' => 98, 'obj_id' => 4, 'obj_type' => 'type3']
    ];

    protected function mockRessourceIDsMatchArrayData(
        array $array,
        RessourceIDInterface ...$ressource_ids
    ): bool {
        $data = [];
        foreach ($ressource_ids as $ressource_id) {
            $data[] = [
                'rbac_id' => $ressource_id->obj_id,
                'obj_id' => $ressource_id->sub_id,
                'obj_type' => $ressource_id->type
            ];
        }

        return $array === $data;
    }

    protected function getDatabaseSearcher(array $db_result): DatabaseSearcher
    {
        $ressource_factory = new class () extends NullRessourceIDFactory {
            public function ressourceID(int $obj_id, int $sub_id, string $type): RessourceIDInterface
            {
                return new class ($obj_id, $sub_id, $type) extends NullRessourceID {
                    public function __construct(
                        public int $obj_id,
                        public int $sub_id,
                        public string $type
                    ) {
                    }
                };
            }
        };

        $paths_parser_factory = new class () extends NullDatabasePathsParserFactory {
            public function forSearch(): DatabasePathsParserInterface
            {
                return new class () extends NullDatabasePathsParser {
                    protected array $paths = [];
                    protected bool $force_join_to_base_table = false;

                    public function addPathAndGetColumn(
                        PathInterface $path,
                        bool $force_join_to_base_table
                    ): string {
                        if (!$this->force_join_to_base_table) {
                            $this->force_join_to_base_table = $force_join_to_base_table;
                        }
                        $path_string = $path->toString();
                        $this->paths[] = $path_string;
                        return $path_string . '_column';
                    }

                    public function getSelectForQuery(): string
                    {
                        if (empty($this->paths)) {
                            throw new \ilMDRepositoryException('no paths!');
                        }
                        return 'selected paths' . ($this->force_join_to_base_table ? ' (join forced)' : '') .
                            ':[' . implode('~', $this->paths) . ']';
                    }

                    public function getTableAliasForFilters(): string
                    {
                        if (empty($this->paths)) {
                            throw new \ilMDRepositoryException('no paths!');
                        }
                        return 'base_table';
                    }
                };
            }
        };

        return new class (
            $ressource_factory,
            $paths_parser_factory,
            $db_result
        ) extends DatabaseSearcher {
            public string $exposed_last_query;

            public function __construct(
                RessourceIDFactoryInterface $ressource_factory,
                DatabasePathsParserFactoryInterface $paths_parser_factory,
                protected array $db_result
            ) {
                $this->ressource_factory = $ressource_factory;
                $this->paths_parser_factory = $paths_parser_factory;
            }

            protected function queryDB(string $query): \Generator
            {
                $this->exposed_last_query = $query;
                yield from $this->db_result;
            }

            protected function quoteIdentifier(string $identifier): string
            {
                return '~identifier:' . $identifier . '~';
            }

            protected function quoteText(string $text): string
            {
                return '~text:' . $text . '~';
            }

            protected function quoteInteger(int $integer): string
            {
                return '~int:' . $integer . '~';
            }
        };
    }

    protected function getBasicClause(
        bool $negated,
        string $path,
        Mode $mode,
        string $value,
        bool $mode_negated
    ): ClauseInterface {
        return new class ($negated, $path, $mode, $value, $mode_negated) extends NullClause {
            public function __construct(
                protected bool $negated,
                protected string $path,
                protected Mode $mode,
                protected string $value,
                protected bool $mode_negated
            ) {
            }

            public function isNegated(): bool
            {
                return $this->negated;
            }

            public function isJoin(): bool
            {
                return false;
            }

            public function basicProperties(): ?BasicPropertiesInterface
            {
                return new class (
                    $this->path,
                    $this->mode,
                    $this->value,
                    $this->mode_negated
                ) extends NullBasicProperties {
                    public function __construct(
                        protected string $path,
                        protected Mode $mode,
                        protected string $value,
                        protected bool $mode_negated
                    ) {
                    }

                    public function path(): PathInterface
                    {
                        return new class ($this->path) extends NullPath {
                            public function __construct(protected string $path)
                            {
                            }

                            public function toString(): string
                            {
                                return $this->path;
                            }
                        };
                    }

                    public function isModeNegated(): bool
                    {
                        return $this->mode_negated;
                    }

                    public function mode(): Mode
                    {
                        return $this->mode;
                    }

                    public function value(): string
                    {
                        return $this->value;
                    }
                };
            }

            public function joinProperties(): ?JoinPropertiesInterface
            {
                return null;
            }
        };
    }

    protected function getJoinedClause(
        bool $negated,
        Operator $operator,
        ClauseInterface ...$clauses
    ): ClauseInterface {
        return new class ($negated, $operator, $clauses) extends NullClause {
            public function __construct(
                protected bool $negated,
                protected Operator $operator,
                protected array $clauses
            ) {
            }

            public function isNegated(): bool
            {
                return $this->negated;
            }

            public function isJoin(): bool
            {
                return true;
            }

            public function basicProperties(): ?BasicPropertiesInterface
            {
                return null;
            }

            public function joinProperties(): ?JoinPropertiesInterface
            {
                return new class ($this->operator, $this->clauses) extends NullJoinProperties {
                    public function __construct(
                        protected Operator $operator,
                        protected array $clauses
                    ) {
                    }

                    public function operator(): Operator
                    {
                        return $this->operator;
                    }

                    public function subClauses(): \Generator
                    {
                        yield from $this->clauses;
                    }
                };
            }
        };
    }

    protected function getFilter(
        int|Placeholder $obj_id,
        int|Placeholder $sub_id,
        string|Placeholder $type
    ): FilterInterface {
        return new class ($obj_id, $sub_id, $type) extends NullFilter {
            public function __construct(
                protected int|Placeholder $obj_id,
                protected int|Placeholder $sub_id,
                protected string|Placeholder $type
            ) {
            }

            public function objID(): int|Placeholder
            {
                return $this->obj_id;
            }

            public function subID(): int|Placeholder
            {
                return $this->sub_id;
            }

            public function type(): string|Placeholder
            {
                return $this->type;
            }
        };
    }

    public function testSearchWithNoResults(): void
    {
        $searcher = $this->getDatabaseSearcher([]);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = $searcher->search($clause, null, null);
        $this->assertNull($result->current());
    }

    public function testSearchWithResults(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = $searcher->search($clause, null, null);
        $this->assertTrue(
            $this->mockRessourceIDsMatchArrayData(self::RESULT, ...$result)
        );
    }

    public function testSearchWithBasicClauseModeEquals(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithBasicClauseModeContains(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::CONTAINS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column LIKE ~text:%value%~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithBasicClauseModeStartsWith(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::STARTS_WITH,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column LIKE ~text:value%~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithBasicClauseModeEndsWith(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::ENDS_WITH,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column LIKE ~text:%value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithBasicClauseNegatedMode(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            true
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN NOT path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithNegatedBasicClause(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            true,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths (join forced):[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING NOT COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }



    public function testSearchWithEmptyValueBasicClause(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            '',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            'selected paths (join forced):[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithORJoinedClauses(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause1 = $this->getBasicClause(
            false,
            'path1',
            Mode::EQUALS,
            'value1',
            false
        );
        $clause2 = $this->getBasicClause(
            false,
            'path2',
            Mode::STARTS_WITH,
            'value2',
            false
        );
        $joined_clause = $this->getJoinedClause(false, Operator::OR, $clause1, $clause2);

        $result = iterator_to_array($searcher->search($joined_clause, null, null));
        $this->assertSame(
            'selected paths:[path1~path2] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING (COUNT(CASE WHEN path1_column = ~text:value1~ THEN 1 END) > 0 ' .
            'OR COUNT(CASE WHEN path2_column LIKE ~text:value2%~ THEN 1 END) > 0) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithANDJoinedClauses(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause1 = $this->getBasicClause(
            false,
            'path1',
            Mode::CONTAINS,
            'value1',
            false
        );
        $clause2 = $this->getBasicClause(
            false,
            'path2',
            Mode::STARTS_WITH,
            'value2',
            false
        );
        $joined_clause = $this->getJoinedClause(false, Operator::AND, $clause1, $clause2);

        $result = iterator_to_array($searcher->search($joined_clause, null, null));
        $this->assertSame(
            'selected paths:[path1~path2] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING (COUNT(CASE WHEN path1_column LIKE ~text:%value1%~ THEN 1 END) > 0 ' .
            'AND COUNT(CASE WHEN path2_column LIKE ~text:value2%~ THEN 1 END) > 0) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithNegatedJoinedClause(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause1 = $this->getBasicClause(
            false,
            'path1',
            Mode::CONTAINS,
            'value1',
            false
        );
        $clause2 = $this->getBasicClause(
            false,
            'path2',
            Mode::EQUALS,
            'value2',
            false
        );
        $joined_clause = $this->getJoinedClause(true, Operator::AND, $clause1, $clause2);

        $result = iterator_to_array($searcher->search($joined_clause, null, null));
        $this->assertSame(
            'selected paths:[path1~path2] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING NOT (COUNT(CASE WHEN path1_column LIKE ~text:%value1%~ THEN 1 END) > 0 ' .
            'AND COUNT(CASE WHEN path2_column = ~text:value2~ THEN 1 END) > 0) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithNestedJoinedClauses(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause1 = $this->getBasicClause(
            false,
            'path1',
            Mode::CONTAINS,
            'value1',
            false
        );
        $clause2 = $this->getBasicClause(
            false,
            'path2',
            Mode::EQUALS,
            'value2',
            false
        );
        $clause3 = $this->getBasicClause(
            false,
            'path3',
            Mode::ENDS_WITH,
            'value3',
            false
        );
        $joined_clause = $this->getJoinedClause(
            false,
            Operator::AND,
            $clause1,
            $this->getJoinedClause(
                true,
                Operator::OR,
                $clause2,
                $clause3
            )
        );

        $result = iterator_to_array($searcher->search($joined_clause, null, null));
        $this->assertSame(
            'selected paths:[path1~path2~path3] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING (COUNT(CASE WHEN path1_column LIKE ~text:%value1%~ THEN 1 END) > 0 ' .
            'AND NOT (COUNT(CASE WHEN path2_column = ~text:value2~ THEN 1 END) > 0 OR ' .
            'COUNT(CASE WHEN path3_column LIKE ~text:%value3~ THEN 1 END) > 0)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithLimit(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, 37, null));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type LIMIT ~int:37~',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithOffset(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, null, 16));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type LIMIT ~int:' . PHP_INT_MAX . '~ OFFSET ~int:16~',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithLimitAndOffset(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );

        $result = iterator_to_array($searcher->search($clause, 37, 16));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type LIMIT ~int:37~ OFFSET ~int:16~',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithEmptyFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::ANY, Placeholder::ANY, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithSingleValueObjIDFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(37, Placeholder::ANY, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.rbac_id = ~int:37~)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithSingleValueSubIDFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::ANY, 15, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.obj_id = ~int:15~)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithSingleValueTypeFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::ANY, Placeholder::ANY, 'some type');

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.obj_type = ~text:some type~)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithMultiValueFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(37, 15, 'some type');

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.rbac_id = ~int:37~ AND ' .
            '~identifier:base_table~.obj_id = ~int:15~ AND ' .
            '~identifier:base_table~.obj_type = ~text:some type~)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithMultipleFilters(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter1 = $this->getFilter(37, 15, Placeholder::ANY);
        $filter2 = $this->getFilter(Placeholder::ANY, 15, 'some type');
        $filter3 = $this->getFilter(37, Placeholder::ANY, 'some type');

        $result = iterator_to_array($searcher->search(
            $clause,
            null,
            null,
            $filter1,
            $filter2,
            $filter3
        ));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.rbac_id = ~int:37~ AND ~identifier:base_table~.obj_id = ~int:15~) ' .
            'OR (~identifier:base_table~.obj_id = ~int:15~ AND ~identifier:base_table~.obj_type = ~text:some type~) ' .
            'OR (~identifier:base_table~.rbac_id = ~int:37~ AND ~identifier:base_table~.obj_type = ~text:some type~)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithObjIDPlaceholderFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::ANY, Placeholder::OBJ_ID, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.obj_id = ~identifier:base_table~.rbac_id)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithSubIDPlaceholderFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::SUB_ID, Placeholder::ANY, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.rbac_id = ~identifier:base_table~.obj_id)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }

    public function testSearchWithTypePlaceholderFilter(): void
    {
        $searcher = $this->getDatabaseSearcher(self::RESULT);
        $clause = $this->getBasicClause(
            false,
            'path',
            Mode::EQUALS,
            'value',
            false
        );
        $filter = $this->getFilter(Placeholder::TYPE, Placeholder::ANY, Placeholder::ANY);

        $result = iterator_to_array($searcher->search($clause, null, null, $filter));
        $this->assertSame(
            'selected paths:[path] GROUP BY ~identifier:base_table~.rbac_id, ' .
            '~identifier:base_table~.obj_id, ~identifier:base_table~.obj_type ' .
            'HAVING COUNT(CASE WHEN path_column = ~text:value~ THEN 1 END) > 0 ' .
            'AND ((~identifier:base_table~.rbac_id = ~identifier:base_table~.obj_type)) ' .
            'ORDER BY rbac_id, obj_id, obj_type',
            $searcher->exposed_last_query
        );
    }
}
