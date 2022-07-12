<?php declare(strict_types=1);

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
 
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as IC;

/**
 * Test on Message Box implementation.
 */
class MessageBoxTest extends ILIAS_UI_TestBase
{
    public function getMessageBoxFactory() : IC\MessageBox\Factory
    {
        return new IC\MessageBox\Factory();
    }
    public function getButtonFactory() : IC\Button\Factory
    {
        return new IC\Button\Factory();
    }
    public function getLinkFactory() : IC\Link\Factory
    {
        return new IC\Link\Factory();
    }

    public function messagebox_type_provider() : array
    {
        return array( array(C\MessageBox\MessageBox::FAILURE)
        , array(C\MessageBox\MessageBox::SUCCESS)
        , array(C\MessageBox\MessageBox::INFO)
        , array(C\MessageBox\MessageBox::CONFIRMATION)
        );
    }

    public static array $canonical_css_classes = array( C\MessageBox\MessageBox::FAILURE => "alert-danger"
    , C\MessageBox\MessageBox::SUCCESS => "alert-success"
    , C\MessageBox\MessageBox::INFO => "alert-info"
    , C\MessageBox\MessageBox::CONFIRMATION => "alert-warning"
    );

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function listing() : IC\Listing\Factory
            {
                return new IC\Listing\Factory();
            }
        };
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_implements_factory_interface(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\Factory", $f);
        $this->assertInstanceOf("ILIAS\\UI\\Component\\MessageBox\\MessageBox", $f->$factory_method("Lorem ipsum dolor sit amet."));
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_messagebox_types(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $this->assertNotNull($g);
        $this->assertEquals($factory_method, $g->getType());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_messagebox_messagetext(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $this->assertNotNull($g);
        $this->assertEquals("Lorem ipsum dolor sit amet.", $g->getMessageText());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_buttons(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $buttons = [$bf->standard("Confirm", "#"), $bf->standard("Cancel", "#")];
        $g2 = $g->withButtons($buttons);

        $this->assertEmpty($g->getButtons());
        $this->assertNotEmpty($g2->getButtons());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_links(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();
        $lf = $this->getLinkFactory();
        $g = $f->$factory_method("Lorem ipsum dolor sit amet.");

        $links = [
            $lf->standard("Open Exercise Assignment", "#"),
            $lf->standard("Open other screen", "#"),
        ];
        $g2 = $g->withLinks($links);

        $this->assertEmpty($g->getLinks());
        $this->assertNotEmpty($g2->getLinks());
    }

    /**
     * @dataProvider messagebox_type_provider
     */
    public function test_with_buttons_and_links(string $factory_method) : void
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
    public function test_render_simple(string $factory_method) : void
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
    public function test_render_with_buttons(string $factory_method) : void
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
    public function test_render_with_links(string $factory_method) : void
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
    public function test_render_with_buttons_and_links(string $factory_method) : void
    {
        $f = $this->getMessageBoxFactory();
        $bf = $this->getButtonFactory();
        $lf = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();
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
