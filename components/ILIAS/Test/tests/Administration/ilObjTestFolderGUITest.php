<?php

namespace Administration;

use ilDBInterface;
use ilObjectDefinition;
use ilObjTestFolderGUI;
use ilRbacSystem;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilObjTestFolderGUITest extends ilTestBaseTestCase
{
    public function test_instantiateGUI(): void
    {
        $ilDBStatement = $this->createMock(\ilDBStatement::class);
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

        $this->mockServiceMethod(service_name: "ilDB", method: "fetchAssoc", will_return: $array);
        $this->mockServiceMethod(service_name: "ilDB", method: "query", will_return: $ilDBStatement);
        $this->mockServiceMethod(service_name: "objDefinition", method: "getClassName", will_return: "TestFolder");
        $this->mockServiceMethod(service_name: "rbacsystem", method: "checkAccess", will_return: true);

        $gui = new ilObjTestFolderGUI(["data"], 1);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $gui);
    }




}
