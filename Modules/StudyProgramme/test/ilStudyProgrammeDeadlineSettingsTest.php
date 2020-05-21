<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeDeadlineSettingsTest extends TestCase
{
    const VALID_DEADLINE_PERIOD_1 = 11;
    const VALID_DEADLINE_PERIOD_2 = 22;
    const INVALID_DEADLINE_PERIOD = -1;
    const VALID_DEADLINE_DATE = '2019-02-14';

    public function testSuccessfulCreate() : void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTime(self::VALID_DEADLINE_DATE)
        );

        $this->assertEquals(self::VALID_DEADLINE_PERIOD_1, $obj->getDeadlinePeriod());
        $this->assertEquals(self::VALID_DEADLINE_DATE, $obj->getDeadlineDate()->format('Y-m-d'));
    }

    public function testFailCreateWithInvalidDeadlinePeriod() : void
    {
        try {
            new ilStudyProgrammeDeadlineSettings(
                self::INVALID_DEADLINE_PERIOD,
                new DateTime(self::VALID_DEADLINE_DATE)
            );
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithDeadlinePeriod() : void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTime(self::VALID_DEADLINE_DATE)
        );

        $new = $obj->withDeadlinePeriod(self::VALID_DEADLINE_PERIOD_2);

        $this->assertEquals(self::VALID_DEADLINE_PERIOD_1, $obj->getDeadlinePeriod());
        $this->assertEquals(self::VALID_DEADLINE_PERIOD_2, $new->getDeadlinePeriod());
    }

    public function testFailWithDeadlinePeriod() : void
    {
        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTime(self::VALID_DEADLINE_DATE)
        );

        try {
            $obj->withDeadlinePeriod(self::INVALID_DEADLINE_PERIOD);
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

        $obj = new ilStudyProgrammeDeadlineSettings(
            self::VALID_DEADLINE_PERIOD_1,
            new DateTime(self::VALID_DEADLINE_DATE)
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
            'ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup',
            $switchable_group
        );

        $date_value = $switchable_group->getValue()[1]['deadline_date'];
        $date = (new DateTime($date_value))->format('Y-m-d');
        $this->assertEquals(self::VALID_DEADLINE_DATE, $date);

        $inputs = $switchable_group->getInputs();
        foreach ($inputs as $input) {
            $this->assertInstanceOf(
                'ILIAS\UI\Implementation\Component\Input\Field\Group',
                $input
            );
        }
    }
}
