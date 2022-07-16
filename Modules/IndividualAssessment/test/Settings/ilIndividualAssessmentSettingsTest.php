<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * @backupGlobals disabled
 */
class ilIndividualAssessmentSettingsTest extends TestCase
{
    public function test_create_settings()
    {
        $obj_id = 10;
        $title = 'My iass';
        $description = 'Special iass for members';
        $content = 'Everything you have learned';
        $record_remplate = 'You should ask these things';
        $event_time_place_required = true;
        $file_required = false;

        $settings = new ilIndividualAssessmentSettings(
            $obj_id,
            $title,
            $description,
            $content,
            $record_remplate,
            $event_time_place_required,
            $file_required
        );
        $this->assertEquals($obj_id, $settings->getObjId());
        $this->assertEquals($title, $settings->getTitle());
        $this->assertEquals($description, $settings->getDescription());
        $this->assertEquals($content, $settings->getContent());
        $this->assertEquals($record_remplate, $settings->getRecordTemplate());
        $this->assertTrue($settings->isEventTimePlaceRequired());
        $this->assertFalse($settings->isFileRequired());
    }

    public function test_to_form_input()
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn("label")
        ;

        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);
        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $obj_id = 10;
        $title = 'My iass';
        $description = 'Special iass for members';
        $content = 'Everything you have learned';
        $record_remplate = 'You should ask these things';
        $event_time_place_required = true;
        $file_required = false;

        $settings = new ilIndividualAssessmentSettings(
            $obj_id,
            $title,
            $description,
            $content,
            $record_remplate,
            $event_time_place_required,
            $file_required
        );

        $input = $settings->toFormInput(
            $f,
            $lng,
            $refinery
        );

        $this->assertInstanceOf(Section::class, $input);
    }
}
