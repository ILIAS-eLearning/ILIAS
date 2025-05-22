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
 * Class ilCtrlStructureReaderTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureReaderTest extends TestCase
{
    /**
     * @var array<string, string[]>
     */
    private array $expected_test_gui_structure;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->expected_test_gui_structure = require __DIR__ . '/../Data/Structure/test_ctrl_structure.php';
    }

    public function testStructureReaderWithValidArrayIterator(): void
    {
        $class_map = require __DIR__ . '/../Data/ClassMaps/valid_class_map.php';
        $reader = new ilCtrlStructureReader(
            new ilCtrlArrayIterator($class_map),
            new ilCtrlStructureCidGenerator()
        );

        $this->assertFalse($reader->isExecuted());
        $this->assertEquals(
            $this->expected_test_gui_structure,
            $reader->readStructure()
        );

        $this->assertTrue($reader->isExecuted());
    }

    public function testStructureReaderWithInvalidArrayIterator(): void
    {
        $class_map = require __DIR__ . '/../Data/ClassMaps/invalid_class_map.php';
        $reader = new ilCtrlStructureReader(
            new ilCtrlArrayIterator($class_map),
            new ilCtrlStructureCidGenerator()
        );

        $this->assertFalse($reader->isExecuted());
        $this->assertEmpty($reader->readStructure());
        $this->assertTrue($reader->isExecuted());
    }

    public function testStructureReaderWithEmptyArrayIterator(): void
    {
        $reader = new ilCtrlStructureReader(
            new ilCtrlArrayIterator([]),
            new ilCtrlStructureCidGenerator()
        );

        $this->assertFalse($reader->isExecuted());
        $this->assertEmpty($reader->readStructure());
        $this->assertTrue($reader->isExecuted());
    }
}
