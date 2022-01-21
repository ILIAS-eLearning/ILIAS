<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\Card\Factory;

/**
 * Test on card implementation.
 */
class CardTest extends ILIAS_UI_TestBase
{
    public function getFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function legacy($content) : C\Legacy\Legacy
            {
                $f = new I\Component\Legacy\Factory(new I\Component\SignalGenerator());
                return $f->legacy($content);
            }
        };
    }

    private function getCardFactory() : I\Component\Card\Factory
    {
        return new Factory();
    }

    private function getBaseCard() : I\Component\Card\Standard
    {
        $cf = $this->getCardFactory();
        $image = new I\Component\Image\Image("standard", "src", "alt");

        return $cf->standard("Card Title", $image);
    }

    public function test_implements_factory_interface() : void
    {
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Card\\Standard", $this->getBaseCard());
    }

    public function test_get_title() : void
    {
        $c = $this->getBaseCard();

        $this->assertEquals("Card Title", $c->getTitle());
    }

    public function test_with_title() : void
    {
        $c = $this->getBaseCard();
        $c = $c->withTitle("Card Title New");

        $this->assertEquals("Card Title New", $c->getTitle());
    }

    public function test_with_string_title_action() : void
    {
        $c = $this->getBaseCard();
        $c = $c->withTitleAction("newAction");
        $this->assertEquals("newAction", $c->getTitleAction());
    }

    public function test_with_signal_title_action() : void
    {
        $c = $this->getBaseCard();
        $signal = $this->createMock(C\Signal::class);
        $c = $c->withTitleAction($signal);
        $this->assertEquals([$signal], $c->getTitleAction());
    }

    public function test_with_highlight() : void
    {
        $c = $this->getBaseCard();
        $c = $c->withHighlight(true);
        $this->assertTrue($c->isHighlighted());
    }

    public function test_get_image() : void
    {
        $card = $this->getBaseCard();
        $image = new I\Component\Image\Image("standard", "src", "alt");

        $this->assertEquals($card->getImage(), $image);
    }

    public function test_with_image() : void
    {
        $card = $this->getBaseCard();
        $image_new = new I\Component\Image\Image("standard", "src/new", "alt");
        $c = $card->withImage($image_new);

        $this->assertEquals($c->getImage(), $image_new);
    }

    public function test_with_section() : void
    {
        $f = $this->getFactory();
        $c = $this->getBaseCard();
        $content = $f->legacy("Random Content");
        $c = $c->withSections(array($content));

        $this->assertEquals($c->getSections(), array($content));
    }

    public function test_render_content_full() : void
    {
        $r = $this->getDefaultRenderer();
        $c = $this->getBaseCard();
        $content = $this->getFactory()->legacy("Random Content");

        $c = $c->withSections(array($content));

        $html = $r->render($c);

        $expected_html =
                "<div class=\"il-card thumbnail\">" .
                "   <img src=\"src\" class=\"img-standard\" alt=\"alt\" />" .
                "   <div class=\"card-no-highlight\"></div>" .
                "   <div class=\"caption\">" .
                "       <div class=\"card-title\">Card Title</div>" .
                "   </div>" .
                "   <div class=\"caption\">Random Content</div>" .
                "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_content_with_highlight() : void
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
            "       <div class=\"card-title\">Card Title</div>" .
            "   </div>" .
            "</div>";

        $this->assertHTMLEquals($expected_html, $html);
    }
}
