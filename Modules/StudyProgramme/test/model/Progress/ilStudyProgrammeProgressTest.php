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

class ilStudyProgrammeProgressTest extends TestCase
{
    protected function getUserIdAndNow(): array
    {
        return [6, new DateTimeImmutable()];
    }

    public function test_init_and_id(): ilStudyProgrammeProgress
    {
        $spp = new ilStudyProgrammeProgress(123);
        $this->assertEquals(123, $spp->getId());
        return $spp;
    }

    /**
     * @depends test_init_and_id
     */
    public function test_assignment_id(): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withAssignmentId(321);
        $this->assertEquals(321, $spp->getAssignmentId());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_node_id(): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withNodeId(321);
        $this->assertEquals(321, $spp->getNodeId());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_user_id(): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withUserId(321);
        $this->assertEquals(321, $spp->getUserId());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_amount_of_points(): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withAmountOfPoints(321);
        $this->assertEquals(321, $spp->getAmountOfPoints());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_amount_of_points_invalid(): void
    {
        $this->expectException(ilException::class);
        (new ilStudyProgrammeProgress(123))->withAmountOfPoints(-321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_current_amount_of_points(): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withCurrentAmountOfPoints(321);
        $this->assertEquals(321, $spp->getCurrentAmountOfPoints());
    }

    public function status(): array
    {
        return [
            //status, count as 'successful'
            [ilStudyProgrammeProgress::STATUS_IN_PROGRESS, false],
            [ilStudyProgrammeProgress::STATUS_COMPLETED, true],
            [ilStudyProgrammeProgress::STATUS_ACCREDITED, true],
            [ilStudyProgrammeProgress::STATUS_NOT_RELEVANT, false],
            [ilStudyProgrammeProgress::STATUS_FAILED, false]
        ];
    }

    /**
     * @dataProvider status
     */
    public function test_status(int $status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus($status);
        $this->assertEquals($spp->getStatus(), $status);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_status_invalid(): void
    {
        $this->expectException(ilException::class);
        (new ilStudyProgrammeProgress(123))->withStatus(321);
    }

    /**
     * @depends test_init_and_id
     */
    public function testWithLastChange(): void
    {
        [$acting_usr, $now] = $this->getUserIdAndNow();
        $spp = (new ilStudyProgrammeProgress(123))->withLastChange($acting_usr, $now);
        $this->assertEquals($spp->getLastChangeBy(), $acting_usr);
        $this->assertEquals($spp->getLastChange()->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_assignment_date(): void
    {
        $ad = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))->withAssignmentDate($ad);
        $this->assertEquals($spp->getAssignmentDate()->format('Y-m-d'), $ad->format('Y-m-d'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_completion(): void
    {
        [$acting_usr, $now] = $this->getUserIdAndNow();

        $spp = (new ilStudyProgrammeProgress(123))->withCompletion($acting_usr, $now);
        $this->assertEquals($now->format('Y-m-d'), $spp->getCompletionDate()->format('Y-m-d'));
        $this->assertEquals($acting_usr, $spp->getCompletionBy());

        $spp = (new ilStudyProgrammeProgress(123))->withCompletion();
        $this->assertNull($spp->getCompletionDate());
        $this->assertNull($spp->getCompletionBy());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_deadline(): void
    {
        $dl = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))->withDeadline($dl);
        $this->assertEquals($spp->getDeadline()->format('Y-m-d'), $dl->format('Y-m-d'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_vq_date(): void
    {
        $dl = DateTimeImmutable::createFromFormat('Ymd', '20201011');
        $spp = (new ilStudyProgrammeProgress(123))->withValidityOfQualification($dl);
        $this->assertEquals('20201011', $spp->getValidityOfQualification()->format('Ymd'));
    }

    /**
     * @depends test_init_and_id
     */
    public function testIndividualPlan(ilStudyProgrammeProgress $spp): void
    {
        $this->assertFalse($spp->hasIndividualModifications());
        $this->assertTrue($spp->withIndividualModifications(true)->hasIndividualModifications());
        $this->assertFalse($spp->withIndividualModifications(false)->hasIndividualModifications());
    }

    /**
     * @depends test_vq_date
     */
    public function test_invalidate(): void
    {
        $spp = new ilStudyProgrammeProgress(123);
        $this->assertFalse($spp->isInvalidated());
        $past = DateTimeImmutable::createFromFormat('Ymd', '20180101');
        $spp = $spp
            ->withValidityOfQualification($past)
            ->invalidate();
        $this->assertTrue($spp->isInvalidated());
    }

    /**
     * @depends test_vq_date
     */
    public function test_invalidate_non_expired_1(): void
    {
        $this->expectException(ilException::class);
        $tomorrow = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        (new ilStudyProgrammeProgress(123))->withValidityOfQualification($tomorrow)->invalidate();
    }

    /**
     * @depends test_vq_date
     */
    public function test_invalidate_non_expired_2(): void
    {
        $this->expectException(ilException::class);
        (new ilStudyProgrammeProgress(123))->invalidate();
    }

    /**
     * @dataProvider status
     */
    public function testIsSuccessful($status, $success): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus($status);
        $this->assertEquals($success, $spp->isSuccessful());
    }

    public function testHasValidQualification(): void
    {
        $today = new DateTimeImmutable();
        $yesterday = $today->sub(new DateInterval('P1D'));
        $tomorrow = $today->add(new DateInterval('P1D'));

        $spp = (new ilStudyProgrammeProgress(123))
            ->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
            ->withValidityOfQualification($today);

        $this->assertNull($spp->hasValidQualification($today));

        $spp = $spp->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED);
        $this->assertTrue($spp->hasValidQualification($yesterday));
        $this->assertTrue($spp->hasValidQualification($today));
        $this->assertFalse($spp->hasValidQualification($tomorrow));
    }

    public function testMarkRelevant(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->markRelevant($now, $usr);

        $this->assertNull($spp->getCompletionBy());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            $spp->getStatus()
        );
        $this->assertTrue($spp->hasIndividualModifications());
    }

    public function testMarkNotRelevant(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->markNotRelevant($now, $usr);

        $this->assertNull($spp->getCompletionBy());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT,
            $spp->getStatus()
        );
        $this->assertTrue($spp->hasIndividualModifications());
    }

    public function testMarkFailed(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->markFailed($now, $usr);

        $this->assertNull($spp->getCompletionDate());
        $this->assertNull($spp->getCompletionBy());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_FAILED,
            $spp->getStatus()
        );
    }

    public function testMarkNotFailed(): void
    {
        [$usr, $now] = $this->getUserIdAndNow();
        $spp = (new ilStudyProgrammeProgress(123))
            ->markNotFailed($now, $usr);

        $this->assertNull($spp->getCompletionDate());
        $this->assertNull($spp->getCompletionBy());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            $spp->getStatus()
        );
    }

    public function testMarkAccredited(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->markAccredited($now, $usr);

        $this->assertEquals($now, $spp->getCompletionDate());
        $this->assertEquals($usr, $spp->getCompletionBy());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_ACCREDITED,
            $spp->getStatus()
        );
    }

