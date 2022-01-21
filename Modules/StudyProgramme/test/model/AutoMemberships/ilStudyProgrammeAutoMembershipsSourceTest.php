<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAutoMembershipsSourceTest extends TestCase
{
    protected int $prg_obj_id;
    protected string $source_type;
    protected int $source_id;
    protected bool $enbl;
    protected int $usr_id;
    protected DateTimeImmutable $dat;

    public function setUp() : void
    {
        $this->prg_obj_id = 123;
        $this->source_type = ilStudyProgrammeAutoMembershipSource::TYPE_ROLE;
        $this->source_id = 666;
        $this->enbl = true;
        $this->usr_id = 6;
        $this->dat = new DateTimeImmutable('2019-06-05 15:25:12');
    }

    public function testConstruction() : ilStudyProgrammeAutoMembershipSource
    {
        $ams = new ilStudyProgrammeAutoMembershipSource(
            $this->prg_obj_id,
            $this->source_type,
            $this->source_id,
            $this->enbl,
            $this->usr_id,
            $this->dat
        );
        $this->assertInstanceOf(
            ilStudyProgrammeAutoMembershipSource::class,
            $ams
        );
        return $ams;
    }

    /**
     * @depends testConstruction
     */
    public function testGetPrgObjId(ilStudyProgrammeAutoMembershipSource $ams) : void
    {
        $this->assertEquals(
            $this->prg_obj_id,
            $ams->getPrgObjId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetSourceType(ilStudyProgrammeAutoMembershipSource $ams) : void
    {
        $this->assertEquals(
            $this->source_type,
            $ams->getSourceType()
        );
    }
    /**
     * @depends testConstruction
     */
    public function testGetSourceId(ilStudyProgrammeAutoMembershipSource $ams) : void
    {
        $this->assertEquals(
            $this->source_id,
            $ams->getSourceId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetLastEditorId(ilStudyProgrammeAutoMembershipSource $ams) : void
    {
        $this->assertEquals(
            $this->usr_id,
            $ams->getLastEditorId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetLastEdited(ilStudyProgrammeAutoMembershipSource $ams) : void
    {
        $this->assertEquals(
            $this->dat,
            $ams->getLastEdited()
        );
    }
}
