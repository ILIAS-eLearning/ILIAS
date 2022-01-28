<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use ILIAS\UI\Component\Signal;
use ILIAS\Data;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the Main Bar.
 */
class MainBarTest extends ILIAS_UI_TestBase
{
    /**
     * @var I\Button\Factory
     */
    protected $button_factory;
    /**
     * @var I\Link\Factory
     */
    protected $link_factory;
    /**
     * @var I\Symbol\Icon\Factory
     */
    protected $icon_factory;
    /**
     * @var I\MainControls\Factory
     */
    protected $factory;
    /**
     * @var C\MainControls\MainBar
     */
    protected $mainbar;

    public function setUp() : void
    {
        $sig_gen = new I\SignalGenerator();
        $this->button_factory = new I\Button\Factory();
        $this->link_factory = new I\Link\Factory();
        $this->icon_factory = new I\Symbol\Icon\Factory();
        $counter_factory = new I\Counter\Factory();
        $slate_factory = new I\MainControls\Slate\Factory(
            $sig_gen,
            $counter_factory,
            new I\Symbol\Factory(
                new I\Symbol\Icon\Factory(),
                new I\Symbol\Glyph\Factory(),
                new I\Symbol\Avatar\Factory()
            )
        );
        $this->factory = new I\MainControls\Factory($sig_gen, $slate_factory);

        $this->mainbar = $this->factory->mainBar();
    }

