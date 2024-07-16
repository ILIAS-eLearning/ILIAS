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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Implementation\Component\Input\Field;

/**
 * Tests for:
 *  ilStudyProgrammeTypeSettings
 *  ilStudyProgrammeAssessmentSettings
 *  ilStudyProgrammeDeadlineSettings
 *  ilStudyProgrammeValidityOfAchievedQualificationSettings
 *  ilStudyProgrammeAutoMailSettings
 */
class ilStudyProgrammeSubSettingsTest extends \PHPUnit\Framework\TestCase
{
    protected ilLanguage $lng;
    protected Refinery $refinery;
    protected FieldFactory $field_factory;

    public function setUp(): void
    {
        $this->data_factory = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);
        $this->refinery = new Refinery($this->data_factory, $this->lng);
        $this->field_factory = new FieldFactory(
            $this->createMock(UploadLimitResolver::class),
            new SignalGenerator(),
            $this->data_factory,
            $this->refinery,
            $this->lng
        );
    }

    public function testPRGSettingsType(): void
    {
        $settings = new ilStudyProgrammeTypeSettings(765);
        $this->assertEquals(765, $settings->getTypeId());
        $this->assertEquals(777, $settings->withTypeId(777)->getTypeId());
    }

    public function testPRGSettingsTypeToForm(): void
    {
        $settings = new ilStudyProgrammeTypeSettings(0);
        $section = $settings->toFormInput(
            $this->field_factory,
            $this->lng,
            $this->refinery,
            [3 => 'AA', 7 => 'BB']
        );
        $this->assertInstanceOf(Field\Section::class, $section);
        $inputs = $section->getInputs();
        $this->assertInstanceOf(Field\Select::class, array_shift($inputs));
    }

    public function testPRGSettingsAssessment(): void
    {
        $settings = new ilStudyProgrammeAssessmentSettings(12, ilStudyProgrammeAssessmentSettings::STATUS_DRAFT);
        $this->assertEquals(12, $settings->getPoints());
        $this->assertEquals(13, $settings->withPoints(13)->getPoints());
        $this->assertEquals(ilStudyProgrammeAssessmentSettings::STATUS_DRAFT, $settings->getStatus());
        $this->assertEquals(
            ilStudyProgrammeAssessmentSettings::STATUS_ACTIVE,
            $settings->withStatus(ilStudyProgrammeAssessmentSettings::STATUS_ACTIVE)->getStatus()
        );
    }

    public function testPRGSettingsAssessmentToForm(): void
    {
        $settings = new ilStudyProgrammeAssessmentSettings(12, ilStudyProgrammeAssessmentSettings::STATUS_DRAFT);
        $section = $settings->toFormInput(
            $this->field_factory,
            $this->lng,
            $this->refinery
        );
        $this->assertInstanceOf(Field\Section::class, $section);
        $inputs = $section->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($inputs));
        $this->assertInstanceOf(Field\Select::class, array_shift($inputs));
    }

    public function testPRGSettingsDeadline(): void
    {
        $settings = new ilStudyProgrammeDeadlineSettings(2, null);
        $this->assertEquals(2, $settings->getDeadlinePeriod());
        $this->assertEquals(13, $settings->withDeadlinePeriod(13)->getDeadlinePeriod());
        $this->assertNull($settings->getDeadlineDate());
        $dat = new \DateTimeImmutable();
        $this->assertEquals($dat, $settings->withDeadlineDate($dat)->getDeadlineDate());
    }

    public function testPRGSettingsDeadlineToForm(): void
    {
        $settings = new ilStudyProgrammeDeadlineSettings(null, null);
        $section = $settings->toFormInput(
            $this->field_factory,
            $this->lng,
            $this->refinery,
            $this->data_factory
        );
        $this->assertInstanceOf(Field\Section::class, $section);
        $group = $section->getInputs();
        $this->assertInstanceOf(Field\SwitchableGroup::class, current($group));
        $inputs = current($group)->getInputs();
        $group1 = array_shift($inputs);
        $this->assertEquals([], $group1->getInputs());
        $group2 = array_shift($inputs);
        $g_inputs = $group2->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($g_inputs));
        $group3 = array_shift($inputs);
        $g_inputs = $group3->getInputs();
        $this->assertInstanceOf(Field\DateTime::class, array_shift($g_inputs));
    }

    public function testPRGSettingsValidity(): void
    {
        $settings = new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, null, null, false);
        $this->assertEquals(365, $settings->withQualificationPeriod(365)->getQualificationPeriod());
        $dat = new \DateTimeImmutable();
        $this->assertEquals($dat, $settings->withQualificationDate($dat)->getQualificationDate());
        $this->assertEquals(31, $settings->withRestartPeriod(31)->getRestartPeriod());
    }

    public function testPRGSettingsValidityToForm(): void
    {
        $settings = new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, null, null, false);
        $section = $settings->toFormInput(
            $this->field_factory,
            $this->lng,
            $this->refinery,
            $this->data_factory
        );
        $this->assertInstanceOf(Field\Section::class, $section);

        $groups = $section->getInputs();
        $sg1 = array_shift($groups);
        $this->assertInstanceOf(Field\SwitchableGroup::class, $sg1);
        $sg2 = array_shift($groups);
        $this->assertInstanceOf(Field\SwitchableGroup::class, $sg1);

        $inputs = $sg1->getInputs();
        $group1 = array_shift($inputs);
        $this->assertEquals([], $group1->getInputs());
        $group2 = array_shift($inputs);
        $g_inputs = $group2->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($g_inputs));
        $group3 = array_shift($inputs);
        $g_inputs = $group3->getInputs();
        $this->assertInstanceOf(Field\DateTime::class, array_shift($g_inputs));

        $inputs = $sg2->getInputs();
        $group4 = array_shift($inputs);
        $this->assertEquals([], $group4->getInputs());
        $group5 = array_shift($inputs);
        $g5_inputs = $group5->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($g5_inputs));
        $this->assertInstanceOf(Field\Checkbox::class, array_shift($g5_inputs));
    }

    public function testPRGSettingsMail(): void
    {
        $settings = new ilStudyProgrammeAutoMailSettings(false, null, null);
        $this->assertTrue($settings->withSendReAssignedMail(true)->getSendReAssignedMail());
        $this->assertEquals(31, $settings->withReminderNotRestartedByUserDays(31)->getReminderNotRestartedByUserDays());
        $this->assertEquals(60, $settings->withProcessingEndsNotSuccessfulDays(60)->getProcessingEndsNotSuccessfulDays());
    }

    public function testPRGSettingsMailToForm(): void
    {
        $settings = new ilStudyProgrammeAutoMailSettings(false, null, null);
        $section = $settings->toFormInput(
            $this->field_factory,
            $this->lng,
            $this->refinery
        );
        $this->assertInstanceOf(Field\Section::class, $section);
        $inputs = $section->getInputs();
        $this->assertInstanceOf(Field\Checkbox::class, array_shift($inputs));
        $opt = array_shift($inputs);
        $this->assertInstanceOf(Field\OptionalGroup::class, $opt);
        $g_inputs = $opt->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($g_inputs));
        $opt = array_shift($inputs);
        $this->assertInstanceOf(Field\OptionalGroup::class, $opt);
        $g_inputs = $opt->getInputs();
        $this->assertInstanceOf(Field\Numeric::class, array_shift($g_inputs));
    }
}
