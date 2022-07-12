<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureHelperTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureHelperTest extends TestCase
{
    public function testStructureHelperWithEmptyArrays() : void
    {
        $helper = new ilCtrlStructureHelper([], []);

        $this->assertEmpty($helper->getStructure());
    }

    public function testStructureHelperWithCtrlStructure() : void
    {
        $expected_value = ['entry0'];
        $helper = new ilCtrlStructureHelper([], $expected_value);

        $this->assertEquals($expected_value, $helper->getStructure());
    }

    public function testStructureHelperUnnecessaryEntryFilter() : void
    {
        $helper = new ilCtrlStructureHelper(
            [
                'baseclass1',
            ],
            [
                'baseclass1' => [],
                'unnecessary_class1' => [],
                'unnecessary_class2' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ],
                'command_class_1' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'baseclass1',
                    ],
                ],
            ]
        );

        $this->assertEquals(
            [
                'baseclass1' => [],
                'command_class_1' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'baseclass1',
                    ],
                ],
            ],
            $helper->filterUnnecessaryEntries()->getStructure()
        );
    }
}
