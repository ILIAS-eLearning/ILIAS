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

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PlayerVideoTest extends ILIAS_UI_TestBase
{
    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function modal() : C\Modal\Factory
            {
                return new I\Component\Modal\Factory(new I\Component\SignalGenerator());
            }
            public function button() : C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }
        };
    }

    public function getFactory() : C\Player\Factory
    {
        return new I\Component\Player\Factory();
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $video = $f->video("/foo");

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Player\\Video", $video);
    }

    public function test_get_title_get_source() : void
    {
        $f = $this->getFactory();

        $video = $f->video("/foo");

        $this->assertEquals("/foo", $video->getSource());
    }

    public function test_get_title_get_poster() : void
    {
        $f = $this->getFactory();

        $video = $f->video("/foo")->withPoster("bar.jpg");

        $this->assertEquals("bar.jpg", $video->getPoster());
    }

    public function test_get_title_get_subtitle_file() : void
    {
        $f = $this->getFactory();

        $video = $f->video("/foo")->withAdditionalSubtitleFile("en", "subtitles.vtt");

        $this->assertEquals(["en" => "subtitles.vtt"], $video->getSubtitleFiles());
    }

    public function test_render_video() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $video = $f->video("/foo");

        $html = $r->render($video);
        $expected = <<<EOT
<div class="il-video-container">
    <video class="il-video-player" id="id_1" src="/foo" style="max-width: 100%;" preload="metadata" >
    </video>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_with_poster() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $video = $f->video("/foo")->withPoster("bar.jpg");

        $html = $r->render($video);

        $expected = <<<EOT
<div class="il-video-container">
    <video class="il-video-player" id="id_1" src="/foo" style="max-width: 100%;" preload="metadata" poster="bar.jpg">
    </video>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_with_subtitles() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $video = $f->video("/foo")->withAdditionalSubtitleFile("en", "subtitles.vtt");

        $html = $r->render($video);
        $expected = <<<EOT
<div class="il-video-container">
    <video class="il-video-player" id="id_1" src="/foo" style="max-width: 100%;" preload="metadata" >
        <track kind="subtitles" src="subtitles.vtt" srclang="en" />
    </video>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
