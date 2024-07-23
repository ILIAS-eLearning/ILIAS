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
        $ilDBStatement->expects($this->any())->method("fetchRow")->willReturn(["type" => "xxx"]);

        $this->mockDBFetchAssoc(["id" => 1, "type" => "xxx"]);
        $this->mockDBQuery($ilDBStatement);
        $this->mockObjectDefinitionGetClassName("TestFolder");
        $this->mockRbacAccess(true);

        $gui = new ilObjTestFolderGUI(["data"], 1);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $gui);
    }




}
