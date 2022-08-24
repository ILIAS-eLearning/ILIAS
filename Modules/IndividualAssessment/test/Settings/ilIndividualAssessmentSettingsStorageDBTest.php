<?php

declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentSettingsStorageDBTest extends TestCase
{
    public function test_createObject(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $obj = new ilIndividualAssessmentSettingsStorageDB($db);

        $this->assertInstanceOf(ilIndividualAssessmentSettingsStorageDB::class, $obj);
    }

    public function test_createSettings(): void
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

        $values1 = [
            "obj_id" => ["integer", $obj_id],
            "content" => ["text", $content],
            "record_template" => ["text", $record_remplate],
            "event_time_place_required" => ["integer", $event_time_place_required],
            "file_required" => ["integer", $file_required]
        ];

        $values2 = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(2))
            ->method("insert")
            ->withConsecutive(
                [ilIndividualAssessmentSettingsStorageDB::IASS_SETTINGS_TABLE, $values1],
                [ilIndividualAssessmentSettingsStorageDB::IASS_SETTINGS_INFO_TABLE, $values2]
            )
        ;

        $obj = new ilIndividualAssessmentSettingsStorageDB($db);
        $obj->createSettings($settings);
    }

    public function test_updateSettings(): void
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

        $values = [
            "content" => ["text", $content],
            "record_template" => ["text", $record_remplate],
            "event_time_place_required" => ["integer", $event_time_place_required],
            "file_required" => ["integer", $file_required]
        ];

        $where = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->once())
            ->method("update")
            ->with(ilIndividualAssessmentSettingsStorageDB::IASS_SETTINGS_TABLE, $values, $where)
        ;

        $obj = new ilIndividualAssessmentSettingsStorageDB($db);
        $obj->updateSettings($settings);
    }

    public function test_updateInfoSettings(): void
    {
        $obj_id = 22;
        $contact = 'contact';
        $responsibility = 'responsibility';
        $phone = 'phone';
        $mails = 'mails';
        $consultation_hours = 'consultation_hours';

        $settings = new ilIndividualAssessmentInfoSettings(
            $obj_id,
            $contact,
            $responsibility,
            $phone,
            $mails,
            $consultation_hours
        );

        $values = [
            "contact" => ["text", $settings->getContact()],
            "responsibility" => ["text", $settings->getResponsibility()],
            "phone" => ["text", $settings->getPhone()],
            "mails" => ["text", $settings->getMails()],
            "consultation_hours" => ["text", $settings->getConsultationHours()]
        ];

        $where = [
            "obj_id" => ["integer", $obj_id]
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->once())
            ->method("update")
            ->with(ilIndividualAssessmentSettingsStorageDB::IASS_SETTINGS_INFO_TABLE, $values, $where)
        ;

        $obj = new ilIndividualAssessmentSettingsStorageDB($db);
        $obj->updateInfoSettings($settings);
    }

    public function test_deleteSettings(): void
    {
        $sql1 = "DELETE FROM iass_settings WHERE obj_id = %s";
        $sql2 = "DELETE FROM iass_info_settings WHERE obj_id = %s";

        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $iass
            ->expects($this->exactly(2))
            ->method("getId")
            ->willReturn(22)
        ;

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(2))
            ->method("manipulateF")
            ->withConsecutive(
                [$sql1, ["integer"], [22]],
                [$sql2, ["integer"], [22]]
            )
        ;

        $obj = new ilIndividualAssessmentSettingsStorageDB($db);
        $obj->deleteSettings($iass);
    }
}