    public function testConstruction() : void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\MainBar",
            $this->mainbar
        );
    }

    protected function getButton() : C\Button\Bulky
    {
        $symbol = $this->icon_factory->custom('', '');
        return $this->button_factory->bulky($symbol, 'TestEntry', '#');
    }

    protected function getLink() : C\Link\Bulky
    {
        $symbol = $this->icon_factory->custom('', '');
        $target = new Data\URI("http://www.ilias.de");
        return $this->link_factory->bulky($symbol, 'TestEntry', $target);
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

    public function testAddEntry() : C\MainControls\MainBar
    {
        $btn = $this->getButton();
        $lnk = $this->getLink();
        $mb = $this->mainbar
            ->withAdditionalEntry('testbtn', $btn)
            ->withAdditionalEntry('testlnk', $lnk);

        $entries = $mb->getEntries();
        $expected = [
            'testbtn' => $btn,
            'testlnk' => $lnk,
        ];
        $this->assertEquals($expected, $entries);
        return $mb;
    }

    public function testDisallowedEntry() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mainbar->withAdditionalEntry('test', 'wrong_param');
    }

    public function testDouplicateIdEntry() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $btn = $this->getButton();
        $this->mainbar
            ->withAdditionalEntry('test', $btn)
            ->withAdditionalEntry('test', $btn);
    }

    public function testDisallowedToolEntry() : void
    {
        $this->expectException(\TypeError::class);
        $this->mainbar->withAdditionalToolEntry('test', 'wrong_param');
    }

    public function testAddToolEntryWithoutToolsButton() : void
    {
        $this->expectException(LogicException::class);
        $this->mainbar->withAdditionalToolEntry('test', $this->getSlate());
    }

    public function testAddToolEntry() : void
    {
        $slate = $this->getSlate();
        $mb = $this->mainbar
            ->withToolsButton($this->getButton())
            ->withAdditionalToolEntry('test', $slate);
        $entries = $mb->getToolEntries();
        $this->assertEquals($slate, $entries['test']);
    }

    public function testDouplicateIdToolEntry() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $btn = $this->getButton();
        $slate = $this->getSlate();
        $this->mainbar
            ->withToolsButton($btn)
            ->withAdditionalToolEntry('test', $slate)
            ->withAdditionalToolEntry('test', $slate);
    }

    /**
     * @depends testAddEntry
     */
    public function testActive(C\MainControls\MainBar $mb) : void
    {
        $mb = $mb->withActive('testbtn');
        $this->assertEquals('testbtn', $mb->getActive());
    }

    public function testWithInvalidActive() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mainbar->withActive('this-is-not-a-valid-entry');
    }

    public function testSignalsPresent() : void
    {
        $this->assertInstanceOf(Signal::class, $this->mainbar->getEntryClickSignal());
        $this->assertInstanceOf(Signal::class, $this->mainbar->getToolsClickSignal());
        $this->assertInstanceOf(Signal::class, $this->mainbar->getToolsRemovalSignal());
        $this->assertInstanceOf(Signal::class, $this->mainbar->getDisengageAllSignal());
    }

    public function getUIFactory() : NoUIFactory
    {
        $factory = new class extends NoUIFactory {
            /**
             * @var C\Button\Factory
             */
            public $button_factory;

            public function button() : C\Button\Factory
            {
                return $this->button_factory;
            }
            public function symbol() : C\Symbol\Factory
            {
                $f_icon = new I\Symbol\Icon\Factory();
                $f_glyph = new I\Symbol\Glyph\Factory();
                $f_avatar = new I\Symbol\Avatar\Factory();

                return new I\Symbol\Factory($f_icon, $f_glyph, $f_avatar);
            }
            public function mainControls() : C\MainControls\Factory
            {
                $sig_gen = new I\SignalGenerator();
                $counter_factory = new I\Counter\Factory();
                $symbol_factory = new I\Symbol\Factory(
                    new I\Symbol\Icon\Factory(),
                    new I\Symbol\Glyph\Factory(),
                    new I\Symbol\Avatar\Factory()
                );
                $slate_factory = new I\MainControls\Slate\Factory($sig_gen, $counter_factory, $symbol_factory);
                return new I\MainControls\Factory($sig_gen, $slate_factory);
            }
            public function legacy($content) : C\Legacy\Legacy
            {
                $sig_gen = new I\SignalGenerator();
                return new I\Legacy\Legacy($content, $sig_gen);
            }
        };
        $factory->button_factory = $this->button_factory;
        return $factory;
    }

    public function testRendering() : void
    {
        $r = $this->getDefaultRenderer();
        $icon = $this->icon_factory->custom('', '');

        $sf = $this->factory->slate();
        $slate = $sf->combined('1', $icon)
            ->withAdditionalEntry(
                $sf->combined('1.1', $icon)
                    ->withAdditionalEntry(
                        $sf->combined('1.1.1', $icon)
                    )
            );

        $toolslate = $sf->legacy('Help', $icon, new I\Legacy\Legacy('Help', new I\SignalGenerator()));

        $mb = $this->factory->mainBar()
            ->withAdditionalEntry('test1', $this->getButton())
            ->withAdditionalEntry('test2', $this->getButton())
            ->withAdditionalEntry('slate', $slate)
            ->withToolsButton($this->getButton())
            ->withAdditionalToolEntry('tool1', $toolslate);

        $html = $r->render($mb);

        $expected = <<<EOT
			<div class="il-maincontrols-mainbar" id="id_16">
				<nav class="il-mainbar" aria-label="mainbar_aria_label">

					<div class="il-mainbar-tools-button">
						<button class="btn btn-bulky" id="id_14"><img class="icon custom small" src="" alt=""/><span class="bulky-label">TestEntry</span></button>
					</div>

					<div class="il-mainbar-triggers">
						<ul class="il-mainbar-entries" role="menubar" style="visibility: hidden">
							<li role="none"><button class="btn btn-bulky" data-action="#" id="id_1" role="menuitem" ><img class="icon custom small" src="" alt=""/><span class="bulky-label">TestEntry</span></button></li>
							<li role="none"><button class="btn btn-bulky" data-action="#" id="id_2" role="menuitem" ><img class="icon custom small" src="" alt=""/><span class="bulky-label">TestEntry</span></button></li>
							<li role="none"><button class="btn btn-bulky" id="id_3" role="menuitem" ><img class="icon custom small" src="" alt=""/><span class="bulky-label">1</span></button></li>
							<li role="none"><button class="btn btn-bulky" id="id_9" role="menuitem" ><span class="glyph" aria-label="show_more" role="img"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></span><span class="bulky-label">mainbar_more_label</span></button></li>
						</ul>
					</div>
				</nav>

				<div class="il-mainbar-slates">
					<div class="il-mainbar-tools-entries">
						<ul class="il-mainbar-tools-entries-bg" role="menubar">
							<li class="il-mainbar-tool-trigger-item" role="none">
								<button class="btn btn-bulky" id="id_11" role="menuitem"><img class="icon custom small" src="" alt=""/><span class="bulky-label">Help</span></button>
							</li>
						</ul>
					</div>
					<div class="il-maincontrols-slate disengaged" id="id_8" data-depth-level="1" role="menu">
						<div class="il-maincontrols-slate-content" data-replace-marker="content">
							<button class="btn btn-bulky" id="id_4" ><img class="icon custom small" src="" alt=""/><span class="bulky-label">1.1</span></button>

							<div class="il-maincontrols-slate disengaged" id="id_7" data-depth-level="2">
								<div class="il-maincontrols-slate-content" data-replace-marker="content">
									<button class="btn btn-bulky" id="id_5" ><img class="icon custom small" src="" alt=""/><span class="bulky-label">1.1.1</span></button>

									<div class="il-maincontrols-slate disengaged" id="id_6" data-depth-level="3">
										<div class="il-maincontrols-slate-content" data-replace-marker="content"></div>
									</div>
								</div>
							</div>

						</div>
					</div>

					<div class="il-maincontrols-slate disengaged" id="id_10" data-depth-level="1" role="menu">
						<div class="il-maincontrols-slate-content" data-replace-marker="content"></div>
					</div>
					
					<div class="il-maincontrols-slate disengaged" id="id_13" data-depth-level="1" role="menu">
						<div class="il-maincontrols-slate-content" data-replace-marker="content">Help</div>
					</div>


					<div class="il-mainbar-close-slates">
						<button class="btn btn-bulky" id="id_15" >
							<span class="glyph" aria-label="back" role="img"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></span>
							<span class="bulky-label">close</span></button>
					</div>
				</div>
			</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
