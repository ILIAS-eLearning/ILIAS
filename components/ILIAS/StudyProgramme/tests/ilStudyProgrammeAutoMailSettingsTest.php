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

use ILIAS\UI\Implementation\Component\Input\Field\Section;
use ILIAS\UI\Implementation\Component\Input\Field\Checkbox;
use ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Numeric;
use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAutoMailSettingsTest extends TestCase
{
    private const VALID_SEND_REASSIGNED_MAIL_1 = true;
    private const VALID_SEND_REASSIGNED_MAIL_2 = false;
    private const VALID_REMINDER_NOT_RESTARTED_BY_USER_1 = null;
    private const VALID_REMINDER_NOT_RESTARTED_BY_USER_2 = 2;
    private const INVALID_REMINDER_NOT_RESTARTED_BY_USER = 0;
    private const VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1 = null;
    private const VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_2 = 2;
    private const INVALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS = 0;

    public function testSuccessfulCreate(): void
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

    public function testFailCreateWithInvalidReminderNotRestartedByUserDays(): void
    {
        try {
            new ilStudyProgrammeAutoMailSettings(
                self::VALID_SEND_REASSIGNED_MAIL_1,
                self::INVALID_REMINDER_NOT_RESTARTED_BY_USER,
                self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidProcessingEndsNotSuccessfulDays(): void
    {
        try {
            new ilStudyProgrammeAutoMailSettings(
                self::VALID_SEND_REASSIGNED_MAIL_1,
                self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
                self::INVALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithSendReAssignedMail(): void
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

    public function testSuccessfulWithReminderNotRestartedByUserDays(): void
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

    public function testFailWithReminderNotRestartedByUserDays(): void
    {
        $obj = new ilStudyProgrammeAutoMailSettings(
            self::VALID_SEND_REASSIGNED_MAIL_1,
            self::VALID_REMINDER_NOT_RESTARTED_BY_USER_1,
            self::VALID_PROCESSING_ENDS_NOT_SUCCESSFUL_DAYS_1
        );
        try {
            $obj->withReminderNotRestartedByUserDays(self::INVALID_REMINDER_NOT_RESTARTED_BY_USER);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithProcessingEndsNotSuccessfulDays(): void
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

    public function testFailWithProcessingEndsNotSuccessfulDays(): void
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
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testToFormInput(): void
    {
        $lng = $this->createMock(ilLanguage::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);

        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
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
            Section::class,
            $field
        );

        $inputs = $field->getInputs();
        $cb = $inputs['send_re_assigned_mail'];

        /** @var ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup $og_1 */
        $og_1 = $inputs['prg_user_not_restarted_time_input'];

        /** @var ILIAS\UI\Implementation\Component\Input\Field\OptionalGroup $og_2 */
        $og_2 = $inputs['processing_ends_not_success'];

        $this->assertInstanceOf(
            Checkbox::class,
            $cb
        );

        $this->assertInstanceOf(
            OptionalGroup::class,
            $og_1
        );

        $this->assertInstanceOf(
            OptionalGroup::class,
            $og_2
        );

        $nm_1 = $og_1->getInputs()[0];
        $nm_2 = $og_2->getInputs()[0];

        $this->assertInstanceOf(
            Numeric::class,
            $nm_1
        );

        $this->assertInstanceOf(
            Numeric::class,
            $nm_2
        );
    }
}
