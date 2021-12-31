<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;

class ilSystemStyleSkinXMLTest extends TestCase
{
    protected ilSkinXML $skin;
    protected ilSkinStyleXML $style1;
    protected ilSkinStyleXML $style2;
    protected ilSystemStyleConfigMock $system_style_config;

    protected \ILIAS\DI\Container $save_dic;

    protected function setUp() : void
    {
        global $DIC;

        $this->save_dic = $DIC;

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

        $this->system_style_config = new ilSystemStyleConfigMock();

        mkdir($this->system_style_config->test_skin_temp_path);
        ilSystemStyleSkinContainer::xCopy($this->system_style_config->test_skin_original_path, $this->system_style_config->test_skin_temp_path);
    }

    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->save_dic;

        ilSystemStyleSkinContainer::recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testSkinNameAndId() : void
    {
        $this->assertEquals("skin1", $this->skin->getId());
        $this->assertEquals("skin 1", $this->skin->getName());
    }

    public function testAddStyle() : void
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

    public function testGetStyles() : void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);

        $this->assertNotEquals($this->skin->getStyle("style2"), $this->style1);
        $this->assertEquals($this->skin->getStyle("style2"), $this->style2);
    }

    public function testRemoveStyles() : void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertCount(2, $this->skin);
        $this->skin->removeStyle("style1");
        $this->assertCount(1, $this->skin);
        $this->skin->removeStyle("style2");
        $this->assertCount(0, $this->skin);
    }

    public function testRemoveTestTwice() : void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertCount(2, $this->skin);
        $this->skin->removeStyle("style1");
        $this->assertCount(1, $this->skin);
        $this->skin->removeStyle("style2");
        $this->assertCount(0, $this->skin);
        try {
            $this->skin->removeStyle("style2");
            $this->assertTrue(false);
        } catch (ilSystemStyleException $e) {
            $this->assertEquals(ilSystemStyleException::INVALID_ID, $e->getCode());
        }
    }

    public function testAsXML() : void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->assertEquals($this->skin->asXML(), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
    }

    public function testWriteXML() : void
    {
        $this->skin->addStyle($this->style1);
        $this->skin->addStyle($this->style2);
        $this->skin->writeToXMLFile($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml");
        $this->assertEquals(file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml"), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
        unlink($this->system_style_config->getCustomizingSkinPath() . "skin1/template-copy.xml");
    }

    public function testReadXML() : void
    {
        $skin = ilSkinXML::parseFromXML($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml");
        $this->assertEquals($skin->asXML(), file_get_contents($this->system_style_config->getCustomizingSkinPath() . "skin1/template.xml"));
    }
}
