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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilPrgRestartAssignmentsCronJobMock extends ilPrgRestartAssignmentsCronJob
{
    public array $logs = [];
    protected ilObjStudyProgramme $prg;

    public function __construct(
        ilPRGAssignmentDBRepository $repo,
        ilPrgCronJobAdapter $adapter,
        ilObjStudyProgramme $prg
    ) {
        $this->assignment_repo = $repo;
        $this->adapter = $adapter;
        $this->prg = $prg;
    }
    protected function getNow(): DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Ymd', '20221224');
    }

    protected function log(string $msg): void
    {
        $this->logs[] = $msg;
    }
    protected function getStudyProgramme(int $prg_obj_id): ilObjStudyProgramme
    {
        return $this->prg;
    }
}

class ilPrgRestartAssignmentsCronJobTest extends TestCase
{
    protected ilPrgRestartAssignmentsCronJobMock $job;
    protected ilStudyProgrammeSettingsDBRepository $settings_repo;
    protected ilPRGAssignmentDBRepository $assignment_repo;
    protected ProgrammeEventsMock $events;

    protected function setUp(): void
    {
        $this->events = new ProgrammeEventsMock();

        $this->settings_repo = $this->getMockBuilder(ilStudyProgrammeSettingsDBRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProgrammeIdsWithReassignmentForExpiringValidity'])
            ->getMock();

        $this->adapter = $this->getMockBuilder(ilPrgRestart::class)
            ->setConstructorArgs([$this->settings_repo, $this->events])
            ->getMock();

        $this->real_adapter = new ilPrgRestart($this->settings_repo, $this->events);

        $this->assignment_repo = $this->getMockBuilder(ilPRGAssignmentDBRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAboutToExpire', 'store'])
            ->getMock();

        $this->prg = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getApplicableMembershipSourceForUser', 'getSettings', 'assignUser', 'getRefIdFor'])
            ->getMock();

        $this->job = new ilPrgRestartAssignmentsCronJobMock($this->assignment_repo, $this->adapter, $this->prg);
    }

    public function testRestartAssignmentsForNoRelevantProgrammes(): void
    {
        $this->adapter
            ->expects($this->once())
            ->method('getRelevantProgrammeIds')
            ->willReturn([]);
        $this->assignment_repo
            ->expects($this->never())
            ->method('getAboutToExpire');
        $this->assignment_repo
            ->expects($this->never())
            ->method('store');
        $this->adapter
            ->expects($this->never())
            ->method('actOnSingleAssignment');
        $this->job->run();
    }

    public function testRestartAssignmentsForRelevantProgrammes(): void
    {
        $pgs1 = (new ilPRGProgress(11, ilPRGProgress::STATUS_COMPLETED));
        $ass1 = (new ilPRGAssignment(42, 7))
            ->withProgressTree($pgs1)
            ->withManuallyAssigned(false);//will not be restarted
        $ass2 = (new ilPRGAssignment(43, 8))
            ->withProgressTree($pgs1)
            ->withManuallyAssigned(true);
        $this->adapter
            ->expects($this->once())
            ->method('getRelevantProgrammeIds')
            ->willReturn([
                1=>3
            ]);
        $this->assignment_repo
            ->expects($this->once())
            ->method('getAboutToExpire')
            ->willReturn([$ass1, $ass2]);

        $this->assignment_repo
            ->expects($this->exactly(1))
            ->method('store');

        $validity_settings = $this->getMockBuilder(ilStudyProgrammeValidityOfAchievedQualificationSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRestartRecheck'])
            ->getMock();

        $validity_settings
            ->expects($this->exactly(2))
            ->method('getRestartRecheck')
            ->willReturn(true);

        $settings = $this->getMockBuilder(ilStudyProgrammeSettings::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValidityOfQualificationSettings'])
            ->getMock();
        $settings
            ->expects($this->exactly(2))
            ->method('getValidityOfQualificationSettings')
            ->willReturn($validity_settings);

        $this->prg
            ->expects($this->exactly(2))
            ->method('getSettings')
            ->willReturn($settings);

        $this->prg
            ->expects($this->exactly(1))
            ->method('getApplicableMembershipSourceForUser')
            ->willReturn(null);

        $this->prg
            ->expects($this->exactly(1))
            ->method('assignUser')
            ->willReturn($ass1);


        $this->adapter
            ->expects($this->exactly(1))
            ->method('actOnSingleAssignment');

        $this->job->run();
    }

    public function testRestartAssignmentsEvents(): void
    {
        $pgs1 = (new ilPRGProgress(11, ilPRGProgress::STATUS_COMPLETED));
        $ass1 = (new ilPRGAssignment(42, 7))->withProgressTree($pgs1);
        $ass2 = (new ilPRGAssignment(43, 8))->withProgressTree($pgs1)
         ->withManuallyAssigned(true);

        $this->settings_repo
            ->expects($this->once())
            ->method('getProgrammeIdsWithReassignmentForExpiringValidity')
            ->willReturn([
                42=>3,
                43=>3
            ]);

        $this->assignment_repo
            ->expects($this->once())
            ->method('getAboutToExpire')
            ->willReturn([$ass1, $ass2]);
        $this->prg
            ->expects($this->exactly(2))
            ->method('assignUser')
            ->will($this->onConsecutiveCalls($ass1, $ass2));

        $job = new ilPrgRestartAssignmentsCronJobMock($this->assignment_repo, $this->real_adapter, $this->prg);
        $job->run();

        $this->assertEquals(2, count($job->logs));
        $expected_events = [
            ['userReAssigned', ["ass_id" => 42, 'root_prg_id' => 11]],
            ['userReAssigned', ["ass_id" => 43, 'root_prg_id' => 11]]
        ];
        $this->assertEquals($expected_events, $this->events->raised);
    }
}
