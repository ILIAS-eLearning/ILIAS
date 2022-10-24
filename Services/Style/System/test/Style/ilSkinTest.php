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

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

class ilSkinTest extends ilSystemStyleBaseFSTest
{
    protected ilSkin $skin;
    protected ilSkinStyle $style1;
    protected ilSkinStyle $style2;
    protected ilSkinStyle $substyle1;
    protected ilSkinStyle $substyle2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skin = new ilSkin('skin1', 'skin 1');

        $this->style1 = new ilSkinStyle('style1', 'Style 1');
        $this->style1->setCssFile('style1css');
        $this->style1->setImageDirectory('style1image');
        $this->style1->setSoundDirectory('style1sound');
        $this->style1->setFontDirectory('style1font');

        $this->style2 = new ilSkinStyle('style2', 'Style 2');
        $this->style2->setCssFile('style2css');
        $this->style2->setImageDirectory('style2image');
        $this->style2->setSoundDirectory('style2sound');
        $this->style2->setFontDirectory('style2font');

        $this->substyle1 = new ilSkinStyle('substyle1', 'Substyle 1');
        $this->substyle1->setSubstyleOf($this->style1->getId());

        $this->substyle2 = new ilSkinStyle('substyle2', 'Substyle 2');
        $this->substyle2->setSubstyleOf($this->style2->getId());
    }

    public function testSkinNameAndId(): void
    {
        $this->assertEquals('skin1', $this->skin->getId());
        $this->assertEquals('skin 1', $this->skin->getName());
    }

    public function testAddStyle(): void
    {
        $this->assertCount(0, $this->skin);
        $this->assertCount(0, $this->skin->getStyles());
        $this->skin->addStyle($this->style1);
        $this->assertCount(1, $this->skin);
        $this->assertCount(1, $this->skin->getStyles());
        $this->skin->addStyle($this->style1);
        $this->assertCount(2, $this->skin);
        $this->assertCount(2, $this->skin->getStyles());
        $this->skin->addStyle($this->style2);
        $this->assertCount(3, $this->skin);
        $this->assertCount(3, $this->skin->getStyles());
    }

    public function testGetStyles(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);

        $this->assertNotEquals($this->style1, $this->skin->getStyle('style2'));
        $this->assertEquals($this->style2, $this->skin->getStyle('style2'));
    }

    public function testAddSubstyle(): void
    {
        $this->skin->addStyle($this->substyle1);
        $this->assertCount(1, $this->skin->getStyles());
        $this->skin->addStyle($this->substyle2);
        $this->assertCount(2, $this->skin);
    }

    public function testGetSubStyles(): void
    {
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);

        $this->assertNotEquals($this->substyle1, $this->skin->getStyle('substyle2'));
        $this->assertEquals($this->substyle2, $this->skin->getStyle('substyle2'));
    }

    public function testGetAllSubStyles(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals([], $this->skin->getSubstylesOfStyle('style1'));
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);
        $this->assertEquals(
            [$this->substyle1->getId() => $this->substyle1],
            $this->skin->getSubstylesOfStyle('style1')
        );
        $this->assertEquals(
            [$this->substyle2->getId() => $this->substyle2],
            $this->skin->getSubstylesOfStyle('style2')
        );
        $this->substyle2->setSubstyleOf($this->style1->getId());
        $this->assertEquals([$this->substyle1->getId() => $this->substyle1,
                             $this->substyle2->getId() => $this->substyle2
        ], $this->skin->getSubstylesOfStyle('style1'));
    }

    public function testUpdateParentOfStyle(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);
        $this->assertEquals(
            $this->skin->getSubstylesOfStyle('style1'),
            [$this->substyle1->getId() => $this->substyle1]
        );
        $this->assertEquals(
            $this->skin->getSubstylesOfStyle('style2'),
            [$this->substyle2->getId() => $this->substyle2]
        );
        $this->skin->updateParentStyleOfSubstyles($this->style2->getId(), $this->style1->getId());
        $this->assertEquals(
            $this->skin->getSubstylesOfStyle('style1'),
            [$this->substyle1->getId() => $this->substyle1, $this->substyle2->getId() => $this->substyle2]
        );
    }

    public function testRemoveStyles(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertCount(2, $this->skin);
        $this->skin->removeStyle('style1');
        $this->assertCount(1, $this->skin);
        $this->skin->removeStyle('style2');
        $this->assertCount(0, $this->skin);
    }

    public function testRemoveTestTwice(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertCount(2, $this->skin);
        $this->skin->removeStyle('style1');
        $this->assertCount(1, $this->skin);
        $this->skin->removeStyle('style2');
        $this->assertCount(0, $this->skin);
        try {
            $this->skin->removeStyle('style2');
            $this->fail();
        } catch (ilSystemStyleException $e) {
            $this->assertEquals(ilSystemStyleException::INVALID_ID, $e->getCode());
        }
    }

    public function testAsXML(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals(
            file_get_contents($this->system_style_config->getCustomizingSkinPath() . 'skin1/template.xml'),
            $this->skin->asXML()
        );
    }

    public function testWriteXML(): void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->skin->writeToXMLFile($this->system_style_config->getCustomizingSkinPath() . 'skin1/template-copy.xml');
        $this->assertEquals(
            file_get_contents($this->system_style_config->getCustomizingSkinPath() . 'skin1/template-copy.xml'),
            file_get_contents($this->system_style_config->getCustomizingSkinPath() . 'skin1/template.xml')
        );
        unlink($this->system_style_config->getCustomizingSkinPath() . 'skin1/template-copy.xml');
    }
}
