<?php declare(strict_types=1);

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

/**
 * Class ilBuddySystemRelationCollectionTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRelationCollectionTest extends ilBuddySystemBaseTest
{
    /**
     * @dataProvider provideElements
     * @param array $elements
     */
    public function testElementsCanBeInitiallyAdded(array $elements) : void
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

    /**
     * @dataProvider provideElements
     * @param array $elements
     */
    public function testElementsCanBeAddedAndRemoved(array $elements) : void
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

    /**
     * @dataProvider provideElements
     * @param array $elements
     */
    public function testIterator(array $elements) : void
    {
        $collection = new ilBuddySystemRelationCollection($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, "Item $key not match");
            $iterations++;
        }
        $this->assertCount($iterations, $elements, 'Number of iterations not match');
    }

    public function testRemovingAnNonExistingElementRaisesAnException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->removeElement(5);
    }

    public function testRemovingAnNonExistingElementByKeyRaisesAnException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->remove('phpunit');
    }

    public function testElementsCanBeSliced() : void
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertCount(2, $collection->filter(function ($elm) {
            return $elm % 2 === 0;
        })->toArray());
    }

    public function testElementsCanBeFiltered() : void
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertSame([3], $collection->slice(2, 1)->getValues());
    }

    public function provideElements() : array
    {
        $relation1 = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation2 = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation3 = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation4 = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation5 = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();

        return [
            'indexed' => [[0, 1, 2, 3, 4, 5]],
            'associative' => [['A' => 'a', 'B' => 'b', 'C' => 'c']],
            'mixed' => [[0, 'A' => 'a', 1, 'B' => 'b', 2, 3]],
            'relations' => [[$relation1, $relation2, $relation3, $relation4, $relation5]]
        ];
    }
}
