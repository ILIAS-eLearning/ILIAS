<?php

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCParagraphTest extends TestCase
{
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp() : void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        if (!defined("COPAGE_TEST")) {
            define("COPAGE_TEST", "1");
        }
        parent::setUp();

        $def_mock = $this->getMockBuilder(ilObjectDefinition::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $def_mock
            ->method('getAllRepositoryTypes')
            ->willReturn(["crs", "grp", "cat"]);
        $this->setGlobalVariable(
            "objDefinition",
            $def_mock
        );

        libxml_use_internal_errors(true);
    }

    protected function tearDown() : void
    {
        libxml_use_internal_errors(false);
    }

    /**
     * Test _input2xml (empty)
     */
    public function test_input2xmlEmpty()
    {
        $res = ilPCParagraph::_input2xml("", "en", true, false);
        $this->assertEquals(
            "",
            $res
        );
    }

    /**
     * Test _input2xml for validity
     */
    public function test_input2xmlValidXml()
    {
        $cases = [
            '',
            'xx',
            'xx [str]xx[/str] xx',
            /*
            'xx [iln cat="106"] xx',
            'xx [/iln] xx',*/
        ];

        foreach ($cases as $case) {
            $text = ilPCParagraph::_input2xml($case, "en", true, false);
            $sxe = simplexml_load_string("<?xml version='1.0'?><dummy>" . $text . "</dummy>");
            $res = true;
            if ($sxe === false) {
                $res = $text;
            }
            $this->assertEquals(
                true,
                $res
            );
        }
    }

    /**
     * Test _input2xml
     */
    public function test_input2xmlResult()
    {
        $cases = [
            ''
                => '',
            'xx'
                => 'xx',
            'xx [str]xx[/str] xx'
                => 'xx <Strong>xx</Strong> xx',
            'xx [iln cat="106"] xx [/iln] xx'
                => 'xx <IntLink Target="il__obj_106" Type="RepositoryItem"> xx </IntLink> xx',
            'xx [iln page="106"] xx [/iln] xx'
                => 'xx <IntLink Target="il__pg_106" Type="PageObject"> xx </IntLink> xx',
            'xx [iln chap="106"] xx [/iln] xx'
                => 'xx <IntLink Target="il__st_106" Type="StructureObject"> xx </IntLink> xx',
            'xx [iln inst="123" page="106"] xx [/iln] xx'
            => 'xx <IntLink Target="il_123_pg_106" Type="PageObject"> xx </IntLink> xx',
            'xx [iln page="106" target="FAQ"] xx [/iln] xx'
            => 'xx <IntLink Target="il__pg_106" Type="PageObject" TargetFrame="FAQ"> xx </IntLink> xx',
            'xx [iln page="106" target="New" anchor="test"] xx [/iln] xx'
            => 'xx <IntLink Target="il__pg_106" Type="PageObject" TargetFrame="New" Anchor="test"> xx </IntLink> xx',
            'xx [iln term="106"] xx [/iln] xx'
            => 'xx <IntLink Target="il__git_106" Type="GlossaryItem" TargetFrame="Glossary"> xx </IntLink> xx',
            'xx [iln wpage="106"] xx [/iln] xx'
            => 'xx <IntLink Target="il__wpage_106" Type="WikiPage"> xx </IntLink> xx',
            'xx [iln ppage="106"] xx [/iln] xx'
            => 'xx <IntLink Target="il__ppage_106" Type="PortfolioPage"> xx </IntLink> xx',
            'xx [iln media="545"/] xx '
            => 'xx <IntLink Target="il__mob_545" Type="MediaObject"/> xx',
            'xx [iln media="108" target="New"] xx [/iln] xx'
            => 'xx <IntLink Target="il__mob_108" Type="MediaObject" TargetFrame="New"> xx </IntLink> xx',
            'xx [iln media="108" target="Media"] xx [/iln] xx'
            => 'xx <IntLink Target="il__mob_108" Type="MediaObject" TargetFrame="Media"> xx </IntLink> xx',
            'xx [iln dfile="546"] xx [/iln] xx'
            => 'xx <IntLink Target="il__dfile_546" Type="File"> xx </IntLink> xx',
            'xx' . chr(13) . chr(10) . 'yy'
            => 'xx<br />yy',
            'xx' . chr(13) . 'yy'
            => 'xx<br />yy',
            'xx' . chr(10) . 'yy'
            => 'xx<br />yy',
            '<ul class="ilc_list_u_BulletedList"><li class="ilc_list_item_StandardListItem">aa</li><li class="ilc_list_item_StandardListItem">bb</li><li class="ilc_list_item_StandardListItem">cc</li></ul>'
            => '&lt;ul class="ilc_list_u_BulletedList"&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;aa&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;bb&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;cc&lt;/li&gt;&lt;/ul&gt;'



        /*'xx [iln cat="106"] xx'
            => 'xx [iln cat="106"] xx',
        'xx [/iln] xx'
            => 'xx [/iln] xx'*/
        ];

        foreach ($cases as $in => $expected) {
            $out = ilPCParagraph::_input2xml($in, "en", true, false);
            $this->assertEquals(
                $expected,
                $out
            );
        }
    }

    /**
     * Test handleAjaxContentPost
     */
    public function testHandleAjaxContentPost()
    {
        $cases = [
            '&lt;ul class="ilc_list_u_BulletedList"&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;aa&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;bb&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;cc&lt;/li&gt;&lt;/ul&gt;'
            => '<SimpleBulletList><SimpleListItem>aa</SimpleListItem><SimpleListItem>bb</SimpleListItem><SimpleListItem>cc</SimpleListItem></SimpleBulletList>'
        ];

        foreach ($cases as $in => $expected) {
            $out = ilPCParagraph::handleAjaxContentPost($in);
            $this->assertEquals(
                $expected,
                $out
            );
        }
    }
}
