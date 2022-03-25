<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureMapperTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureMapperTest extends TestCase
{
    public function testStructureMapperWithEmptyArray() : void
    {
        $mapper = new ilCtrlStructureMapper([]);
        $this->assertEmpty($mapper->getStructure());
    }

    public function testStructureMapperWithCommonStringArray() : void
    {
        $expected_values = ['entry0', 'entry1', 'entry2'];
        $mapper = new ilCtrlStructureMapper($expected_values);

        $this->assertEmpty($mapper->getStructure());
    }

    public function testStructureMapperWithAssociativeStringArray() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'key0' => 'entry0',
            'key1' => 'entry1',
            'key2' => 'entry2'
        ]);

        $this->assertEmpty($mapper->getStructure());
    }

    public function testStructureMapperWithEmptyClassData() : void
    {
        $expected_values = [
            'key0' => [],
            'key1' => [],
            'key2' => []
        ];

        $mapper = new ilCtrlStructureMapper($expected_values);

        $this->assertEquals(
            $expected_values,
            $mapper->getStructure()
        );
    }

    public function testStructureMapperWithStructureArray() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'class0' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class1',
                ],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => []
            ],
            'class1' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    'class2',
                ],
            ],
            'class2' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
            ]
        ]);

        $this->assertEquals(
            [
                'class0' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class1',
                    ],
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                ],
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class2',
                        'class0',
                    ],
                ],
                'class2' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class1',
                    ],
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                ]
            ],
            $mapper->getStructure()
        );
    }

    public function testStructureMapperWithMissingReferenceLists() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'class0' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class1',
                ],
            ],
            'class1' => [
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    'class2',
                ],
            ],
            'class2' => []
        ]);

        $this->assertEquals(
            [
                'class0' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class1',
                    ],
                ],
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class2',
                        'class0',
                    ],
                ],
                'class2' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class1',
                    ],
                ]
            ],
            $mapper->getStructure()
        );
    }

    public function testStructureMapperWithInvalidReferenceInList() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'class1' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class2',
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
                ]
            ],
            $mapper->getStructure()
        );
    }

    public function testStructureMapperReferenceListIndexesAfterInvalidReferenceIsRemoved() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'class1' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    0 => 'class3',
                    1 => 'class2',
                ],
            ],
            'class2' => [],
        ]);

        $this->assertEquals(
            [
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        0 => 'class2',
                    ],
                ],
                'class2' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        0 => 'class1',
                    ],
                ],
            ],
            $mapper->getStructure()
        );
    }

    public function testStructureMapperWithMixedArray() : void
    {
        $mapper = new ilCtrlStructureMapper([
            'class1' => [],
            'class2' => false,
            3 => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class1',
                ],
            ],
            'class3' => [
                ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                    'class1',
                    'class2',
                ],
            ],
            'class4' => [
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    3
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'class1' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                        'class3',
                    ],
                ],
                'class3' => [
                    ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
                        'class1',
                    ],
                ],
                'class4' => [
                    ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
                ],
            ],
            $mapper->getStructure()
        );
    }
}
