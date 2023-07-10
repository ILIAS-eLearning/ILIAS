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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../prg_mocks.php");


class ilStudyProgrammeAssignmentActionsTest extends \PHPUnit\Framework\TestCase
{
    protected ilStudyProgrammeSettingsRepository $settings_repo;
    protected ilPRGMessageCollection $messages;
    protected ilPRGAssignment $ass;
    protected ilStudyProgrammeEvents $events;

    public function setUp(): void
    {
        $this->settings_repo = new SettingsRepoMock();
        foreach ([1, 11, 12, 13, 111, 112] as $pgs_id) {
            $settings = new SettingsMock($pgs_id);
            $settings->setLPMode(ilStudyProgrammeSettings::MODE_POINTS);
            $this->settings_repo->update($settings);
        }

        $this->messages = new ilPRGMessageCollection();
        $this->events = new ProgrammeEventsMock();

        $udf = $this->getMockBuilder(ilUserDefinedData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user_info = new ilPRGUserInformation(
            $udf,
            'some OrgU',
            'firstname',
            'lasttname',
            'login',
            true,
            'f.lastname@example.com',
            'f',
            'Prof. Dr.',
        );

        $this->ass = (new ilPRGAssignment(42, 7))
            ->withUserInformation($user_info)
            ->withProgressTree(
                $this->getProgressesWithDefaultStatus(
                    ilPRGProgress::STATUS_NOT_RELEVANT
                )
            );
    }

    protected function getProgressesWithDefaultStatus(int $status): ilPRGProgress
    {
        /*
        └── 1
            ├── 11
            │   ├── 111
            │   └── 112
            ├── 12
            └── 13
        */
        $pgs112 = (new ilPRGProgress(112, $status))->withAmountOfPoints(4);
        $pgs111 = (new ilPRGProgress(111, $status))->withAmountOfPoints(5);
        $pgs11 = (new ilPRGProgress(11, $status))->setSubnodes([$pgs111, $pgs112])->withAmountOfPoints(8);
        $pgs12 = new ilPRGProgress(12, $status);
        $pgs13 = new ilPRGProgress(13, $status);
        $pgs1 = (new ilPRGProgress(1, $status))->setSubnodes([$pgs11, $pgs12, $pgs13]);
        return $pgs1;
    }


    public function testPRGAssignmentActionsInitDates(): void
    {
        $today = (new \DateTimeImmutable())->format('Ymd');
        $ass = $this->ass->initAssignmentDates();
        foreach ([1, 11, 12, 13, 111, 112] as $pgs_id) {
            $this->assertEquals(
                $today,
                $ass->getProgressForNode($pgs_id)->getAssignmentDate()->format('Ymd')
            );
        }
    }

    public function testPRGAssignmentActionsMarkRelevant(): void
    {
        $ass = $this->ass->markRelevant($this->settings_repo, 111, 7, $this->messages);

        $this->assertEquals(
            ilPRGProgress::STATUS_IN_PROGRESS,
            $ass->getProgressForNode(111)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $ass->getProgressForNode(11)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $ass->getProgressForNode(12)->getStatus()
        );

        $messages = clone $this->messages;
        $ass = $this->ass
            ->markNotRelevant($this->settings_repo, 12, 7, $messages)
            ->markNotRelevant($this->settings_repo, 111, 7, $messages);

        $this->assertTrue($messages->hasErrors());
        $this->assertEquals(
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $ass->getProgressForNode(12)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $ass->getProgressForNode(111)->getStatus()
        );
    }

    public function testPRGAssignmentActionsMarkAccredited(): void
    {
        $ass = $this->ass->withProgressTree(
            $this->getProgressesWithDefaultStatus(ilPRGProgress::STATUS_IN_PROGRESS)
        );

        $ass = $ass
            ->markAccredited($this->settings_repo, $this->events, 111, 7, $this->messages)
            ->markAccredited($this->settings_repo, $this->events, 112, 7, $this->messages)
            ->markAccredited($this->settings_repo, $this->events, 13, 7, $this->messages);
        $this->assertEquals(
            ilPRGProgress::STATUS_ACCREDITED,
            $ass->getProgressForNode(111)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_ACCREDITED,
            $ass->getProgressForNode(112)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_COMPLETED,
            $ass->getProgressForNode(11)->getStatus()
        );
        $this->assertEquals(5 + 4, $ass->getProgressForNode(11)->getCurrentAmountOfPoints());
        $this->assertEquals(
            ilPRGProgress::STATUS_ACCREDITED,
            $ass->getProgressForNode(13)->getStatus()
        );

        $ass = $ass
            ->unmarkAccredited($this->settings_repo, 112, 7, $this->messages)
            ->unmarkAccredited($this->settings_repo, 111, 7, $this->messages)
            ->unmarkAccredited($this->settings_repo, 13, 7, $this->messages);

        $this->assertEquals(
            ilPRGProgress::STATUS_IN_PROGRESS,
            $ass->getProgressForNode(112)->getStatus()
        );
        $this->assertEquals(0, $ass->getProgressForNode(11)->getCurrentAmountOfPoints());
        $this->assertEquals(
            ilPRGProgress::STATUS_COMPLETED, //completion may not be revoked manually
            $ass->getProgressForNode(13)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_IN_PROGRESS, //completion may be revoked automatically
            $ass->getProgressForNode(11)->getStatus()
        );
    }

    public function testPRGAssignmentActionsSucceedAndDeadline(): void
    {
        $today = new DateTimeImmutable();
        $yesterday = $today->sub(new DateInterval('P1D'));

        $pgss = $this->getProgressesWithDefaultStatus(ilPRGProgress::STATUS_IN_PROGRESS);
        $pgs12 = $pgss->getSubnode('13')->withDeadline($today);
        $pgs13 = $pgss->getSubnode('13')->withDeadline($yesterday);
        $pgss = $pgss
            ->withSubnode($pgs12)
            ->withSubnode($pgs13);

        $ass = $this->ass
            ->withProgressTree($pgss)
            ->succeed($this->settings_repo, 12, 777)
            ->succeed($this->settings_repo, 13, 777);

        $this->assertEquals(
            ilPRGProgress::STATUS_COMPLETED,
            $ass->getProgressForNode(12)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_IN_PROGRESS,
            $ass->getProgressForNode(13)->getStatus()
        );

        $ass = $ass
            ->changeProgressDeadline(
                $this->settings_repo,
                11,
                7,
                $this->messages,
                $yesterday
            )
            ->changeProgressDeadline(
                $this->settings_repo,
                13,
                7,
                $this->messages,
                $today
            );

        $this->assertEquals(
            ilPRGProgress::STATUS_FAILED,
            $ass->getProgressForNode(11)->getStatus()
        );
        $this->assertEquals(
            ilPRGProgress::STATUS_COMPLETED,
            $ass->getProgressForNode(13)->getStatus()
        );
    }

    public function testPRGAssignmentActionsChangePoints(): void
    {
        $ass = $this->ass->changeAmountOfPoints(
            $this->settings_repo,
            111,
            7,
            $this->messages,
            1987
        );

        $this->assertEquals(5, $ass->getProgressForNode(111)->getAmountOfPoints());
        $ass = $this->ass
            ->markRelevant($this->settings_repo, 111, 7, $this->messages)
            ->changeAmountOfPoints(
                $this->settings_repo,
                111,
                7,
                $this->messages,
                1987
            );
        $this->assertEquals(1987, $ass->getProgressForNode(111)->getAmountOfPoints());
    }
}
