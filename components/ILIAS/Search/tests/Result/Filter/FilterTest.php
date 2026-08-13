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

namespace ILIAS\Search\Result\Filter;

use PHPUnit\Framework\TestCase;
use ILIAS\Search\Result\Filter\Criterion\Criterion;
use PHPUnit\Framework\Attributes\TestWith;

class FilterTest extends TestCase
{
    public function getEvenNumberCriterion(): Criterion
    {
        return new class () implements Criterion {
            public int $does_fulfill_count = 0;

            public function doesFulfill(int $id): bool
            {
                $this->does_fulfill_count++;
                return $id % 2 === 0;
            }

            public function preloadData(int ...$ids): void
            {
            }
        };
    }

    #[TestWith([
        'offset' => 0,
        'limit' => 2,
        'ids' => [1, 2, 3, 4],
        'expected_filtered_ids' => [2, 4]
    ], 'exactly as much data as needed')]
    #[TestWith([
        'offset' => 0,
        'limit' => 2,
        'ids' => [1, 2, 3, 4, 5, 6, 7],
        'expected_filtered_ids' => [2, 4]
    ], 'with more data than needed')]
    #[TestWith([
        'offset' => 0,
        'limit' => 2,
        'ids' => [1, 2, 3],
        'expected_filtered_ids' => [2]
    ], 'with less data than needed')]
    #[TestWith([
        'offset' => 1,
        'limit' => 2,
        'ids' => [1, 2, 3, 4, 5, 6],
        'expected_filtered_ids' => [4, 6]
    ], 'with offset and exactly as much data as needed')]
    #[TestWith([
        'offset' => 1,
        'limit' => 2,
        'ids' => [1, 2, 3, 4, 5, 6, 7],
        'expected_filtered_ids' => [4, 6]
    ], 'with offset and more data than needed')]
    #[TestWith([
        'offset' => 1,
        'limit' => 2,
        'ids' => [1, 2, 3, 4, 5],
        'expected_filtered_ids' => [4]
    ], 'with offset and less data than needed')]
    #[TestWith([
        'offset' => 1,
        'limit' => 2,
        'ids' => [1, 2, 3],
        'expected_filtered_ids' => []
    ], 'with offset and much less data than needed')]
    #[TestWith([
        'offset' => 0,
        'limit' => 2,
        'ids' => [],
        'expected_filtered_ids' => []
    ], 'empty')]
    public function testFilterWithoutPreloading(
        int $offset,
        int $limit,
        array $ids,
        array $expected_filtered_ids
    ): void {
        $filter = new FilterImpl();
        $filtered_ids = $filter->filter(
            $this->getEvenNumberCriterion(),
            $offset,
            $limit,
            ...$ids
        );
        $this->assertSame($expected_filtered_ids, iterator_to_array($filtered_ids, false));
    }

    #[TestWith(['ids' => [1, 2]], 'fewer valid results, ends with valid value')]
    #[TestWith(['ids' => [1, 2, 3]], 'fewer valid results, ends with invalid value')]
    #[TestWith(['ids' => [1, 2, 3, 4]], 'exactly enough valid results, ends with valid value')]
    #[TestWith(['ids' => [1, 2, 3, 4, 5]], 'exactly enough valid results, ends with invalid value')]
    public function testFilterResultComplete(
        array $ids
    ): void {
        $filter = new FilterImpl();
        $filtered_ids = $filter->filter(
            $this->getEvenNumberCriterion(),
            0,
            2,
            ...$ids
        );
        $this->assertTrue($filtered_ids->isResultComplete());
    }

    public function testFilterResultIncomplete(): void
    {
        $filter = new FilterImpl();
        $ids = [1, 2, 3, 4, 5, 6];
        $filtered_ids = $filter->filter(
            $this->getEvenNumberCriterion(),
            0,
            2,
            ...$ids
        );
        $this->assertFalse($filtered_ids->isResultComplete());
    }

    public function testFilterCriterionNotCalledMoreThanNeeded(): void
    {
        $filter = new FilterImpl();
        $ids = [1, 2, 3, 4, 5, 6, 7, 8];
        $filtered_ids = $filter->filter(
            $this->getEvenNumberCriterion(),
            0,
            2,
            ...$ids
        );
        $this->assertLessThanOrEqual(
            6, // the filter always looks for one extra valid result
            $this->getEvenNumberCriterion()->does_fulfill_count,
            'The filter calls the criterion more than needed.'
        );
    }

    public function testFilterWithPreloading(): void
    {
        $preloading_mock_criterion = new class () implements Criterion {
            public array $preloaded_ids = [];

            public function doesFulfill(int $id): bool
            {
                return false;
            }

            public function preloadData(int ...$ids): void
            {
                $this->preloaded_ids = $ids;
            }
        };
        $filter = new FilterImpl();
        $ids = [1, 2, 3, 4, 5, 6, 7, 8];
        $filtered_ids = $filter->filter(
            $preloading_mock_criterion,
            0,
            2,
            ...$ids
        );
        $this->assertSame($ids, $preloading_mock_criterion->preloaded_ids);
    }
}
