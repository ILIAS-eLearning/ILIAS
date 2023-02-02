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

class ilStudyProgrammeProgressIdTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function testPRGProgressId(): void
    {
        $ass_id = 12;
        $usr_id = 34;
        $node_id = 56;
        $id = new PRGProgressId($ass_id, $usr_id, $node_id);

        $this->assertEquals($id->getAssignmentId(), $ass_id);
        $this->assertEquals($id->getUsrId(), $usr_id);
        $this->assertEquals($id->getNodeId(), $node_id);
        $this->assertEquals((string) $id, '12_34_56');
    }

    public function testPRGProgressIdFromString(): void
    {
        $src = '12_34_56';
        $id = PRGProgressId::createFromString($src);
        $this->assertInstanceOf(PRGProgressId::class, $id);
        $this->assertEquals((string) $id, $src);
    }
}
