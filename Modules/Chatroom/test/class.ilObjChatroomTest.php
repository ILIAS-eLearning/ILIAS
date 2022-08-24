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

/**
 * Class ilObjChatroomTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilObjChatroomTest extends ilChatroomAbstractTest
{
    protected ilObjChatroom $object;

    public function testPersonalInformationCanBeRetrieved(): void
    {
        $this->ilChatroomUserMock->expects($this->once())->method('getUserId')->willReturn(6);
        $this->ilChatroomUserMock->expects($this->once())->method('getUsername')->willReturn('username');

        $userInfo = $this->object->getPersonalInformation($this->ilChatroomUserMock);

        $this->assertInstanceOf(stdClass::class, $userInfo);
        $this->assertSame('username', $userInfo->username);
        $this->assertSame(6, $userInfo->id);
    }

    public function testPublicRoomObjIdCanBeRetrieved(): void
    {
        $db = $this->createGlobalIlDBMock();

        $db->expects($this->once())->method('fetchAssoc')->willReturn(['object_id' => '6']);

        $this->assertSame(6, $this->object::_getPublicObjId());
    }

    public function testPublicRoomObjIdDefaultValueCanBeRetrieved(): void
    {
        $db = $this->createGlobalIlDBMock();

        $db->expects($this->once())->method('fetchAssoc')->willReturn(null);

        $this->assertSame(0, $this->object::_getPublicObjId());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createIlChatroomUserMock();

        $this->object = (new ReflectionClass(ilObjChatroom::class))->newInstanceWithoutConstructor();
    }
}
