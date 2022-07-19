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
 */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;

/**
 * @backupGlobals disabled
 */
class ilIndividualAssessmentUserGradingTest extends TestCase
{
    public function test_create_instance()
    {
        $name = 'Hans Günther';
        $record = 'The guy was really good';
        $internal_note = 'This is a node just for me.';
        $file = null;
        $is_file_visible = false;
        $learning_progress = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
        $place = 'Area 51';
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
        $this->assertEquals($record, $grading->getRecord());
        $this->assertEquals($internal_note, $grading->getInternalNote());
        $this->assertNull($grading->getFile());
        $this->assertFalse($grading->isFileVisible());
        $this->assertEquals($learning_progress, $grading->getLearningProgress());
        $this->assertEquals($place, $grading->getPlace());
        $this->assertEquals($event_time, $grading->getEventTime());
        $this->assertTrue($grading->isNotify());
        $this->assertFalse($grading->isFinalized());
    }

    public function test_with_finalized_changed()
    {
        $name = 'Hans Günther';
        $record = 'The guy was really good';
        $internal_note = 'This is a node just for me.';
        $file = 'report.pdf';
        $is_file_visible = true;
        $learning_progress = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
        $place = 'Area 51 Underground';
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
        $this->assertEquals($record, $grading->getRecord());
        $this->assertEquals($internal_note, $grading->getInternalNote());
        $this->assertEquals($file, $grading->getFile());
        $this->assertTrue($grading->isFileVisible());
        $this->assertEquals($learning_progress, $grading->getLearningProgress());
        $this->assertEquals($place, $grading->getPlace());
        $this->assertEquals($event_time, $grading->getEventTime());
        $this->assertFalse($grading->isNotify());
        $this->assertFalse($grading->isFinalized());

        $n_grading = $grading->withFinalized(true);
        $this->assertEquals($name, $n_grading->getName());
        $this->assertEquals($record, $n_grading->getRecord());
        $this->assertEquals($internal_note, $n_grading->getInternalNote());
        $this->assertEquals($file, $n_grading->getFile());
        $this->assertTrue($n_grading->isFileVisible());
        $this->assertEquals($learning_progress, $n_grading->getLearningProgress());
        $this->assertEquals($place, $n_grading->getPlace());
        $this->assertEquals($event_time, $n_grading->getEventTime());
        $this->assertFalse($n_grading->isNotify());
        $this->assertTrue($n_grading->isFinalized());

        $this->assertNotSame($n_grading, $grading);
    }

    public function testToFormInput() : void
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn("label")
        ;
        $file_handler = $this->createMock(AbstractCtrlAwareUploadHandler::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);
        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $name = 'Hans Günther';
        $record = 'The guy was really good';
        $internal_note = 'This is a node just for me.';
        $file = 'report.pdf';
        $is_file_visible = true;
        $learning_progress = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
        $place = 'Area 51 Underground';
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

        $input = $grading->toFormInput(
            $f,
            $df,
            $lng,
            $refinery,
            $file_handler,
            [
                ilIndividualAssessmentMembers::LP_IN_PROGRESS,
                ilIndividualAssessmentMembers::LP_FAILED,
                ilIndividualAssessmentMembers::LP_COMPLETED
            ]
        );

        $this->assertInstanceOf(Section::class, $input);
    }
}
