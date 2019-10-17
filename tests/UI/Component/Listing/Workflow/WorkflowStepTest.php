<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component\Listing\Workflow;

class WorkflowStepTest extends ILIAS_UI_TestBase
{
    protected function buildFactory()
    {
        return new ILIAS\UI\Implementation\Component\Listing\Workflow\Factory();
    }
    public function setUp()
    {
        $this->f = $this->buildFactory();
    }

    public function test_implements_factory_interface()
    {
        $step = $this->f->step('', '');
        $this->assertInstanceOf(Workflow\Step::class, $step);
    }

    public function test_constructor_params()
    {
        $label = 'label';
        $description = 'description';
        $step = $this->f->step($label, $description);
        $this->assertEquals($label, $step->getLabel());
        $this->assertEquals($description, $step->getDescription());
        $this->assertEquals(Workflow\Step::NOT_STARTED, $step->getStatus());
    }

    public function test_withStatus()
    {
        $status = Workflow\Step::SUCCESSFULLY;
        $step = $this->f->step('', '')->withStatus($status);
        $this->assertEquals($status, $step->getStatus());
    }

    public function test_withStatus_wrong_args()
    {
        $status = 100;
        $raised = false;
        try {
            $step = $this->f->step('', '')->withStatus($status);
        } catch (\InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
