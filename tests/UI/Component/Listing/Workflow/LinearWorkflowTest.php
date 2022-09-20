<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
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

    public function test_implements_factory_interface(): void
    {
        $this->assertInstanceOf(Workflow\Workflow::class, $this->wf);
    }

    public function test_constructor_params(): void
    {
        $this->assertEquals($this->title, $this->wf->getTitle());
        $this->assertEquals($this->steps, $this->wf->getSteps());
        $this->assertEquals(0, $this->wf->getActive());
    }

    public function test_constructor(): void
    {
        $this->assertEquals($this->title, $this->wf->getTitle());
        $this->assertEquals($this->steps, $this->wf->getSteps());
        $this->assertEquals(0, $this->wf->getActive());
    }

    public function test_amount_of_steps(): void
    {
        $this->assertEquals(count($this->steps), $this->wf->getAmountOfSteps());
    }

    public function test_active(): void
    {
        $wf = $this->wf->withActive(1);
        $this->assertEquals(1, $wf->getActive());
    }

    public function test_withActive_throws(): void
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
}
