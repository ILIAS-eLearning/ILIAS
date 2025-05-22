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

use PHPUnit\Framework\Attributes\DataProvider;

class ilBuddySystemRelationCollectionTestCase extends ilBuddySystemBaseTestCase
{
    #[DataProvider('provideElements')]
    public function testElementsCanBeInitiallyAdded(array $elements): void
    {
        $collection = new ilBuddySystemRelationCollection($elements);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame($elements, $collection->toArray());
        $this->assertSame(array_values($elements), $collection->getValues());
        $this->assertSame(array_keys($elements), $collection->getKeys());

        foreach ($elements as $key => $elm) {
            $this->assertArrayHasKey($collection->getKey($elm), $elements);
            $this->assertTrue(isset($collection[$key]));
            $this->assertEquals($collection[$key], $elm);
        }
    }

    #[DataProvider('provideElements')]
    public function testElementsCanBeAddedAndRemoved(array $elements): void
    {
        $collection = new ilBuddySystemRelationCollection();
        $this->assertTrue($collection->isEmpty());

        foreach ($elements as $elm) {
            $collection->add($elm);
            $this->assertTrue($collection->contains($elm));
        }

        foreach ($elements as $elm) {
            $collection->removeElement($elm);
            $this->assertFalse($collection->contains($elm));
        }

        $this->assertTrue($collection->isEmpty());

        foreach ($elements as $elm) {
            $collection->add($elm);
            $this->assertTrue($collection->contains($elm));
        }

        foreach ($elements as $elm) {
            $key = $collection->getKey($elm);
            $collection->remove($key);
            $this->assertFalse($collection->contains($elm));
        }

        $this->assertTrue($collection->isEmpty());

        foreach ($elements as $key => $elm) {
            $collection[$key] = $elm;
            $this->assertTrue($collection->contains($elm));
        }

        foreach ($elements as $key => $elm) {
            unset($collection[$key]);
            $this->assertFalse($collection->contains($elm));
        }

        $this->assertTrue($collection->isEmpty());

        $collection[] = 5;

        $data = $collection->toArray();
        $this->assertSame(5, reset($data));
    }

    #[DataProvider('provideElements')]
    public function testIterator(array $elements): void
    {
        $collection = new ilBuddySystemRelationCollection($elements);
        $iterations = 0;
        foreach ($collection as $key => $item) {
            $this->assertSame($elements[$key], $item, "Item $key not match");
            $iterations++;
        }
        $this->assertCount($iterations, $elements, 'Number of iterations not match');
    }

    public function testRemovingAnNonExistingElementRaisesAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->removeElement(5);
    }

    public function testRemovingAnNonExistingElementByKeyRaisesAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->remove('phpunit');
    }

    public function testElementsCanBeSliced(): void
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertCount(2, $collection->filter(fn(int $elm): bool => $elm % 2 === 0)->toArray());
    }

    public function testElementsCanBeFiltered(): void
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertSame([3], $collection->slice(2, 1)->getValues());
    }

    /**
     * @return array{indexed: int[][], associative: array<int, array{A: string, B: string, C: string}>, mixed: array<int, array<int|string, int|string>>, relations: \ilBuddySystemRelation&\PHPUnit\Framework\MockObject\MockObject[][]}
     */
    public static function provideElements(): array
    {
        $relation1 = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState(), 1, 2, false, time());
        $relation2 = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState(), 3, 4, false, time());
        $relation3 = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState(), 5, 6, false, time());
        $relation4 = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState(), 7, 8, false, time());
        $relation5 = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState(), 9, 10, false, time());

        return [
            'indexed' => [[0, 1, 2, 3, 4, 5]],
            'associative' => [['A' => 'a', 'B' => 'b', 'C' => 'c']],
            'mixed' => [[0, 'A' => 'a', 1, 'B' => 'b', 2, 3]],
            'relations' => [[$relation1, $relation2, $relation3, $relation4, $relation5]]
        ];
    }
}
