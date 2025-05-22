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
 * Class ilCtrlStructureHelperTest
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureHelperTest extends TestCase
{
    public function testStructureHelperWithEmptyArrays(): void
    {
        $helper = new ilCtrlStructureHelper([], []);

        $this->assertEmpty($helper->getStructure());
    }

    public function testStructureHelperWithCtrlStructure(): void
    {
        $expected_value = ['entry0'];
        $helper = new ilCtrlStructureHelper([], $expected_value);

        $this->assertEquals($expected_value, $helper->getStructure());
    }

    public function testStructureHelperUnnecessaryEntryFilter(): void
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
