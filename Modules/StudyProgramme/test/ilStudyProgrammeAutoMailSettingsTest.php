<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAutoMailSettingsTest extends TestCase
{
    const VALID_SEND_REASSIGNED_MAIL_1 = true;
    const VALID_SEND_REASSIGNED_MAIL_2 = false;
    const VALID_REMINDER_NOT_RESTARTED_BY_USER_1 = null;
    const VALID_REMINDER_NOT_RESTARTED_BY_USER_2 = 2;
    const INVALID_REMINDER_NOT_RESTARTED_BY_USER = 0;
    const VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1 = null;
    const VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_2 = 2;
    const INVALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS = 0;

    public function testSuccessfulCreate() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );

        $this->assertEquals(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            $obj->getSendReAssignedMail()
        );

        $this->assertEquals(
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            $obj->getReminderNotRestartedByUserDays()
        );

        $this->assertEquals(
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1,
            $obj->getProcessingEndsNotSuccessfulDays()
        );
    }

    public function testFailCreateWithInvalidReminderNotRestartedByUserDays() : void
    {
        try {
            new ilStudyProgrammeAutoMailSettings(
                self::VALID_SEND_REASSIGNED_MAIL_1,
                self::INVALID_REMINDER_NOT_RESTARTED_BY_USER,
                self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
            );
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidProcessingEndsNotSuccessfulDays() : void
    {
        try {
            new ilStudyProgrammeAutoMailSettings(
                self::VALID_SEND_REASSIGNED_MAIL_1,
                self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
                self::INVALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS
            );
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithSendReAssignedMail() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );

        $new = $obj->withSendReAssignedMail(
            self::VALID_SEND_REASSIGNED_MAIL_2
        );

        $this->assertEquals(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            $obj->getSendReAssignedMail()
        );

        $this->assertEquals(
            self::VALID_SEND_REASSIGNED_MAIL_2,
            $new->getSendReAssignedMail()
        );
    }

    public function testSuccessfulWithReminderNotRestartedByUserDays() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );

        $new = $obj->withReminderNotRestartedByUserDays(
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_2
        );

        $this->assertEquals(
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            $obj->getReminderNotRestartedByUserDays()
        );

        $this->assertEquals(
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_2,
            $new->getReminderNotRestartedByUserDays()
        );
    }

    public function testFailWithReminderNotRestartedByUserDays() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );
        try {
            $obj->withReminderNotRestartedByUserDays(self::INVALID_REMINDER_NOT_RESTARTED_BY_USER);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithProcessingEndsNotSuccessfulDays() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );

        $new = $obj->withProcessingEndsNotSuccessfulDays(
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_2
        );

        $this->assertEquals(
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1,
            $obj->getProcessingEndsNotSuccessfulDays()
        );

        $this->assertEquals(
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_2,
            $new->getProcessingEndsNotSuccessfulDays()
        );
    }

    public function testFailWithProcessingEndsNotSuccessfulDays() : void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );
        try {
            $obj->withProcessingEndsNotSuccessfulDays(
                self::INVALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS
            );
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testToFormInput() : void
    {
        $lng = $this->createMock(ilLanguage::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);

        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(
                ['send_re_assigned_mail'],
                ['send_re_assigned_mail_info'],
                ['prg_user_not_restarted_time_input'],
                ['prg_user_not_restarted_time_input_info'],
                ['send_info_to_re_assign_mail'],
                ['send_info_to_re_assign_mail_info'],
                ['prg_processing_ends_no_success'],
                ['prg_processing_ends_no_success_info'],
                ['send_risky_to_fail_mail'],
                ['send_risky_to_fail_mail_info'],
                ['prg_cron_job_configuration']
            )
            ->will($this->onConsecutiveCalls(
                'send_re_assigned_mail',
                'send_re_assigned_mail_info',
                'prg_user_not_restarted_time_input',
                'prg_user_not_restarted_time_input_info',
                'send_info_to_re_assign_mail',
                'send_info_to_re_assign_mail_info',
                'prg_processing_ends_no_success',
                'prg_processing_ends_no_success_info',
                'send_risky_to_fail_mail',
                'send_risky_to_fail_mail_info',
                'prg_cron_job_configuration'
            ))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery
        );

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Section',
            $field
        );

        $inputs = $field->getInputs();
        $cb = $inputs['send_re_assigned_mail'];

        /** @var ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup $og_1 */
        $og_1 = $inputs['prg_user_not_restarted_time_input'];

        /** @var ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup $og_2 */
        $og_2 = $inputs['processing_ends_not_success'];

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Checkbox',
            $cb
        );

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup',
            $og_1
        );

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup',
            $og_2
        );

        $nm_1 = $og_1->getInputs()[0];
        $nm_2 = $og_2->getInputs()[0];

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Numeric',
            $nm_1
        );

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Numeric',
            $nm_2
        );
    }
}
