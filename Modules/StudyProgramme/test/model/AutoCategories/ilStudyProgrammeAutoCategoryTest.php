<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAutoCategoryTest extends TestCase
{
    protected int $prg_obj_id;
    protected int $cat_ref_id;
    protected int $usr_id;
    protected DateTimeImmutable $dat;

    public function setUp() : void
    {
        $this->prg_obj_id = 123;
        $this->cat_ref_id = 666;
        $this->usr_id = 6;
        $this->dat = new DateTimeImmutable('2019-06-05 15:25:12');
    }

    public function testConstruction() : ilStudyProgrammeAutoCategory
    {
        $ac = new ilStudyProgrammeAutoCategory(
            $this->prg_obj_id,
            $this->cat_ref_id,
            $this->usr_id,
            $this->dat
        );
        $this->assertInstanceOf(
            ilStudyProgrammeAutoCategory::class,
            $ac
        );
        return $ac;
    }

    /**
     * @depends testConstruction
     */
    public function testGetPrgObjId(ilStudyProgrammeAutoCategory $ac) : void
    {
        $this->assertEquals(
            $this->prg_obj_id,
            $ac->getPrgObjId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetCategoryRefId(ilStudyProgrammeAutoCategory $ac) : void
    {
        $this->assertEquals(
            $this->cat_ref_id,
            $ac->getCategoryRefId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetLastEditorId(ilStudyProgrammeAutoCategory $ac) : void
    {
        $this->assertEquals(
            $this->usr_id,
            $ac->getLastEditorId()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetLastEdited(ilStudyProgrammeAutoCategory $ac) : void
    {
        $this->assertEquals(
            $this->dat,
            $ac->getLastEdited()
        );
    }
}
