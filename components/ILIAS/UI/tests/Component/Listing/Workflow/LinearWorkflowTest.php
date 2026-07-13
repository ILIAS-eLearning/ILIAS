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

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Listing\Workflow;

class LinearWorkflowTest extends ILIAS_UI_TestBase
{
    protected string $title;
    protected array $steps;
    protected Workflow\Linear $wf;

    protected function buildFactory(): Workflow\Factory
    {
        return new ILIAS\UI\Implementation\Component\Listing\Workflow\Factory();
    }

    public function setUp(): void
    {
        $f = $this->buildFactory();
        $this->title = 'title';
        $this->steps = [
            $f->step(''),
            $f->step('')
        ];
        $this->wf = $f->linear($this->title, $this->steps);
    }

    public function testImplementsFactoryInterface(): void
    {
        $this->assertInstanceOf(Workflow\Workflow::class, $this->wf);
    }

    public function testConstructorParams(): void
    {
        $this->assertEquals($this->title, $this->wf->getTitle());
        $this->assertEquals($this->steps, $this->wf->getSteps());
        $this->assertEquals(0, $this->wf->getActive());
    }

    public function testConstructor(): void
    {
        $this->assertEquals($this->title, $this->wf->getTitle());
        $this->assertEquals($this->steps, $this->wf->getSteps());
        $this->assertEquals(0, $this->wf->getActive());
    }

    public function testAmountOfSteps(): void
    {
        $this->assertEquals(count($this->steps), $this->wf->getAmountOfSteps());
    }

    public function testActive(): void
    {
        $wf = $this->wf->withActive(1);
        $this->assertEquals(1, $wf->getActive());
    }

    public function testWithActiveThrows(): void
    {
        $raised = false;
        try {
            $this->wf->withActive(-1);
            $this->assertFalse("This should not happen.");
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $this->wf->withActive(2);
            $this->assertFalse("This should not happen.");
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testLinearWorkflowRendering(): void
    {
        $expected = <<<EOT
            <div class="il-workflow linear" id="id_1">
                <div class="il-workflow-header">
                    <h3 class="il-workflow-title">title</h3>
                </div>
                <ul class="il-workflow-container">
                    <li class="il-workflow-step first available not-started">
                        <div class="text">
                            <span class="il-workflow-step-label"></span>
                            <span class="il-workflow-step-description"></span>
                        </div>
                    </li>
                    <li class="il-workflow-step last active not-started" aria-current="step">
                        <div class="text">
                            <span class="il-workflow-step-label"></span>
                            <span class="il-workflow-step-description"></span>
                        </div>
                    </li>
                </ul>
            </div>
EOT;
        $actual = $this->getDefaultRenderer()->render($wf = $this->wf->withActive(1));
        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($actual),
        );
    }
}
