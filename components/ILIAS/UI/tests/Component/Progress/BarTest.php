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

namespace Component\Progress;

require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class BarTest extends \ILIAS_UI_TestBase
{
    protected I\SignalGeneratorInterface $signal_generator;
    protected I\Symbol\Glyph\Factory $glyph_factory;
    protected I\Symbol\Glyph\Glyph $glyph_mock;
    protected string $glyph_html;
    protected \ILIAS\Data\URI $uri_mock;
    protected string $uri;

    protected function setUp(): void
    {
        $this->glyph_html = sha1(C\Symbol\Glyph\Glyph::class);

        $this->glyph_mock = $this->createMock(I\Symbol\Glyph\Glyph::class);
        $this->glyph_mock->method('getCanonicalName')->willReturn($this->glyph_html);

        $this->glyph_factory = $this->createMock(I\Symbol\Glyph\Factory::class);
        $this->glyph_factory->method('apply')->willReturn($this->glyph_mock);
        $this->glyph_factory->method('close')->willReturn($this->glyph_mock);

        $this->uri = sha1('https://example.com?foo=bar&bar=foobar');

        $this->uri_mock = $this->createMock(\ILIAS\Data\URI::class);
        $this->uri_mock->method('__toString')->willReturn($this->uri);

        $this->signal_generator = new \IncrementalSignalGenerator();

        parent::setUp();
    }

    public function testRender(): void
    {
        $label = sha1('progress_bar_label');

        $progress_bar = $this->getUIFactory()->progress()->bar($label);

        $renderer = $this->getDefaultRenderer(null, [$this->glyph_mock]);

        $actual_html = $renderer->render($progress_bar);

        $expected_html = <<<EOT
<div class="c-progress c-progress-bar">
    <div class="c-progress-bar__label"><label for="id_1">$label</label>
        <span data-status="success" class="c-progress-bar--success hidden">$this->glyph_html</span>
        <span data-status="failure" class="c-progress-bar--failure hidden">$this->glyph_html</span>
    </div>
    <progress id="id_1" class="c-progress-bar__progress" value="0" max="100"></progress>
    <div class="c-progress-bar__message text-left invisible" aria-live="polite"></div>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testRenderWithAsyncUrl(): void
    {
        $label = sha1('progress_bar_label');

        $progress_bar = $this->getUIFactory()->progress()->bar($label, $this->uri_mock);

        $renderer = $this->getDefaultRenderer(null, [$this->glyph_mock]);

        $actual_html = $renderer->render($progress_bar);

        $expected_html = "data-url=\"$this->uri\"";

        $this->assertTrue(
            str_contains($actual_html, $expected_html)
        );
    }

    public function getUIFactory(): \NoUIFactory
    {
        $symbol_factory = new I\Symbol\Factory(
            $this->createMock(I\Symbol\Icon\Factory::class),
            $this->glyph_factory,
            $this->createMock(I\Symbol\Avatar\Factory::class),
        );

        $progress_factory = new I\Progress\Factory(
            $this->createMock(C\Progress\AsyncRefreshInterval::class),
            $this->signal_generator,
            $this->createMock(I\Progress\State\Factory::class),
        );

        return new class ($progress_factory, $symbol_factory) extends \NoUIFactory {
            public function __construct(
                protected I\Progress\Factory $progress_factory,
                protected I\Symbol\Factory $symbol_factory,
            ) {
            }
            public function progress(): I\Progress\Factory
            {
                return $this->progress_factory;
            }
            public function symbol(): I\Symbol\Factory
            {
                return $this->symbol_factory;
            }
        };
    }
}
