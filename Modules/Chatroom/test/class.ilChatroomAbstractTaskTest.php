<?php

declare(strict_types=1);

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

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * Class ilChatroomAbstractTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTaskTest extends ilChatroomAbstractTest
{
    /** @var MockObject&ilChatroomObjectGUI */
    protected $gui;
    /** @var MockObject&ilChatroomServerConnector */
    protected $ilChatroomServerConnectorMock;
    /** @var MockObject&ilObjChatroom */
    protected $object;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', time());
        }
    }

    protected function createGlobalIlLanguageMock(): ilLanguage
    {
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->onlyMethods(
            ['loadLanguageModule', 'txt']
        )->getMock();

        $lng->method('loadLanguageModule')->with(
            $this->logicalOr(
                $this->equalTo('chatroom'),
                $this->equalTo('meta')
            )
        );
        $lng->method('txt');

        $this->setGlobalVariable('lng', $lng);

        return $lng;
    }

    protected function createGlobalRbacSystemMock(): ilRbacSystem
    {
        $rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->onlyMethods(
            ['checkAccess']
        )->getMock();

        $this->setGlobalVariable('rbacsystem', $rbacsystem);

        return $rbacsystem;
    }

    protected function createGlobalRbacSystemCheckAccessMock(
        string $permission,
        bool $result,
        ?InvocationOrder $times = null
    ) {
        if ($times === null) {
            $times = $this->any();
        }

        return $GLOBALS['rbacsystem']->expects($times)
            ->method('checkAccess')
            ->with($this->equalTo($permission))->willReturn(
                $result
            );
    }

    protected function createGlobalIlCtrlMock(): ilCtrlInterface
    {
        $ctrl = $this->createMock(ilCtrlInterface::class);

        $this->setGlobalVariable('ilCtrl', $ctrl);

        return $GLOBALS['ilCtrl'];
    }

    protected function createGlobalIlUserMock(): ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $this->setGlobalVariable('ilUser', $user);

        return $user;
    }

    protected function createIlObjChatroomGUIGetConnectorMock($returnValue): void
    {
        $this->gui->method('getConnector')->willReturn($returnValue);
    }

    protected function createIlChatroomIsOwnerOfPrivateRoomMock(
        int $userId,
        int $subRoomId,
        bool $result
    ): InvocationMocker {
        return $this->ilChatroomMock->method('isOwnerOfPrivateRoom')->with(
            $this->equalTo($userId),
            $this->equalTo($subRoomId)
        )->willReturn($result);
    }

    protected function createIlChatroomUserGetUserIdMock(int $userId): InvocationMocker
    {
        return $this->ilChatroomUserMock->method('getUserId')->willReturn($userId);
    }

    protected function createIlChatroomServerConnectorMock(
        ilChatroomServerSettings $settings
    ): ilChatroomServerConnector {
        $this->ilChatroomServerConnectorMock = $this->getMockBuilder(ilChatroomServerConnector::class)->setConstructorArgs(
            [$settings]
        )->onlyMethods(['file_get_contents'])->getMock();

        return $this->ilChatroomServerConnectorMock;
    }

    protected function createIlChatroomServerConnectorFileGetContentsMock($returnValue): InvocationMocker
    {
        return $this->ilChatroomServerConnectorMock->method('file_get_contents')->willReturn(
            $returnValue
        );
    }

    protected function createIlObjChatroomMock(int $id): ilObjChatroom
    {
        $this->object = $this->getMockBuilder(ilObjChatroom::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $this->object->method('getId')->willReturn($id);

        return $this->object;
    }

    protected function createSendResponseMock(MockObject $mock, $response): void
    {
        $mock->expects($this->once())->method('sendResponse')->with(
            $this->equalTo($response)
        )->willReturnCallback(
            static function (): void {
                throw new Exception('Exit', 0);
            }
        );
    }
}
