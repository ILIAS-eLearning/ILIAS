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
class BarStateTest extends \ILIAS_UI_TestBase
{
    protected string $indeterminate_status_value;
    protected string $determinate_status_value;
    protected string $success_status_value;
    protected string $failure_status_value;
    protected string $message;

    protected function setUp(): void
    {
        $this->indeterminate_status_value = I\Progress\State\Bar\Status::INDETERMINATE->value;
        $this->determinate_status_value = I\Progress\State\Bar\Status::DETERMINATE->value;
        $this->success_status_value = I\Progress\State\Bar\Status::SUCCESS->value;
        $this->failure_status_value = I\Progress\State\Bar\Status::FAILURE->value;
        $this->message = sha1('progress_bar_state_message');

        parent::setUp();
    }

    public function testIndeterminateConstruction(): void
    {
        /** @var I\Progress\State\Bar\State $indeterminate */
        $indeterminate = $this->getUIFactory()->progress()->state()->bar()->indeterminate($this->message);

        $this->assertEquals($indeterminate->getStatus()->value, $this->indeterminate_status_value);
        $this->assertNull($indeterminate->getVisualProgressValue());
        $this->assertEquals($indeterminate->getMessage(), $this->message);
    }

    public function testDeterminateConstruction(): void
    {
        $progress = 55;
        /** @var I\Progress\State\Bar\State $determinate */
        $determinate = $this->getUIFactory()->progress()->state()->bar()->determinate($progress, $this->message);

        $this->assertEquals($determinate->getStatus()->value, $this->determinate_status_value);
        $this->assertEquals($determinate->getVisualProgressValue(), $progress);
        $this->assertEquals($determinate->getMessage(), $this->message);
    }

    public function testDeterminateConstructionWithNegativeNumber(): void
    {
        $invalid_progress = -1;
        $this->expectException(\InvalidArgumentException::class);
        $determinate = $this->getUIFactory()->progress()->state()->bar()->determinate($invalid_progress);
    }

    public function testDeterminateConstructionWithMaximumValue(): void
    {
        $invalid_progress = 100;
        $this->expectException(\InvalidArgumentException::class);
        $determinate = $this->getUIFactory()->progress()->state()->bar()->determinate($invalid_progress);
    }

    public function testSuccessConstruction(): void
    {
        /** @var I\Progress\State\Bar\State $success */
        $success = $this->getUIFactory()->progress()->state()->bar()->success($this->message);

        $this->assertEquals($success->getStatus()->value, $this->success_status_value);
        $this->assertEquals($success->getVisualProgressValue(), I\Progress\Bar::MAX_VALUE);
        $this->assertEquals($success->getMessage(), $this->message);
    }

    public function testFailureConstruction(): void
    {
        /** @var I\Progress\State\Bar\State $failure */
        $failure = $this->getUIFactory()->progress()->state()->bar()->failure($this->message);

        $this->assertEquals($failure->getStatus()->value, $this->failure_status_value);
        $this->assertEquals($failure->getVisualProgressValue(), I\Progress\Bar::MAX_VALUE);
        $this->assertEquals($failure->getMessage(), $this->message);
    }

    public function testRenderIndeterminate(): void
    {
        $state = $this->getUIFactory()->progress()->state()->bar()->indeterminate($this->message);

        $renderer = $this->getDefaultRenderer();

        $actual_html = $renderer->render($state);

        $expected_html = <<<EOT
<div class="c-progress c-progress-bar--state">
    <section data-section="progress"><progress max="100"></progress></section>
    <section data-section="status" data-status="$this->indeterminate_status_value"></section>
    <section data-section="message">$this->message</section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testRenderDeterminate(): void
    {
        $progress = 55;
        $state = $this->getUIFactory()->progress()->state()->bar()->determinate($progress, $this->message);

        $renderer = $this->getDefaultRenderer();

        $actual_html = $renderer->render($state);

        $expected_html = <<<EOT
<div class="c-progress c-progress-bar--state">
    <section data-section="progress"><progress value="$progress" max="100"></progress></section>
    <section data-section="status" data-status="$this->determinate_status_value"></section>
    <section data-section="message">$this->message</section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testRenderSuccess(): void
    {
        $state = $this->getUIFactory()->progress()->state()->bar()->success($this->message);

        $renderer = $this->getDefaultRenderer();

        $actual_html = $renderer->render($state);

        $expected_html = <<<EOT
<div class="c-progress c-progress-bar--state">
    <section data-section="progress"><progress value="100" max="100"></progress></section>
    <section data-section="status" data-status="$this->success_status_value"></section>
    <section data-section="message">$this->message</section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testRenderFailure(): void
    {
        $state = $this->getUIFactory()->progress()->state()->bar()->failure($this->message);

        $renderer = $this->getDefaultRenderer();

        $actual_html = $renderer->render($state);

        $expected_html = <<<EOT
<div class="c-progress c-progress-bar--state">
    <section data-section="progress"><progress value="100" max="100"></progress></section>
    <section data-section="status" data-status="$this->failure_status_value"></section>
    <section data-section="message">$this->message</section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function getUIFactory(): \NoUIFactory
    {
        $bar_state_factory = new I\Progress\State\Bar\Factory();

        $state_factory = $this->createMock(I\Progress\State\Factory::class);
        $state_factory->method('bar')->willReturn($bar_state_factory);

        $progress_factory = $this->createMock(I\Progress\Factory::class);
        $progress_factory->method('state')->willReturn($state_factory);

        return new class ($progress_factory) extends \NoUIFactory {
            public function __construct(
                protected I\Progress\Factory $progress_factory,
            ) {
            }
            public function progress(): I\Progress\Factory
            {
                return $this->progress_factory;
            }
        };
    }
}
