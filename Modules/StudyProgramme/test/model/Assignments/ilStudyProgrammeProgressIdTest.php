<?php

declare(strict_types=1);

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
