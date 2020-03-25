<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals disabled
 */
class ilIndividualAssessmentUserGradingTest extends TestCase
{
    public function test_create_instance()
    {
        $name = "Hans Günther";
        $record = "The guy was really good";
        $internal_note = "This is a node just for me.";
        $file = null;
        $is_file_visible = false;
        $learning_progress = 1;
        $place = "Area 51";
        $event_time = new DateTimeImmutable();
        $notify = true;
        $finalized = false;
        $grading = new ilIndividualAssessmentUserGrading(
            $name,
            $record,
            $internal_note,
            $file,
            $is_file_visible,
            $learning_progress,
            $place,
            $event_time,
            $notify,
            $finalized
        );

        $this->assertInstanceOf(ilIndividualAssessmentUserGrading::class, $grading);
        $this->assertEquals($name, $grading->getName());
        $this->assertEquals($name, $grading->getRecord());
        $this->assertEquals($name, $grading->getInternalNote());
        $this->assertNull($grading->getFile());
        $this->assertFalse($grading->isFileVisible());
        $this->assertEquals($name, $grading->getLearningProgress());
        $this->assertEquals($name, $grading->getPlace());
        $this->assertEquals($name, $grading->getEventTime());
        $this->assertTrue($grading->isNotify());
        $this->assertFalse($grading->isFinalized());
    }

    public function test_with_finalized_changed()
    {
        $name = "Hans Günther";
        $record = "The guy was really good";
        $internal_note = "This is a node just for me.";
        $file = "report.pdf";
        $is_file_visible = true;
        $learning_progress = 2;
        $place = "Area 51 Underground";
        $event_time = new DateTimeImmutable();
        $notify = false;
        $finalized = false;
        $grading = new ilIndividualAssessmentUserGrading(
            $name,
            $record,
            $internal_note,
            $file,
            $is_file_visible,
            $learning_progress,
            $place,
            $event_time,
            $notify,
            $finalized
        );

        $this->assertInstanceOf(ilIndividualAssessmentUserGrading::class, $grading);
        $this->assertEquals($name, $grading->getName());
        $this->assertEquals($name, $grading->getRecord());
        $this->assertEquals($name, $grading->getInternalNote());
        $this->assertEquals($name, $grading->getFile());
        $this->assertTrue($grading->isFileVisible());
        $this->assertEquals($name, $grading->getLearningProgress());
        $this->assertEquals($name, $grading->getPlace());
        $this->assertEquals($name, $grading->getEventTime());
        $this->assertFalse($grading->isNotify());
        $this->assertFalse($grading->isFinalized());

        $n_grading = $grading->withFinalized(true);
        $this->assertEquals($name, $n_grading->getName());
        $this->assertEquals($name, $n_grading->getRecord());
        $this->assertEquals($name, $n_grading->getInternalNote());
        $this->assertEquals($name, $n_grading->getFile());
        $this->assertTrue($n_grading->isFileVisible());
        $this->assertEquals($name, $n_grading->getLearningProgress());
        $this->assertEquals($name, $n_grading->getPlace());
        $this->assertEquals($name, $n_grading->getEventTime());
        $this->assertFalse($n_grading->isNotify());
        $this->assertTrue($n_grading->isFinalized());

        $this->assertNotSame($n_grading, $grading);
    }
}