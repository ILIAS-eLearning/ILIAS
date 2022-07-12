<?php declare(strict_types=1);

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

class ilStudyProgrammeAutoCategoryTest extends TestCase
{
    protected int $prg_obj_id;
    protected int $cat_ref_id;
    protected int $usr_id;
    protected DateTimeImmutable $dat;

    protected function setUp() : void
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
