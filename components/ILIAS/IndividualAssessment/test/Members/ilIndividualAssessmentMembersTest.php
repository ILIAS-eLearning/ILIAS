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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TestObj extends ilIndividualAssessmentMembers
{
    public function buildNewRecordOfUserWrapper(ilObjUser $user): array
    {
        return $this->buildNewRecordOfUser($user);
    }

    public function initMemberRecords(array $arr): void
    {
        $this->member_records = $arr;
    }

    public function getMemberRecords(): array
    {
        return $this->member_records;
    }

    protected function userExists(int $usr_id): bool
    {
        if ($usr_id === 22) {
            return false;
        }
        return true;
    }
}

class ilIndividualAssessmentMembersTest extends TestCase
{
    /**
     * @var ilObjIndividualAssessment|mixed|MockObject
     */
    private $iass;

    protected function setUp(): void
    {
        $this->iass = $this->createMock(ilObjIndividualAssessment::class);
    }

    public function test_createObject(): void
    {
        $obj = new ilIndividualAssessmentMembers($this->iass);
        $this->assertInstanceOf(ilIndividualAssessmentMembers::class, $obj);
    }

    public function test_count(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4,5]);
        $this->assertEquals(5, $obj->count());
    }

    public function test_current(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4,5]);
        $this->assertEquals(1, $obj->current());
        $obj->initMemberRecords([21,324,53,54,55]);
        $this->assertEquals(21, $obj->current());
        $obj->next();
        $this->assertEquals(324, $obj->current());
    }

    public function test_current_false(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1]);
        $obj->next();
        $this->assertFalse($obj->current());
    }

    public function test_key(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4]);
        $this->assertEquals(0, $obj->key());
        $obj->next();
        $this->assertEquals(1, $obj->key());
    }

    public function test_key_null(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([]);
        $this->assertNull($obj->key());
    }

    public function test_key_string(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords(["test" => "foo", "bar" => "boo"]);
        $this->assertEquals("test", $obj->key());
        $obj->next();
        $this->assertEquals("bar", $obj->key());
    }

    public function test_next(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4,5]);
        $obj->next();
        $obj->next();
        $obj->next();
        $this->assertEquals(4, $obj->current());
    }

    public function test_rewind(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4,5]);
        $obj->next();
        $obj->next();
        $obj->next();
        $this->assertEquals(4, $obj->current());
        $obj->rewind();
        $this->assertEquals(1, $obj->current());
    }

    public function test_valid(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1,2,3,4,5]);
        $this->assertTrue($obj->valid());
    }

    public function test_valid_false(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([1]);
        $this->assertTrue($obj->valid());
        $obj->next();
        $this->assertFalse($obj->valid());
    }

    public function test_recordOK_with_non_existing_user(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;
        $usr
            ->expects($this->once())
            ->method("getFirstname")
            ->willReturn("Firstname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLastname")
            ->willReturn("Lastname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLogin")
            ->willReturn("Firstname Lastname")
        ;

        $obj = new TestObj($this->iass);
        $record = $obj->buildNewRecordOfUserWrapper($usr);

        $this->assertFalse($obj->recordOK($record));
    }

    public function test_recordOK_with_already_existing_user(): void
    {
        $usr = $this->getRecordOKUserMock();

        $obj = new TestObj($this->iass);

        $obj->initMemberRecords([23 => "already_member"]);
        $record = $obj->buildNewRecordOfUserWrapper($usr);

        $this->assertFalse($obj->recordOK($record));
    }

    public function test_recordOK_with_wrong_lp_status(): void
    {
        $usr = $this->getRecordOKUserMock();

        $obj = new TestObj($this->iass);

        $record = $obj->buildNewRecordOfUserWrapper($usr);
        $record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS] = 5;

        $this->assertFalse($obj->recordOK($record));
    }

    public function test_recordOK(): void
    {
        $usr = $this->getRecordOKUserMock();

        $obj = new TestObj($this->iass);

        $record = $obj->buildNewRecordOfUserWrapper($usr);

        $this->assertTrue($obj->recordOK($record));
    }

    /**
     * @return ilObjUser|MockObject
     */
    public function getRecordOKUserMock()
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(23)
        ;
        $usr
            ->expects($this->once())
            ->method("getFirstname")
            ->willReturn("Firstname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLastname")
            ->willReturn("Lastname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLogin")
            ->willReturn("Firstname Lastname")
        ;

        return $usr;
    }

    public function test_userAllreadyMemberByUsrId(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([22 => "is_set"]);
        $this->assertTrue($obj->userAllreadyMemberByUsrId(22));
    }

    public function test_userAllreadyMemberByUsrId_false(): void
    {
        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([23 => "is_set"]);
        $this->assertFalse($obj->userAllreadyMemberByUsrId(22));
    }

    public function test_userAllreadyMember(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([22 => "is_set"]);
        $this->assertTrue($obj->userAllreadyMember($usr));
    }

    public function test_userAllreadyMember_false(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([23 => "is_set"]);
        $this->assertFalse($obj->userAllreadyMember($usr));
    }

    public function test_withAdditionalRecord(): void
    {
        $usr = $this->getRecordOKUserMock();

        $obj = new TestObj($this->iass);

        $record = $obj->buildNewRecordOfUserWrapper($usr);
        $new_obj = $obj->withAdditionalRecord($record);

        $records = $obj->getMemberRecords();
        $this->assertEmpty($records);

        $records = $new_obj->getMemberRecords();
        $record = $records[23];

        $this->assertEquals(23, $record[ilIndividualAssessmentMembers::FIELD_USR_ID]);
        $this->assertEquals("", $record[ilIndividualAssessmentMembers::FIELD_RECORD]);
        $this->assertEquals(0, $record[ilIndividualAssessmentMembers::FIELD_NOTIFY]);
        $this->assertEquals("Firstname", $record[ilIndividualAssessmentMembers::FIELD_FIRSTNAME]);
        $this->assertEquals("Lastname", $record[ilIndividualAssessmentMembers::FIELD_LASTNAME]);
        $this->assertEquals("Firstname Lastname", $record[ilIndividualAssessmentMembers::FIELD_LOGIN]);
        $this->assertEquals(0, $record[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_EXAMINER_LASTNAME]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_FILE_NAME]);
        $this->assertFalse($record[ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE]);
        $this->assertEquals(0, $record[ilIndividualAssessmentMembers::FIELD_FINALIZED]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_CHANGER_ID]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_CHANGER_FIRSTNAME]);
        $this->assertNull($record[ilIndividualAssessmentMembers::FIELD_CHANGER_LASTNAME]);
    }

    public function test_withAdditionalRecord_exceptio(): void
    {
        $obj = new TestObj($this->iass);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("Ill defined record.");
        $obj->withAdditionalRecord([ilIndividualAssessmentMembers::FIELD_USR_ID => 22]);
    }

    public function test_withAdditionalUser(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->any())
            ->method("getId")
            ->willReturn(23)
        ;
        $usr
            ->expects($this->once())
            ->method("getFirstname")
            ->willReturn("Firstname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLastname")
            ->willReturn("Lastname")
        ;
        $usr
            ->expects($this->once())
            ->method("getLogin")
            ->willReturn("Firstname Lastname")
        ;

        $obj = new TestObj($this->iass);

        $new_obj = $obj->withAdditionalUser($usr);
        $record = $obj->getMemberRecords();
        $this->assertFalse(isset($record[23]));


        $record = $new_obj->getMemberRecords();
        $this->assertTrue(isset($record[23]));
    }

    public function test_withAdditionalUser_exception(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->any())
            ->method("getId")
            ->willReturn(23)
        ;

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([23 => "test"]);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("User allready member");
        $obj->withAdditionalUser($usr);
    }

    public function test_withoutPresentUser(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->any())
            ->method("getId")
            ->willReturn(23)
        ;

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([23 => [ilIndividualAssessmentMembers::FIELD_FINALIZED => "0"]]);

        $new_obj = $obj->withoutPresentUser($usr);
        $record = $obj->getMemberRecords();
        $this->assertTrue(isset($record[23]));

        $record = $new_obj->getMemberRecords();
        $this->assertFalse(isset($record[23]));
    }

    public function test_withoutPresentUser_without_existing_user(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->any())
            ->method("getId")
            ->willReturn(23)
        ;

        $obj = new TestObj($this->iass);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("User not member or allready finished");
        $obj->withoutPresentUser($usr);
    }

    public function test_withoutPresentUser_already_finalized(): void
    {
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->any())
            ->method("getId")
            ->willReturn(23)
        ;

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([23 => [ilIndividualAssessmentMembers::FIELD_FINALIZED => "1"]]);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("User not member or allready finished");
        $obj->withoutPresentUser($usr);
    }

    public function test_withOnlyUsersByIds(): void
    {
        $keep_usr_ids = [18, 22];

        $obj = new TestObj($this->iass);
        $obj->initMemberRecords([
            23 => "user",
            44 => "user",
            365 => "user",
            18 => "user",
            45 => "user",
            22 => "user",
            16 => "user"
        ]);

        $new_obj = $obj->withOnlyUsersByIds($keep_usr_ids);
        $this->assertEmpty(array_diff([23, 44, 365, 18, 44, 22, 16], array_keys($obj->getMemberRecords())));

        $this->assertEmpty(array_diff([18, 22], array_keys($new_obj->getMemberRecords())));
    }
}
