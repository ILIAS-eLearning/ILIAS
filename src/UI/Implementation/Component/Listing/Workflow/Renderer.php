<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Triggerer;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Listing\Workflow\Linear) {
            return $this->render_linear($component, $default_renderer);
        }
    }

    /**
     * @param Component\Listing\Linear $component
     * @param RendererInterface $default_renderer
     * @return string
     */
    protected function render_linear(Component\Listing\Workflow\Linear $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.linear.html", true, true);
        $tpl->setVariable("TITLE", $component->getTitle());

        foreach ($component->getSteps() as $index => $step) {
            $tpl->setCurrentBlock("step");

            $action = $step->getAction();
            if (!is_null($action) && $step->getAvailability() === Component\Listing\Workflow\Step::AVAILABLE) {
                $f = $this->getUIFactory();
                $shy = $f->button()->shy($step->getLabel(), $action);
                $tpl->setVariable("LABEL", $default_renderer->render($shy));
            } else {
                $tpl->setVariable("LABEL", $step->getLabel());
            }

            $tpl->setVariable("DESCRIPTION", $step->getDescription());

            if ($index === 0) {
                $tpl->touchBlock('first');
                $tpl->setCurrentBlock("step");
            }
            if ($index === $component->getAmountOfSteps() - 1) {
                $tpl->touchBlock('last');
                $tpl->setCurrentBlock("step");
            }

            if ($index === $component->getActive()) {
                $tpl->touchBlock('active');
            } else {
                switch ($step->getAvailability()) {
                    case Component\Listing\Workflow\Step::AVAILABLE:
                        $tpl->touchBlock('available');
                        break;
                    case Component\Listing\Workflow\Step::NOT_AVAILABLE:
                        $tpl->touchBlock('not_available');
                        break;
                    case Component\Listing\Workflow\Step::NOT_ANYMORE:
                        $tpl->touchBlock('not_anymore');
                        break;
                }
            }
            $tpl->setCurrentBlock("step");

            switch ($step->getStatus()) {
                case Component\Listing\Workflow\Step::NOT_STARTED:
                    $tpl->touchBlock('status_notstarted');
                    break;
                case Component\Listing\Workflow\Step::IN_PROGRESS:
                    $tpl->touchBlock('status_inprogress');
                    break;
                case Component\Listing\Workflow\Step::SUCCESSFULLY:
                    $tpl->touchBlock('status_completed_successfully');
                    break;
                case Component\Listing\Workflow\Step::UNSUCCESSFULLY:
                    $tpl->touchBlock('status_completed_unsuccessfully');
                    break;
            }
            $tpl->setCurrentBlock("step");
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName()
    {
        return [
            Component\Listing\Workflow\Workflow::class
            //, Component\Listing\Workflow\Step::class
        ];
    }
}
