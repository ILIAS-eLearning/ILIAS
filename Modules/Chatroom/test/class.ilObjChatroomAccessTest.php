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
 * Class ilObjChatroomAccessTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomAccessTest extends ilChatroomAbstractTest
{
    protected ilObjChatroomAccess $access;
    /** @var ilDBInterface&MockObject */
    protected ilDBInterface $db;

    public function testCommandDefitionFullfilsExpectations() : void
    {
        $expected = [
            ['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'enter', 'default' => true],
            ['permission' => 'write', 'cmd' => 'settings-general', 'lang_var' => 'settings'],
        ];

        $commands = $this->access::_getCommands();

        $this->assertIsArray($commands);
        $this->assertSame($expected, $commands);
    }

    public function testGotoCheckFails() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $user->method('getId')->willReturn(6);

        $this->setGlobalVariable('ilUser', $user);

        $chatroomSettings = $this->createMock(ilDBStatement::class);
        $chatroomSettings
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'keyword' => 'public_room_ref',
                    'value' => '1',
                ],
                null,
            );

        $this->db
            ->method('fetchAssoc')
            ->willReturnCallback(static function (ilDBStatement $statement) {
                return $statement->fetchAssoc();
            });

        $this->db
            ->method('query')
            ->with($this->stringContains("FROM settings WHERE module='chatroom'", false))
            ->willReturn($chatroomSettings);
        $this->setGlobalVariable('ilDB', $this->db);

        $rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->onlyMethods(
            ['checkAccess', 'checkAccessOfUser']
        )->getMock();
        $rbacsystem->method('checkAccess')->with(
            $this->logicalOr($this->equalTo('read'), $this->equalTo('visible')),
            $this->equalTo('1')
        )->willReturn(false);
        $rbacsystem->method('checkAccessOfUser')->with(
            $this->equalTo(6),
            $this->logicalOr($this->equalTo('read'), $this->equalTo('visible')),
            $this->equalTo('1')
        )->willReturn(false);

        $this->setGlobalVariable('rbacsystem', $rbacsystem);

        $this->assertFalse($this->access::_checkGoto(''));
        $this->assertFalse($this->access::_checkGoto('chtr'));
        $this->assertFalse($this->access::_checkGoto('chtr_'));
        $this->assertFalse($this->access::_checkGoto('chtr_'));
        $this->assertFalse($this->access::_checkGoto('chtr_test'));
        $this->assertFalse($this->access::_checkGoto('chtr_1'));
    }

    public function testGotoCheckSucceeds() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $user->method('getId')->willReturn(6);

        $this->setGlobalVariable('ilUser', $user);

        $chatroomSettings = $this->createMock(ilDBStatement::class);
        $chatroomSettings
            ->method('fetchAssoc')
            ->willReturnOnConsecutiveCalls(
                [
                    'keyword' => 'public_room_ref',
                    'value' => '5',
                ],
                null
            );

        $this->db
            ->method('fetchAssoc')
            ->willReturnCallback(static function (ilDBStatement $statement) {
                return $statement->fetchAssoc();
            });

        $this->db
            ->method('query')
            ->with($this->stringContains("FROM settings WHERE module='chatroom'", false))
            ->willReturn($chatroomSettings);
        $this->setGlobalVariable('ilDB', $this->db);

        $rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->onlyMethods(
            ['checkAccess', 'checkAccessOfUser']
        )->getMock();
        $rbacsystem->method('checkAccess')->with(
            $this->logicalOr($this->equalTo('read'), $this->equalTo('visible'), $this->equalTo('write')),
            $this->equalTo('5')
        )->willReturn(true);
        $rbacsystem->method('checkAccessOfUser')->with(
            $this->equalTo(6),
            $this->logicalOr($this->equalTo('read'), $this->equalTo('visible'), $this->equalTo('write')),
            $this->equalTo('5')
        )->willReturn(true);

        $this->setGlobalVariable('rbacsystem', $rbacsystem);

        $this->assertTrue($this->access::_checkGoto('chtr_5'));
    }

    public function testAccessChecksFail() : void
    {
        $userId = 1;
        $refId = 99;
        $objId = 6;

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $user->expects($this->once())->method('getId')->willReturn($userId);

        $this->setGlobalVariable('ilUser', $user);

        $rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->onlyMethods(
            ['checkAccessOfUser']
        )->getMock();
        $rbacsystem->expects($this->once())->method('checkAccessOfUser')->with(
            $this->equalTo($userId),
            $this->equalTo('write'),
            $this->equalTo($refId)
        )->willReturn(false);

        $this->setGlobalVariable('rbacsystem', $rbacsystem);

        $this->assertFalse($this->access->_checkAccess('unused', 'write', $refId, $objId));
    }

    public function testAccessChecksSucceed() : void
    {
        $userId = 1;
        $refId = 99;
        $objId = 6;

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $user->expects($this->once())->method('getId')->willReturn($userId);

        $this->setGlobalVariable('ilUser', $user);

        $this->db->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            ['keyword' => 'chat_enabled', 'value' => '0'],
            null
        );

        $rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->onlyMethods(
            ['checkAccessOfUser']
        )->getMock();
        $rbacsystem->expects($this->once())->method('checkAccessOfUser')->with(
            $this->equalTo($userId),
            $this->equalTo('write'),
            $this->equalTo($refId)
        )->willReturn(true);

        $this->setGlobalVariable('rbacsystem', $rbacsystem);

        $this->assertTrue($this->access->_checkAccess('unused', 'write', $refId, $objId));
    }

    protected function setUp() : void
    {
        parent::setUp();

        $settingsReflection = new ReflectionClass(ilSetting::class);
        $cache = $settingsReflection->getProperty('settings_cache');
        $cache->setAccessible(true);
        $cache->setValue($settingsReflection, []);

        $this->access = new ilObjChatroomAccess();
        $this->db = $this->createGlobalIlDBMock();
    }
}
