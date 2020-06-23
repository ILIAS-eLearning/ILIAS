<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAssessmentSettingsTest extends TestCase
{
    const VALID_POINTS_1 = 22;
    const VALID_POINTS_2 = 44;
    const INVALID_POINTS = -11;
    const VALID_STATUS_1 = 20;
    const VALID_STATUS_2 = 30;
    const INVALID_STATUS = -1;

    public function testSuccessfulCreate() : void
    {
        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        $this->assertEquals(self::VALID_POINTS_1, $obj->getPoints());
        $this->assertEquals(self::VALID_STATUS_1, $obj->getStatus());
    }

    public function testFailCreateWithInvalidPoints() : void
    {
        try {
            new ilStudyProgrammeAssessmentSettings(self::INVALID_POINTS, self::VALID_STATUS_1);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidStatus() : void
    {
        try {
            new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::INVALID_STATUS);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidPointAndInvalidStatus() : void
    {
        try {
            new ilStudyProgrammeAssessmentSettings(self::INVALID_POINTS, self::INVALID_STATUS);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithPoints() : void
    {
        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        $new = $obj->withPoints(self::VALID_POINTS_2);

        $this->assertEquals(self::VALID_POINTS_1, $obj->getPoints());
        $this->assertEquals(self::VALID_POINTS_2, $new->getPoints());
    }

    public function testFailWithPoints() : void
    {
        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        try {
            $obj->withPoints(self::INVALID_POINTS);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSuccessfulWithStatus() : void
    {
        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        $new = $obj->withStatus(self::VALID_STATUS_2);

        $this->assertEquals(self::VALID_STATUS_1, $obj->getStatus());
        $this->assertEquals(self::VALID_STATUS_2, $new->getStatus());
    }

    public function testFailWithStatus() : void
    {
        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        try {
            $obj->withStatus(self::INVALID_STATUS);
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

        $obj = new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::VALID_STATUS_1);

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(
                ['prg_points'],
                ['prg_points_byline'],
                ['prg_status'],
                ['prg_status_draft'],
                ['prg_status_active'],
                ['prg_status_outdated'],
                ['prg_status_byline'],
                ['prg_assessment']
            )
            ->will($this->onConsecutiveCalls(
                'prg_points',
                'prg_points_byline',
                'prg_status',
                'prg_status_draft',
                'prg_status_active',
                'prg_status_outdated',
                'prg_status_byline',
                'prg_assessment'
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

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Numeric',
            $inputs['points']
        );

        $this->assertInstanceOf(
            'ILIAS\UI\Implementation\Component\Input\Field\Select',
            $inputs['status']
        );
    }
}
