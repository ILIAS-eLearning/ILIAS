<?php

namespace Administration;

use ilDBInterface;
use ilDBStatement;
use ilObjectDefinition;
use ilObjTestFolderGUI;
use ilRbacSystem;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ilObjTestFolderGUITest extends ilTestBaseTestCase
{
    /**
     * @throws \Exception|Exception
     */
    public function testConstruct(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $ilDBStatement = $this->createMock(ilDBStatement::class);
            $ilDBStatement->method('numRows')->willReturn(1);
            $ilDBStatement->method('fetchRow')->willReturnOnConsecutiveCalls(
                (object) ['type' => 'xxx', 'obj_id' => 1, 'log_level' => '1', 'component_id' => 1],
                null
            );

            $array = [
                'id' => 1,
                'type' => 'xxx',
                'obj_id' => 1,
                'title' => 'test',
                'description' => 'test',
                'owner' => 1,
                'create_date' => 1,
                'last_update' => 1,
                'import_id' => 1,
                'keyword' => '',
                'value' => ''
            ];

            $mock
                ->method('fetchAssoc')
                ->willReturnOnConsecutiveCalls($array, null);

            $mock
                ->method('query')
                ->willReturn($ilDBStatement);
        });

        $this->adaptDICServiceMock(ilObjectDefinition::class, function (ilObjectDefinition|MockObject $mock) {
            $mock
                ->method('getClassName')
                ->willReturn('TestFolder');
        });

        $this->adaptDICServiceMock(ilRbacSystem::class, function (ilRbacSystem|MockObject $mock) {
            $mock
                ->method('checkAccess')
                ->willReturn(true);
        });

        $il_obj_test_folder_gui = $this->createInstanceOf(ilObjTestFolderGUI::class, ['a_data' => ['data']]);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $il_obj_test_folder_gui);
    }
}
