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

class PRGPlaceholderMock extends ilStudyProgrammePlaceholderValues
{
    public function __construct()
    {
    }

    public function getRelevantProgress(array $assignments)
    {
        return $this->getRelevantProgressFromAssignments($assignments);
    }
}

class ilObjStudyProgrammeCertificateTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;
    protected PRGPlaceholderMock $placeholder_mock;

    public function setUp(): void
    {
        $pgs = (new ilPRGProgress(11, ilPRGProgress::STATUS_COMPLETED))
            ->withCompletion(7, new DateTimeImmutable('2023-12-01'))
            ->withValidityOfQualification(new DateTimeImmutable('2023-12-31'));
        $ass0 = (new ilPRGAssignment(1, 6))
            ->withProgressTree($pgs);

        $pgs = new ilPRGProgress(12, ilPRGProgress::STATUS_COMPLETED);
        $ass1 = (new ilPRGAssignment(1, 6))
                ->withProgressTree($pgs);

        $pgs = (new ilPRGProgress(13, ilPRGProgress::STATUS_IN_PROGRESS))
            ->withCompletion(7, new DateTimeImmutable('2023-12-02'))
            ->withValidityOfQualification(new DateTimeImmutable('2023-12-30'));
        $ass2 = (new ilPRGAssignment(1, 6))
            ->withProgressTree($pgs);

        $pgs = (new ilPRGProgress(14, ilPRGProgress::STATUS_COMPLETED))
            ->withCompletion(7, new DateTimeImmutable('2023-11-01'));
        $ass3 = (new ilPRGAssignment(1, 6))
            ->withProgressTree($pgs);

        $this->assignments = [
            $ass0, $ass1, $ass2, $ass3
        ];
        $this->placeholder_mock = new PRGPlaceholderMock();
    }

    public function testPRGCertificateLatestProgressNoAssignments(): void
    {
        $assignments = [];
        $pgs = $this->placeholder_mock->getRelevantProgress($assignments);
        $this->assertNull($pgs);
    }

    public function testPRGCertificateLatestProgressUnsuccesfulAssignments(): void
    {
        $assignments = [$this->assignments[1]];
        $pgs = $this->placeholder_mock->getRelevantProgress($assignments);
        $this->assertEquals($this->assignments[1]->getProgressTree(), $pgs);
    }

    public function testPRGCertificateLatestProgressWithOnlySuccessfulAssignments(): void
    {
        $assignments = [
            $this->assignments[0],
            $this->assignments[2],
            $this->assignments[3]
        ];
        $pgs = $this->placeholder_mock->getRelevantProgress($assignments);
        $this->assertEquals($this->assignments[3]->getProgressTree(), $pgs);
    }

    public function testPRGCertificateLatestProgressWithMixedAssignments(): void
    {
        $assignments = $this->assignments;
        $pgs = $this->placeholder_mock->getRelevantProgress($assignments);
        $this->assertEquals($this->assignments[3]->getProgressTree(), $pgs);
    }

    public function testPRGCertificateLatestProgressWithOnlyLimitedAssignments(): void
    {
        $assignments = [
            $this->assignments[0],
            $this->assignments[2]
        ];
        $pgs = $this->placeholder_mock->getRelevantProgress($assignments);
        $this->assertEquals($this->assignments[0]->getProgressTree(), $pgs);
    }
}
