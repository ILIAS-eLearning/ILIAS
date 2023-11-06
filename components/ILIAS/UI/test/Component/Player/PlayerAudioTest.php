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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PlayerAudioTest extends ILIAS_UI_TestBase
{
    public function getUIFactory(): NoUIFactory
    {
        return new class (
            $this->createMock(C\Modal\InterruptiveItem\Factory::class),
            $this->createMock(FieldFactory::class),
        ) extends NoUIFactory {
            public function __construct(
                protected C\Modal\InterruptiveItem\Factory $item_factory,
                protected FieldFactory $field_factory,
            ) {
            }

            public function modal(): C\Modal\Factory
            {
                return new I\Component\Modal\Factory(
                    new I\Component\SignalGenerator(),
                    $this->item_factory,
                    $this->field_factory,
                );
            }
            public function button(): C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }
        };
    }

    public function getFactory(): C\Player\Factory
    {
        return new I\Component\Player\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $audio = $f->audio("/foo", "bar");

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Player\\Audio", $audio);
    }

    public function testGetTitleGetSource(): void
    {
        $f = $this->getFactory();

        $audio = $f->audio("/foo");

        $this->assertEquals("/foo", $audio->getSource());
    }

    public function testGetTitleGetTranscript(): void
    {
        $f = $this->getFactory();

        $audio = $f->audio("/foo", "bar");

        $this->assertEquals("bar", $audio->getTranscription());
    }

    public function testRenderAudio(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $audio = $f->audio("/foo");

        $html = $r->render($audio);

        $expected = <<<EOT
<div class="il-audio-container">
    <audio class="il-audio-player" id="id_1" src="/foo" preload="metadata"></audio>
</div>
EOT;
        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    public function testRenderWithTranscript(): void
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
