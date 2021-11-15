<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Listing\Workflow;

class WorkflowStepTest extends ILIAS_UI_TestBase
{
    protected Workflow\Factory $f;

    protected function buildFactory() : Workflow\Factory
    {
        return new ILIAS\UI\Implementation\Component\Listing\Workflow\Factory();
    }

    public function setUp() : void
    {
        $this->f = $this->buildFactory();
    }

    public function test_implements_factory_interface() : void
    {
        $step = $this->f->step('');
        $this->assertInstanceOf(Workflow\Step::class, $step);
    }

    public function test_constructor_params() : void
    {
        $label = 'label';
        $description = 'description';
        $step = $this->f->step($label, $description);
        $this->assertEquals($label, $step->getLabel());
        $this->assertEquals($description, $step->getDescription());
        $this->assertEquals(Workflow\Step::NOT_STARTED, $step->getStatus());
    }

    public function test_withStatus() : void
    {
        $status = Workflow\Step::SUCCESSFULLY;
        $step = $this->f->step('')->withStatus($status);
        $this->assertEquals($status, $step->getStatus());
    }

    public function test_withStatus_wrong_args() : void
    {
        $status = 100;
        $raised = false;
        try {
            $this->f->step('')->withStatus($status);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
