<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilIndividualAssessmentMemberTest extends TestCase
{
    /**
     * @var ilObjIndividualAssessment|mixed|MockObject
     */
    private $iass_object;
    /**
     * @var ilIndividualAssessmentUserGrading|mixed|MockObject
     */
    private $grading;
    /**
     * @var ilObjUser|mixed|MockObject
     */
    private $obj_user;

    protected function setUp(): void
    {
        $this->iass_object = $this->createMock(ilObjIndividualAssessment::class);
        $this->grading = $this->createMock(ilIndividualAssessmentUserGrading::class);
        $this->obj_user = $this->createMock(ilObjUser::class);
    }

    public function test_createObject(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertInstanceOf(ilIndividualAssessmentMember::class, $obj);
    }

    public function test_getRecord(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getRecord")
            ->willReturn("testRecord")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("testRecord", $obj->record());
    }

    public function test_internalNote(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getInternalNote")
            ->willReturn("internalNote")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("internalNote", $obj->internalNote());
    }

    public function test_examinerId_not_set(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertNull($obj->examinerId());
    }

    public function test_examinerId(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222,
            3434
        );

        $this->assertEquals(3434, $obj->examinerId());
    }

    public function test_changerId_not_set(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertNull($obj->changerId());
    }

    public function test_changerId(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222,
            0,
            5656
        );

        $this->assertEquals(5656, $obj->changerId());
    }

    public function test_changeTime_not_set(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertNull($obj->changeTime());
    }

    public function test_changeTime(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222,
            0,
            0,
            new DateTime('2021-11-25')
        );

        $this->assertEquals('2021-11-25', $obj->changeTime()->format('Y-m-d'));
    }

    public function test_notify(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("isNotify")
            ->willReturn(true)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertTrue($obj->notify());
    }

    public function test_maybeSendNotification_not_finalized(): void
    {
        $notificator = $this->createMock(ilIndividualAssessmentNotificator::class);

        $this->grading
            ->expects($this->once())
            ->method("isFinalized")
            ->willReturn(false)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->expectException(ilIndividualAssessmentException::class);
        $this->expectExceptionMessage('must finalize before notification');
        $obj->maybeSendNotification($notificator);
    }

    public function test_maybeSendNotification_not_notify(): void
    {
        $notificator = $this->createMock(ilIndividualAssessmentNotificator::class);

        $this->grading
            ->expects($this->once())
            ->method("isFinalized")
            ->willReturn(true)
        ;
        $this->grading
            ->expects($this->once())
            ->method("isNotify")
            ->willReturn(false)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals($obj, $obj->maybeSendNotification($notificator));
    }

    public function test_id(): void
    {
        $this->obj_user
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals(22, $obj->id());
    }

    public function test_assessmentId(): void
    {
        $this->iass_object
            ->expects($this->once())
            ->method("getId")
            ->willReturn(22)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals(22, $obj->assessmentId());
    }

    public function test_assessment(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals($this->iass_object, $obj->assessment());
    }

    public function test_finalized(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("isFinalized")
            ->willReturn(true)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertTrue($obj->finalized());
    }

    public function fileNamesDataProvider(): array
    {
        return [
            [''],
            [null]
        ];
    }

    /**
     * @dataProvider fileNamesDataProvider
     */
    public function test_mayBeFinalized_file_required_filename_empty(?string $filename): void
    {
        $settings = $this->createMock(ilIndividualAssessmentSettings::class);
        $settings
            ->expects($this->once())
            ->method("isFileRequired")
            ->willReturn(true)
        ;

        $this->iass_object
            ->expects($this->once())
            ->method("getSettings")
            ->willReturn($settings)
        ;

        $this->grading
            ->expects($this->once())
            ->method("getFile")
            ->willReturn($filename)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertFalse($obj->mayBeFinalized());
    }

    public function positiveLPStatusDataProvider(): array
    {
        return [
            [ilIndividualAssessmentMembers::LP_COMPLETED],
            [ilIndividualAssessmentMembers::LP_FAILED]
        ];
    }

    /**
     * @dataProvider positiveLPStatusDataProvider
     */
    public function test_mayBeFinalized_with_positive_lp_status(int $lp_status): void
    {
        $settings = $this->createMock(ilIndividualAssessmentSettings::class);
        $settings
            ->expects($this->once())
            ->method("isFileRequired")
            ->willReturn(false)
        ;

        $this->iass_object
            ->expects($this->once())
            ->method("getSettings")
            ->willReturn($settings)
        ;

        $this->grading
            ->expects($this->once())
            ->method("getLearningProgress")
            ->willReturn($lp_status)
        ;
        $this->grading
            ->expects($this->once())
            ->method("isFinalized")
            ->willReturn(false)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertTrue($obj->mayBeFinalized());
    }

    public function test_mayBeFinalized_already_finalized(): void
    {
        $settings = $this->createMock(ilIndividualAssessmentSettings::class);
        $settings
            ->expects($this->once())
            ->method("isFileRequired")
            ->willReturn(false)
        ;

        $this->iass_object
            ->expects($this->once())
            ->method("getSettings")
            ->willReturn($settings)
        ;

        $this->grading
            ->expects($this->once())
            ->method("getLearningProgress")
            ->willReturn(ilIndividualAssessmentMembers::LP_COMPLETED)
        ;
        $this->grading
            ->expects($this->once())
            ->method("isFinalized")
            ->willReturn(true)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertFalse($obj->mayBeFinalized());
    }

    public function negativeLPStatusDataProvider(): array
    {
        return [
            [ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED],
            [ilIndividualAssessmentMembers::LP_IN_PROGRESS]
        ];
    }

    /**
     * @dataProvider negativeLPStatusDataProvider
     */
    public function test_mayBeFinalized_with_negative_lp_status(int $lp_status): void
    {
        $settings = $this->createMock(ilIndividualAssessmentSettings::class);
        $settings
            ->expects($this->once())
            ->method("isFileRequired")
            ->willReturn(false)
        ;

        $this->iass_object
            ->expects($this->once())
            ->method("getSettings")
            ->willReturn($settings)
        ;

        $this->grading
            ->expects($this->once())
            ->method("getLearningProgress")
            ->willReturn($lp_status)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertFalse($obj->mayBeFinalized());
    }

    public function test_withExaminerId(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $new_obj = $obj->withExaminerId(333);

        $this->assertNull($obj->examinerId());
        $this->assertEquals(333, $new_obj->examinerId());
    }

    public function test_withChangerId(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $new_obj = $obj->withChangerId(534);

        $this->assertNull($obj->changerId());
        $this->assertEquals(534, $new_obj->changerId());
    }

    public function test_withChangeTime(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $new_obj = $obj->withChangeTime(new DateTime("2021-11-25"));

        $this->assertNull($obj->changeTime());
        $this->assertEquals("2021-11-25", $new_obj->changeTime()->format("Y-m-d"));
    }

    public function test_lastname(): void
    {
        $this->obj_user
            ->expects($this->once())
            ->method("getLastname")
            ->willReturn("lastname")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("lastname", $obj->lastname());
    }

    public function test_firstname(): void
    {
        $this->obj_user
            ->expects($this->once())
            ->method("getFirstname")
            ->willReturn("firstname")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("firstname", $obj->firstname());
    }

    public function test_login(): void
    {
        $this->obj_user
            ->expects($this->once())
            ->method("getLogin")
            ->willReturn("login")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("login", $obj->login());
    }

    public function test_name(): void
    {
        $this->obj_user
            ->expects($this->once())
            ->method("getFullName")
            ->willReturn("first last")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("first last", $obj->name());
    }

    public function test_LPStatus(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getLearningProgress")
            ->willReturn(ilIndividualAssessmentMembers::LP_COMPLETED)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals(ilIndividualAssessmentMembers::LP_COMPLETED, $obj->LPStatus());
    }

    public function test_notificationTS(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals(22222, $obj->notificationTS());
    }

    public function test_place(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getPlace")
            ->willReturn("place")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("place", $obj->place());
    }

    public function test_eventTime(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getEventTime")
            ->willReturn(new DateTimeImmutable("2021-11-25"))
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("2021-11-25", $obj->eventTime()->format("Y-m-d"));
    }

    public function test_fileName(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("getFile")
            ->willReturn("file_name")
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals("file_name", $obj->fileName());
    }

    public function test_viewFile(): void
    {
        $this->grading
            ->expects($this->once())
            ->method("isFileVisible")
            ->willReturn(true)
        ;

        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertTrue($obj->viewFile());
    }

    public function test_getGrading(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $this->assertEquals($this->grading, $obj->getGrading());
    }

    public function test_withGrading(): void
    {
        $obj = new ilIndividualAssessmentMember(
            $this->iass_object,
            $this->obj_user,
            $this->grading,
            22222
        );

        $new_grading = $this->createMock(ilIndividualAssessmentUserGrading::class);
        $new_grading = $new_grading->withFinalized(true);
        $new_obj = $obj->withGrading($new_grading);

        $this->assertNotEquals($new_grading, $obj->getGrading());
        $this->assertEquals($new_grading, $new_obj->getGrading());
    }
}
