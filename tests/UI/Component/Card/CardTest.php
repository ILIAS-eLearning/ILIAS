<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on card implementation.
 */
class CardTest extends ILIAS_UI_TestBase
{

    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        return new \ILIAS\UI\Implementation\Factory();
    }

    public function test_implements_factory_interface()
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Card\\Card", $f->card("Card Title"));
    }

    public function test_get_title()
    {
        $f = $this->getFactory();
        $c = $f->card("Card Title");

        $this->assertEquals($c->getTitle(), "Card Title");
    }

    public function test_with_title()
    {
        $f = $this->getFactory();

        $c = $f->card("Card Title");
        $c = $c->withTitle("Card Title New");

        $this->assertEquals($c->getTitle(), "Card Title New");
    }

    public function test_with_title_action()
    {
        $f = $this->getFactory();
        $c = $f->card("Card Title");
        $c = $c->withTitleAction("newAction");
        $this->assertEquals("newAction", $c->getTitleAction());
    }

    public function test_with_highlight()
    {
        $f = $this->getFactory();
        $c = $f->card("Card Title");
        $c = $c->withHighlight(true);
        $this->assertTrue($c->isHighlighted());
    }

    public function test_get_image()
    {
        $f = $this->getFactory();

        $image = $f->image()->standard("src", "str");
        $c = $f->card("Card Title", $image);

        $this->assertEquals($c->getImage(), $image);
    }

    public function test_with_image()
    {
        $f = $this->getFactory();

        $image = $f->image()->standard("src", "str");
        $c = $f->card("Card Title", $image);

        $image_new = $f->image()->standard("src/new", "str");

        $c = $c->withImage($image_new);

        $this->assertEquals($c->getImage(), $image_new);
    }

    public function test_with_section()
    {
        $f = $this->getFactory();

        $c = $f->card("Card Title");

        $content = $f->legacy("Random Content");

        $c = $c->withSections(array($content));

        $this->assertEquals($c->getSections(), array($content));
    }

    public function test_render_content_empty()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->card("Card Title");

        $html = $r->render($c);

        $expected_html =
                "<div class=\"il-card thumbnail\">" .
                "   <div class=\"card-no-highlight\"></div>" .
                "   <div class=\"caption\">" .
                "       <h5 class=\"card-title\">Card Title</h5>" .
                "   </div>" .
                "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_content_full()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $image = $f->image()->standard("src", "alt");

        $c = $f->card("Card Title", $image);

        $content = $f->legacy("Random Content");

        $c = $c->withSections(array($content));

        $html = $r->render($c);

        $expected_html =
                "<div class=\"il-card thumbnail\">" .
                "   <img src=\"src\" class=\"img-standard\" alt=\"alt\" />" .
                "   <div class=\"card-no-highlight\"></div>" .
                "   <div class=\"caption\">" .
                "       <h5 class=\"card-title\">Card Title</h5>" .
                "   </div>" .
                "   <div class=\"caption\">Random Content</div>" .
                "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_content_with_highlight()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $image = $f->image()->standard("src", "alt");

        $c = $f->card("Card Title", $image)->withHighlight(true);

        $html = $r->render($c);

        $expected_html =
            "<div class=\"il-card thumbnail\">" .
            "   <img src=\"src\" class=\"img-standard\" alt=\"alt\" />" .
            "   <div class=\"card-highlight\"></div>" .
            "   <div class=\"caption\">" .
            "       <h5 class=\"card-title\">Card Title</h5>" .
            "   </div>" .
            "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }
}
