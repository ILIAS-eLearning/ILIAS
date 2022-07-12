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
 
use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\MainControls\SystemInfo;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol\Factory;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;
use ILIAS\UI\Component\MainControls\SystemInfo as SystemInfoAlias;

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

/**
 * Class SystemInfoTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SystemInfoTest extends ILIAS_UI_TestBase
{
    private SignalGenerator $sig_gen;

    public function setUp() : void
    {
        parent::setUp();
        $this->sig_gen = new SignalGenerator();
    }

    public function testRenderingDefault() : void
    {
        $headline = 'That\'s one small step for [a] man';
        $information = 'Lorem IPsum dolor sit amet';
        $r = $this->getDefaultRenderer();
        $system_info = new SystemInfo($this->sig_gen, $headline, $information);

        // Neutral
        $expected = <<<EOT
<div id="id" class="container-fluid il-system-info il-system-info-neutral" data-close-uri="" aria-live="polite" aria-labelledby="il-system-info-headline" aria-describedby="il-system-info-headline">
    <div class="il-system-info-content-wrapper">
        <div class="il-system-info-content">
            <span class="il-system-info-headline">$headline</span>
            <span class="il-system-info-body">$information</span>
        </div>
    </div>
    <div class="il-system-info-actions">
        <span class="il-system-info-more">
            <a class="glyph" href="#" aria-label="show_more"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></a>
        </span>
        <span class="il-system-info-close"></span>
    </div>
</div>
EOT;

        $actual = $r->render($system_info);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual)
        );
    }

    public function testRenderingNeutral() : void
    {
        $headline = 'That\'s one small step for [a] man';
        $information = 'Lorem IPsum dolor sit amet';
        $r = $this->getDefaultRenderer();
        $system_info = (new SystemInfo($this->sig_gen, $headline, $information))
            ->withDenotation(SystemInfoAlias::DENOTATION_NEUTRAL);

        // Neutral
        $expected = <<<EOT
<div id="id" class="container-fluid il-system-info il-system-info-neutral" data-close-uri="" aria-live="polite" aria-labelledby="il-system-info-headline" aria-describedby="il-system-info-headline">
    <div class="il-system-info-content-wrapper">
        <div class="il-system-info-content">
            <span class="il-system-info-headline">$headline</span>
            <span class="il-system-info-body">$information</span>
        </div>
    </div>
    <div class="il-system-info-actions">
        <span class="il-system-info-more">
            <a class="glyph" href="#" aria-label="show_more"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></a>
        </span>
        <span class="il-system-info-close"></span>
    </div>
</div>
EOT;

        $actual = $r->render($system_info);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual)
        );
    }

    public function testRenderingImportant() : void
    {
        $headline = 'That\'s one small step for [a] man';
        $information = 'Lorem IPsum dolor sit amet';
        $r = $this->getDefaultRenderer();
        $system_info = (new SystemInfo($this->sig_gen, $headline, $information))
            ->withDenotation(SystemInfoAlias::DENOTATION_IMPORTANT);

        $actual = $r->render($system_info);
        $expected = <<<EOT
<div id="id" class="container-fluid il-system-info il-system-info-important" data-close-uri="" aria-live="polite" aria-labelledby="il-system-info-headline" aria-describedby="il-system-info-headline">
    <div class="il-system-info-content-wrapper">
        <div class="il-system-info-content">
            <span class="il-system-info-headline">$headline</span>
            <span class="il-system-info-body">$information</span>
        </div>
    </div>
    <div class="il-system-info-actions">
        <span class="il-system-info-more">
            <a class="glyph" href="#" aria-label="show_more"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></a>
        </span>
        <span class="il-system-info-close"></span>
    </div>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual)
        );
    }

    public function testRenderingBreaking() : void
    {
        $headline = 'That\'s one small step for [a] man';
        $information = 'Lorem IPsum dolor sit amet';
        $r = $this->getDefaultRenderer();
        $system_info = (new SystemInfo($this->sig_gen, $headline, $information))
            ->withDenotation(SystemInfoAlias::DENOTATION_BREAKING);

        // Breaking
        $expected = <<<EOT
<div id="id" class="container-fluid il-system-info il-system-info-breaking" data-close-uri="" role="alert" aria-labelledby="il-system-info-headline" aria-describedby="il-system-info-headline">
    <div class="il-system-info-content-wrapper">
        <div class="il-system-info-content">
            <span class="il-system-info-headline">$headline</span>
            <span class="il-system-info-body">$information</span>
        </div>
    </div>
    <div class="il-system-info-actions">
        <span class="il-system-info-more">
            <a class="glyph" href="#" aria-label="show_more"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></a>
        </span>
        <span class="il-system-info-close"></span>
    </div>
</div>
EOT;

        $actual = $r->render($system_info);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual)
        );
    }


    public function testRenderingCloseAction() : void
    {
        $headline = 'That\'s one small step for [a] man';
        $information = 'Lorem IPsum dolor sit amet';
        $uri_string = 'http://one_giant_leap?for=mankind';
        $action = new URI($uri_string);
        $r = $this->getDefaultRenderer();
        $system_info = (new SystemInfo($this->sig_gen, $headline, $information))
            ->withDismissAction($action);

        $expected = <<<EOT
<div id="id" class="container-fluid il-system-info il-system-info-neutral" data-close-uri="$uri_string" aria-live="polite" aria-labelledby="il-system-info-headline" aria-describedby="il-system-info-headline">
    <div class="il-system-info-content-wrapper">
        <div class="il-system-info-content">
            <span class="il-system-info-headline">$headline</span>
            <span class="il-system-info-body">$information</span>
        </div>
    </div>
    <div class="il-system-info-actions">
        <span class="il-system-info-more">
            <a class="glyph" href="#" aria-label="show_more"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></a>
        </span>
        <span class="il-system-info-close"><a class="glyph" href="#" aria-label="close" id="id"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a></span>
    </div>
</div>
EOT;

        $actual = $r->render($system_info);
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual)
        );
    }

    public function getDefaultRenderer(
        JavaScriptBinding $js_binding = null,
        array $with_stub_renderings = []
    ) : TestDefaultRenderer {
        return parent::getDefaultRenderer(new class implements JavaScriptBinding {
            public function createId() : string
            {
                return "id";
            }

            public array $on_load_code = array();

            public function addOnLoadCode(string $code) : void
            {
                $this->on_load_code[] = $code;
            }

            public function getOnLoadCodeAsync() : string
            {
            }
        });
    }

    public function getUIFactory() : NoUIFactory
    {
        $factory = new class() extends NoUIFactory {
            public SignalGenerator $sig_gen;

            public function __construct()
            {
                $this->sig_gen = new SignalGenerator();
            }

            public function symbol() : ILIAS\UI\Component\Symbol\Factory
            {
                return new Factory(
                    new \ILIAS\UI\Implementation\Component\Symbol\Icon\Factory(),
                    new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory(),
                    new \ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory()
                );
            }

            public function mainControls() : \ILIAS\UI\Component\MainControls\Factory
            {
                return new \ILIAS\UI\Implementation\Component\MainControls\Factory(
                    $this->sig_gen,
                    new \ILIAS\UI\Implementation\Component\MainControls\Slate\Factory(
                        $this->sig_gen,
                        new \ILIAS\UI\Implementation\Component\Counter\Factory(),
                        $this->symbol()
                    )
                );
            }
        };
        $factory->sig_gen = $this->sig_gen;

        return $factory;
    }
}
