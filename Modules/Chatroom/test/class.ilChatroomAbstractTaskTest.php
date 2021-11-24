<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * Class ilChatroomAbstractTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTaskTest extends ilChatroomAbstractTest
{
    private const TEST_REF_ID = 99;

    /** @var MockObject|ilChatroomObjectGui */
    protected $gui;

    /** @var MockObject|ilChatroomServerConnector */
    protected $ilChatroomServerConnectorMock;

    /** @var MockObject|ilObjChatroom */
    protected $object;

    protected function setUp() : void
    {
        parent::setUp();

        if (!defined('ROOT_FOLDER_ID')) {
            define('ROOT_FOLDER_ID', time());
        }
    }

    protected function createGlobalIlLanguageMock() : ilLanguage
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

    protected function createGlobalRbacSystemMock() : ilRbacSystem
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

    protected function createGlobalIlCtrlMock() : ilCtrl
    {
        $ctrl = $this->getMockBuilder('ilCtrl')->disableOriginalConstructor()->onlyMethods(
            ['setParameterByClass', 'redirectByClass', 'forwardCommand']
        )->getMock();
        $ctrl->method('setParameterByClass');
        $ctrl->method('redirectByClass');

        $this->setGlobalVariable('ilCtrl', $ctrl);

        return $GLOBALS['ilCtrl'];
    }

    protected function createGlobalIlUserMock() : ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        $this->setGlobalVariable('ilUser', $user);

        return $user;
    }

    protected function createIlObjChatroomGUIMock($object) : ilObjChatroomGUI
    {
        $this->gui = $this->getMockBuilder(ilObjChatroomGUI::class)
            ->disableOriginalConstructor()
            ->disableArgumentCloning()
            ->onlyMethods(
                ['getRefId', 'getConnector', 'switchToVisibleMode']
            )->getMock();
        $this->gui->ref_id = self::TEST_REF_ID;
        $this->gui->object = $object;

        return $this->gui;
    }

    protected function createIlObjChatroomGUIGetConnectorMock($returnValue) : void
    {
        $this->gui->method('getConnector')->willReturn($returnValue);
    }

    protected function createIlChatroomIsOwnerOfPrivateRoomMock(
        int $userId,
        int $subRoomId,
        bool $result
    ) : InvocationMocker {
        return $this->ilChatroomMock->method('isOwnerOfPrivateRoom')->with(
            $this->equalTo($userId),
            $this->equalTo($subRoomId)
        )->willReturn($result);
    }

    protected function createIlChatroomUserGetUserIdMock(int $userId) : InvocationMocker
    {
        return $this->ilChatroomUserMock->method('getUserId')->willReturn($userId);
    }

    protected function createIlChatroomServerConnectorMock(
        ilChatroomServerSettings $settings
    ) : ilChatroomServerConnector {
        $this->ilChatroomServerConnectorMock = $this->getMockBuilder(ilChatroomServerConnector::class)->setConstructorArgs(
            [$settings]
        )->onlyMethods(['file_get_contents'])->getMock();

        return $this->ilChatroomServerConnectorMock;
    }

    protected function createIlChatroomServerConnectorFileGetContentsMock($returnValue) : InvocationMocker
    {
        return $this->ilChatroomServerConnectorMock->method('file_get_contents')->willReturn(
            $returnValue
        );
    }

    protected function createIlObjChatroomMock(int $id) : ilObjChatroom
    {
        $this->object = $this->getMockBuilder(ilObjChatroom::class)->disableOriginalConstructor()->onlyMethods(
            ['getId']
        )->getMock();
        $this->object->method('getId')->willReturn($id);

        return $this->object;
    }

    protected function createSendResponseMock(MockObject $mock, $response) : InvocationMocker
    {
        $mock->expects($this->once())->method('sendResponse')->with(
            $this->equalTo($response)
        )->willReturnCallback(
            static function () : void {
                throw new Exception('Exit', 0);
            }
        );
    }
}
