<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as IC;

/**
 * Test on Message Box implementation.
 */
class MessageBoxTest extends ILIAS_UI_TestBase
{
    public function getMessageBoxFactory()
    {
        return new \ILIAS\UI\Implementation\Component\MessageBox\Factory();
    }
    public function getButtonFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Button\Factory();
    }
    public function getLinkFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Link\Factory();
    }

    public function messagebox_type_provider()
    {
        return array( array(C\MessageBox\MessageBox::FAILURE)
        , array(C\MessageBox\MessageBox::SUCCESS)
        , array(C\MessageBox\MessageBox::INFO)
        , array(C\MessageBox\MessageBox::CONFIRMATION)
        );
    }

    public static $canonical_css_classes = array( C\MessageBox\MessageBox::FAILURE => "alert-danger"
    , C\MessageBox\MessageBox::SUCCESS => "alert-success"
    , C\MessageBox\MessageBox::INFO => "alert-info"
    , C\MessageBox\MessageBox::CONFIRMATION => "alert-warning"
    );

    public function getUIFactory()
    {
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            $this->createMock(C\Glyph\Factory::class),
            $this->createMock(C\Button\Factory::class),
            new IC\Listing\Factory(),
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


    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_implements_factory_interface($factory_method)
    {
        $f = $this->getMessageBoxFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\MessageBox", $f->$factory_method("Lorem ipsum dolor sit amet."));
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_messagebox_types($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $this->assertNotNull($g);
        $this->assertEquals($factory_method, $g->getType());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_messagebox_messagetext($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $this->assertNotNull($g);
        $this->assertEquals("Lorem ipsum dolor sit amet.", $g->getMessageText());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_buttons($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $buttons = [$bf->standard("Confirm", "#"), $bf->standard("Cancel", "#")];
        $g2 = $g->withButtons($buttons);

        $this->assertFalse(count($g->getButtons()) > 0);
        $this->assertTrue(count($g2->getButtons()) > 0);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_links($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $lf = $this->getLinkFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $links = [
            $lf->standard("Open Exercise Assignment", "#"),
            $lf->standard("Open other screen", "#"),
        ];
        $g2 = $g->withLinks($links);

        $this->assertFalse(count($g->getLinks()) > 0);
        $this->assertTrue(count($g2->getLinks()) > 0);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_buttons_and_links($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $lf = $this->getLinkFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $buttons = [$bf->standard("Confirm", "#"), $bf->standard("Cancel", "#")];
        $links = [
            $lf->standard("Open Exercise Assignment", "#"),
            $lf->standard("Open other screen", "#"),
        ];
        $g2 = $g->withButtons($buttons)->withLinks($links);

        $this->assertFalse(count($g->getButtons()) > 0 && count($g->getLinks()) > 0);
        $this->assertTrue(count($g2->getButtons()) > 0 && count($g2->getLinks()) > 0);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_render_simple($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $r = $this->getDefaultRenderer();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");
        $css_classes = self::$canonical_css_classes[$factory_method];

        $html = $this->normalizeHTML($r->render($g));
        $expected = "<div class=\"alert $css_classes\" role=\"alert\">" .
                    "<h5 class=\"ilAccHeadingHidden\"><a id=\"il_message_focus\" name=\"il_message_focus\">" .
                    $g->getType() . "_message</a></h5>Lorem ipsum dolor sit amet.</div>";
        $this->assertHTMLEquals($expected, $html);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_render_with_buttons($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $r = $this->getDefaultRenderer();
        $css_classes = self::$canonical_css_classes[$factory_method];

        $buttons = [$bf->standard("Confirm", "#"), $bf->standard("Cancel", "#")];

        $g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withButtons($buttons);

        $html = $this->normalizeHTML($r->render($g));
        $expected = "<div class=\"alert $css_classes\" role=\"alert\">" .
                    "<h5 class=\"ilAccHeadingHidden\"><a id=\"il_message_focus\" name=\"il_message_focus\">" .
                    $g->getType() . "_message</a></h5>Lorem ipsum dolor sit amet." .
                    "<div><button class=\"btn btn-default\"   data-action=\"#\" id=\"id_1\">Confirm</button>" .
                    "<button class=\"btn btn-default\"   data-action=\"#\" id=\"id_2\">Cancel</button></div></div>";
        $this->assertHTMLEquals($expected, $html);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_render_with_links($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $lf = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();
        $css_classes = self::$canonical_css_classes[$factory_method];

        $links = [
            $lf->standard("Open Exercise Assignment", "#"),
            $lf->standard("Open other screen", "#"),
        ];

        $g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withLinks($links);

        $html = $this->normalizeHTML($r->render($g));
        $expected = "<div class=\"alert $css_classes\" role=\"alert\">" .
                    "<h5 class=\"ilAccHeadingHidden\"><a id=\"il_message_focus\" name=\"il_message_focus\">" .
                    $g->getType() . "_message</a></h5>Lorem ipsum dolor sit amet." .
                    "<ul><li><a href=\"#\" >Open Exercise Assignment</a></li>" .
                    "<li><a href=\"#\" >Open other screen</a></li></ul></div>";
        $this->assertHTMLEquals($expected, $html);
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_render_with_buttons_and_links($factory_method)
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $lf = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");
        $css_classes = self::$canonical_css_classes[$factory_method];

        $buttons = [$bf->standard("Confirm", "#"), $bf->standard("Cancel", "#")];
        $links = [
            $lf->standard("Open Exercise Assignment", "#"),
            $lf->standard("Open other screen", "#"),
        ];

        $g = $f->$factory_method("Lorem ipsum dolor sit amet.")->withButtons($buttons)->withLinks($links);

        $html = $this->normalizeHTML($r->render($g));
        $expected = "<div class=\"alert $css_classes\" role=\"alert\">" .
                    "<h5 class=\"ilAccHeadingHidden\"><a id=\"il_message_focus\" name=\"il_message_focus\">" .
                    $g->getType() . "_message</a></h5>Lorem ipsum dolor sit amet." .
                    "<div><button class=\"btn btn-default\"   data-action=\"#\" id=\"id_1\">Confirm</button>" .
                    "<button class=\"btn btn-default\"   data-action=\"#\" id=\"id_2\">Cancel</button></div>" .
                    "<ul><li><a href=\"#\" >Open Exercise Assignment</a></li>" .
                    "<li><a href=\"#\" >Open other screen</a></li></ul></div>";
        $this->assertHTMLEquals($expected, $html);
    }
}
