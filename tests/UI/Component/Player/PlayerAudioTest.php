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
class PlayerAudioTest extends ILIAS_UI_TestBase
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

        $audio = $f->audio("/foo", "bar");

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Player\\Audio", $audio);
    }

    public function test_get_title_get_source() : void
    {
        $f = $this->getFactory();

        $audio = $f->audio("/foo");

        $this->assertEquals("/foo", $audio->getSource());
    }

    public function test_get_title_get_transcript() : void
    {
        $f = $this->getFactory();

        $audio = $f->audio("/foo", "bar");

        $this->assertEquals("bar", $audio->getTranscription());
    }

    public function test_render_audio() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $audio = $f->audio("/foo");

        $html = $r->render($audio);

        $expected = <<<EOT
<div class="il-audio-container">
    <audio class="il-audio-player" id="id_1" src="/foo" preload="meta"></audio>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function test_render_with_transcript() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $audio = $f->audio("/foo", "x*123");

        $html = $r->render($audio);

        $this->assertEquals(
            true,
            is_int(strpos($html, "ui_transcription</button>"))
        );
        $this->assertEquals(
            true,
            is_int(strpos($html, "il-modal-lightbox"))
        );
        $this->assertEquals(
            true,
            is_int(strpos($html, "x*123"))
        );
    }
}