    public function testUnmarkAccredited(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->unmarkAccredited($now, $usr);

        $this->assertNull($spp->getCompletionDate());
        $this->assertNull($spp->getCompletionBy());
        $this->assertNull($spp->getValidityOfQualification());
        $this->assertEquals($usr, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            $spp->getStatus()
        );
    }

    public function testSucceed(): void
    {
        $triggering_obj = 777;
        $now = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))
            ->succeed($now, $triggering_obj);

        $this->assertEquals($now, $spp->getCompletionDate());
        $this->assertEquals($triggering_obj, $spp->getCompletionBy());
        $this->assertEquals($triggering_obj, $spp->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilStudyProgrammeProgress::DATE_FORMAT),
            $spp->getLastChange()->format(ilStudyProgrammeProgress::DATE_FORMAT)
        );
        $this->assertEquals(
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            $spp->getStatus()
        );
    }

    /**
     * @dataProvider status
     */
    public function testAllowedTransitionsForInProgress($status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
        if (in_array($status, [
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT,
            ilStudyProgrammeProgress::STATUS_ACCREDITED,
            ilStudyProgrammeProgress::STATUS_FAILED,
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            $spp->getStatus()
        ])) {
            $this->assertTrue($spp->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($spp->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testAllowedTransitionsForAccredited($status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(ilStudyProgrammeProgress::STATUS_ACCREDITED);
        if (in_array($status, [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_FAILED,
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT,
            $spp->getStatus()
        ])) {
            $this->assertTrue($spp->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($spp->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testAllowedTransitionsForCompleted($status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED);
        if (in_array($status, [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            $spp->getStatus()
        ])) {
            $this->assertTrue($spp->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($spp->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testAllowedTransitionsForFailed($status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(ilStudyProgrammeProgress::STATUS_FAILED);
        if (in_array($status, [
            ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT,
            $spp->getStatus()
        ])) {
            $this->assertTrue($spp->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($spp->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testAllowedTransitionsForIrrelevant($status): void
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT);
        if ($status === ilStudyProgrammeProgress::STATUS_IN_PROGRESS
            || $status === $spp->getStatus()
        ) {
            $this->assertTrue($spp->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($spp->isTransitionAllowedTo($status));
        }
    }
}
