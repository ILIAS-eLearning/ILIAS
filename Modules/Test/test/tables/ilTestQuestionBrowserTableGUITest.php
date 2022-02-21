<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestQuestionBrowserTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionBrowserTableGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionBrowserTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    protected function setUp() : void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->willReturnCallback(function () {
                     return "testTranslation";
                 });

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });

        $mainTpl_mock = $this->createMock(ilGlobalPageTemplate::class);
        $db_mock = $this->createMock(ilDBInterface::class);
        $tree_mock = $this->createMock(ilTree::class);
        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $mainTpl_mock);
        $this->setGlobalVariable("tree", $tree_mock);
        $this->setGlobalVariable("ilDB", $db_mock);
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));
        $this->setGlobalVariable("ilObjDataCache", $this->createMock(ilObjectDataCache::class));
        $this->setGlobalVariable("component.repository", $this->createMock(ilComponentRepository::class));
        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->setGlobalVariable("component.factory", $component_factory);
        $pluginAdmin = new ilPluginAdmin($this->createMock(ilComponentRepository::class));
        $this->setGlobalVariable("ilPluginAdmin", $pluginAdmin);

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilTestQuestionBrowserTableGUI(
            $ctrl_mock,
            $mainTpl_mock,
            $this->createMock(ilTabsGUI::class),
            $lng_mock,
            $tree_mock,
            $db_mock,
            $pluginAdmin,
            $this->parentObj_mock->object,
            $this->createMock(ilAccessHandler::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestQuestionBrowserTableGUI::class, $this->tableGui);
    }

    public function testWriteAccess() : void
    {
        $this->tableGui->setWriteAccess(false);
        $this->assertFalse($this->tableGui->hasWriteAccess());
        $this->tableGui->setWriteAccess(true);
        $this->assertTrue($this->tableGui->hasWriteAccess());
    }
}
