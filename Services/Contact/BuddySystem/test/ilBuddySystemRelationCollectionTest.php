<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/ilBuddySystemBaseTest.php';

/**
 * Class ilBuddySystemRelationCollectionTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemRelationCollectionTest extends ilBuddySystemBaseTest
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @dataProvider provideElements
     * @param $elements array
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
     * @param $elements array
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
     * @param $elements array
     */
    public function testIterator(array $elements) : void
    {
        $collection = new ilBuddySystemRelationCollection($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, "Item {$key} not match");
            $iterations++;
        }
        $this->assertCount($iterations, $elements, 'Number of iterations not match');
    }

    /**
     *
     */
    public function testRemovingAnNonExistingElementRaisesAnException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->removeElement(5);
    }

    /**
     *
     */
    public function testRemovingAnNonExistingElementByKeyRaisesAnException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->remove('phpunit');
    }

    /**
     *
     */
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

    /**
     *
     */
    public function testElementsCanBeFiltered() : void
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertSame([3], $collection->slice(2, 1)->getValues());
    }

    /**
     * @return array
     */
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