<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

include_once("./Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");
include_once("./Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("./Services/Style/System/test/fixtures/mocks/ilSystemStyleConfigMock.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");

use PHPUnit\Framework\TestCase;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinXMLTest extends TestCase
{


    /**
     * @var ilSkinXML
     */
    protected $skin;

    /**
     * @var ilSkinStyleXML
     */
    protected $style1 = null;

    /**
     * @var ilSkinStyleXML
     */
    protected $style2 = null;

    /**
     * @var ilSkinStyleXML
     */
    protected $substyle1 = null;

    /**
     * @var ilSkinStyleXML
     */
    protected $substyle2 = null;

    /**
     * @var ilSystemStyleConfigMock
     */
    protected $system_style_config;

    protected function setUp() : void
    {
        $this->skin = new ilSkinXML("skin1", "skin 1");

        $this->style1 = new ilSkinStyleXML("style1", "Style 1");
        $this->style1->setCssFile("style1css");
        $this->style1->setImageDirectory("style1image");
        $this->style1->setSoundDirectory("style1sound");
        $this->style1->setFontDirectory("style1font");

        $this->style2 = new ilSkinStyleXML("style2", "Style 2");
        $this->style2->setCssFile("style2css");
        $this->style2->setImageDirectory("style2image");
        $this->style2->setSoundDirectory("style2sound");
        $this->style2->setFontDirectory("style2font");

        $this->substyle1 = new ilSkinStyleXML("substyle1", "Substyle 1");
        $this->substyle1->setSubstyleOf($this->style1->getId());

        $this->substyle2 = new ilSkinStyleXML("substyle2", "Substyle 2");
        $this->substyle2->setSubstyleOf($this->style2->getId());

        $this->system_style_config = new ilSystemStyleConfigMock();

        mkdir($this->system_style_config->test_skin_temp_path);
        ilSystemStyleSkinContainer::xCopy($this->system_style_config->test_skin_original_path, $this->system_style_config->test_skin_temp_path);
    }

    protected function tearDown() : void
    {
        ilSystemStyleSkinContainer::recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testSkinNameAndId()
    {
        $this->assertEquals("skin1", $this->skin->getId());
        $this->assertEquals("skin 1", $this->skin->getName());
    }

    public function testAddStyle()
    {
        $this->assertEquals(count($this->skin), 0);
        $this->assertEquals(count($this->skin->getStyles()), 0);
        $this->skin->addStyle($this->style1);
        $this->assertEquals(count($this->skin), 1);
        $this->assertEquals(count($this->skin->getStyles()), 1);
        $this->skin->addStyle($this->style1);
        $this->assertEquals(count($this->skin), 2);
        $this->assertEquals(count($this->skin->getStyles()), 2);
        $this->skin->addStyle($this->style2);
        $this->assertEquals(count($this->skin), 3);
        $this->assertEquals(count($this->skin->getStyles()), 3);
    }

    public function testGetStyles()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);

        $this->assertNotEquals($this->skin->getStyle("style2"), $this->style1);
        $this->assertEquals($this->skin->getStyle("style2"), $this->style2);
    }

    public function testAddSubstyle()
    {
        $this->skin->addStyle($this->substyle1);
        $this->assertEquals(count($this->skin->getStyles()), 1);
        $this->skin->addStyle($this->substyle2);
        $this->assertEquals(count($this->skin), 2);
    }

    public function testGetSubStyles()
    {
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);

        $this->assertNotEquals($this->skin->getStyle("substyle2"), $this->substyle1);
        $this->assertEquals($this->skin->getStyle("substyle2"), $this->substyle2);
    }

    public function testGetAllSubStyles()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals($this->skin->getSubstylesOfStyle("style1"), []);
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);
        $this->assertEquals($this->skin->getSubstylesOfStyle("style1"), [$this->substyle1->getId() => $this->substyle1]);
        $this->assertEquals($this->skin->getSubstylesOfStyle("style2"), [$this->substyle2->getId() => $this->substyle2]);
        $this->substyle2->setSubstyleOf($this->style1->getId());
        $this->assertEquals($this->skin->getSubstylesOfStyle("style1"), [$this->substyle1->getId() => $this->substyle1,$this->substyle2->getId() => $this->substyle2]);
    }

    public function testUpdateParentOfStyle()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->skin->addStyle($this->substyle1);
        $this->skin->addStyle($this->substyle2);
        $this->assertEquals($this->skin->getSubstylesOfStyle("style1"), [$this->substyle1->getId() => $this->substyle1]);
        $this->assertEquals($this->skin->getSubstylesOfStyle("style2"), [$this->substyle2->getId() => $this->substyle2]);
        $this->skin->updateParentStyleOfSubstyles($this->style2->getId(), $this->style1->getId());
        $this->assertEquals($this->skin->getSubstylesOfStyle("style1"), [$this->substyle1->getId() => $this->substyle1,$this->substyle2->getId() => $this->substyle2]);
    }

    public function testRemoveStyles()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals(count($this->skin), 2);
        $this->skin->removeStyle("style1");
        $this->assertEquals(count($this->skin), 1);
        $this->skin->removeStyle("style2");
        $this->assertEquals(count($this->skin), 0);
    }

    public function testRemoveTestTwice()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals(count($this->skin), 2);
        $this->skin->removeStyle("style1");
        $this->assertEquals(count($this->skin), 1);
        $this->skin->removeStyle("style2");
        $this->assertEquals(count($this->skin), 0);
        try {
            $this->skin->removeStyle("style2");
            $this->assertTrue(false);
        } catch (ilSystemStyleException $e) {
            $this->assertEquals($e->getCode(), ilSystemStyleException::INVALID_ID);
        }
    }

    public function testAsXML()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals($this->skin->asXML(), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
    }

    public function testWriteXML()
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->skin->writeToXMLFile($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml");
        $this->assertEquals(file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml"), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
        unlink($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml");
    }

    public function testReadXML()
    {
        $skin = ilSkinXML::parseFromXML($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml");
        $this->assertEquals($skin->asXML(), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
    }
}
