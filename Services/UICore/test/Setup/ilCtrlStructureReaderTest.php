<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureReaderTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureReaderTest extends TestCase
{
    protected const INVALID_CLASS_MAP_PATH = __DIR__ . '/data/invalid_test_class_map.php';
    protected const VALID_CLASS_MAP_PATH   = __DIR__ . '/data/valid_test_class_map.php';

    public function testArrayStructureWithValidDataSource() : void
    {
        $expected_value = [
            'ilctrlbaseclasstestgui' => [
                ilCtrlStructureInterface::KEY_CLASS_CID      => '1',
                ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlBaseClassTestGUI',
                ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/GUI/class.ilCtrlBaseClassTestGUI.php',
                ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    'ilctrlcommandclass1testgui',
                ],
            ],

            'ilctrlcommandclass1testgui' => [
                ilCtrlStructureInterface::KEY_CLASS_CID      => '2',
                ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlCommandClass1TestGUI',
                ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/GUI/class.ilCtrlCommandClass1TestGUI.php',
                ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [
                    'ilctrlbaseclasstestgui',
                ],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                    'ilctrlcommandclass2testgui',
                ],
            ],

            'ilctrlcommandclass2testgui' => [
                ilCtrlStructureInterface::KEY_CLASS_CID      => '3',
                ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlCommandClass2TestGUI',
                ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/GUI/class.ilCtrlCommandClass2TestGUI.php',
                ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [
                    'ilctrlcommandclass1testgui',
                ],
                ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
            ],
        ];

        $reader = $this->getReaderWithDataSource(self::VALID_CLASS_MAP_PATH);

        // check if the output matches the expected array structure.
        $this->assertEquals(
            $expected_value,
            $reader->readStructure()
        );

        // check if the structure-reader updates its execution status.
        $this->assertEquals(
            true,
            $reader->isExecuted()
        );
    }

    public function testArrayStructureWithInvalidDataSource() : void
    {
        $reader = $this->getReaderWithDataSource(self::INVALID_CLASS_MAP_PATH);

        // check if the output matches the expected array structure.
        $this->assertEmpty(
            $reader->readStructure()
        );

        // check if the structure-reader updates its execution status.
        $this->assertEquals(
            true,
            $reader->isExecuted()
        );
    }

    /**
     * Returns an instance of the ilCtrlStructureReader provided
     * with the given data-source (class-map).
     *
     * @param string $data_source
     * @return ilCtrlStructureReader
     */
    protected function getReaderWithDataSource(string $data_source) : ilCtrlStructureReader
    {
        return new ilCtrlStructureReader($data_source, dirname(__FILE__, 5));
    }
}