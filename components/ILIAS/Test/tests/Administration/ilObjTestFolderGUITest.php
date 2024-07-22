<?php

namespace Administration;

use ilDBInterface;
use ilObjectDefinition;
use ilObjTestFolderGUI;
use ilRbacSystem;
use ilTestBaseTestCase;

class ilObjTestFolderGUITest extends ilTestBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilias();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilTabs();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilRbacAdmin();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilObjectCustomIconFactory();
        $this->addGlobal_filesystem();
    }

    public function test_instantiateGUI(): void
    {
        $ilDBStatement = $this->createMock(\ilDBStatement::class);
        $ilDBStatement->expects($this->any())->method("numRows")->willReturn(1);
        $ilDBStatement->expects($this->any())->method("fetchRow")->willReturn(["type" => "xxx"]);
        $dbMock = $this->createMock(ilDBInterface::class);
        $dbMock->expects($this->any())->method("query")->willReturn($ilDBStatement);
        $dbMock->expects($this->any())->method("fetchAssoc")->willReturn(["id" => 1, "type" => "xxx"]);
        $this->setGlobalVariable('ilDB', $dbMock);
        $rbacsystemMock = $this->createMock(ilRbacSystem::class);
        $rbacsystemMock->expects($this->any())->method("checkAccess")->willReturn(true);
        $this->setGlobalVariable('rbacsystem', $rbacsystemMock);
        $objDefinitionMock = $this->createMock(ilObjectDefinition::class);
        $objDefinitionMock->expects($this->any())->method("getClassName")->willReturn("TestFolder");
        $this->setGlobalVariable('objDefinition', $objDefinitionMock);
        $gui = new ilObjTestFolderGUI(["data"], 1);
        $this->assertInstanceOf(ilObjTestFolderGUI::class, $gui);
    }


}
