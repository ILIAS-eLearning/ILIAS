<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjChatroomAdminAccessTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAdminAccessTest extends ilChatroomAbstractTest
{
    protected ilObjChatroomAdminAccess $adminAccess;

    protected ilRbacSystem $ilAccessMock;

    public function testCommandDefitionFullfilsExpectations() : void
    {
        $expected = [
            ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'enter', 'default' => true],
            ['permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'],
            ['permission' => 'write', 'cmd' => 'versions', 'lang_var' => 'versions'],
        ];

        $commands = $this->adminAccess::_getCommands();

        $this->assertIsArray($commands);
        $this->assertEquals($expected, $commands);
    }

    public function testGotoCheckFails() : void
    {
        $this->ilAccessMock
            ->method('checkAccess')
            ->with(
                $this->equalTo('visible'),
                $this->equalTo('1')
            )->willReturn(false);

        $this->assertFalse($this->adminAccess::_checkGoto(''));
        $this->assertFalse($this->adminAccess::_checkGoto('chtr'));
        $this->assertFalse($this->adminAccess::_checkGoto('chtr_'));
        $this->assertFalse($this->adminAccess::_checkGoto('chtr_'));
        $this->assertFalse($this->adminAccess::_checkGoto('chtr_test'));
        $this->assertFalse($this->adminAccess::_checkGoto('chtr_1'));
    }

    public function testGotoCheckSucceeds() : void
    {
        $this->ilAccessMock->expects($this->once())
            ->method('checkAccess')
            ->with(
                $this->equalTo('visible'),
                $this->equalTo('5')
            )->willReturn(true);

        $this->assertTrue($this->adminAccess::_checkGoto('chtr_5'));
    }

    public function testGotoCheckFailsWithTargetNotBeingOfTypeString() : void
    {
        $this->assertFalse($this->adminAccess::_checkGoto(['chtr', '5']));
        $this->assertFalse($this->adminAccess::_checkGoto(5));
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->ilAccessMock = $this->getMockBuilder(ilRbacSystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkAccess'])
            ->getMock();
        $this->setGlobalVariable('rbacsystem', $this->ilAccessMock);

        $this->adminAccess = new ilObjChatroomAdminAccess();
    }
}
