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

class ilStudyProgrammeValidityOfAchievedQualificationSettingsTest extends TestCase
{
    private const VALID_QUALIFICATION_PERIOD_1 = 1;
    private const VALID_QUALIFICATION_PERIOD_2 = 2;
    private const INVALID_QUALIFICATION_PERIOD = -1;
    private const VALID_QUALIFICATION_DATE = '2019-02-14';
    private const VALID_RESTART_PERIOD_1 = 1;
    private const VALID_RESTART_PERIOD_2 = 2;
    private const INVALID_RESTART_PERIOD = -1;

    public function testSuccessfulCreate(): void
    {
        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        $this->assertEquals(self::VALID_QUALIFICATION_PERIOD_1, $obj->getQualificationPeriod());
        $this->assertEquals(
            self::VALID_QUALIFICATION_DATE,
            $obj->getQualificationDate()->format('Y-m-d')
        );
        $this->assertEquals(self::VALID_RESTART_PERIOD_1, $obj->getRestartPeriod());
    }

    public function testFailCreateWithInvalidQualificationPeriod(): void
    {
        try {
            new ilStudyProgrammeValidityOfAchievedQualificationSettings(
                self::INVALID_QUALIFICATION_PERIOD,
                new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
                self::VALID_RESTART_PERIOD_1
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidRestartPeriod(): void
    {
        try {
            new ilStudyProgrammeValidityOfAchievedQualificationSettings(
                self::VALID_QUALIFICATION_PERIOD_1,
                new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
                self::INVALID_RESTART_PERIOD
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithQualificationPeriod(): void
    {
        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        $new = $obj->withQualificationPeriod(self::VALID_QUALIFICATION_PERIOD_2);

        $this->assertEquals(self::VALID_QUALIFICATION_PERIOD_1, $obj->getQualificationPeriod());
        $this->assertEquals(self::VALID_QUALIFICATION_PERIOD_2, $new->getQualificationPeriod());
    }

    public function testFailWithQualificationPeriod(): void
    {
        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        try {
            $obj->withQualificationPeriod(self::INVALID_QUALIFICATION_PERIOD);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithRestartPeriod(): void
    {
        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        $new = $obj->withRestartPeriod(self::VALID_RESTART_PERIOD_2);

        $this->assertEquals(self::VALID_RESTART_PERIOD_1, $obj->getRestartPeriod());
        $this->assertEquals(self::VALID_RESTART_PERIOD_2, $new->getRestartPeriod());
    }

    public function testFailWithRestartPeriod(): void
    {
        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        try {
            $obj->withRestartPeriod(self::INVALID_RESTART_PERIOD);
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

        $obj = new ilStudyProgrammeValidityOfAchievedQualificationSettings(
            self::VALID_QUALIFICATION_PERIOD_1,
            new DateTimeImmutable(self::VALID_QUALIFICATION_DATE),
            self::VALID_RESTART_PERIOD_1
        );

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(
                ['prg_no_validity_qualification'],
                ['validity_qualification_period_desc'],
                ['validity_qualification_period'],
                ['validity_qualification_date_desc'],
                ['validity_qualification_date'],
                ['prg_no_restart'],
                ['restart_period_desc'],
                ['restart_period'],
                ['prg_validity_of_qualification']
            )
            ->will($this->onConsecutiveCalls(
                'prg_no_validity_qualification',
                'validity_qualification_period_desc',
                'validity_qualification_period',
                'validity_qualification_date_desc',
                'validity_qualification_date',
                'prg_no_restart',
                'restart_period_desc',
                'restart_period',
                'prg_validity_of_qualification'
            ))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery,
            $df
        );

        $date_value = $field->getInputs()['validity_qualification']->getValue()[1]['vq_date'];
        $date = (new DateTimeImmutable($date_value))->format('Y-m-d');

        $this->assertEquals(self::VALID_QUALIFICATION_DATE, $date);

        $restart_field = $field->getInputs()['restart']->getValue()[1]['vq_restart_period'];

        $this->assertEquals(self::VALID_RESTART_PERIOD_1, $restart_field);
    }
}
