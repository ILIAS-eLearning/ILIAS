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

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlArrayIteratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayIteratorTest extends TestCase
{
    public function testArrayIteratorWithAssociativeStringArray(): void
    {
        // iterator value = key
        // array value of key = value
        $iterator = new ilCtrlArrayIterator(
            $this->getIteratorStub([
                'key0',
                'key1',
                'key2',
            ]), [
                'key0' => 'entry0',
                'key1' => 'entry1',
                'key2' => 'entry2',
            ]
        );

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

    public function testArrayIteratorWithEmptyArray(): void
    {
        $iterator = new ilCtrlArrayIterator($this->getIteratorStub([]), []);

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    protected function getIteratorStub(array $values): Generator
    {
        return (static fn() => yield from $values)();
    }
}
