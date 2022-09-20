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

class ilIndividualAssessmentMembersStorageDBWrapper extends ilIndividualAssessmentMembersStorageDB
{
    public function createAssessmentMemberWrapper(
        ilObjIndividualAssessment $obj,
        ilObjUser $usr,
        array $record
    ): ilIndividualAssessmentMember {
        return $this->createAssessmentMember($obj, $usr, $record);
    }

    // The access modifier is changed to public here to allow the actual test
    // to access this.
    public function getActualDateTime(): string
    {
        return "2021-12-02 12:55:33";
    }

    /**
     * @param string|int $filter
     */
    public function getWhereFromFilterWrapper($filter): string
    {
        return $this->getWhereFromFilter($filter);
    }

    public function getOrderByFromSortWrapper(string $sort): string
    {
        return $this->getOrderByFromSort($sort);
    }
}

class ilIndividualAssessmentMembersStorageDBTest extends TestCase
{
    public function testCreateObject(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDB($db);
        $this->assertInstanceOf(ilIndividualAssessmentMembersStorageDB::class, $obj);
    }

    public function test_loadMembers(): void
    {
        $sql = "SELECT ex.firstname as " . ilIndividualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME
            . "     , ex.lastname as " . ilIndividualAssessmentMembers::FIELD_EXAMINER_LASTNAME
            . "     , ud.firstname as " . ilIndividualAssessmentMembers::FIELD_CHANGER_FIRSTNAME
            . "     , ud.lastname as " . ilIndividualAssessmentMembers::FIELD_CHANGER_LASTNAME
            . "     ,usr.firstname as " . ilIndividualAssessmentMembers::FIELD_FIRSTNAME
            . "     ,usr.lastname as " . ilIndividualAssessmentMembers::FIELD_LASTNAME
            . "     ,usr.login as " . ilIndividualAssessmentMembers::FIELD_LOGIN
            . "	   ,iassme." . ilIndividualAssessmentMembers::FIELD_FILE_NAME
            . "     ,iassme.obj_id, iassme.usr_id, iassme.examiner_id, iassme.record, iassme.internal_note, iassme.notify"
            . "     ,iassme.notification_ts, iassme.learning_progress, iassme.finalized,iassme.place"
            . "     ,iassme.event_time, iassme.changer_id, iassme.change_time\n"
            . " FROM iass_members iassme"
            . " JOIN usr_data usr ON iassme.usr_id = usr.usr_id"
            . " LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id"
            . " LEFT JOIN usr_data ud ON iassme.changer_id = ud.usr_id"
            . " WHERE obj_id = 22";


        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $db_statement = $this->createMock(ilDBStatement::class);

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->once())
            ->method("quote")
            ->with(22, "integer")
            ->willReturn("22")
        ;
        $db
            ->expects($this->once())
            ->method("query")
            ->with($sql)
            ->willReturn($db_statement)
        ;
        $db
            ->expects($this->any())
            ->method("fetchAssoc")
            ->with($db_statement)
            ->willReturn(null)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);
        $result = $obj->loadMembers($iass);
        $this->assertInstanceOf(ilIndividualAssessmentMembers::class, $result);
    }

    public function test_loadMembersAsSingleObjects(): void
    {
        $sql = "SELECT "
            . "iassme.obj_id,"
            . "iassme.usr_id,"
            . "iassme.examiner_id,"
            . "iassme.record,"
            . "iassme.internal_note,"
            . "iassme.notify,"
            . "iassme.notification_ts,"
            . "iassme.learning_progress,"
            . "iassme.finalized,"
            . "iassme.place,"
            . "iassme.event_time,"
            . "iassme.user_view_file,"
            . "iassme.file_name,"
            . "iassme.changer_id,"
            . "iassme.change_time,"
            . "usr.login AS user_login,"
            . "ex.login AS examiner_login"
            . " FROM " . ilIndividualAssessmentMembersStorageDB::MEMBERS_TABLE . " iassme\n"
            . "	JOIN usr_data usr ON iassme.usr_id = usr.usr_id\n"
            . "	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id\n"
            . "	WHERE obj_id = 22"
        ;

        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $db_statement = $this->createMock(ilDBStatement::class);

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->once())
            ->method("quote")
            ->with(22, "integer")
            ->willReturn("22")
        ;
        $db
            ->expects($this->once())
            ->method("query")
            ->with($sql)
            ->willReturn($db_statement)
        ;
        $db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->with($db_statement)
            ->willReturn(null)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);
        $result = $obj->loadMembersAsSingleObjects($iass);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_loadMember_exception(): void
    {
        $sql = "SELECT "
            . "iassme.obj_id,"
            . "iassme.usr_id,"
            . "iassme.examiner_id,"
            . "iassme.record,"
            . "iassme.internal_note,"
            . "iassme.notify,"
            . "iassme.notification_ts,"
            . "iassme.learning_progress,"
            . "iassme.finalized,"
            . "iassme.place,"
            . "iassme.event_time,"
            . "iassme.user_view_file,"
            . "iassme.file_name,"
            . "iassme.changer_id,"
            . "iassme.change_time,"
            . "usr.login AS user_login,"
            . "ex.login AS examiner_login"
            . " FROM " . ilIndividualAssessmentMembersStorageDB::MEMBERS_TABLE . " iassme\n"
            . "	JOIN usr_data usr ON iassme.usr_id = usr.usr_id\n"
            . "	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id\n"
            . "	WHERE obj_id = 22\n"
            . "		AND iassme.usr_id = 33"
        ;

        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(33)
        ;

        $db_statement = $this->createMock(ilDBStatement::class);

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(2))
            ->method("quote")
            ->withConsecutive([22, "integer"], [33, "integer"])
            ->willReturnOnConsecutiveCalls("22", "33")
        ;
        $db
            ->expects($this->once())
            ->method("query")
            ->with($sql)
            ->willReturn($db_statement)
        ;
        $db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->with($db_statement)
            ->willReturn(null)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("invalid usr-obj combination");
        $obj->loadMember($iass, $usr);
    }

    public function test_loadMember(): void
    {
        $sql = "SELECT "
            . "iassme.obj_id,"
            . "iassme.usr_id,"
            . "iassme.examiner_id,"
            . "iassme.record,"
            . "iassme.internal_note,"
            . "iassme.notify,"
            . "iassme.notification_ts,"
            . "iassme.learning_progress,"
            . "iassme.finalized,"
            . "iassme.place,"
            . "iassme.event_time,"
            . "iassme.user_view_file,"
            . "iassme.file_name,"
            . "iassme.changer_id,"
            . "iassme.change_time,"
            . "usr.login AS user_login,"
            . "ex.login AS examiner_login"
            . " FROM " . ilIndividualAssessmentMembersStorageDB::MEMBERS_TABLE . " iassme\n"
            . "	JOIN usr_data usr ON iassme.usr_id = usr.usr_id\n"
            . "	LEFT JOIN usr_data ex ON iassme.examiner_id = ex.usr_id\n"
            . "	WHERE obj_id = 22\n"
            . "		AND iassme.usr_id = 33"
        ;

        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getId")
            ->willReturn(33)
        ;

        $db_statement = $this->createMock(ilDBStatement::class);

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(2))
            ->method("quote")
            ->withConsecutive([22, "integer"], [33, "integer"])
            ->willReturnOnConsecutiveCalls("22", "33")
        ;
        $db
            ->expects($this->once())
            ->method("query")
            ->with($sql)
            ->willReturn($db_statement)
        ;
        $db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->with($db_statement)
            ->willReturn(null)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage("invalid usr-obj combination");
        $obj->loadMember($iass, $usr);
    }

    public function test_createAssessmentMember(): void
    {
        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $usr = $this->createMock(ilObjUser::class);
        $usr
            ->expects($this->once())
            ->method("getFullname")
            ->willReturn("Full Name")
        ;

        $timestamp = 1638431626;

        $record = [
            ilIndividualAssessmentMembers::FIELD_CHANGER_ID => 11,
            ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => "2021-12-02",
            ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => 22,
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => $timestamp,
            ilIndividualAssessmentMembers::FIELD_EVENTTIME => $timestamp,
            ilIndividualAssessmentMembers::FIELD_RECORD => "record",
            ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => "internal_note",
            ilIndividualAssessmentMembers::FIELD_FILE_NAME => "file_name",
            ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => true,
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => 33,
            ilIndividualAssessmentMembers::FIELD_PLACE => "place",
            ilIndividualAssessmentMembers::FIELD_NOTIFY => true,
            ilIndividualAssessmentMembers::FIELD_FINALIZED => true
        ];

        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDBWrapper($db);

        $member = $obj->createAssessmentMemberWrapper($iass, $usr, $record);

        $this->assertEquals("Full Name", $member->getGrading()->getName());
        $this->assertEquals(11, $member->changerId());
        $this->assertEquals("2021-12-02", $member->changeTime()->format("Y-m-d"));
        $this->assertEquals(22, $member->examinerId());
        $this->assertEquals($timestamp, $member->notificationTS());
        $this->assertEquals($timestamp, $member->eventTime()->getTimestamp());
        $this->assertEquals("record", $member->record());
        $this->assertEquals("internal_note", $member->internalNote());
        $this->assertEquals("file_name", $member->fileName());
        $this->assertTrue($member->viewFile());
        $this->assertEquals(33, $member->LPStatus());
        $this->assertTrue($member->notify());
        $this->assertTrue($member->finalized());
    }

    public function test_updateMember(): void
    {
        $timestamp = 1638431626;
        $date = (new DateTimeImmutable())->setTimestamp($timestamp);

        $member = $this->createMock(ilIndividualAssessmentMember::class);
        $member
            ->expects($this->once())
            ->method("assessmentId")
            ->willReturn(11)
        ;
        $member
            ->expects($this->once())
            ->method("id")
            ->willReturn(22)
        ;
        $member
            ->expects($this->once())
            ->method("eventTime")
            ->willReturn($date)
        ;
        $member
            ->expects($this->once())
            ->method("LPStatus")
            ->willReturn(33)
        ;
        $member
            ->expects($this->once())
            ->method("examinerId")
            ->willReturn(44)
        ;
        $member
            ->expects($this->once())
            ->method("record")
            ->willReturn("record")
        ;
        $member
            ->expects($this->once())
            ->method("internalNote")
            ->willReturn("internalNote")
        ;
        $member
            ->expects($this->once())
            ->method("place")
            ->willReturn("place")
        ;
        $member
            ->expects($this->once())
            ->method("notify")
            ->willReturn(true)
        ;
        $member
            ->expects($this->once())
            ->method("finalized")
            ->willReturn(true)
        ;
        $member
            ->expects($this->once())
            ->method("notificationTS")
            ->willReturn($timestamp)
        ;
        $member
            ->expects($this->once())
            ->method("fileName")
            ->willReturn("fileName")
        ;
        $member
            ->expects($this->once())
            ->method("viewFile")
            ->willReturn(true)
        ;
        $member
            ->expects($this->once())
            ->method("changerId")
            ->willReturn(55)
        ;

        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDBWrapper($db);

        $where = [
            "obj_id" => ["integer", 11],
            "usr_id" => ["integer", 22]
        ];

        $values = [
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => ["text", 33],
            ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => ["integer", 44],
            ilIndividualAssessmentMembers::FIELD_RECORD => ["text", "record"],
            ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => ["text", "internalNote"],
            ilIndividualAssessmentMembers::FIELD_PLACE => ["text", "place"],
            ilIndividualAssessmentMembers::FIELD_EVENTTIME => ["integer", $timestamp],
            ilIndividualAssessmentMembers::FIELD_NOTIFY => ["integer", true],
            ilIndividualAssessmentMembers::FIELD_FINALIZED => ["integer", true],
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => ["integer", $timestamp],
            ilIndividualAssessmentMembers::FIELD_FILE_NAME => ["text", "fileName"],
            ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => ["integer", true],
            ilIndividualAssessmentMembers::FIELD_CHANGER_ID => ["integer", 55],
            ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => ["string", $obj->getActualDateTime()]
        ];

        $db
            ->expects($this->once())
            ->method("update")
            ->with("iass_members", $values, $where)
        ;


        $obj->updateMember($member);
    }

    public function test_deleteMembers(): void
    {
        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $sql = "DELETE FROM iass_members WHERE obj_id = 22";

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->once())
            ->method("quote")
            ->with(22, "integer")
            ->willReturn("22")
        ;
        $db
            ->expects($this->once())
            ->method("manipulate")
            ->with($sql)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);
        $obj->deleteMembers($iass);
    }

    public function test_insertMembersRecord(): void
    {
        $timestamp = 1638431626;

        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(11)
        ;

        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDBWrapper($db);

        $record = [
            ilIndividualAssessmentMembers::FIELD_USR_ID => 22,
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => 33,
            ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => 44,
            ilIndividualAssessmentMembers::FIELD_RECORD => "record",
            ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => "internalNote",
            ilIndividualAssessmentMembers::FIELD_PLACE => "place",
            ilIndividualAssessmentMembers::FIELD_EVENTTIME => $timestamp,
            ilIndividualAssessmentMembers::FIELD_NOTIFY => true,
            ilIndividualAssessmentMembers::FIELD_FINALIZED => 0,
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => -1,
            ilIndividualAssessmentMembers::FIELD_FILE_NAME => "fileName",
            ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => true,
            ilIndividualAssessmentMembers::FIELD_CHANGER_ID => 55,
            ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => $obj->getActualDateTime()
        ];

        $values = [
            "obj_id" => ["integer", 11],
            ilIndividualAssessmentMembers::FIELD_USR_ID => ["integer", 22],
            ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS => ["text", 33],
            ilIndividualAssessmentMembers::FIELD_NOTIFY => ["integer", true],
            ilIndividualAssessmentMembers::FIELD_FINALIZED => ["integer", 0],
            ilIndividualAssessmentMembers::FIELD_NOTIFICATION_TS => ["integer", -1],
            ilIndividualAssessmentMembers::FIELD_EXAMINER_ID => ["integer", 44],
            ilIndividualAssessmentMembers::FIELD_RECORD => ["text", "record"],
            ilIndividualAssessmentMembers::FIELD_INTERNAL_NOTE => ["text", "internalNote"],
            ilIndividualAssessmentMembers::FIELD_PLACE => ["text", "place"],
            ilIndividualAssessmentMembers::FIELD_EVENTTIME => ["integer", $timestamp],
            ilIndividualAssessmentMembers::FIELD_FILE_NAME => ["text", "fileName"],
            ilIndividualAssessmentMembers::FIELD_USER_VIEW_FILE => ["integer", true],
            ilIndividualAssessmentMembers::FIELD_CHANGER_ID => ["integer", 55],
            ilIndividualAssessmentMembers::FIELD_CHANGE_TIME => ["text", $obj->getActualDateTime()]
        ];

        $db
            ->expects($this->once())
            ->method("insert")
            ->with("iass_members", $values)
        ;

        $obj->insertMembersRecord($iass, $record);
    }

    public function test_removeMembersRecord(): void
    {
        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->once())
            ->method("getId")
            ->willReturn(11)
        ;

        $record[ilIndividualAssessmentMembers::FIELD_USR_ID] = 22;

        $sql =
            "DELETE FROM iass_members" . PHP_EOL
            . "WHERE obj_id = 11" . PHP_EOL
            . "AND usr_id = 22" . PHP_EOL
        ;

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(2))
            ->method("quote")
            ->withConsecutive([11, "integer"], [22, "integer"])
            ->willReturnOnConsecutiveCalls("11", "22")
        ;
        $db
            ->expects($this->once())
            ->method("manipulate")
            ->with($sql)
        ;

        $obj = new ilIndividualAssessmentMembersStorageDB($db);
        $obj->removeMembersRecord($iass, $record);
    }

    public function dataFor_getWhereFromFilter(): array
    {
        return [
            [
                ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED,
                "      AND finalized = 0 AND examiner_id IS NULL\n"
            ],
            [
                ilIndividualAssessmentMembers::LP_IN_PROGRESS,
                "      AND finalized = 0 AND examiner_id IS NOT NULL\n"
            ],
            [
                ilIndividualAssessmentMembers::LP_COMPLETED,
                "      AND finalized = 1 AND learning_progress = 2\n"
            ],
            [
                ilIndividualAssessmentMembers::LP_FAILED,
                "      AND finalized = 1 AND learning_progress = 3\n"
            ],
            [
                "test",
                ""
            ]
        ];
    }

    /**
     * @dataProvider dataFor_getWhereFromFilter
     */
    public function test_getWhereFromFilter($filter, $result): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDBWrapper($db);
        $res = $obj->getWhereFromFilterWrapper($filter);

        $this->assertEquals($result, $res);
    }

    public function test_getOrderByFromSort(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentMembersStorageDBWrapper($db);

        $sort = "test:foo";

        $res = $obj->getOrderByFromSortWrapper($sort);

        $this->assertEquals(" ORDER BY test foo", $res);
    }
}
