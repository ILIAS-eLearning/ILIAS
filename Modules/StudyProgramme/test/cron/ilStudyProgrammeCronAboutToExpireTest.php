<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ilStudyProgrammeCronAboutToExpireTest extends TestCase
{
    /**
     * @var ilPrgUserNotRestartedCronJob|mixed|MockObject
     */
    protected $job;
    /**
     * @var ilStudyProgrammeSettingsDBRepository|mixed|MockObject
     */
    protected $settings_repo;
    /**
     * @var ilStudyProgrammeProgressDBRepository|mixed|MockObject
     */
    protected $progress_repo;
    protected ProgrammeEventsMock $events;

    public function setUp() : void
    {
        $this->job = $this
            ->getMockBuilder(ilPrgUserNotRestartedCronJob::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvents', 'getSettingsRepository', 'getProgressRepository', 'log'])
            ->getMock();

        $this->settings_repo = $this->getMockBuilder(ilStudyProgrammeSettingsDBRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProgrammeIdsWithMailsForExpiringValidity'])
            ->getMock();

        $this->progress_repo = $this->getMockBuilder(ilStudyProgrammeProgressDBRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAboutToExpire'])
            ->getMock();

        $this->events = new ProgrammeEventsMock();
    }

    public function testRiskyToFailNoSettings() : void
    {
        $this->settings_repo
            ->expects($this->once())
            ->method('getProgrammeIdsWithMailsForExpiringValidity')
            ->willReturn([]);


        $this->job->expects($this->once())
            ->method('getSettingsRepository')
            ->willReturn($this->settings_repo);
      
        $this->job->expects($this->never())
            ->method('getProgressRepository');
      
        $this->job->expects($this->never())
            ->method('getEvents');

        $this->job->run();
    }

    public function testRiskyToFailNoRepos() : void
    {
        $this->settings_repo
            ->expects($this->once())
            ->method('getProgrammeIdsWithMailsForExpiringValidity')
            ->willReturn([
                71 => 2, //id 71, 2 days
                72 => 4
            ]);

        $this->progress_repo
            ->expects($this->once())
            ->method('getAboutToExpire')
            ->willReturn([]);

        $this->job->expects($this->once())
            ->method('getSettingsRepository')
            ->willReturn($this->settings_repo);
      
        $this->job->expects($this->once())
            ->method('getProgressRepository')
            ->willReturn($this->progress_repo);
      
        $this->job->expects($this->never())
            ->method('getEvents');
        
        $this->job->run();
    }

    public function testRiskyToFail() : void
    {
        $this->settings_repo
            ->expects($this->once())
            ->method('getProgrammeIdsWithMailsForExpiringValidity')
            ->willReturn([71 => 2]);

        $progress_1 = (new ilStudyProgrammeProgress(1))->withUserId(11)->withNodeId(71)->withAssignmentId(61);
        $progress_2 = (new ilStudyProgrammeProgress(2))->withUserId(22)->withNodeId(71)->withAssignmentId(62);
        $progress_3 = (new ilStudyProgrammeProgress(3))->withUserId(33)->withNodeId(71)->withAssignmentId(63);
  
        $expected_events = [
            ['informUserToRestart', ["usr_id" => 11, "ass_id" => 61, 'progress_id' => 1]],
            ['informUserToRestart', ["usr_id" => 22, "ass_id" => 62, 'progress_id' => 2]],
            ['informUserToRestart', ["usr_id" => 33, "ass_id" => 63, 'progress_id' => 3]]
        ];
  
        $this->progress_repo
            ->expects($this->once())
            ->method('getAboutToExpire')
            ->willReturn([
                $progress_1,
                $progress_2,
                $progress_3
            ]);

        $this->job->expects($this->once())
            ->method('getSettingsRepository')
            ->willReturn($this->settings_repo);
      
        $this->job->expects($this->once())
            ->method('getProgressRepository')
            ->willReturn($this->progress_repo);
      
        $this->job->expects($this->once())
            ->method('getEvents')
            ->willReturn($this->events);

        $this->job->run();
        $this->assertEquals($expected_events, $this->events->raised);
    }
}
