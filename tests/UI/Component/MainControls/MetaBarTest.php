<?php

declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use ILIAS\UI\Component\Signal;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the Meta Bar.
 */
class MetaBarTest extends ILIAS_UI_TestBase
{
    protected I\Component\Button\Factory $button_factory;
    protected I\Component\Symbol\Icon\Factory $icon_factory;
    protected I\Component\Counter\Factory $counter_factory;
    protected I\Component\MainControls\Factory $factory;
    protected C\MainControls\MetaBar $metabar;

    public function setUp(): void
    {
        $sig_gen = new I\Component\SignalGenerator();
        $this->button_factory = new I\Component\Button\Factory();
        $this->icon_factory = new I\Component\Symbol\Icon\Factory();
        $this->counter_factory = new I\Component\Counter\Factory();

        $slate_factory = new I\Component\MainControls\Slate\Factory(
            $sig_gen,
            $this->counter_factory,
            new I\Component\Symbol\Factory(
                new I\Component\Symbol\Icon\Factory(),
                new I\Component\Symbol\Glyph\Factory(),
                new I\Component\Symbol\Avatar\Factory()
            )
        );

        $this->factory = new I\Component\MainControls\Factory($sig_gen, $slate_factory);
        $this->metabar = $this->factory->metabar();
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\MetaBar",
            $this->metabar
        );
    }

    protected function getButton(): C\Button\Bulky
    {
        $symbol = $this->icon_factory->custom('', '');
        return $this->button_factory->bulky($symbol, 'TestEntry', '#');
    }

    /**
     * @return Legacy|mixed|MockObject
     */
    protected function getSlate()
    {
        $mock = $this->getMockBuilder(Legacy::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    public function testAddEntry(): void
    {
        $button = $this->getButton();
        $slate = $this->getSlate();
        $mb = $this->metabar
            ->withAdditionalEntry('button', $button)
            ->withAdditionalEntry('slate', $slate);
        $entries = $mb->getEntries();
        $this->assertEquals($button, $entries['button']);
        $this->assertEquals($slate, $entries['slate']);
    }

    public function testDisallowedEntry(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->metabar->withAdditionalEntry('test', 'wrong_param');
    }

    public function testSignalsPresent(): void
    {
        $this->assertInstanceOf(Signal::class, $this->metabar->getEntryClickSignal());
    }

    public function getUIFactory(): NoUIFactory
    {
        $factory = new class () extends NoUIFactory {
            public C\Button\Factory $button_factory;
            public C\MainControls\Factory $mc_factory;
            public C\Counter\Factory $counter_factory;

            public function button(): C\Button\Factory
            {
                return $this->button_factory;
            }
            public function mainControls(): C\MainControls\Factory
            {
                return $this->mc_factory;
            }
            public function symbol(): C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
            public function counter(): C\Counter\Factory
            {
                return $this->counter_factory;
            }
        };
        $factory->button_factory = $this->button_factory;
        $factory->mc_factory = $this->factory;
        $factory->counter_factory = $this->counter_factory;
        return $factory;
    }

    public function brutallyTrimHTML(string $html): string
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = str_replace('> <', '><', $html);
        return trim($html);
    }

    public function testRendering(): void
    {
        $r = $this->getDefaultRenderer();

        $button = $this->getButton();
        $mb = $this->metabar
            ->withAdditionalEntry('button', $button)
            ->withAdditionalEntry('button2', $button);

        $html = $r->render($mb);

        $expected = '
   <ul class="il-maincontrols-metabar" role="menubar" style="visibility: hidden" aria-label="metabar_aria_label" id="id_5" >
      <li role="none">
        <button class="btn btn-bulky" data-action="#" id="id_1" role="menuitem" >
            <img class="icon custom small" src="" alt=""/><span class="bulky-label">TestEntry</span>
        </button>
      </li>
      <li role="none">
        <button class="btn btn-bulky" data-action="#" id="id_2" role="menuitem" >
            <img class="icon custom small" src="" alt=""/><span class="bulky-label">TestEntry</span>
        </button>
      </li>
      <li role="none">
         <button class="btn btn-bulky" id="id_3" role="menuitem" aria-haspopup="true" >
             <span class="glyph" role="img">
                <span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>
                <span class="il-counter"><span class="badge badge-notify il-counter-status" style="display:none">0</span></span>
                <span class="il-counter"><span class="badge badge-notify il-counter-novelty" style="display:none">0</span></span>
             </span>
             <span class="bulky-label">more</span>
         </button>
         <div class="il-metabar-slates">
            <div class="il-maincontrols-slate disengaged" id="id_4" role="menu">
               <div class="il-maincontrols-slate-content" data-replace-marker="content"></div>
            </div>
         </div>
      </li>
   </ul>
';

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }


    public function testAcceptsBulkyLinkAsEntry(): void
    {
        $r = $this->getDefaultRenderer();

        $bulky_link = $this->createMock(ILIAS\UI\Component\Link\Bulky::class);
        $mb = $this->metabar
            ->withAdditionalEntry('bulky link', $bulky_link);

        $this->assertTrue(true); // Should not throw...
    }
}
