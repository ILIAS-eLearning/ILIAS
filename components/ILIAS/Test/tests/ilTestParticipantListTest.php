<?php

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

declare(strict_types=1);

/**
 * Class ilTestParticipantListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantListTest extends ilTestBaseTestCase
{
    private ilTestParticipantList $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilUser();
        $this->addGlobal_lng();

        $this->testObj = new ilTestParticipantList(
            $this->createMock(ilObjTest::class),
            $DIC['ilUser'],
            $DIC['ilLanguage'],
            $DIC['ilDB'],
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantList::class, $this->testObj);
    }

    public function testAddParticipant(): void
    {
        $activeId = 22;
        $participant = new ilTestParticipant();
        $participant->setActiveId($activeId);
        $this->testObj->addParticipant($participant);
        $this->assertEquals($participant, $this->testObj->getParticipantByActiveId($activeId));
    }

    public function testGetParticipantByUsrId(): void
    {
        $usr_id = 125;
        $participant = new ilTestParticipant();
        $participant->setUsrId($usr_id);
        $this->testObj->addParticipant($participant);
        $this->assertEquals($participant, $this->testObj->getParticipantByUsrId($usr_id));
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

            $this->assertTrue($this->testObj->isActiveIdInList($id));
        }
        $this->assertFalse($this->testObj->isActiveIdInList(PHP_INT_MAX));
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
            176
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $callback = static function ($userIds) use ($expected) {
            return $expected;
        };

        foreach ($expected as $value) {
            $this->assertInstanceOf(ilTestParticipant::class, $this->testObj->getAccessFilteredList($callback)->getParticipantByUsrId($value));
        }
    }

    public function testCurrent(): void
    {
        $ids = [
            12,
            125,
            176
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        if (isset($ids[0])) {
            $this->assertEquals($ids[0], $this->testObj->current()->getUsrId());
        } else {
            $this->assertTrue(false);
        }
    }

    public function testNext(): void
    {
        $ids = [
            12,
            125,
            176
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $countIds = count($ids);
        if ($countIds > 0) {
            for ($i = 1; $i < $countIds; $i++) {
                $this->testObj->next();
            }

            $this->assertEquals($ids[--$countIds], $this->testObj->current()->getUsrId());
        }
    }

    public function testKey(): void
    {
        $ids = [
            12,
            125,
            176
        ];

        foreach ($ids as $key => $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);

            $this->assertEquals($key, $this->testObj->key());
            $this->testObj->next();
        }
    }

    public function testValid(): void
    {
        $ids = [
            12,
            125,
            176
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        for ($i = 0, $iMax = count($ids) - 1; $i < $iMax; $i++) {
            $this->testObj->next();
        }
        $this->assertTrue($this->testObj->valid());

        $this->testObj->next();
        $this->assertFalse($this->testObj->valid());
    }

    public function testRewind(): void
    {
        $ids = [
            12,
            125,
            176
        ];

        foreach ($ids as $id) {
            $participant = new ilTestParticipant();
            $participant->setUsrId($id);
            $this->testObj->addParticipant($participant);
        }

        $countIds = count($ids);
        if ($countIds > 0) {
            for ($i = 1; $i < $countIds; $i++) {
                $this->testObj->next();
            }
            $this->assertTrue($this->testObj->valid());

            $this->testObj->rewind();
            $this->assertEquals($ids[0], $this->testObj->current()->getUsrId());
        } else {
            $this->assertTrue(false);
        }
    }
}
