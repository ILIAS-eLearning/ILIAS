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

namespace ILIAS\MetaData\Search\Clauses;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Search\Clauses\Properties\JoinProperties;
use ILIAS\MetaData\Search\Clauses\Properties\BasicProperties;
use ILIAS\MetaData\Paths\Steps\NullStep;
use ILIAS\MetaData\Paths\PathInterface;

class ClauseWithPropertiesAndFactoryTest extends TestCase
{
    protected function getNonEmptyPath(): PathInterface
    {
        return new class () extends NullPath {
            public function steps(): \Generator
            {
                yield new NullStep();
            }
        };
    }

    public function testGetBasicClause(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause($this->getNonEmptyPath(), Mode::CONTAINS, 'value');

        $this->assertFalse($basic_clause->isJoin());
        $this->assertNull($basic_clause->joinProperties());
        $this->assertNotNull($basic_clause->basicProperties());
    }

    public function testGetBasicClauseEmptyPathException(): void
    {
        $factory = new Factory();

        $this->expectException(\ilMDRepositoryException::class);
        $basic_clause = $factory->getBasicClause(new NullPath(), Mode::CONTAINS, 'value');
    }

    public function testGetBasicClauseNotNegated(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause($this->getNonEmptyPath(), Mode::CONTAINS, 'value');

        $this->assertFalse($basic_clause->isNegated());
    }

    public function testBasicClausePath(): void
    {
        $factory = new Factory();
        $path = $this->getNonEmptyPath();
        $basic_clause = $factory->getBasicClause($path, Mode::CONTAINS, 'value');
        $this->assertSame($path, $basic_clause->basicProperties()->path());
    }

    public function testBasicClauseMode(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause($this->getNonEmptyPath(), Mode::CONTAINS, 'value');
        $this->assertSame(Mode::CONTAINS, $basic_clause->basicProperties()->mode());
    }

    public function testBasicClauseNegatedModeTrue(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause(
            $this->getNonEmptyPath(),
            Mode::CONTAINS,
            'value',
            true
        );
        $this->assertTrue($basic_clause->basicProperties()->isModeNegated());
    }

    public function testBasicClauseNegatedModeDefaultFalse(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause(
            $this->getNonEmptyPath(),
            Mode::CONTAINS,
            'value'
        );
        $this->assertFalse($basic_clause->basicProperties()->isModeNegated());
    }

    public function testBasicClauseValue(): void
    {
        $factory = new Factory();
        $basic_clause = $factory->getBasicClause($this->getNonEmptyPath(), Mode::CONTAINS, 'value');
        $this->assertSame('value', $basic_clause->basicProperties()->value());
    }

    public function testGetNegatedClause(): void
    {
        $factory = new Factory();
        $join_props = new JoinProperties(Operator::AND, new NullClause(), new NullClause());
        $basic_props = new BasicProperties(
            $this->getNonEmptyPath(),
            Mode::ENDS_WITH,
            'value',
            false
        );
        $clause = new Clause(false, true, $join_props, $basic_props);

        $negated = $factory->getNegatedClause($clause);

        $this->assertTrue($negated->isNegated());
        $this->assertTrue($negated->isJoin());
        $this->assertSame($basic_props, $negated->basicProperties());
        $this->assertSame($join_props, $negated->joinProperties());
    }

    public function testNegateNegatedClause(): void
    {
        $factory = new Factory();
        $join_props = new JoinProperties(Operator::AND, new NullClause(), new NullClause());
        $basic_props = new BasicProperties(
            $this->getNonEmptyPath(),
            Mode::ENDS_WITH,
            'value',
            false
        );
        $clause = new Clause(true, true, $join_props, $basic_props);

        $negated = $factory->getNegatedClause($clause);

        $this->assertFalse($negated->isNegated());
        $this->assertTrue($negated->isJoin());
        $this->assertSame($basic_props, $negated->basicProperties());
        $this->assertSame($join_props, $negated->joinProperties());
    }

    public function testGetJoinedClauses(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $clause_2 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(Operator::OR, $clause_1, $clause_2);

        $this->assertTrue($joined_clause->isJoin());
        $this->assertNull($joined_clause->basicProperties());
        $this->assertNotNull($joined_clause->joinProperties());
    }

    public function testGetJoinedClausesWithOneClause(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(Operator::OR, $clause_1);

        $this->assertSame($clause_1, $joined_clause);
    }


    public function testGetJoinedClausesNotNegated(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $clause_2 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(Operator::OR, $clause_1, $clause_2);

        $this->assertFalse($joined_clause->isNegated());
    }

    public function testJoinedClauseOperator(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $clause_2 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(Operator::OR, $clause_1, $clause_2);

        $this->assertSame(Operator::OR, $joined_clause->joinProperties()->operator());
    }

    public function testJoinedClauseSubClausesWithTwo(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $clause_2 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(Operator::OR, $clause_1, $clause_2);

        $sub_clauses = iterator_to_array($joined_clause->joinProperties()->subClauses());
        $this->assertSame([$clause_1, $clause_2], $sub_clauses);
    }

    public function testJoinedClauseSubClausesWithMoreThanTwo(): void
    {
        $factory = new Factory();
        $clause_1 = new NullClause();
        $clause_2 = new NullClause();
        $clause_3 = new NullClause();
        $clause_4 = new NullClause();
        $joined_clause = $factory->getJoinedClauses(
            Operator::OR,
            $clause_1,
            $clause_2,
            $clause_3,
            $clause_4
        );

        $sub_clauses = iterator_to_array($joined_clause->joinProperties()->subClauses());
        $this->assertSame(
            [$clause_1, $clause_2, $clause_3, $clause_4],
            $sub_clauses
        );
    }
}
