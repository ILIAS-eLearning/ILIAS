<?php

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

declare(strict_types=1);

class ilStudyProgrammeSettingsTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->id = 123;
        $this->prg_settings = new ilStudyProgrammeSettings(
            $this->id,
            $this->createMock(ilStudyProgrammeTypeSettings::class),
            $this->createMock(ilStudyProgrammeAssessmentSettings::class),
            $this->createMock(ilStudyProgrammeDeadlineSettings::class),
            $this->createMock(ilStudyProgrammeValidityOfAchievedQualificationSettings::class),
            $this->createMock(ilStudyProgrammeAutoMailSettings::class)
        );
    }

    public function testPRGSettingsBasicProperties(): void
    {
        $this->assertEquals($this->id, $this->prg_settings->getObjId());
        $this->assertEquals(345, $this->prg_settings->withObjId(345)->getObjId());
        $this->assertInstanceOf(ilStudyProgrammeSettings::class, $this->prg_settings->updateLastChange());
        $last_change = new DateTime();
        $this->assertEquals(
            $last_change->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT),
            $this->prg_settings->setLastChange($last_change)->getLastChange()->format(ilStudyProgrammeSettings::DATE_TIME_FORMAT)
        );
    }
    public function testPRGSettingsReturnOfSubSettings(): void
    {
        $this->assertInstanceOf(ilStudyProgrammeTypeSettings::class, $this->prg_settings->getTypeSettings());
        $this->assertInstanceOf(ilStudyProgrammeAssessmentSettings::class, $this->prg_settings->getAssessmentSettings());
        $this->assertInstanceOf(ilStudyProgrammeDeadlineSettings::class, $this->prg_settings->getDeadlineSettings());
        $this->assertInstanceOf(ilStudyProgrammeValidityOfAchievedQualificationSettings::class, $this->prg_settings->getValidityOfQualificationSettings());
        $this->assertInstanceOf(ilStudyProgrammeAutoMailSettings::class, $this->prg_settings->getAutoMailSettings());
    }

    public function testPRGSettingsLPMode(): void
    {
        $last_change = new DateTime();
        $prg_settings = $this->prg_settings->setLastChange($last_change);
        $this->assertEquals(ilStudyProgrammeSettings::MODE_UNDEFINED, $prg_settings->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)->getLPMode());
        $this->assertEquals(ilStudyProgrammeSettings::MODE_POINTS, $prg_settings->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)->getLPMode());
        $this->assertEquals(ilStudyProgrammeSettings::MODE_LP_COMPLETED, $prg_settings->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)->getLPMode());
        $this->assertNotEquals($last_change, $prg_settings->getLastChange());
    }

    public function testPRGSettingsLPModeFails(): void
    {
        $this->expectException(\ilException::class);
        $this->prg_settings->setLPMode(-200);
    }

    public function testPRGSettingsValidationExpires(): void
    {
        $mock_validity_settings = $this->createMock(ilStudyProgrammeValidityOfAchievedQualificationSettings::class);
        $mock_validity_settings
            ->expects($this->once())
            ->method('getQualificationDate')
            ->willReturn(new \DateTimeImmutable());

        $this->assertTrue(
            $this->prg_settings
                ->withValidityOfQualificationSettings($mock_validity_settings)
                ->validationExpires()
        );

        $mock_validity_settings = $this->createMock(ilStudyProgrammeValidityOfAchievedQualificationSettings::class);
        $mock_validity_settings
            ->expects($this->once())
            ->method('getQualificationPeriod')
            ->willReturn(10);

        $this->assertTrue(
            $this->prg_settings
                ->withValidityOfQualificationSettings($mock_validity_settings)
                ->validationExpires()
        );
        $mock_validity_settings = $this->createMock(ilStudyProgrammeValidityOfAchievedQualificationSettings::class);
        $mock_validity_settings
            ->expects($this->once())
            ->method('getQualificationDate')
            ->willReturn(null);
        $mock_validity_settings
            ->expects($this->once())
            ->method('getQualificationPeriod')
            ->willReturn(ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD);

        $this->assertFalse(
            $this->prg_settings
                ->withValidityOfQualificationSettings($mock_validity_settings)
                ->validationExpires()
        );
    }

}
