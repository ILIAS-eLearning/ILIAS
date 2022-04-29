<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilObjChatroomAdminAccessTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAdminAccessTest extends ilChatroomAbstractTest
{
    protected ilObjChatroomAdminAccess $adminAccess;
    /** @var ilRbacSystem&MockObject */
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
        $this->assertSame($expected, $commands);
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
