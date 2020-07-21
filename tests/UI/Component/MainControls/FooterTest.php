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
        $signalGenerator = new SignalGenerator();
        $mf = new I\Modal\Factory($signalGenerator);
        $legacy = new ILIAS\UI\Implementation\Component\Legacy\Legacy('PhpUnit', $signalGenerator);

        $shyButton1 = $bf->shy('Button1', '#');
        $shyButton2 = $bf->shy('Button2', '#');

        $modal1 = $mf->roundtrip('Modal1', $legacy);
        $modal2 = $mf->roundtrip('Modal2', $legacy);

        $shyButton1 = $shyButton1->withOnClick($modal1->getShowSignal());
        $shyButton2 = $shyButton2->withOnClick($modal2->getShowSignal());

        $footer = $footer
            ->withAdditionalModalAndTrigger($modal1, $shyButton1)
            ->withAdditionalModalAndTrigger($modal2, $shyButton2);

        $this->assertCount(2, $footer->getModals());
        $this->assertEquals([$modal1, $shyButton1], $footer->getModals()[0]);
        $this->assertEquals([$modal2, $shyButton2], $footer->getModals()[1]);

        return $footer;
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

    /**
     * @depends testGetAndSetModalsWithTrigger
     */
    public function testRenderingModalsWithTriggers(C\MainControls\Footer $footer)
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
        <div class="il-maincontrols-footer">
            <div class="il-footer-content">
                <div class="il-footer-text">footer text</div>

                <div class="il-footer-links">
                    <ul>
                        <li><a href="http://www.ilias.de" >Goto ILIAS</a></li>
                        <li><a href="#" >go up</a></li>
                        <li><button class="btn btn-link" id="id_1" >Button1</button></li>
                        <li><button class="btn btn-link" id="id_2">Button2</button></li>
                    </ul>
                </div>
            </div>
            <div class="il-footer-modals">
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_3">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">Modal1</h4>
                            </div>
                            <div class="modal-body">PhpUnit</div>
                            <div class="modal-footer">
                                <a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade il-modal-roundtrip" tabindex="-1" role="dialog" id="id_5">
                    <div class="modal-dialog" role="document" data-replace-marker="component">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">Modal2</h4>
                            </div>
                            <div class="modal-body">PhpUnit</div>
                            <div class="modal-footer">
                                <a class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</a>
                            </div>
                        </div>
                    </div>
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
