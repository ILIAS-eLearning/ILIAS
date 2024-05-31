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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Test\Repository\QuestionRepository;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuestionRepositoryTest extends TestCase
{
    private readonly ilDBInterface|MockObject $database;
    private readonly ilComponentRepository|MockObject $component_repository;
    private readonly ilLanguage|MockObject $lng;
    private readonly Refinery|MockObject $reinery;
    private readonly QuestionRepository $repository;

    /**
     * @throws MockObjectException
     */
    protected function setUp(): void
    {
        $this->database = $this->createMock(ilDBInterface::class);
        $this->component_repository = $this->createMock(ilComponentRepository::class);
        $this->lng = $this->createMock(ilLanguage::class);
        $this->reinery = $this->createMock(Refinery::class);

        $this->repository = new QuestionRepository(
            $this->database,
            $this->component_repository,
            $this->lng,
            $this->reinery
        );
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(QuestionRepository::class, $this->repository);
    }

    /**
     * @dataProvider provideFindByFilterDataProvider
     */
    public function testFindByFilter(
        array $filter,
        array $raw_data,
        int $expected_count,
        array $expected_query_parts,
        array $unexpected_query_parts
    ): void {
        $this->database
            ->expects($this->once())
            ->method('query')
            ->willReturnCallback(function (string $query) use ($raw_data, $expected_query_parts, $unexpected_query_parts) {
                $this->assertQueryContains($query, $expected_query_parts);
                $this->assertQueryNotContains($query, $unexpected_query_parts);

                $il_db_statement = $this->createMock(ilDBStatement::class);
                $il_db_statement
                    ->expects($this->exactly(count($raw_data)))
                    ->method('fetchAssoc')
                    ->willReturnOnConsecutiveCalls(...$raw_data);

                return $il_db_statement;
            });

        $result = $this->repository->findByFilter($filter);

        $this->assertIsIterable($result);
        $this->assertCount($expected_count, iterator_to_array($result, false));
    }

    public static function provideFindByFilterDataProvider(): array
    {
        return [
            '' => [[], [], 0, [], []]
        ];
    }

    private function assertQueryContains(string $query, array $expectedQueryParts): void
    {
        foreach ($expectedQueryParts as $part) {
            $this->assertStringContainsString($part, $query);
        }
    }

    private function assertQueryNotContains(string $query, array $unexpectedQueryParts): void
    {
        foreach ($unexpectedQueryParts as $part) {
            $this->assertStringNotContainsString($part, $query);
        }
    }
}
