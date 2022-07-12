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
 
namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Renderer extends AbstractComponentRenderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Linear) {
            return $this->render_linear($component, $default_renderer);
        }
        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function render_linear(Linear $component, RendererInterface $default_renderer) : string
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

    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Listing\Workflow\Workflow::class
        ];
    }
}
