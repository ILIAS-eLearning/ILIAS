<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlArrayIteratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayIteratorTest extends TestCase
{
    public function testArrayIteratorWithAssociativeStringArray() : void
    {
        $iterator = new ilCtrlArrayIterator([
            'key0' => 'entry0',
            'key1' => 'entry1',
            'key2' => 'entry2',
        ]);

        $expected_iterator_values = ['entry0', 'entry1', 'entry2'];
        $expected_iterator_keys = ['key0', 'key1', 'key2'];

        for ($i = 0, $i_max = 3; $i < $i_max; $i++) {
            $this->assertTrue($iterator->valid());
            $this->assertEquals(
                $expected_iterator_values[$i],
                $iterator->current(),
            );
            $this->assertEquals(
                $expected_iterator_keys[$i],
                $iterator->key()
            );

            $iterator->next();
        }

        $this->assertFalse($iterator->valid());
    }

    public function testArrayIteratorWithCommonStringArray() : void
    {
        $iterator = new ilCtrlArrayIterator([
            'entry0',
            'entry1',
            'entry2',
        ]);

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    public function testArrayIteratorWithMixedArray() : void
    {
        $iterator = new ilCtrlArrayIterator([
            'key0' => 0,
            1 => 'entry1',
            2 => 2,
            'key3' => 'entry3',
            'key4' => false
        ]);

        $this->assertTrue($iterator->valid());
        $this->assertEquals(
            'entry3',
            $iterator->current()
        );
        $this->assertEquals(
            'key3',
            $iterator->key()
        );

        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testArrayIteratorWithEmptyArray() : void
    {
        $iterator = new ilCtrlArrayIterator([]);

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }
}
