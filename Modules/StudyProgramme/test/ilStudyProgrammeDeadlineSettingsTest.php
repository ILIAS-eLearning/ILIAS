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

use ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup;
use ILIAS\UI\Implementation\Component\Input\Field\Group;
use PHPUnit\Framework\TestCase;

class ilStudyProgrammeDeadlineSettingsTest extends TestCase
{
    private const VALID_DEADLINE_PERIOD_1 = 11;
    private const VALID_DEADLINE_PERIOD_2 = 22;
    private const INVALID_DEADLINE_PERIOD = -1;
    private const VALID_DEADLINE_DATE = '2019-02-14';

    public function testSuccessfulCreate(): void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTimeImmutable(self::VALID_DEADLINE_DATE)
        );

        $this->assertEquals(self::VALID_DEADLINE_PERIOD_1, $obj->getDeadlinePeriod());
        $this->assertEquals(self::VALID_DEADLINE_DATE, $obj->getDeadlineDate()->format('Y-m-d'));
    }

    public function testFailCreateWithInvalidDeadlinePeriod(): void
    {
        try {
            new ilStudyProgrammeDeadlineSettings(
                self::INVALID_DEADLINE_PERIOD,
                new DateTimeImmutable(self::VALID_DEADLINE_DATE)
            );
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithDeadlinePeriod(): void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTimeImmutable(self::VALID_DEADLINE_DATE)
        );

        $new = $obj->withDeadlinePeriod(self::VALID_DEADLINE_PERIOD_2);

        $this->assertEquals(self::VALID_DEADLINE_PERIOD_1, $obj->getDeadlinePeriod());
        $this->assertEquals(self::VALID_DEADLINE_PERIOD_2, $new->getDeadlinePeriod());
    }

    public function testFailWithDeadlinePeriod(): void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTimeImmutable(self::VALID_DEADLINE_DATE)
        );

        try {
            $obj->withDeadlinePeriod(self::INVALID_DEADLINE_PERIOD);
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

        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTimeImmutable(self::VALID_DEADLINE_DATE)
        );

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(
                ['prg_no_deadline'],
                ['prg_deadline_period_desc'],
                ['prg_deadline_period'],
                ['prg_deadline_date_desc'],
                ['prg_deadline_date'],
                ['prg_deadline_settings']
            )
            ->will($this->onConsecutiveCalls(
                'prg_no_deadline',
                'prg_deadline_period_desc',
                'prg_deadline_period',
                'prg_deadline_date_desc',
                'prg_deadline_date',
                'prg_deadline_settings'
            ))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery,
            $df
        );

        $switchable_group = $field->getInputs()['prg_deadline'];
        $this->assertInstanceOf(
            SwitchableGroup::class,
            $switchable_group
        );

        $date_value = $switchable_group->getValue()[1]['deadline_date'];
        $date = (new DateTimeImmutable($date_value))->format('Y-m-d');
        $this->assertEquals(self::VALID_DEADLINE_DATE, $date);

        $inputs = $switchable_group->getInputs();
        foreach ($inputs as $input) {
            $this->assertInstanceOf(
                Group::class,
                $input
            );
        }
    }
}
