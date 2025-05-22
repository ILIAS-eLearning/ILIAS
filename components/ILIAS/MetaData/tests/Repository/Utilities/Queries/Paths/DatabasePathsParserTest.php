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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Paths;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Navigator\StructureNavigatorInterface;
use ILIAS\MetaData\Paths\Navigator\NullStructureNavigator;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Paths\Steps\NullStep;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Elements\Data\Type as DataType;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Repository\Dictionary\NullTag;
use ILIAS\MetaData\Vocabularies\Factory\FactoryInterface as LOMVocabInitiator;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Paths\Filters\FilterInterface as PathFilter;
use ILIAS\MetaData\Paths\Filters\NullFilter as NullPathFilter;
use ILIAS\MetaData\Elements\Data\Type;

class DatabasePathsParserTest extends TestCase
{
    protected function getDatabasePathsParser(): DatabasePathsParser
    {
        return new class () extends DatabasePathsParser {
            public function __construct()
            {
            }

            protected function getNavigatorForPath(PathInterface $path): StructureNavigatorInterface
            {
                return new class ($path) extends NullStructureNavigator {
                    protected array $steps;

                    public function __construct(PathInterface $path)
                    {
                        $this->steps = iterator_to_array($path->steps());
                    }

                    public function hasNextStep(): bool
                    {
                        return count($this->steps) > 1;
                    }

                    public function nextStep(): ?StructureNavigatorInterface
                    {
                        if (!$this->hasNextStep()) {
                            return null;
                        }
                        $clone = clone $this;
                        array_shift($clone->steps);
                        return $clone;
                    }

                    public function currentStep(): ?StepInterface
                    {
                        return $this->steps[0];
                    }
                };
            }

            protected function getTagForCurrentStepOfNavigator(
                StructureNavigatorInterface $navigator
            ): ?TagInterface {
                return $navigator->currentStep()->tag;
            }

            protected function getDataTypeForCurrentStepOfNavigator(StructureNavigatorInterface $navigator): DataType
            {
                return $navigator->currentStep()->tag->data_type;
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

            protected function checkTable(string $table): void
            {
                if ($table === 'WRONG') {
                    throw new \ilMDRepositoryException('Invalid MD table: ' . $table);
                }
            }

            protected function table(string $table): ?string
            {
                return $table === 'WRONG' ? null : $table . '_name';
            }

            protected function IDName(string $table): ?string
            {
                return $table === 'WRONG' ? null : $table . '_id';
            }
        };
    }

    protected function getPath(TagInterface ...$tags): PathInterface
    {
        array_unshift(
            $tags,
            $this->getTag('', '', '', 'root'),
        );

        return new class ($tags) extends NullPath {
            public function __construct(protected array $tags)
            {
            }

            public function steps(): \Generator
            {
                foreach ($this->tags as $tag) {
                    yield new class ($tag) extends NullStep {
                        public function __construct(public TagInterface $tag)
                        {
                        }

                        public function name(): string|StepToken
                        {
                            return $this->tag->step_name;
                        }

                        public function filters(): \Generator
                        {
                            yield from $this->tag->filters;
                        }
                    };
                }
            }

            public function toString(): string
            {
                $string = '@';
                foreach ($this->tags as $tag) {
                    $step_name = is_string($tag->step_name) ? $tag->step_name : $tag->step_name->value;
                    $string .= '.' . $step_name;
                    foreach ($tag->filters as $filter) {
                        $string .= ':' . $filter->type()->value;
                        foreach ($filter->values() as $value) {
                            $string .= '~' . $value;
                        }
                    }
                }
                return $string;
            }
        };
    }

    /**
     * To build mock-paths I start from the tags I want the mock-dictionary
     * to return at that step. Kind of backwards, but turned out the most
     * convenient here.
     */
    protected function getTag(
        string $table,
        string $parent,
        string $data_field,
        string|StepToken $step_name,
        DataType $data_type = DataType::STRING,
        PathFilter ...$filters,
    ): TagInterface {
        return new class ($table, $parent, $data_field, $step_name, $data_type, $filters) extends NullTag {
            public function __construct(
                protected string $table,
                protected string $parent,
                protected string $data_field,
                public string|StepToken $step_name,
                public DataType $data_type,
                public array $filters
            ) {
            }

            public function table(): string
            {
                return $this->table;
            }

            public function hasParent(): bool
            {
                return $this->parent !== '';
            }

            public function parent(): string
            {
                return $this->parent;
            }

            public function hasData(): bool
            {
                return $this->data_field !== '';
            }

            public function dataField(): string
            {
                return $this->data_field;
            }
        };
    }

    protected function getPathFilter(
        FilterType $type,
        string ...$values
    ): PathFilter {
        return new class ($type, $values) extends NullPathFilter {
            public function __construct(
                protected FilterType $type,
                protected array $values
            ) {
            }

            public function type(): FilterType
            {
                return $this->type;
            }

            public function values(): \Generator
            {
                yield from $this->values;
            }
        };
    }

    public function testGetTableAliasForFilters(): void
    {
        $parser = $this->getDatabasePathsParser();
        $parser->addPathAndGetColumn(
            $this->getPath($this->getTag('table', '', '', 'step')),
            false
        );

        $this->assertSame('p1t1', $parser->getTableAliasForFilters());
    }

    public function testGetTableAliasForFiltersNoPathsException(): void
    {
        $parser = $this->getDatabasePathsParser();

        $this->expectException(\ilMDRepositoryException::class);
        $parser->getTableAliasForFilters();
    }

    public function testPathAndGetColumnWrongTableException(): void
    {
        $parser = $this->getDatabasePathsParser();

        $this->expectException(\ilMDRepositoryException::class);
        $parser->addPathAndGetColumn(
            $this->getPath($this->getTag('WRONG', '', '', 'step')),
            false
        );
    }

    public function testGetSelectForQueryNoPathsException(): void
    {
        $parser = $this->getDatabasePathsParser();

        $this->expectException(\ilMDRepositoryException::class);
        $parser->getSelectForQuery();
    }

    public function testGetSelectForQueryWithSinglePathAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', '', 'step1'),
                $this->getTag('table', '', 'data', 'step2')
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t1~.~identifier:data~, '')",
            $data_column
        );
        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table_name~ AS ~identifier:p1t1~',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithSinglePathAddedAndForcedJoin(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', '', 'step1'),
                $this->getTag('table', '', 'data', 'step2')
            ),
            true
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t1~.~identifier:data~, '')",
            $data_column
        );
        $this->assertSame(
            'SELECT ~identifier:base~.rbac_id, ~identifier:base~.obj_id, ~identifier:base~.obj_type ' .
            'FROM il_meta_general AS base LEFT JOIN (~identifier:table_name~ AS ~identifier:p1t1~) ' .
            'ON ~identifier:base~.rbac_id = ~identifier:p1t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p1t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p1t1~.obj_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithSinglePathAcrossMultipleTablesAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2'),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table2', 'table1', 'data', 'step4')
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t2~.~identifier:data~, '')",
            $data_column
        );
        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathToElementWithoutValueAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', '', 'step1'),
                $this->getTag('table', '', '', 'step2')
            ),
            false
        );

        $this->assertSame(
            '~text:~',
            $data_column
        );
        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table_name~ AS ~identifier:p1t1~',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithSinglePathAddedMultipleTimes(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column_1 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', '', 'step1'),
                $this->getTag('table', '', 'data', 'step2')
            ),
            false
        );
        $data_column_2 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', '', 'step1'),
                $this->getTag('table', '', 'data', 'step2')
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t1~.~identifier:data~, '')",
            $data_column_1
        );
        $this->assertSame(
            "COALESCE(~identifier:p1t1~.~identifier:data~, '')",
            $data_column_2
        );
        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table_name~ AS ~identifier:p1t1~',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithMultiplePathsAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column_1 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2'),
                $this->getTag('table2', 'table2', '', 'step3'),
                $this->getTag('table2', 'table2', 'data1', 'step4')
            ),
            false
        );
        $data_column_2 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', 'data2', 'step2'),
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t2~.~identifier:data1~, '')",
            $data_column_1
        );
        $this->assertSame(
            "COALESCE(~identifier:p2t1~.~identifier:data2~, '')",
            $data_column_2
        );
        $this->assertSame(
            'SELECT ~identifier:base~.rbac_id, ~identifier:base~.obj_id, ~identifier:base~.obj_type ' .
            'FROM il_meta_general AS base LEFT JOIN (' .
            '~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table2~ = ~identifier:p1t2~.parent_type) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p1t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p1t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p1t1~.obj_type LEFT JOIN ' .
            '(~identifier:table1_name~ AS ~identifier:p2t1~) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p2t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p2t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p2t1~.obj_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithMultiplePathsAddedForceJoin(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column_1 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2'),
                $this->getTag('table2', 'table2', '', 'step3'),
                $this->getTag('table2', 'table2', 'data1', 'step4')
            ),
            true
        );
        $data_column_2 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', 'data2', 'step2'),
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t2~.~identifier:data1~, '')",
            $data_column_1
        );
        $this->assertSame(
            "COALESCE(~identifier:p2t1~.~identifier:data2~, '')",
            $data_column_2
        );
        $this->assertSame(
            'SELECT ~identifier:base~.rbac_id, ~identifier:base~.obj_id, ~identifier:base~.obj_type ' .
            'FROM il_meta_general AS base LEFT JOIN (' .
            '~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table2~ = ~identifier:p1t2~.parent_type) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p1t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p1t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p1t1~.obj_type LEFT JOIN ' .
            '(~identifier:table1_name~ AS ~identifier:p2t1~) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p2t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p2t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p2t1~.obj_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithStepsToSuperElementsAcrossTablesAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2'),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table1', '', 'data1', StepToken::SUPER),
                $this->getTag('table2', 'table1', 'data2', 'step5')
            ),
            false
        );

        $this->assertSame(
            "COALESCE(~identifier:p1t3~.~identifier:data2~, '')",
            $data_column
        );
        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t3~ ON ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type AND ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t3~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t3~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t3~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t3~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t3~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithMDIDFilterAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::MDID,
            '13'
        );
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2', DataType::STRING, $filter),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table2', 'table1', 'data', 'step4')
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            '~identifier:p1t1~.~identifier:table1_id~ IN (~int:13~) AND ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithDataFilterAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::DATA,
            'some data'
        );
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', 'filter_data', 'step2', DataType::STRING, $filter),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table2', 'table1', 'data', 'step4')
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            "COALESCE(~identifier:p1t1~.~identifier:filter_data~, '') IN (~text:some data~) AND " .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithIndexFilterAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::INDEX,
            '2'
        );
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', '', 'step2', DataType::STRING, $filter),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table2', 'table1', 'data', 'step4')
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithMultiValueFilterAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::DATA,
            'some data',
            'some other data',
            'more'
        );
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', 'filter_data', 'step2', DataType::STRING, $filter),
                $this->getTag('table2', 'table1', '', 'step3'),
                $this->getTag('table2', 'table1', 'data', 'step4')
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table1_name~ AS ~identifier:p1t1~ JOIN ' .
            '~identifier:table2_name~ AS ~identifier:p1t2~ ON ' .
            "COALESCE(~identifier:p1t1~.~identifier:filter_data~, '') " .
            'IN (~text:some data~, ~text:some other data~, ~text:more~) AND ' .
            '~identifier:p1t1~.rbac_id = ~identifier:p1t2~.rbac_id AND ' .
            '~identifier:p1t1~.obj_id = ~identifier:p1t2~.obj_id AND ' .
            '~identifier:p1t1~.obj_type = ~identifier:p1t2~.obj_type AND ' .
            'p1t1.~identifier:table1_id~ = ~identifier:p1t2~.parent_id AND ' .
            '~text:table1~ = ~identifier:p1t2~.parent_type',
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithFilterOnOnlyTableAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::DATA,
            'some data'
        );
        $data_column = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', 'filter_data', 'step1', DataType::STRING, $filter),
                $this->getTag('table', '', 'data', 'step2'),
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:p1t1~.rbac_id, ~identifier:p1t1~.obj_id, ~identifier:p1t1~.obj_type ' .
            'FROM ~identifier:table_name~ AS ~identifier:p1t1~ WHERE ' .
            "COALESCE(~identifier:p1t1~.~identifier:filter_data~, '') IN (~text:some data~)",
            $parser->getSelectForQuery()
        );
    }

    public function testGetSelectForQueryWithPathWithFilterOnOnlyTableButMultiplePathsAdded(): void
    {
        $parser = $this->getDatabasePathsParser();
        $filter = $this->getPathFilter(
            FilterType::DATA,
            'some data'
        );
        $data_column_1 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table', '', 'filter_data', 'step1', DataType::STRING, $filter),
                $this->getTag('table', '', 'data', 'step2'),
            ),
            false
        );
        $data_column_2 = $parser->addPathAndGetColumn(
            $this->getPath(
                $this->getTag('table1', '', '', 'step1'),
                $this->getTag('table1', '', 'data2', 'step2'),
            ),
            false
        );

        $this->assertSame(
            'SELECT ~identifier:base~.rbac_id, ~identifier:base~.obj_id, ~identifier:base~.obj_type ' .
            'FROM il_meta_general AS base LEFT JOIN (' .
            '~identifier:table_name~ AS ~identifier:p1t1~) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p1t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p1t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p1t1~.obj_type AND ' .
            "COALESCE(~identifier:p1t1~.~identifier:filter_data~, '') IN (~text:some data~) LEFT JOIN " .
            '(~identifier:table1_name~ AS ~identifier:p2t1~) ON ' .
            '~identifier:base~.rbac_id = ~identifier:p2t1~.rbac_id AND ' .
            '~identifier:base~.obj_id = ~identifier:p2t1~.obj_id AND ' .
            '~identifier:base~.obj_type = ~identifier:p2t1~.obj_type',
            $parser->getSelectForQuery()
        );
    }
}
