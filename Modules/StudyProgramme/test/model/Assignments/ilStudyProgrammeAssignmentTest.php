<?php declare(strict_types=1);

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");

class ilStudyProgrammeAssignmentTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function testInitAndId() : ilStudyProgrammeAssignment
    {
        $spa = new ilStudyProgrammeAssignment(123);
        $this->assertEquals($spa->getId(), 123);
        return $spa;
    }

    /**
     * @depends testInitAndId
     */
    public function testRootId() : void
    {
        $spa = (new ilStudyProgrammeAssignment(123))->withRootId(321);
        $this->assertEquals($spa->getRootId(), 321);
    }

    /**
     * @depends testInitAndId
     */
    public function testUserId() : void
    {
        $spa = (new ilStudyProgrammeAssignment(123))->withUserId(321);
        $this->assertEquals($spa->getUserId(), 321);
    }

    /**
     * @depends testInitAndId
     */
    public function testWithLastChange() : void
    {
        $spa = (new ilStudyProgrammeAssignment(123))->withLastChange(
            6,
            $now = new DateTimeImmutable()
        );
        $this->assertEquals($spa->getLastChangeBy(), 6);
        $this->assertEquals($spa->getLastChange()->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }


    /**
     * @depends testInitAndId
     */
    public function testRestartDate() : void
    {
        $dl = DateTimeImmutable::createFromFormat('Ymd', '20201001');
        $spa = (new ilStudyProgrammeAssignment(123))->withRestarted(321, $dl);
        $this->assertEquals($spa->getRestartDate()->format('Ymd'), '20201001');
        $this->assertEquals($spa->getRestartedAssignmentId(), 321);
    }
}
