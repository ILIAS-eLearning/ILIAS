<?php
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
     *
     */
    public function setUp()
    {
    }

    /**
     * @dataProvider provideElements
     * @param $elements array
     */
    public function testElementsCanBeInitiallyAdded($elements)
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
    public function testElementsCanBeAddedAndRemoved($elements)
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
    public function testIterator($elements)
    {
        $collection = new ilBuddySystemRelationCollection($elements);
        $iterations = 0;
        foreach ($collection->getIterator() as $key => $item) {
            $this->assertSame($elements[$key], $item, "Item {$key} not match");
            $iterations++;
        }
        $this->assertCount($iterations, $elements, "Number of iterations not match");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRemovingAnNonExistingElementRaisesAnException()
    {
        $this->assertException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->removeElement(5);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRemovingAnNonExistingElementByKeyRaisesAnException()
    {
        $this->assertException(InvalidArgumentException::class);
        $collection = new ilBuddySystemRelationCollection();
        $collection->remove("phpunit");
    }

    /**
     *
     */
    public function testElementsCanBeSliced()
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
    public function testElementsCanBeFiltered()
    {
        $collection = new ilBuddySystemRelationCollection();
        $collection->add(1);
        $collection->add(2);
        $collection->add(3);
        $collection->add(4);

        $this->assertSame(array(3), $collection->slice(2, 1)->getValues());
    }

    /**
     * @return array
     */
    public function provideElements()
    {
        $relation_1 = $this->getMockBuilder('ilBuddySystemRelation')->disableOriginalConstructor()->getMock();
        $relation_2 = $this->getMockBuilder('ilBuddySystemRelation')->disableOriginalConstructor()->getMock();
        $relation_3 = $this->getMockBuilder('ilBuddySystemRelation')->disableOriginalConstructor()->getMock();
        $relation_4 = $this->getMockBuilder('ilBuddySystemRelation')->disableOriginalConstructor()->getMock();
        $relation_5 = $this->getMockBuilder('ilBuddySystemRelation')->disableOriginalConstructor()->getMock();

        return array(
            'indexed' => array(array(0, 1, 2, 3, 4, 5)),
            'associative' => array(array('A' => 'a', 'B' => 'b', 'C' => 'c')),
            'mixed' => array(array(0, 'A' => 'a', 1, 'B' => 'b', 2, 3)),
            'relations' => array(array($relation_1, $relation_2, $relation_3, $relation_4, $relation_5))
        );
    }
}
