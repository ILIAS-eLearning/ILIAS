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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data;

/**
 * Tests for the Footer.
 */
class FooterTest extends ILIAS_UI_TestBase
{
    private array $links = [];
    private string $text = '';
    private string $perm_url = '';

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

    protected function getFactory() : I\MainControls\Factory
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
        return new I\MainControls\Factory($sig_gen, $slate_factory);
    }

    public function testConstruction() : C\MainControls\Footer
    {
        $footer = $this->getFactory()->footer($this->links, $this->text);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\Footer",
            $footer
        );
        return $footer;
    }

    public function testConstructionNoLinks() : C\MainControls\Footer
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
    public function testGetLinks(C\MainControls\Footer $footer) : void
    {
        $this->assertEquals(
            $this->links,
            $footer->getLinks()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetText(C\MainControls\Footer $footer) : void
    {
        $this->assertEquals(
            $this->text,
            $footer->getText()
        );
    }

    /**
     * @depends testConstruction
     */
    public function testGetAndSetModalsWithTrigger(C\MainControls\Footer $footer) : C\MainControls\Footer
    {
        $bf = new I\Button\Factory();
        $signalGenerator = new SignalGenerator();
        $mf = new I\Modal\Factory($signalGenerator);
        $legacy = new ILIAS\UI\Implementation\Component\Legacy\Legacy('PhpUnit', $signalGenerator);

        $shyButton1 = $bf->shy('Button1', '#');
        $shyButton2 = $bf->shy('Button2', '#');

        $modal1 = $mf->roundtrip('Modal1', $legacy);
        $modal2 = $mf->roundtrip('Modal2', $legacy);

        $footer = $footer
            ->withAdditionalModalAndTrigger($modal1, $shyButton1)
            ->withAdditionalModalAndTrigger($modal2, $shyButton2);

        $this->assertCount(2, $footer->getModals());

        return $footer;
    }

    /**
     * @depends testConstruction
     */
    public function testPermanentURL(C\MainControls\Footer $footer) : C\MainControls\Footer
    {
        $df = new Data\Factory();
        $footer = $footer->withPermanentURL($df->uri($this->perm_url));
        $perm_url = $footer->getPermanentURL();
        $this->assertInstanceOf("\\ILIAS\\Data\\URI", $perm_url);
        $this->assertEquals(
            $perm_url->getBaseURI() . '?' . $perm_url->getQuery(),
            $this->perm_url
        );
        return $footer;
    }

    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function listing() : C\Listing\Factory
            {
                return new I\Listing\Factory();
            }

            public function button() :  C\Button\Factory
            {
                return new I\Button\Factory();
            }
        };
    }

    /**
     * @depends testConstruction
     */
    public function testRendering(C\MainControls\Footer $footer) : void
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
    public function testRenderingNoLinks(C\MainControls\Footer $footer) : void
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
    public function testRenderingPermUrl($footer) : void
    {
        $r = $this->getDefaultRenderer();
        $html = $r->render($footer);

        $expected = <<<EOT
        <div class="il-maincontrols-footer">
            <div class="il-footer-content">
                <div class="il-footer-permanent-url"><button class="btn btn-link" data-action="http://www.ilias.de/goto.php?target=xxx_123" id="id_1">copy_perma_link</button>
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
    public function testRenderingModalsWithTriggers(C\MainControls\Footer $footer) : void
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
                                <span class="modal-title">Modal1</span>
                            </div>
                            <div class="modal-body">PhpUnit</div>
                            <div class="modal-footer">
                                <button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button>
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
                                <span class="modal-title">Modal2</span>
                            </div>
                            <div class="modal-body">PhpUnit</div>
                            <div class="modal-footer">
                                <button class="btn btn-default" data-dismiss="modal" aria-label="Close">cancel</button>
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
