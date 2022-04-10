<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjChatroomTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomTest extends ilChatroomAbstractTest
{
    protected ilObjChatroom $object;

    public function testPersonalInformationCanBeRetrieved() : void
    {
        $this->ilChatroomUserMock->expects($this->once())->method('getUserId')->willReturn(6);
        $this->ilChatroomUserMock->expects($this->once())->method('getUsername')->willReturn('username');

        $userInfo = $this->object->getPersonalInformation($this->ilChatroomUserMock);

        $this->assertInstanceOf(stdClass::class, $userInfo);
        $this->assertEquals('username', $userInfo->username);
        $this->assertEquals(6, $userInfo->id);
    }

    public function testPublicRoomRefIdCanBeRetrieved() : void
    {
        $this->createGlobalIlDBMock();

        $this->assertEquals(0, $this->object::_getPublicRefId());
    }

    public function testPublicRoomObjIdCanBeRetrieved() : void
    {
        $db = $this->createGlobalIlDBMock();

        $db->expects($this->once())->method('fetchAssoc')->willReturn(['object_id' => '6']);

        $this->assertEquals(6, $this->object::_getPublicObjId());
    }

    public function testPublicRoomObjIdDefaultValueCanBeRetrieved() : void
    {
        $db = $this->createGlobalIlDBMock();

        $db->expects($this->once())->method('fetchAssoc')->willReturn(null);

        $this->assertEquals(0, $this->object::_getPublicObjId());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->createIlChatroomUserMock();

        $this->object = (new ReflectionClass(ilObjChatroom::class))->newInstanceWithoutConstructor();
    }
}
