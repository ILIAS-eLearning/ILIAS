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

use ILIAS\StudyProgramme\Assignment\Node;

class ilStudyProgrammeProgressTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function testPRGProgressInitAndId(): void
    {
        $pgs = new ilPRGProgress(123);
        $this->assertInstanceOf(Node::class, $pgs);
        $this->assertEquals($pgs->getNodeId(), 123);
    }

    public function testPRGProgressProperties(): void
    {
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);
        $dat = DateTimeImmutable::createFromFormat('Ymd', '20221024');

        $this->assertEquals($pgs->withAmountOfPoints(12)->getAmountOfPoints(), 12);
        $this->assertEquals($pgs->withCurrentAmountOfPoints(8)->getCurrentAmountOfPoints(), 8);

        $this->assertEquals($pgs->withStatus(ilPRGProgress::STATUS_COMPLETED)->getStatus(), ilPRGProgress::STATUS_COMPLETED);
        $pgs = $pgs->withLastChange(6, $dat);
        $this->assertEquals($pgs->getLastChangeBy(), 6);
        $this->assertEquals($pgs->getLastChange(), $dat);
        $this->assertEquals($pgs->withAssignmentDate($dat)->getAssignmentDate(), $dat);
        $pgs = $pgs->withCompletion(6, $dat);
        $this->assertEquals($pgs->getCompletionBy(), 6);
        $this->assertEquals($pgs->getCompletionDate(), $dat);
        $this->assertEquals($pgs->withDeadline($dat)->getDeadline(), $dat);
        $this->assertEquals($pgs->withValidityOfQualification($dat)->getValidityOfQualification(), $dat);
        $this->assertTrue($pgs->withIndividualModifications(true)->hasIndividualModifications());
    }

    public function testPRGProgressStatusChecker(): void
    {
        $pgs = (new ilPRGProgress(444))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);
        $this->assertTrue($pgs->isRelevant());
        $this->assertTrue($pgs->withStatus(ilPRGProgress::STATUS_ACCREDITED)->isSuccessful());
        $this->assertFalse($pgs->withStatus(ilPRGProgress::STATUS_FAILED)->isSuccessful());
        $this->assertFalse($pgs->withStatus(ilPRGProgress::STATUS_NOT_RELEVANT)->isRelevant());
        $this->assertFalse($pgs->isAccredited());
        $this->assertTrue($pgs->withStatus(ilPRGProgress::STATUS_ACCREDITED)->isAccredited());
    }

    public function testPRGProgressHasValidQualification(): void
    {
        $today = new DateTimeImmutable();
        $yesterday = $today->sub(new DateInterval('P1D'));
        $tomorrow = $today->add(new DateInterval('P1D'));

        $pgs = (new ilPRGProgress(555))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
            ->withValidityOfQualification($today);

        $this->assertNull($pgs->hasValidQualification($today));

        $pgs = $pgs->withStatus(ilPRGProgress::STATUS_COMPLETED);
        $this->assertTrue($pgs->hasValidQualification($yesterday));
        $this->assertTrue($pgs->hasValidQualification($today));
        $this->assertFalse($pgs->hasValidQualification($tomorrow));
    }

    public function testPRGProgressInvalidation(): void
    {
        $today = new DateTimeImmutable();
        $yesterday = $today->sub(new DateInterval('P1D'));

        $pgs = (new ilPRGProgress(666))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
            ->withStatus(ilPRGProgress::STATUS_COMPLETED)
            ->withValidityOfQualification($today);
        $this->assertFalse($pgs->isInvalidated());

        $pgs = $pgs
            ->withValidityOfQualification($yesterday)
            ->invalidate();
        $this->assertTrue($pgs->isInvalidated());
    }

    public function testPRGProgressInvalidInvalidation(): void
    {
        $this->expectException(\ilException::class);
        $today = new DateTimeImmutable();
        $tomorrow = $today->add(new DateInterval('P1D'));
        $pgs = (new ilPRGProgress(777))
            ->withStatus(ilPRGProgress::STATUS_COMPLETED)
            ->withValidityOfQualification($tomorrow)
            ->invalidate();
    }

    public function testPRGProgressStatusActionsMarkAccredited(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->markAccredited($now, $usr);
        $this->assertEquals($now, $pgs->getCompletionDate());
        $this->assertEquals($usr, $pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_ACCREDITED, $pgs->getStatus());
    }

    public function testPRGProgressStatusActionsUnmarkAccredited(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->unmarkAccredited($now, $usr);
        $this->assertNull($pgs->getCompletionDate());
        $this->assertNull($pgs->getCompletionBy());
        $this->assertNull($pgs->getValidityOfQualification());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_IN_PROGRESS, $pgs->getStatus());
    }

    public function testPRGProgressStatusActionsMarkFailed(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->markFailed($now, $usr);
        $this->assertNull($pgs->getCompletionDate());
        $this->assertNull($pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_FAILED, $pgs->getStatus());
    }

    public function testPRGProgressStatusActionsMarkNotFailed(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->markNotFailed($now, $usr);
        $this->assertNull($pgs->getCompletionDate());
        $this->assertNull($pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_IN_PROGRESS, $pgs->getStatus());
    }

    public function testPRGProgressStatusActionsSucceed(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->succeed($now, $usr);
        $this->assertEquals($now, $pgs->getCompletionDate());
        $this->assertEquals($usr, $pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_COMPLETED, $pgs->getStatus());
    }

    public function testPRGProgressStatusActionsMarkRelevant(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->markRelevant($now, $usr);
        $this->assertNull($pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_IN_PROGRESS, $pgs->getStatus());
        $this->assertTrue($pgs->hasIndividualModifications());
    }

    public function testPRGProgressStatusActionsMarkNotRelevant(): void
    {
        $usr = 6;
        $now = new DateTimeImmutable();
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);

        $pgs = $pgs->markNotRelevant($now, $usr);
        $this->assertNull($pgs->getCompletionBy());
        $this->assertEquals($usr, $pgs->getLastChangeBy());
        $this->assertEquals(
            $now->format(ilPRGProgress::DATE_TIME_FORMAT),
            $pgs->getLastChange()->format(ilPRGProgress::DATE_TIME_FORMAT)
        );
        $this->assertEquals(ilPRGProgress::STATUS_NOT_RELEVANT, $pgs->getStatus());
        $this->assertTrue($pgs->hasIndividualModifications());
    }

    public function status(): array
    {
        return [
            //status, count as 'successful'
            [ilPRGProgress::STATUS_IN_PROGRESS],
            [ilPRGProgress::STATUS_COMPLETED],
            [ilPRGProgress::STATUS_ACCREDITED],
            [ilPRGProgress::STATUS_NOT_RELEVANT],
            [ilPRGProgress::STATUS_FAILED]
        ];
    }

    public function testPRGProgressInvalidStatus(): void
    {
        $this->expectException(\ilException::class);
        $pgs = (new ilPRGProgress(123))->withStatus(777);
    }

    /**
     * @dataProvider status
     */
    public function testPRGProgressAllowedTransitionsForInProgress(int $status): void
    {
        $pgs = (new ilPRGProgress(123))->withStatus(ilPRGProgress::STATUS_IN_PROGRESS);
        if (in_array($status, [
            ilPRGProgress::STATUS_NOT_RELEVANT,
            ilPRGProgress::STATUS_ACCREDITED,
            ilPRGProgress::STATUS_FAILED,
            ilPRGProgress::STATUS_COMPLETED,
            $pgs->getStatus()
        ])) {
            $this->assertTrue($pgs->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($pgs->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testPRGProgressAllowedTransitionsForAccredited($status)
    {
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
            ->withStatus(ilPRGProgress::STATUS_ACCREDITED);
        if (in_array($status, [
            ilPRGProgress::STATUS_IN_PROGRESS,
            ilPRGProgress::STATUS_COMPLETED,
            ilPRGProgress::STATUS_FAILED,
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $pgs->getStatus()
        ])) {
            $this->assertTrue($pgs->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($pgs->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testPRGProgressAllowedTransitionsForCompleted($status)
    {
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
            ->withStatus(ilPRGProgress::STATUS_COMPLETED);
        if (in_array($status, [
            ilPRGProgress::STATUS_IN_PROGRESS,
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $pgs->getStatus()
        ])) {
            $this->assertTrue($pgs->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($pgs->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testPRGProgressAllowedTransitionsForFailed($status)
    {
        $pgs = (new ilPRGProgress(123))
            ->withStatus(ilPRGProgress::STATUS_IN_PROGRESS)
            ->withStatus(ilPRGProgress::STATUS_FAILED);
        if (in_array($status, [
            ilPRGProgress::STATUS_IN_PROGRESS,
            ilPRGProgress::STATUS_COMPLETED,
            ilPRGProgress::STATUS_NOT_RELEVANT,
            $pgs->getStatus()
        ])) {
            $this->assertTrue($pgs->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($pgs->isTransitionAllowedTo($status));
        }
    }

    /**
     * @dataProvider status
     */
    public function testPRGProgressAllowedTransitionsForIrrelevant($status): void
    {
        $pgs = (new ilPRGProgress(123))->withStatus(ilPRGProgress::STATUS_NOT_RELEVANT);
        if ($status === ilPRGProgress::STATUS_IN_PROGRESS
            || $status === $pgs->getStatus()
        ) {
            $this->assertTrue($pgs->isTransitionAllowedTo($status));
        } else {
            $this->assertFalse($pgs->isTransitionAllowedTo($status));
        }
    }

    public function testPRGProgressPointsOfChildren(): void
    {
        /*
        └── 1
            ├── 11
            │   ├── 111
            │   └── 112
            ├── 12
            └── 13
        */

        $pgs112 = (new ilPRGProgress(112, ilPRGProgress::STATUS_COMPLETED))
            ->withAmountOfPoints(112);
        $pgs111 = (new ilPRGProgress(111, ilPRGProgress::STATUS_COMPLETED))
            ->withAmountOfPoints(111);
        $pgs11 = (new ilPRGProgress(11, ilPRGProgress::STATUS_IN_PROGRESS))
            ->setSubnodes([$pgs111, $pgs112])
            ->withAmountOfPoints(11);
        $pgs12 = (new ilPRGProgress(12, ilPRGProgress::STATUS_IN_PROGRESS))
            ->withAmountOfPoints(12);
        $pgs13 = (new ilPRGProgress(13, ilPRGProgress::STATUS_NOT_RELEVANT))
            ->withAmountOfPoints(13);
        $pgs1 = (new ilPRGProgress(1, ilPRGProgress::STATUS_IN_PROGRESS))
            ->setSubnodes([$pgs11, $pgs12, $pgs13])
            ->withAmountOfPoints(1);

        $this->assertEquals(111 + 112, $pgs11->getPossiblePointsOfRelevantChildren());
        $this->assertEquals(0, $pgs12->getPossiblePointsOfRelevantChildren());
        $this->assertEquals(11 + 12 + 13 * 0, $pgs1->getPossiblePointsOfRelevantChildren());
        $this->assertEquals(111 + 112, $pgs11->getAchievedPointsOfChildren());
        $this->assertEquals(0, $pgs1->getAchievedPointsOfChildren());
    }
}
