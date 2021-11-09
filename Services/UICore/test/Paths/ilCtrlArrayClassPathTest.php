<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlArrayClassPathTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayClassPathTest extends TestCase
{
    private ilCtrlStructureInterface $structure;

    protected function setUp() : void
    {
        $structure_artifact  = require __DIR__ . '/../Data/Structure/test_ctrl_structure.php';
        $plugin_artifact     = require __DIR__ . '/../Data/Structure/test_plugin_ctrl_structure.php';
        $base_class_artifact = require __DIR__ . '/../Data/Structure/test_base_classes.php';
    }

    public function testArrayClassPathWithValidControlFlow() : void
    {
        $context = $this->createMock(ilCtrlContextInterface::class);
        $structure = require __DIR__ . '/../Data/Structure/test_ctrl_structure.php';
        $structure = new ilCtrlStructure(
            $structure,
            [],
            ['ilctrlbaseclasstestgui'],
            []
        );

        $array_path = new ilCtrlArrayClassPath($structure, $context, []);
    }
}