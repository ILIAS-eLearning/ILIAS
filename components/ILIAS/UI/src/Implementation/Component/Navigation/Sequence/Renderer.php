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

namespace ILIAS\UI\Implementation\Component\Navigation\Sequence;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as I;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof I\Navigation\Sequence\Sequence) {
            return $this->renderSequence($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderSequence(
        Sequence $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.sequence.html", true, true);

        $binding = $component->getSegmentRetrieval();
        $request = $component->getRequest();
        $vc_data = $component->getViewControls()?->getData() ?? [];
        $filter_data = [];
        $positions = $binding->getAllPositions(
            $request,
            $vc_data,
            $filter_data
        );

        $position = $component->getCurrentPosition();
        if ($position >= count($positions) || $position < 0) {
            $position = 0;
            $component = $component->withCurrentPosition($position);
        }

        $segment = $binding->getSegment(
            $request,
            $positions[$position],
            $vc_data,
            $filter_data
        );

        $ui_factory = $this->getUIFactory();
        $back = $ui_factory->button()->standard($this->txt('back'), $component->getNext(-1)->__toString())
            ->withSymbol($ui_factory->symbol()->glyph()->back())
            ->withUnavailableAction($position - 1 < 0);

        $next = $ui_factory->button()->standard($this->txt('next'), $component->getNext(1)->__toString())
            ->withSymbol($ui_factory->symbol()->glyph()->next())
            ->withUnavailableAction($position + 1 === count($positions));

        $tpl->setVariable('BACK', $default_renderer->render($back));
        $tpl->setVariable('NEXT', $default_renderer->render($next));

        if ($viewcontrols = $component->getViewControls()) {
            $tpl->setVariable('VIEWCONTROLS', $default_renderer->render($viewcontrols));
        }

        if ($actions = $component->getActions()) {
            $tpl->setVariable('ACTIONS_GLOBAL', $default_renderer->render($actions));
        }

        if ($actions = $segment->getSegmentActions()) {
            $tpl->setVariable('ACTIONS_SEGMENT', $default_renderer->render($actions));
        }

        $tpl->setVariable('SEGMENT_TITLE', $segment->getSegmentTitle());
        $tpl->setVariable('SEGMENT_CONTENTS', $default_renderer->render($segment));

        $content_region_id = $this->createId();
        $tpl->setVariable('CONTENT_REGION_ID', $content_region_id);

        $headline_id = $this->createId();
        $tpl->setVariable('HEADLINE_ID', $headline_id);

        $navigation_id = $this->createId();
        $tpl->setVariable('NAVIGATION_ID', $navigation_id);

        $navigation_description_id = $this->createId();
        $tpl->setVariable('NAVIGATION_DESCRIPTION_ID', $navigation_description_id);

        $nav_label = $this->txt("ui_nav_sequence_control_label");
        $tpl->setVariable('NAVIGATION_LABEL', $nav_label);

        $nav_description = $this->txt("ui_nav_sequence_description");
        $tpl->setVariable('NAVIGATION_DESCRIPTION', $nav_description);

        return $tpl->get();
    }
}
