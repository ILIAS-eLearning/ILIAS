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
use ReflectionException;

class ilObjTestFolderGUITest extends ilTestBaseTestCase
{
    /**
     * @throws \Exception|Exception
     */
    public function test_instantiateGUI(): void
    {
        $this->adaptDICServiceMock(ilDBInterface::class, function (ilDBInterface|MockObject $mock) {
            $ilDBStatement = $this->createMock(ilDBStatement::class);
            $ilDBStatement->method("numRows")->willReturn(1);
            $ilDBStatement->method("fetchRow")->willReturn(["type" => "xxx", "obj_id" => 1]);

            $array = [
                "id" => 1,
                "type" => "xxx",
                "obj_id" => 1,
                "title" => "test",
                "description" => "test",
                "owner" => 1,
                "create_date" => 1,
                "last_update" => 1,
                "import_id" => 1
            ];

            $mock
                ->method('fetchAssoc')
                ->willReturn($array);

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

        $gui = new ilObjTestFolderGUI(["data"], 1);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $gui);
    }
}
