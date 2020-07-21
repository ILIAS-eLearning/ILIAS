<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Tests for the Footer.
 */
class FooterTest extends ILIAS_UI_TestBase
{
    private $links = [];
    private $text = '';
    private $perm_url = '';

    public function setUp() : void
    {
        $f = new I\Link\Factory();
        $this->links = [
            $f->standard("Goto ILIAS", "http://www.ilias.de"),
            $f->standard("go up", "#")
        ];
        $this->text = 'footer text';
        $this->perm_url = 'http://www.ilias.de/goto.php?target=xxx_123';
    }

    protected function getFactory()
    {
        $sig_gen = new I\SignalGenerator();
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
        $factory = new I\MainControls\Factory($sig_gen, $slate_factory);
        return $factory;
    }

    public function testConstruction()
    {
        $footer = $this->getFactory()->footer($this->links, $this->text);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\Footer",
            $footer
        );
        return $footer;
    }

    public function testConstructionNoLinks()
    {
        $footer = $this->getFactory()->footer([], $this->text);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\Footer",
            $footer
        );
        return $footer;
    }

    /**
     * @depends testConstruction
     */
    public function testGetLinks($footer)
    {
        $this->assertEquals(
            $this->links,
            $footer->getLinks()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetText($footer)
    {
        $this->assertEquals(
            $this->text,
            $footer->getText()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetAndSetModalsWithTrigger(C\MainControls\Footer $footer)
    {
        $bf = new I\Button\Factory();
        $mf = new I\Modal\Factory(new SignalGenerator());
        $dummyComponent = new class() implements \ILIAS\UI\Component\Component {
            public function getCanonicalName()
            {
                return 'dummy';
            }
        };

        $shyButton1 = $bf->shy('Button1', '#');
        $shyButton2 = $bf->shy('Button2', '#');

        $modal1 = $mf->roundtrip('Modal1', $dummyComponent);
        $modal2 = $mf->roundtrip('Modal2', $dummyComponent);

        $footer = $footer
            ->withAdditionalModalAndTrigger($modal1, $shyButton1)
            ->withAdditionalModalAndTrigger($modal2, $shyButton2);

        $this->assertCount(2, $footer->getModals());
        $this->assertEquals([$modal1, $shyButton1], $footer->getModals()[0]);
        $this->assertEquals([$modal2, $shyButton2], $footer->getModals()[1]);
    }

    /**
     * @depends testConstruction
     */
    public function testPermanentURL($footer)
    {
        $df = new \ILIAS\Data\Factory();
        $footer = $footer->withPermanentURL($df->uri($this->perm_url));
        $perm_url = $footer->getPermanentURL();
        $this->assertInstanceOf("\\ILIAS\\Data\\URI", $perm_url);
        $this->assertEquals(
            $perm_url->getBaseURI() . '?' . $perm_url->getQuery(),
            $this->perm_url
        );
        return $footer;
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function listing()
            {
                return new I\Listing\Factory();
            }
        };
        return $factory;
    }

    /**
     * @depends testConstruction
     */
    public function testRendering($footer)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
		<div class="il-maincontrols-footer">
			<div class="il-footer-content">
				<div class="il-footer-text">
					footer text
				</div>

				<div class="il-footer-links">
					<ul>
						<li><a href="http://www.ilias.de" >Goto ILIAS</a></li>
						<li><a href="#" >go up</a></li>
					</ul>
				</div>
			</div>
		</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    /**
     * @depends testConstructionNoLinks
     */
    public function testRenderingNoLinks($footer)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
		<div class="il-maincontrols-footer">
			<div class="il-footer-content">
				<div class="il-footer-text">
					footer text
				</div>
			</div>
		</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }

    /**
     * @depends testPermanentURL
     */
    public function testRenderingPermUrl($footer)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
        <div class="il-maincontrols-footer">
            <div class="il-footer-content">
                <div class="il-footer-permanent-url">perma_link<input id="current_perma_link" type="text" value="http://www.ilias.de/goto.php?target=xxx_123" readonly="readOnly">
                </div>

                <div class="il-footer-text">footer text</div>

                <div class="il-footer-links">
                    <ul>
                        <li><a href="http://www.ilias.de" >Goto ILIAS</a></li>
                        <li><a href="#" >go up</a></li>
                    </ul>
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
