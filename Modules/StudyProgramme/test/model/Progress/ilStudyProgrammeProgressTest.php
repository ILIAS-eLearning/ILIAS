<?php declare(strict_types=1);

class ilStudyProgrammeProgressTest extends \PHPUnit\Framework\TestCase
{
    protected function getUserIdAndNow() : array
    {
        return [6, new DateTimeImmutable()];
    }

    public function test_init_and_id()
    {
        $spp = new ilStudyProgrammeProgress(123);
        $this->assertEquals($spp->getId(), 123);
        return $spp;
    }

    /**
     * @depends test_init_and_id
     */
    public function test_assignment_id()
    {
        $spp = (new ilStudyProgrammeProgress(123))->withAssignmentId(321);
        $this->assertEquals($spp->getAssignmentId(), 321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_node_id()
    {
        $spp = (new ilStudyProgrammeProgress(123))->withNodeId(321);
        $this->assertEquals($spp->getNodeId(), 321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_user_id()
    {
        $spp = (new ilStudyProgrammeProgress(123))->withUserId(321);
        $this->assertEquals($spp->getUserId(), 321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_amount_of_points()
    {
        $spp = (new ilStudyProgrammeProgress(123))->withAmountOfPoints(321);
        $this->assertEquals($spp->getAmountOfPoints(), 321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_amount_of_points_invalid()
    {
        $this->expectException(\ilException::class);
        $spp = (new ilStudyProgrammeProgress(123))->withAmountOfPoints(-321);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_current_amount_of_points()
    {
        $spp = (new ilStudyProgrammeProgress(123))->withCurrentAmountOfPoints(321);
        $this->assertEquals($spp->getCurrentAmountOfPoints(), 321);
    }

    public function status()
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
    public function test_status($status)
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus($status);
        $this->assertEquals($spp->getStatus(), $status);
    }

    /**
     * @depends test_init_and_id
     */
    public function test_status_invalid()
    {
        $this->expectException(\ilException::class);
        $spp = (new ilStudyProgrammeProgress(123))->withStatus(321);
    }

    /**
     * @depends test_init_and_id
     */
    public function testWithLastChange() : void
    {
        list($acting_usr, $now) = $this->getUserIdAndNow();
        $spp = (new ilStudyProgrammeProgress(123))->withLastChange($acting_usr, $now);
        $this->assertEquals($spp->getLastChangeBy(), $acting_usr);
        $this->assertEquals($spp->getLastChange()->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_assignment_date()
    {
        $ad = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))->withAssignmentDate($ad);
        $this->assertEquals($spp->getAssignmentDate()->format('Y-m-d'), $ad->format('Y-m-d'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_completion()
    {
        list($acting_usr, $now) = $this->getUserIdAndNow();

        $spp = (new ilStudyProgrammeProgress(123))->withCompletion($acting_usr, $now);
        $this->assertEquals($now->format('Y-m-d'), $spp->getCompletionDate()->format('Y-m-d'));
        $this->assertEquals($acting_usr, $spp->getCompletionBy());

        $spp = (new ilStudyProgrammeProgress(123))->withCompletion(null, null);
        $this->assertNull($spp->getCompletionDate());
        $this->assertNull($spp->getCompletionBy());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_deadline()
    {
        $dl = new DateTimeImmutable();
        $spp = (new ilStudyProgrammeProgress(123))->withDeadline($dl);
        $this->assertEquals($spp->getDeadline()->format('Y-m-d'), $dl->format('Y-m-d'));
    }

    /**
     * @depends test_init_and_id
     */
    public function test_vq_date()
    {
        $dl = DateTimeImmutable::createFromFormat('Ymd', '20201011');
        $spp = (new ilStudyProgrammeProgress(123))->withValidityOfQualification($dl);
        $this->assertEquals($spp->getValidityOfQualification()->format('Ymd'), '20201011');
    }

    /**
     * @depends test_init_and_id
     */
    public function testIndividualPlan(ilStudyProgrammeProgress $spp)
    {
        $this->assertFalse($spp->hasIndividualModifications());

        $this->assertTrue($spp->withIndividualModifications(true)->hasIndividualModifications());
        $this->assertFalse($spp->withIndividualModifications(false)->hasIndividualModifications());
    }

    /**
     * @depends test_vq_date
     */
    public function test_invalidate()
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
    public function test_invalidate_non_expired_1()
    {
        $this->expectException(\ilException::class);
        $tomorrow = (new DateTimeImmutable())->add(new DateInterval('P1D'));
        $spp = (new ilStudyProgrammeProgress(123))->withValidityOfQualification($tomorrow)->invalidate();
    }

    /**
     * @depends test_vq_date
     */
    public function test_invalidate_non_expired_2()
    {
        $this->expectException(\ilException::class);
        $spp = (new ilStudyProgrammeProgress(123))->invalidate();
    }

    /**
     * @dataProvider status
     */
    public function testIsSuccessful($status, $success)
    {
        $spp = (new ilStudyProgrammeProgress(123))->withStatus($status);
        $this->assertEquals($success, $spp->isSuccessful());
    }

    public function testHasValidQualification()
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

    public function testMarkRelevant()
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
    
    public function testMarkNotRelevant()
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

    public function testMarkFailed()
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

    public function testMarkNotFailed()
    {
        list($usr, $now) = $this->getUserIdAndNow();
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

    public function testMarkAccredited()
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

    public function testUnmarkAccredited()
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

    public function testSucceed()
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
    public function testAllowedTransitionsForInProgress($status)
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
    public function testAllowedTransitionsForAccredited($status)
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
    public function testAllowedTransitionsForCompleted($status)
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
    public function testAllowedTransitionsForFailed($status)
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
    public function testAllowedTransitionsForIrrelevant($status)
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
