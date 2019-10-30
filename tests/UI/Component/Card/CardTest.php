<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

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
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            $this->createMock(C\Glyph\Factory::class),
            $this->createMock(C\Button\Factory::class),
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            $this->createMock(C\Modal\Factory::class),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            $this->createMock(C\Link\Factory::class),
            $this->createMock(C\Dropdown\Factory::class),
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }

    private function getCardFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Card\Factory();
    }

    private function getBaseCard()
    {
        $cf = $this->getCardFactory();
        $image = new I\Component\Image\Image("standard", "src", "alt");

        return $cf->standard("Card Title", $image);
    }

    public function test_implements_factory_interface()
    {
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Card\\Standard", $this->getBaseCard());
    }

    public function test_get_title()
    {
        $c = $this->getBaseCard();

        $this->assertEquals($c->getTitle(), "Card Title");
    }

    public function test_with_title()
    {
        $c = $this->getBaseCard();
        $c = $c->withTitle("Card Title New");

        $this->assertEquals($c->getTitle(), "Card Title New");
    }

    public function test_with_title_action()
    {
        $c = $this->getBaseCard();
        $c = $c->withTitleAction("newAction");
        $this->assertEquals("newAction", $c->getTitleAction());
    }

    public function test_with_highlight()
    {
        $c = $this->getBaseCard();
        $c = $c->withHighlight(true);
        $this->assertTrue($c->isHighlighted());
    }

    public function test_get_image()
    {
        $card = $this->getBaseCard();
        $image = new I\Component\Image\Image("standard", "src", "alt");

        $this->assertEquals($card->getImage(), $image);
    }

    public function test_with_image()
    {
        $card = $this->getBaseCard();
        $image_new = new I\Component\Image\Image("standard", "src/new", "alt");
        $c = $card->withImage($image_new);

        $this->assertEquals($c->getImage(), $image_new);
    }

    public function test_with_section()
    {
        $f = $this->getFactory();
        $c = $this->getBaseCard();
        $content = $f->legacy("Random Content");
        $c = $c->withSections(array($content));

        $this->assertEquals($c->getSections(), array($content));
    }

    public function test_with_image_action()
    {
        $c = $this->getBaseCard();
        $action = "https://www.ilias.de";
        $c = $c->withImageAction($action);

        $this->assertEquals($c->getImageAction(), $action);
    }

    public function test_render_content_full()
    {
        $r = $this->getDefaultRenderer();

        $c = $this->getBaseCard();

        $content = new I\Component\Legacy\Legacy("Random Content");

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
        $r = $this->getDefaultRenderer();
        $c = $this->getBaseCard();
        $c = $c->withHighlight(true);

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
