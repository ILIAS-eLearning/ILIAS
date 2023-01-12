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

class ilStudyProgrammeAssignmentTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function testPRGAssignmentInitAndId(): void
    {
        $ass = new ilPRGAssignment(123, 6);
        $this->assertEquals($ass->getId(), 123);
        $this->assertEquals($ass->getUserId(), 6);
    }

    public function testPRGAssignmentProperties(): void
    {
        $ass = new ilPRGAssignment(123, 456);
        $now = new DateTimeImmutable();

        $ass = $ass->withLastChange(6, $now);
        $this->assertEquals($ass->getLastChangeBy(), 6);
        $this->assertEquals($ass->getLastChange()->format(ilPRGAssignment::DATE_TIME_FORMAT), $now->format(ilPRGAssignment::DATE_TIME_FORMAT));

        $this->assertFalse($ass->withManuallyAssigned(false)->isManuallyAssigned());
        $this->assertTrue($ass->withManuallyAssigned(true)->isManuallyAssigned());

        $dl = DateTimeImmutable::createFromFormat('Ymd', '20201001');
        $ass = $ass->withRestarted(321, $dl);
        $this->assertEquals($ass->getRestartDate()->format('Ymd'), '20201001');
        $this->assertEquals($ass->getRestartedAssignmentId(), 321);
    }
}
