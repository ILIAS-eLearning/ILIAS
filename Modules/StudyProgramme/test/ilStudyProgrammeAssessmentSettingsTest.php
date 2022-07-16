<?php declare(strict_types=1);

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
use ILIAS\UI\Implementation\Component\Input\Field\Numeric;
use ILIAS\UI\Implementation\Component\Input\Field\Select;
use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAssessmentSettingsTest extends TestCase
{
    private const VALID_POINTS_1 = 22;
    private const VALID_POINTS_2 = 44;
    private const INVALID_POINTS = -11;
    private const VALID_STATUS_1 = 20;
    private const VALID_STATUS_2 = 30;
    private const INVALID_STATUS = -1;

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
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidStatus() : void
    {
        try {
            new ilStudyProgrammeAssessmentSettings(self::VALID_POINTS_1, self::INVALID_STATUS);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testFailCreateWithInvalidPointAndInvalidStatus() : void
    {
        try {
            new ilStudyProgrammeAssessmentSettings(self::INVALID_POINTS, self::INVALID_STATUS);
            $this->fail();
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
            $this->fail();
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
            $this->fail();
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
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
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
            Section::class,
            $field
        );

        $inputs = $field->getInputs();

        $this->assertInstanceOf(
            Numeric::class,
            $inputs['points']
        );

        $this->assertInstanceOf(
            Select::class,
            $inputs['status']
        );
    }
}
