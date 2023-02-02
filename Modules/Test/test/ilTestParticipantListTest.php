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
 * Class ilTestParticipantListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantListTest extends ilTestBaseTestCase
{
    private ilTestParticipantList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantList($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantList::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestObj());
    }

    public function testAddParticipant(): void
    {
        $participant = new ilTestParticipant();
        $participant->setActiveId(22);
        $this->testObj->addParticipant($participant);
        $this->assertEquals($participant, $this->testObj->getParticipantByActiveId(22));
    }

    public function testGetParticipantByUsrId(): void
    {
        $participant = new ilTestParticipant();
        $participant->setUsrId(125);
        $this->testObj->addParticipant($participant);
        $this->assertEquals($participant, $this->testObj->getParticipantByUsrId(125));
    }

    public function testHasUnfinishedPasses(): void
    {
        $participant = new ilTestParticipant();
        $participant->setUnfinishedPasses(false);
        $this->testObj->addParticipant($participant);

        $this->assertFalse($this->testObj->hasUnfinishedPasses());

        $participant = new ilTestParticipant();
        $participant->setUnfinishedPasses(true);
        $this->testObj->addParticipant($participant);

        $this->assertTrue($this->testObj->hasUnfinishedPasses());
    }

    public function testGetAllUserIds(): void
    {
        $ids = [
            12,
            125,
            176,
            12111,
            1
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }
        $this->assertEquals($ids, $this->testObj->getAllUserIds());
    }

    public function testGetAllActiveIds(): void
    {
        $ids = [
            12,
            125,
            176,
            12111,
            1
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setActiveId($id);
            $this->testObj->addParticipant($participant);
        }
        $this->assertEquals($ids, $this->testObj->getAllActiveIds());
    }

    public function testIsActiveIdInList(): void
    {
        $ids = [
            12,
            125,
            176,
            12111,
            1
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setActiveId($id);
            $this->testObj->addParticipant($participant);
        }
        $this->assertTrue($this->testObj->isActiveIdInList(12));
        $this->assertFalse($this->testObj->isActiveIdInList(222222));
    }

    public function testGetAccessFilteredList(): void
    {
        $ids = [
            12,
            125,
            176,
            12111,
            1
        ];

        $expected = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $callback = static function ($userIds) use ($expected) {
            return $expected;
        };

        $result = $this->testObj->getAccessFilteredList($callback);

        $this->assertNotNull($result->getParticipantByUsrId(12));
        $this->assertNotNull($result->getParticipantByUsrId(125));
        $this->assertNotNull($result->getParticipantByUsrId(176));
        $this->assertNull($result->getParticipantByUsrId(212121));
    }

    public function testCurrent(): void
    {
        $ids = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $this->assertEquals($ids[0], $this->testObj->current()->getUsrId());

        $this->testObj->next();
        $this->assertEquals($ids[1], $this->testObj->current()->getUsrId());
    }

    public function testNext(): void
    {
        $ids = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $this->testObj->next();
        $this->testObj->next();

        $this->assertEquals($ids[2], $this->testObj->current()->getUsrId());
    }

    public function testKey(): void
    {
        $ids = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $this->testObj->next();
        $this->testObj->next();

        $this->assertEquals(2, $this->testObj->key());
    }

    public function testValid(): void
    {
        $ids = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $this->testObj->next();
        $this->testObj->next();
        $this->assertTrue($this->testObj->valid());

        $this->testObj->next();
        $this->assertFalse($this->testObj->valid());
    }

    public function testRewind(): void
    {
        $ids = [
            12,
            125,
            176,
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $this->testObj->next();
        $this->testObj->next();
        $this->assertEquals($ids[2], $this->testObj->current()->getUsrId());

        $this->testObj->rewind();
        $this->assertEquals($ids[0], $this->testObj->current()->getUsrId());
    }
}
