<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;

/**
 * Test on Repository Object card implementation.
 */
class RepositoryObjectTest extends ILIAS_UI_TestBase
{
    /**
     * @return \ILIAS\UI\Implementation\Factory
     */
    public function getFactory()
    {
        $mocks = [
            'button' => $this->createMock(C\Button\Factory::class),
            'divider' => $this->createMock(C\Divider\Factory::class),
        ];
        $factory = new class($mocks) extends NoUIFactory {
            public function __construct($mocks)
            {
                $this->mocks = $mocks;
            }
            public function legacy($content)
            {
                $f = new I\Component\Legacy\Factory(new I\Component\SignalGenerator());
                return $f->legacy($content);
            }
            public function button()
            {
                return $this->mocks['button'];
            }
            public function divider()
            {
                return $this->mocks['divider'];
            }
            public function symbol() : C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory()
                );
            }
        };
        return $factory;
    }

    private function getCardFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Card\Factory();
    }

    private function getBaseCard()
    {
        $cf = $this->getCardFactory();
        $image = new I\Component\Image\Image("standard", "src", "alt");

        return $cf->repositoryObject("Card Title", $image);
    }

    public function test_implements_factory_interface()
    {
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Card\\RepositoryObject", $this->getBaseCard());
    }

    public function test_with_object_icon()
    {
        $icon = new I\Component\Symbol\Icon\Standard("crs", 'Course', 'medium', false);
        $card = $this->getBaseCard();
        $card = $card->withObjectIcon($icon);

        $this->assertEquals($card->getObjectIcon(), $icon);
    }

    public function test_with_progress()
    {
        $progressmeter = new I\Component\Chart\ProgressMeter\Mini(100, 70);
        $card = $this->getBaseCard();
        $card = $card->withProgress($progressmeter);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Chart\\ProgressMeter\\Mini", $progressmeter);
        $this->assertEquals($progressmeter, $card->getProgress());
    }

    public function test_with_certificate_icon()
    {
        $card = $this->getBaseCard();
        $card_with_cert_true = $card->withCertificateIcon(true);
        $card_with_cert_false = $card->withCertificateIcon(false);

        $this->assertNull($card->getCertificateIcon());
        $this->assertTrue($card_with_cert_true->getCertificateIcon());
        $this->assertFalse($card_with_cert_false->getCertificateIcon());
    }

    public function test_with_actions()
    {
        $f = $this->getFactory();
        $items = array(
            $f->button()->shy("Go to Course", "#"),
            $f->button()->shy("Go to Portfolio", "#"),
            $f->divider()->horizontal(),
            $f->button()->shy("ilias.de", "http://www.ilias.de")
        );

        $dropdown = new I\Component\Dropdown\Standard($items);
        $card = $this->getBaseCard();
        $card = $card->withActions($dropdown);

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Dropdown\\Standard", $dropdown);
        $this->assertEquals($card->getActions(), $dropdown);
    }

    public function test_render_with_object_icon()
    {
        $r = $this->getDefaultRenderer();

        $icon = new I\Component\Symbol\Icon\Standard("crs", 'Course', 'medium', false);
        $c = $this->getBaseCard();
        $c = $c->withObjectIcon($icon);

        $html = $this->brutallyTrimHTML($r->render($c));

        $expected_html = $this->brutallyTrimHTML(<<<EOT
<div class="il-card thumbnail">
	<div class="il-card-repository-head">
		<div>
			<img class="icon crs medium" src="./templates/default/images/icon_crs.svg" alt="Course" />
		</div>
		<div>
			
		</div>
		<div class="il-card-repository-dropdown">
			
		</div>
	</div>
    <div class="il-card-image-container"><img src="src" class="img-standard" alt="alt" /></div>
	<div class="card-no-highlight"></div>
    <div class="caption card-title">Card Title</div>
</div>
EOT);

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_certificate_icon()
    {
        $r = $this->getDefaultRenderer();
        $c = $this->getBaseCard();

        //TODO get skin fail?
        $c = $c->withCertificateIcon(true);

        $html = $this->brutallyTrimHTML($r->render($c));

        $expected_html = $this->brutallyTrimHTML(<<<EOT
<div class="il-card thumbnail">
	
	<div class="il-card-repository-head">
		<div>
			
		</div>
		<div>
			<img class="icon cert medium outlined" src="./templates/default/images/outlined/icon_cert.svg" alt="Certificate" />
		</div>
		<div class="il-card-repository-dropdown">
			
		</div>
	</div>
    <div class="il-card-image-container"><img src="src" class="img-standard" alt="alt" /></div>
	<div class="card-no-highlight"></div>
    <div class="caption card-title">Card Title</div>
</div>
EOT);

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_progressmeter()
    {
        $r = $this->getDefaultRenderer();
        $c = $this->getBaseCard();
        $prg = new I\Component\Chart\ProgressMeter\Mini(100, 80);
        $c = $c->withProgress($prg);

        $html = $this->brutallyTrimHTML($r->render($c));

        $expected_html = $this->brutallyTrimHTML('
                <div class="il-card thumbnail">
                   <div class="il-card-repository-head">
                      <div></div>
                      <div>
                         <div class="il-chart-progressmeter-box il-chart-progressmeter-mini">
                            <div class="il-chart-progressmeter-container">
                               <svg viewBox="0 0 50 40" class="il-chart-progressmeter-viewbox">
                                  <path class="il-chart-progressmeter-circle-bg" stroke-dasharray="100, 100" d="M9,35 q-4.3934,-4.3934 -4.3934,-10.6066 a1,1 0 1,1 40,0 q0,6.2132 -4.3934,10.6066"></path>
                                  <path class="il-chart-progressmeter-circle no-success" stroke-dasharray="69.2, 100" d="M9,35 q-4.3934,-4.3934 -4.3934,-10.6066 a1,1 0 1,1 40,0 q0,6.2132 -4.3934,10.6066"></path>
                                  <path class="il-chart-progressmeter-needle no-needle" stroke-dasharray="100, 100" d="M25,10 l0,15" style="transform: rotate(deg)"></path>
                               </svg>
                            </div>
                         </div>
                      </div>
                      <div class="il-card-repository-dropdown"></div>
                   </div>
                   <div class="il-card-image-container"><img src="src" class="img-standard" alt="alt"/></div>
                   <div class="card-no-highlight"></div>
                   <div class="caption card-title">Card Title</div>
                </div>');

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_actions()
    {
        $r = $this->getDefaultRenderer();
        $c = $this->getBaseCard();
        $items = array(
            new I\Component\Button\Shy("Visit ILIAS", "https://www.ilias.de")
        );
        $dropdown = new I\Component\Dropdown\Standard($items);
        $c = $c->withActions($dropdown);
        $html = $this->brutallyTrimHTML($r->render($c));

        $expected_html = $this->brutallyTrimHTML('
            <div class="il-card thumbnail">
                <div class="il-card-repository-head">
                    <div></div>
                    <div></div>
                    <div class="il-card-repository-dropdown">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="actions" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><button class="btn btn-link" data-action="https://www.ilias.de" id="id_2">Visit ILIAS</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="il-card-image-container"><img src="src" class="img-standard" alt="alt" /></div>
                <div class="card-no-highlight"></div>
                <div class="caption card-title">Card Title</div>
            </div>
        ');

        $this->assertHTMLEquals($expected_html, $html);
    }
}
