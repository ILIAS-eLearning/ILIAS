<?php

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

class ilStudyProgrammeTypeTranslationTest extends \PHPUnit\Framework\TestCase
{
    protected $backupGlobals = false;

    public function test_init_and_id()
    {
        $tt = new ilStudyProgrammeTypeTranslation(123);
        $this->assertEquals($tt->getId(), 123);
        return $tt;
    }

    /**
     * @depends test_init_and_id
     */
    public function test_prg_type_id($tt)
    {
        $this->assertEquals(0, $tt->getPrgTypeId());
        $tt->setPrgTypeId(123);
        $this->assertEquals(123, $tt->getPrgTypeId());
    }


    /**
     * @depends test_init_and_id
     */
    public function test_lang($tt)
    {
        $this->assertEquals('', $tt->getLang());
        $tt->setLang('de');
        $this->assertEquals('de', $tt->getLang());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_member($tt)
    {
        $this->assertEquals('', $tt->getMember());
        $tt->setMember('a_member');
        $this->assertEquals('a_member', $tt->getMember());
    }

    /**
     * @depends test_init_and_id
     */
    public function test_value($tt)
    {
        $this->assertEquals('', $tt->getValue());
        $tt->setValue('a_value');
        $this->assertEquals('a_value', $tt->getValue());
    }
}
