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

require_once(__DIR__ . "/prg_mocks.php");

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeProgressCalculationsTest extends TestCase
{
    public ProgressRepoMock $progress_repo;
    public AssignmentRepoMock $assignment_repo;
    public SettingsRepoMock $settings_repo;
    public ilPRGMessageCollection $messages;
    public array $mock_tree = [];

    protected function buildProgramme(int $prg_id): ilObjStudyProgramme
    {
        $settings = new SettingsMock(
            $prg_id
        );
        $settings->setLPMode(ilStudyProgrammeSettings::MODE_POINTS);
        $set_ass = new ilStudyProgrammeAssessmentSettings(100, ilStudyProgrammeAssessmentSettings::STATUS_ACTIVE);
        $set_dl = new ilStudyProgrammeDeadlineSettings(null, null);
        $set_vq = new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, null, null);
        $settings = $settings
            ->withAssessmentSettings($set_ass)
            ->withDeadlineSettings($set_dl)
            ->withValidityOfQualificationSettings($set_vq);
        $this->settings_repo->update($settings);

        $progress = (new ilStudyProgrammeProgress($prg_id * -1))
                ->withUserId(666)
                ->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                ->withAmountOfPoints(2)
                ->withAssignmentId(-42)
                ->withNodeId($prg_id);
        $this->progress_repo->update($progress);
        return new PrgMock($prg_id, $this->progress_repo, $this->assignment_repo, $this->settings_repo, $this->mock_tree);
    }

    protected function setUp(): void
    {
        $this->progress_repo = new ProgressRepoMock();
        $this->assignment_repo = new AssignmentRepoMock();
        $this->settings_repo = new SettingsRepoMock();
        $this->messages = new ilPRGMessageCollection();

        /*
        └── 1
            ├── 11
            │   ├── 111
            │   └── 112
            ├── 12
            └── 13
        */
        $this->mock_tree[1] = ['parent' => null, 'children' => [11,12,13], 'prg' => $this->buildProgramme(1)];
        $this->mock_tree[11] = ['parent' => 1, 'children' => [111,112], 'prg' => $this->buildProgramme(11)];
        $this->mock_tree[111] = ['parent' => 11, 'children' => [], 'prg' => $this->buildProgramme(111)];
        $this->mock_tree[112] = ['parent' => 11, 'children' => [], 'prg' => $this->buildProgramme(112)];
        $this->mock_tree[12] = ['parent' => 1, 'children' => [], 'prg' => $this->buildProgramme(12)];
        $this->mock_tree[13] = ['parent' => 1, 'children' => [], 'prg' => $this->buildProgramme(13)];

        $assignment = (new ilStudyProgrammeAssignment(-42))
            ->withRootId(1);
        $this->assignment_repo->update($assignment);
    }


    protected function getRootPrg(): PrgMock
    {
        return $this->mock_tree[1]['prg'];
    }

    protected function setPointsForNode(int $node_id, int $points): void
    {
        $set = new ilStudyProgrammeAssessmentSettings($points, ilStudyProgrammeAssessmentSettings::STATUS_ACTIVE);
        $this->settings_repo->update($this->settings_repo->get($node_id)->withAssessmentSettings($set));
        $this->progress_repo->update($this->progress_repo->get($node_id)->withAmountOfPoints($points));

        $this->assertEquals($points, $this->mock_tree[$node_id]['prg']->getPoints());
    }

    protected function setModeForNode(int $node_id, int $mode): void
    {
        $set = $this->settings_repo->get($node_id)->setLPMode($mode);
        $this->settings_repo->update($set);
    }

    //this is a meta-test to assure that this setup is working properly
    public function testInternalTreeIntegrity(): void
    {
        $progress = $this->progress_repo->get(111);
        $parent = $this->getRootPrg()->getParentProgress($progress);
        $this->assertEquals(111, $progress->getNodeId());
        $this->assertEquals(11, $parent->getNodeId());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $parent->getStatus());
        $this->progress_repo->update(
            $parent->withStatus(ilStudyProgrammeProgress::STATUS_FAILED)
        );
        $progress = $this->progress_repo->get(112);
        $parent = $this->getRootPrg()->getParentProgress($progress);
        $this->assertEquals(11, $parent->getNodeId());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_FAILED, $parent->getStatus());

        $progress = $this->progress_repo->get(11);
        $this->assertEquals(
            [
                $this->progress_repo->get(111),
                $this->progress_repo->get(112)
            ],
            $this->getRootPrg()->getChildrenProgress($progress)
        );
    }

    //this is another meta-test to assure that there are no unwanted side effects in the repos
    public function testInternalRunIntegrity(): void
    {
        $progress = $this->progress_repo->get(11);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $progress->getStatus());
    }


    /**
     ── 1
        ├── 11
        │   ├── 111
        │   └── 112
        ├── 12
        └── 13
     */
    public function testParentAcquisition(): void
    {
        $progress = $this->getRootPrg()
            ->testUpdateParentProgress($this->progress_repo->get(112));
        $this->assertEquals($this->progress_repo->get(1), $progress);

        $progress = $this->getRootPrg()
            ->testUpdateParentProgress($this->progress_repo->get(13));
        $this->assertEquals($this->progress_repo->get(1), $progress);
    }

    /**
     ── 1 (1)
        ├── 11 (8)
        │   ├── 111 (5)
        │   └── 112 (10) <-- turns irrelevant
        ├── 12 (12) <-- turns irrelevant
        └── 13 (10)
     */
    public function testChildrenPossiblePointsAddition(): void
    {
        $this->setPointsForNode(111, 5);
        $this->setPointsForNode(112, 10);
        $this->setPointsForNode(11, 8);
        $this->setPointsForNode(1, 1);
        $this->setPointsForNode(12, 12);
        $this->setPointsForNode(13, 10);

        $this->assertEquals(
            15, //111+112
            $this->getRootPrg()->getPossiblePointsOfRelevantChildren(
                $this->progress_repo->get(11)
            )
        );

        $this->assertEquals(
            30, //11+12+13
            $this->getRootPrg()->getPossiblePointsOfRelevantChildren(
                $this->progress_repo->get(1)
            )
        );

        $this->getRootPrg()->markNotRelevant(12, 6, $this->messages);
        $this->getRootPrg()->markNotRelevant(112, 6, $this->messages);

        $this->assertEquals(
            5, //111 (w/o + 112)
            $this->getRootPrg()->getPossiblePointsOfRelevantChildren(
                $this->progress_repo->get(11)
            )
        );

        $this->assertEquals(
            18,
            $this->getRootPrg()->getPossiblePointsOfRelevantChildren(
                $this->progress_repo->get(1)
            )
        );
    }

    /**
     ── 1 (1)
        ├── 11 (8)
        │   ├── 111 (5)
        │   └── 112 (10)
        ├── 12 (12)
        └── 13 (10)
     */
    public function testAchievedPoints(): void
    {
        $this->setPointsForNode(111, 5);
        $this->setPointsForNode(112, 10);
        $this->setPointsForNode(11, 8);
        $this->setPointsForNode(1, 1);
        $this->setPointsForNode(12, 12);
        $this->setPointsForNode(13, 10);
        $prg = $this->getRootPrg();

        $this->assertEquals(0, $prg->getAchievedPointsOfChildren($this->progress_repo->get(12)));
        $this->assertEquals(0, $prg->getAchievedPointsOfChildren($this->progress_repo->get(11)));
        $this->assertEquals(0, $prg->getAchievedPointsOfChildren($this->progress_repo->get(1)));

        $prg->markAccredited(12, 6, $this->messages);
        $prg->markAccredited(13, 6, $this->messages);
        $this->assertEquals(22, $prg->getAchievedPointsOfChildren($this->progress_repo->get(1)));
        $this->assertEquals(22, $this->progress_repo->get(1)->getCurrentAmountOfPoints());

        $prg->markAccredited(111, 6, $this->messages);
        $this->assertEquals(5, $prg->getAchievedPointsOfChildren($this->progress_repo->get(11)));
    }


    /**
     PRG: MODE_POINTS
     ── 1 (5)
        ├── 11 (12)
        │   ├── 111 (5)
        │   └── 112 (7)
        ├── 12 (2)
        └── 13 (2)
     */
    public function testMarkAccreditedShouldCompleteParentBySufficientPoints(): void
    {
        $this->setPointsForNode(111, 5);
        $this->setPointsForNode(112, 7);
        $this->setPointsForNode(11, 12);
        $this->setPointsForNode(1, 5);

        $this->getRootPrg()->markAccredited(111, 6, $this->messages);
        $this->getRootPrg()->markAccredited(112, 6, $this->messages);

        $this->assertEquals(5, $this->progress_repo->get(111)->getCurrentAmountOfPoints());
        $this->assertEquals(7, $this->progress_repo->get(112)->getCurrentAmountOfPoints());
        $this->assertEquals(12, $this->progress_repo->get(11)->getCurrentAmountOfPoints());
        $this->assertEquals(12, $this->progress_repo->get(1)->getCurrentAmountOfPoints());

        $this->assertEquals(
            16,
            $this->getRootPrg()->getPossiblePointsOfRelevantChildren(
                $this->progress_repo->get(1)
            )
        );
        $this->assertEquals(
            12,
            $this->getRootPrg()->getAchievedPointsOfChildren(
                $this->progress_repo->get(1)
            )
        );

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(111)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(112)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(1)->getStatus());
    }

    /**
     ── 1 (10)
        ├── 11 (5, LP)
        └── 12 (5)
     */
    public function testMarkAccreditedOnLPNode(): void
    {
        $this->setPointsForNode(1, 10);
        $this->setPointsForNode(11, 5);
        $this->setPointsForNode(12, 5);
        $this->setModeForNode(11, ilStudyProgrammeSettings::MODE_LP_COMPLETED);
        $this->mock_tree = [
            1 => $this->mock_tree[1],
                11 => $this->mock_tree[13],
                12 => $this->mock_tree[12]
        ];

        $this->getRootPrg()->markAccredited(11, 6, $this->messages);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(5, $this->progress_repo->get(11)->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $this->progress_repo->get(12)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $this->progress_repo->get(1)->getStatus());

        $this->getRootPrg()->markAccredited(12, 6, $this->messages);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(12)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(1)->getStatus());
        $this->assertEquals(5, $this->progress_repo->get(11)->getCurrentAmountOfPoints());
        $this->assertEquals(5, $this->progress_repo->get(12)->getCurrentAmountOfPoints());
        $this->assertEquals(10, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
    }

    /**
     ── 1
        ├── 11 (irrelevant)
        │   ├── 111
        │   └── 112
        ├── 12
        └── 13
     */
    public function testMarkAccreditedShouldNotChangeIrrelevantParent(): void
    {
        $this->progress_repo->update(
            $this->progress_repo->get(11)->withStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
        );

        $this->getRootPrg()->markAccredited(111, 6, $this->messages);
        $this->getRootPrg()->markAccredited(112, 6, $this->messages);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(111)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(112)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $this->progress_repo->get(1)->getStatus());
        $this->assertEquals(0, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
    }

    public function testMarkRelevantShouldRecalculateStatusAndPoints(): void
    {
        $this->progress_repo->update(
            $this->progress_repo->get(11)->withStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
        );

        $this->getRootPrg()->markAccredited(111, 6, $this->messages);
        $this->getRootPrg()->markAccredited(112, 6, $this->messages);
        $this->assertEquals(0, $this->progress_repo->get(1)->getCurrentAmountOfPoints());

        $this->getRootPrg()->markRelevant(11, 6, $this->messages);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(2, $this->progress_repo->get(1)->getCurrentAmountOfPoints());

        $this->getRootPrg()->markNotRelevant(11, 6, $this->messages);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_ACCREDITED, $this->progress_repo->get(111)->getStatus());
        $this->assertEquals(2, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
    }

    /*
     ── 1 (6 points)
        ├── 11 (4 points)
        │   ├── 111 (LP-Mode, 2 points) <-- succeed
        │   └── 112 (accredited, 3 points)
        ├── 12 (2 points)
        └── 13 (accredited, 2 points)
     */
    public function testSuccessionCompletingParents(): void
    {
        $this->setPointsForNode(1, 6);
        $this->setPointsForNode(11, 4);
        $this->setPointsForNode(112, 3);
        $this->setModeForNode(111, ilStudyProgrammeSettings::MODE_LP_COMPLETED);

        $this->mock_tree[112]['prg']->markAccredited(112, 6, $this->messages);
        $this->mock_tree[12]['prg']->markAccredited(13, 6, $this->messages);
        $triggering_obj_id = 9001;
        $this->mock_tree[111]['prg']->succeed(111, $triggering_obj_id);

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(111)->getStatus());
        $this->assertEquals(2, $this->progress_repo->get(111)->getCurrentAmountOfPoints());

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(11)->getStatus());
        $this->assertEquals(5, $this->progress_repo->get(11)->getCurrentAmountOfPoints());

        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $this->progress_repo->get(1)->getStatus());
        $this->assertEquals(6, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
    }

    /*
     ── 1 (6 points)
        ├── 11 (accredited, 2 points)
        │   ├── 111
        │   └── 112
        ├── 12 (2 points)
        └── 13 (accredited, 2 points)
     */
    public function testMarkIrrelevantDoesNotCompleteParent(): void
    {
        $this->setPointsForNode(1, 6);
        $this->getRootPrg()->markAccredited(11, 6, $this->messages);
        $this->getRootPrg()->markAccredited(13, 6, $this->messages);

        $this->assertEquals(4, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $this->progress_repo->get(1)->getStatus());

        $this->getRootPrg()->markNotRelevant(12, 6, $this->messages);

        $this->assertEquals(4, $this->progress_repo->get(1)->getCurrentAmountOfPoints());
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_IN_PROGRESS, $this->progress_repo->get(1)->getStatus());
    }


    public function testFailByDeadline(): void
    {
        $past = DateTimeImmutable::createFromFormat('Ymd', '20010101');
        $progress = $this->progress_repo->get(13)->withDeadline($past);

        $progress = $this->getRootPrg()->testApplyProgressDeadline($progress);

        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_FAILED,
            $progress->getStatus()
        );
    }

    public function testDontFailByFutureDeadline(): void
    {
        $progress = $this->progress_repo->get(13);
        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $progress = $this->getRootPrg()->testApplyProgressDeadline(
            $progress->withDeadline($future)
        );

        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            $progress->getStatus()
        );
    }

    public function testDontFailByDeadlineIfSucceeded(): void
    {
        $progress = $this->progress_repo->get(13)
            ->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED);
        $this->progress_repo->update($progress);

        $past = DateTimeImmutable::createFromFormat('Ymd', '20010101');
        $progress = $this->getRootPrg()->testApplyProgressDeadline(
            $progress->withDeadline($past)
        );

        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            $progress->getStatus()
        );
    }

    public function testChangingDeadline(): void
    {
        $past = DateTimeImmutable::createFromFormat('Ymd', '20010101');
        $this->getRootPrg()->changeProgressDeadline(12, 6, $this->messages, $past);

        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_FAILED,
            $this->progress_repo->get(12)->getStatus()
        );
    }

    public function testDontChangeDeadlineForCompleted(): void
    {
        $this->progress_repo->update(
            $this->progress_repo->get(12)
            ->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED)
        );

        $past = DateTimeImmutable::createFromFormat('Ymd', '20010101');
        $this->getRootPrg()->changeProgressDeadline(12, 6, $this->messages, $past);

        $progress = $this->progress_repo->get(12);
        $this->assertEquals(ilStudyProgrammeProgress::STATUS_COMPLETED, $progress->getStatus());
        $this->assertNull($progress->getDeadline());
    }

    public function testTranstitionToProgressWithPastDeadline(): void
    {
        $past = DateTimeImmutable::createFromFormat('Ymd', '20010101');
        $this->progress_repo->update(
            $this->progress_repo->get(11)
                ->withStatus(ilStudyProgrammeProgress::STATUS_ACCREDITED)
                ->withDeadline($past)
        );

        $this->getRootPrg()->unmarkAccredited(11, 6, $this->messages);

        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_FAILED,
            $this->progress_repo->get(11)->getStatus()
        );
    }

    public function testChangingValidity(): void
    {
        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $this->progress_repo->update(
            $this->progress_repo->get(1)
                ->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED)
        );

        $this->getRootPrg()->changeProgressValidityDate(1, 6, $this->messages, $future);
        $this->assertEquals(
            $future,
            $this->progress_repo->get(1)->getValidityOfQualification()
        );
    }

    public function testDontChangeValidityForIncomplete(): void
    {
        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $this->progress_repo->update(
            $this->progress_repo->get(1)
                ->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
        );
        $this->getRootPrg()->changeProgressValidityDate(1, 6, $this->messages, $future);
        $this->assertNull($this->progress_repo->get(1)->getValidityOfQualification());
    }

    public function testMarkAsIndividual(): void
    {
        $this->assertFalse($this->progress_repo->get(11)->hasIndividualModifications());

        $this->getRootPrg()->markNotRelevant(11, 6, $this->messages);
        $this->assertTrue($this->progress_repo->get(11)->hasIndividualModifications());

        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));

        $this->getRootPrg()->changeProgressDeadline(12, 6, $this->messages, $future);
        $this->assertTrue($this->progress_repo->get(12)->hasIndividualModifications());

        $this->getRootPrg()->changeProgressValidityDate(13, 6, $this->messages, $future);
        $this->assertFalse($this->progress_repo->get(13)->hasIndividualModifications());
    }

    public function testUpdateFromSettingsResetsIndividual(): void
    {
        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $prg = $this->getRootPrg();
        $prg->mock_tree = $this->mock_tree;

        $prg->changeProgressDeadline(12, 6, $this->messages, $future);
        $this->assertTrue($this->progress_repo->get(12)->hasIndividualModifications());
        $prg->updatePlanFromRepository(12, 6);
        $this->assertFalse($this->progress_repo->get(12)->hasIndividualModifications());
    }

    public function testUpdatePlanFromSettings(): void
    {
        $future = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $future2 = (new DateTimeImmutable())->add(new DateInterval('P4D'));
        $points = 73;

        $set_dl = new ilStudyProgrammeDeadlineSettings(null, $future);
        $set_vq = new ilStudyProgrammeValidityOfAchievedQualificationSettings(null, $future2, null);
        $set_as = new ilStudyProgrammeAssessmentSettings($points, ilStudyProgrammeAssessmentSettings::STATUS_ACTIVE);

        foreach ([1,11,111] as $id) {
            $settings = $this->mock_tree[$id]['prg']->getSettings()
                ->withDeadlineSettings($set_dl)
                ->withValidityOfQualificationSettings($set_vq)
                ->withAssessmentSettings($set_as);

            $this->mock_tree[$id]['prg']->updateSettings($settings);
            $progress = $this->progress_repo->get($id)
                ->withAmountOfPoints(69)
                ->withDeadline($future2)
                ->withValidityOfQualification($future);
            $progress = $progress->markAccredited(
                new DateTimeImmutable(),
                6
            );

            $this->progress_repo->update($progress);
            $progress = $this->progress_repo->get($id);
            $this->assertEquals($future2, $progress->getDeadline());
            $this->assertEquals($future, $progress->getValidityOfQualification());
            $this->assertEquals(69, $progress->getAmountOfPoints());

            $prg = $this->getRootPrg();
            $prg->mock_tree = &$this->mock_tree;
        }

        $this->getRootPrg()->updatePlanFromRepository(11, 6);

        foreach ([1,11,111] as $prg_id) {
            $progress = $this->progress_repo->get($prg_id);
            $this->assertEquals($points, $progress->getAmountOfPoints());
            $this->assertEquals($future, $progress->getDeadline());
        }
    }
}
