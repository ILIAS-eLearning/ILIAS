<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlDirectoryIteratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlDirectoryIteratorTest extends TestCase
{
    public function testDirectoryIteratorWithValidDirectory() : void
    {
        $gui_dir  = realpath(__DIR__ . '/../Data/GUI');
        $iterator = new ilCtrlDirectoryIterator($gui_dir);

        $expected_iterator_values = [
            $gui_dir . '/class.ilCtrlBaseClass1TestGUI.php',
            $gui_dir . '/class.ilCtrlBaseClass2TestGUI.php',
            $gui_dir . '/class.ilCtrlCommandClass1TestGUI.php',
            $gui_dir . '/class.ilCtrlCommandClass2TestGUI.php',
        ];

        $expected_iterator_keys   = [
            'ilCtrlBaseClass1TestGUI',
            'ilCtrlBaseClass2TestGUI',
            'ilCtrlCommandClass1TestGUI',
            'ilCtrlCommandClass2TestGUI',
        ];

        for ($i = 0, $i_max = 4; $i < $i_max; $i++) {
            $this->assertTrue($iterator->valid());
            $this->assertEquals(
                $expected_iterator_values[$i],
                $iterator->current()
            );

            $this->assertEquals(
                $expected_iterator_keys[$i],
                $iterator->key()
            );

            $iterator->next();
        }

        $this->assertFalse($iterator->valid());
    }

    public function testDirectoryIteratorWithEmptyDirectory() : void
    {
        $empty_dir = __DIR__ . '/../Data/EmptyDirectory';
        $iterator  = new ilCtrlDirectoryIterator($empty_dir);

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->current());
        $this->assertNull($iterator->key());
    }

    public function testDirectoryIteratorWithInvalidDirectory() : void
    {
        $not_existing_dir = __DIR__ . '/not_existing_dir';

        $this->expectException(UnexpectedValueException::class);
        // exception messages differ in PHP7.4 and PHP8 - therefore we only assert the expected exception.
        // $this->expectExceptionMessage("RecursiveDirectoryIterator::__construct($not_existing_dir): failed to open dir: No such file or directory");
        $iterator = new ilCtrlDirectoryIterator($not_existing_dir);
    }
}