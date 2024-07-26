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
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_instantiateGUI(): void
    {
        $ilDBStatement = $this->createMock(\ilDBStatement::class);
        $ilDBStatement->expects($this->any())->method("numRows")->willReturn(1);
        $ilDBStatement->expects($this->any())->method("fetchRow")->willReturn(["type" => "xxx", "obj_id" => 1]);

        $this->mockDBFetchAssoc($this->any(), ["id" => 1, "type" => "xxx", "obj_id" => 1, "title" => "test", "description" => "test", "owner" => 1, "create_date" => 1, "last_update" => 1, "import_id" => 1]);
        $this->mockDBQuery($this->any(), $ilDBStatement);
        $this->mockObjectDefinitionGetClassName("TestFolder");
        $this->mockRbacAccess(true);

        $gui = new ilObjTestFolderGUI(["data"], 1);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $gui);
    }




}
