<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Meta Bar.
 */
class MetaBarTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $sig_gen = new I\Component\SignalGenerator();
        $this->button_factory = new I\Component\Button\Factory($sig_gen);
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

    public function testConstruction()
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\MetaBar",
            $this->metabar
        );
    }

    protected function getButton()
    {
        $symbol = $this->icon_factory->custom('', '');
        return $this->button_factory->bulky($symbol, 'TestEntry', '#');
    }

    protected function getSlate()
    {
        $mock = $this->getMockBuilder(Legacy::class)
            ->disableOriginalConstructor()
            ->setMethods(["transformToLegacyComponent"])
            ->getMock();

        $mock->method('transformToLegacyComponent')->willReturn('content');
        return $mock;
    }

    public function testAddEntry()
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

    public function testDisallowedEntry()
    {
        $this->expectException(\InvalidArgumentException::class);
        $mb = $this->metabar->withAdditionalEntry('test', 'wrong_param');
    }

    public function testSignalsPresent()
    {
        $this->assertInstanceOf(Signal::class, $this->metabar->getEntryClickSignal());
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return $this->button_factory;
            }
            public function mainControls() : C\MainControls\Factory
            {
                return $this->mc_factory;
            }
            public function symbol() : C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory(),
                    new I\Component\Symbol\Avatar\Factory()
                );
            }
            public function counter() : C\Counter\Factory
            {
                return $this->counter_factory;
            }
        };
        $factory->button_factory = $this->button_factory;
        $factory->mc_factory = $this->factory;
        $factory->counter_factory = $this->counter_factory;
        return $factory;
    }

    public function brutallyTrimHTML($html)
    {
        $html = str_replace(["\n", "\r", "\t"], "", $html);
        $html = preg_replace('# {2,}#', " ", $html);
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        $html = str_replace('> <', '><', $html);
        return trim($html);
    }

    public function testRendering()
    {
        $r = $this->getDefaultRenderer();

        $button = $this->getButton();
        $slate = $this->getSlate();
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
             <span class="glyph" aria-label="disclose" role="img">
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


    public function testAcceptsBulkyLinkAsEntry() : void
    {
        $r = $this->getDefaultRenderer();

        $bulky_link = $this->createMock(ILIAS\UI\Component\Link\Bulky::class);
        $mb = $this->metabar
            ->withAdditionalEntry('bulky link', $bulky_link);

        $this->assertTrue(true); // Should not throw...
    }
}
