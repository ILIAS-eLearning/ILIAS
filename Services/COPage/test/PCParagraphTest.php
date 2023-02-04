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

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCParagraphTest extends COPageTestBase
{
    //
    // test legacy static methods
    //


    /**
     * Test _input2xml (empty)
     */
    public function test_input2xmlEmpty(): void
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
    public function test_input2xmlValidXml(): void
    {
        $cases = [
            '',
            'xx',
            'xx [str]xx[/str] xx',
            'xx [iln cat="106"] xx',
            'xx [/iln] xx',
        ];

        foreach ($cases as $case) {
            $text = ilPCParagraph::_input2xml($case, "en", true, false);
            $use_internal_errors = libxml_use_internal_errors(true);
            $sxe = simplexml_load_string("<?xml version='1.0'?><dummy>" . $text . "</dummy>");
            libxml_use_internal_errors($use_internal_errors);
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
    public function test_input2xmlResult(): void
    {
        $cases = [
            ''
                => '',
            'xx'
                => 'xx',

            // text mark-up
            'xx [str]xx[/str] xx'
                => 'xx <Strong>xx</Strong> xx',
            'xx [com]xx[/com] xx'
                => 'xx <Comment>xx</Comment> xx',
            'xx [emp]xx[/emp] xx'
                => 'xx <Emph>xx</Emph> xx',
            'xx [fn]xx[/fn] xx'
                => 'xx <Footnote>xx</Footnote> xx',
            'xx [code]xx[/code] xx'
                => 'xx <Code>xx</Code> xx',
            'xx [acc]xx[/acc] xx'
                => 'xx <Accent>xx</Accent> xx',
            'xx [imp]xx[/imp] xx'
                => 'xx <Important>xx</Important> xx',
            'xx [kw]xx[/kw] xx'
                => 'xx <Keyw>xx</Keyw> xx',
            'xx [sub]xx[/sub] xx'
                => 'xx <Sub>xx</Sub> xx',
            'xx [sup]xx[/sup] xx'
                => 'xx <Sup>xx</Sup> xx',
            'xx [quot]xx[/quot] xx'
                => 'xx <Quotation>xx</Quotation> xx',

            // internal links
            'xx [iln cat="106"] xx [/iln] xx'
                => 'xx <IntLink Target="il__obj_106" Type="RepositoryItem"> xx </IntLink> xx',
            'xx [iln page="106"] xx [/iln] xx'
                => 'xx <IntLink Target="il__pg_106" Type="PageObject"> xx </IntLink> xx',
            'xx [iln page="106"] xx  xx'
            => 'xx  xx  xx',
            'xx xx [/iln] xx'
            => 'xx xx [/iln] xx',
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
            'xx [iln term="106" target="New"] xx [/iln] xx'
            => 'xx <IntLink Target="il__git_106" Type="GlossaryItem" TargetFrame="New"> xx </IntLink> xx',
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

            // returns
            'xx' . chr(13) . chr(10) . 'yy'
            => 'xx<br />yy',
            'xx' . chr(13) . 'yy'
            => 'xx<br />yy',
            'xx' . chr(10) . 'yy'
            => 'xx<br />yy',

            // lists
            '<ul class="ilc_list_u_BulletedList"><li class="ilc_list_item_StandardListItem">aa</li><li class="ilc_list_item_StandardListItem">bb</li><li class="ilc_list_item_StandardListItem">cc</li></ul>'
            => '&lt;ul class="ilc_list_u_BulletedList"&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;aa&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;bb&lt;/li&gt;&lt;li class="ilc_list_item_StandardListItem"&gt;cc&lt;/li&gt;&lt;/ul&gt;',

            // external links
            'xx [xln url="http://"][/xln] xxxx'
            => 'xx  xxxx',
            'xx [xln url="http://ilias.de"]www[/xln] xxxx'
            => 'xx <ExtLink Href="http://ilias.de">www</ExtLink> xxxx',
            'xx [xln url="http://ilias.php?x=1&y=2"]www[/xln] xxxx'
            => 'xx <ExtLink Href="http://ilias.php?x=1&y=2">www</ExtLink> xxxx',
            'xx [xln url="http://ilias.de/my+document.pdf"]doc[/xln] xxxx'
            => 'xx <ExtLink Href="http://ilias.de/my+document.pdf">doc</ExtLink> xxxx',

            // anchor
            'xx [anc name="test"]test[/anc] xxxx'
            => 'xx <Anchor Name="test">test</Anchor> xxxx',

            // marked
            'xx [marked class="test"]test[/marked] xxxx'
            => 'xx <Marked Class="test">test</Marked> xxxx',


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
    public function testHandleAjaxContentPost(): void
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

    /**
     * Test HTML to BB transformation, spans
     */
    public function testHandleAjaxContentSpans(): void
    {
        $cases = [
            // Standard, Strong
            '<div id="1:1234" class="ilc_text_block_Standard">xxx<span class="ilc_text_inline_Strong">xxx</span>xxx</div>'
            => [
                "text" => 'xxx[str]xxx[/str]xxx',
                "id" => '1:1234',
                "class" => 'Standard'
            ],
            // Mnemonic, Emphatic
            '<div id="1:1235" class="ilc_text_block_Mnemonic">xxx<span class="ilc_text_inline_Emph">xxx</span>xxx</div>'
            => [
                "text" => 'xxx[emp]xxx[/emp]xxx',
                "id" => '1:1235',
                "class" => 'Mnemonic'
            ],
            // Headline1, Important
            '<div id="1:1236" class="ilc_text_block_Headline1">xxx<span class="ilc_text_inline_Important">xxx</span>xxx</div>'
            => [
                "text" => 'xxx[imp]xxx[/imp]xxx',
                "id" => '1:1236',
                "class" => 'Headline1'
            ],
            // Standard, Sup
            '<div id="1:1237" class="ilc_text_block_Standard">xxx a<sup class="ilc_sup_Sup">b*c</sup> xxx</div>'
            => [
                "text" => 'xxx a[sup]b*c[/sup] xxx',
                "id" => '1:1237',
                "class" => 'Standard'
            ],
            // Standard, Sub
            '<div id="1:1238" class="ilc_text_block_Standard">xxx a<sub class="ilc_sub_Sub">2</sub> xxx</div>'
            => [
                "text" => 'xxx a[sub]2[/sub] xxx',
                "id" => '1:1238',
                "class" => 'Standard'
            ],
            // Headline2, Comment
            '<div id="1:1239" class="ilc_text_block_Headline2">xxx <span class="ilc_text_inline_Comment">xxx</span> xxx</div>'
            => [
                "text" => 'xxx [com]xxx[/com] xxx',
                "id" => '1:1239',
                "class" => 'Headline2'
            ],
            // Headline3, Comment
            '<div id="1:1240" class="ilc_text_block_Headline3">xxx <span class="ilc_text_inline_Quotation">xxx</span> xxx</div>'
            => [
                "text" => 'xxx [quot]xxx[/quot] xxx',
                "id" => '1:1240',
                "class" => 'Headline3'
            ],
            // Book, Accent
            '<div id="1:1241" class="ilc_text_block_Book">xxx <span class="ilc_text_inline_Accent">xxx</span> xxx</div>'
            => [
                "text" => 'xxx [acc]xxx[/acc] xxx',
                "id" => '1:1241',
                "class" => 'Book'
            ],
            // Numbers, Code
            '<div id="1:1242" class="ilc_text_block_Numbers">xxx <code>xxx</code> xxx</div>'
            => [
                "text" => 'xxx [code]xxx[/code] xxx',
                "id" => '1:1242',
                "class" => 'Numbers'
            ],
            // Verse, Mnemonic
            '<div id="1:1243" class="ilc_text_block_Verse">xxx <span class="ilc_text_inline_Mnemonic">xxx</span> xxx</div>'
            => [
                "text" => 'xxx [marked class="Mnemonic"]xxx[/marked] xxx',
                "id" => '1:1243',
                "class" => 'Verse'
            ],
            // List, Attention
            '<div id="1:1244" class="ilc_text_block_List">xxx <span class="ilc_text_inline_Attention">xxx</span> xxx</div>'
            => [
                "text" => 'xxx [marked class="Attention"]xxx[/marked] xxx',
                "id" => '1:1244',
                "class" => 'List'
            ],
        ];

        foreach ($cases as $in => $expected) {
            $out = ilPCParagraph::handleAjaxContent($in);
            $this->assertEquals(
                $expected["text"],
                $out["text"]
            );
            $this->assertEquals(
                $expected["id"],
                $out["id"]
            );
            $this->assertEquals(
                $expected["class"],
                $out["class"]
            );
        }
    }

    /**
     * Test HTML to BB transformation, lists (are not transformed in this first step)
     */
    public function testHandleAjaxContentLists(): void
    {
        $cases = [
            // List, Bullet
            '<div id="1:1234" class="ilc_text_block_List"><ul class="ilc_list_u_BulletedList"><li class="ilc_list_item_StandardListItem">one</li><li class="ilc_list_item_StandardListItem">two</li><li class="ilc_list_item_StandardListItem">three</li></ul></div>'
            => [
                "text" => '<ul class="ilc_list_u_BulletedList"><li class="ilc_list_item_StandardListItem">one</li><li class="ilc_list_item_StandardListItem">two</li><li class="ilc_list_item_StandardListItem">three</li></ul>',
                "id" => '1:1234',
                "class" => 'List'
            ],
            // List, Numberd
            '<div id="1:1235" class="ilc_text_block_List"><ol class="ilc_list_o_NumberedList"><li class="ilc_list_item_StandardListItem">one</li><li class="ilc_list_item_StandardListItem">two</li><li class="ilc_list_item_StandardListItem">three</li></ol></div>'
            => [
                "text" => '<ol class="ilc_list_o_NumberedList"><li class="ilc_list_item_StandardListItem">one</li><li class="ilc_list_item_StandardListItem">two</li><li class="ilc_list_item_StandardListItem">three</li></ol>',
                "id" => '1:1235',
                "class" => 'List'
            ],
        ];

        foreach ($cases as $in => $expected) {
            $out = ilPCParagraph::handleAjaxContent($in);
            $this->assertEquals(
                $expected["text"],
                $out["text"]
            );
            $this->assertEquals(
                $expected["id"],
                $out["id"]
            );
            $this->assertEquals(
                $expected["class"],
                $out["class"]
            );
        }
    }

    //
    // test basic dom creation
    //

    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCParagraph($page);
        $this->assertEquals(
            ilPCParagraph::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCParagraph($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Paragraph Language=""></Paragraph></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    //
    // test setTest using legacy (saveJS) way
    //

    // see saveJs in ilPCParagraph
    protected function legacyHtmlToXml(string $content): string
    {
        $content = str_replace("<br>", "<br />", $content);
        $content = ilPCParagraph::handleAjaxContent($content);
        $content = ilPCParagraph::_input2xml($content["text"], true, false);
        $content = ilPCParagraph::handleAjaxContentPost($content);
        return $content;
    }

    public function testLegacyHtml2Text(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCParagraph($page);
        $pc->create($page, "pg");

        $cases = [
            ''
            => '',
            'Some text'
            => 'Some text',
            'test &amp; the &lt; and the &gt; and the \ also'
            => 'test &amp;amp; the &amp;lt; and the &amp;gt; and the \ also',
            'xxx <span class="ilc_text_inline_Strong">xxx</span> xxx'
            => 'xxx <Strong>xxx</Strong> xxx',
        ];

        foreach ($cases as $html => $expected) {
            $html = '<div id="1:1234" class="ilc_text_block_Standard">' . $html . '</div>';
            $xml = $this->legacyHtmlToXml($html);
            $pc->setText($xml, false);

            $expected = '<PageObject HierId="pg"><PageContent><Paragraph Language="">' . $expected . '</Paragraph></PageContent></PageObject>';

            $this->assertEquals(
                $xml,
                $pc->getText()
            );

            $this->assertXmlEquals(
                $expected,
                $page->getXMLFromDom()
            );
        }
    }
}
