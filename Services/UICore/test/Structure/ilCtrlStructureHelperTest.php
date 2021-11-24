<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureHelperTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureHelperTest extends TestCase
{
    public function testStructureHelperWithEmptyArrays() : void
    {
        $helper = new ilCtrlStructureHelper([], []);

        $this->assertEmpty($helper->getStructure());
        $this->assertEmpty($helper->getPluginStructure());

        $helper = new ilCtrlStructureHelper([], []);

        $this->assertEmpty($helper->getStructure());
        $this->assertEmpty($helper->getPluginStructure());
    }

    public function testStructureHelperWithCtrlStructure() : void
    {
        $expected_value = ['entry0'];
        $helper = new ilCtrlStructureHelper([], $expected_value);

        $this->assertEquals($expected_value, $helper->getStructure());
        $this->assertEmpty($helper->getPluginStructure());
    }

    public function testStructureHelperWithCtrlAndPluginStructure() : void
    {
        $expected_ctrl_structure = ['entry0'];
        $expected_plugin_structure = ['entry1'];
        $helper = new ilCtrlStructureHelper([], $expected_ctrl_structure, $expected_plugin_structure);

        $this->assertEquals($expected_ctrl_structure, $helper->getStructure());
        $this->assertEquals($expected_plugin_structure, $helper->getPluginStructure());
    }

    public function testStructureHelperPluginMergingWithInvalidPluginStructure() : void
    {
        $expected_ctrl_structure = [
            'class1' => [],
        ];

        $helper = new ilCtrlStructureHelper(
            [],
            $expected_ctrl_structure, [
                'class2',
            ],
        );

        $this->assertEquals(
            $expected_ctrl_structure,
            $helper->mergePluginStructure()->getStructure()
        );
    }

    public function testStructureHelperPluginMergingWithValidPluginStructure() : void
    {
        $helper = new ilCtrlStructureHelper(
            [], [
                'class1' => [],
                'class2' => [],
            ], [
                'plugin1' => [
                    'class3' => [],
                ],
                'plugin2' => [
                    'class4' => [],
                ],
            ]
        );

        $this->assertEquals(
            [
                'class1' => [],
                'class2' => [],
                'class3' => [],
                'class4' => [],
            ],
            $helper->mergePluginStructure()->getStructure()
        );
    }

    public function testStructureHelperPluginMergingWithMixedPluginStructure() : void
    {
        $helper = new ilCtrlStructureHelper(
            [], [
                'class1' => [],
                'class2' => [],
            ], [
                'plugin1' => [
                    'class3' => null,
                ],
                'plugin2' => [
                    'class4' => [],
                ],
                3 => [
                    'class5' => []
                ],
            ]
        );

        $this->assertEquals(
            [
                'class1' => [],
                'class2' => [],
                'class4' => [],
            ],
            $helper->mergePluginStructure()->getStructure()
        );
    }

    public function testStructureHelperReferencesMappingWithValidArray() : void
    {
        $helper = new ilCtrlStructureHelper(
            [], [
                'class1' => [],
                'class2' => [],
            ], [
                'plugin1' => [
                    'class3' => [
                        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                            'class2',
                        ],
                    ],
                ],
                'plugin2' => [
                    'class4' => [
                        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                            'class1',
                        ],
                    ],
                ]
            ]
        );

        $this->assertEquals(
            [
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class4',
                    ],
                ],
                'class2' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class3',
                    ],
                ],
                'class3' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class2',
                    ],
                ],
                'class4' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class1',
                    ],
                ],
            ],
            $helper->mergePluginStructure()->mapStructureReferences()->getStructure()
        );
    }

    public function testStructureHelperReferencesMappingWithMixedArray() : void
    {
        $helper = new ilCtrlStructureHelper(
            [], [
                'class1' => [],
                'class2' => false,
            ], [
                'plugin1' => [
                    'class3' => [
                        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                            'class2',
                        ],
                    ],
                ],
                'plugin2' => [
                    'class4' => [
                        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                            'class1',
                        ],
                    ],
                    1 => false,
                ],
                3 => [],
            ]
        );

        $this->assertEquals(
            [
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class4',
                    ],
                ],
                'class3' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ],
                'class4' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class1',
                    ],
                ],
            ],
            $helper->mergePluginStructure()->mapStructureReferences()->getStructure()
        );
    }

    public function testStructureHelperUnnecessaryEntryFilter() : void
    {
        $helper = new ilCtrlStructureHelper(
            [
                'baseclass1',
            ], [
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