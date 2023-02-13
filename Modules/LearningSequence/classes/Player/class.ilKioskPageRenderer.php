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

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Listing\Workflow\Workflow;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

class ilKioskPageRenderer
{
    protected MetaContent $layout_meta_content;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;
    protected ilLanguage $lng;
    protected ilTemplate $tpl;
    protected ilLSTOCGUI $toc_gui;
    protected ilLSLocatorGUI $loc_gui;
    protected string $window_base_title;
    protected \Closure $lso_lp_state_completed;

    public function __construct(
        MetaContent $layout_meta_content,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        ilTemplate $kiosk_template,
        ilLSTOCGUI $toc_gui,
        ilLSLocatorGUI $loc_gui,
        string $window_base_title,
        \Closure $lso_lp_state_completed
    ) {
        $this->layout_meta_content = $layout_meta_content;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
        $this->tpl = $kiosk_template;
        $this->toc_gui = $toc_gui;
        $this->loc_gui = $loc_gui;
        $this->window_base_title = $window_base_title;
        $this->lso_lp_state_completed = $lso_lp_state_completed;
    }

    public function buildCurriculumSlate(Workflow $curriculum): Slate
    {
        $f = $this->ui_factory;
        return $this->ui_factory->maincontrols()->slate()->legacy(
            $this->lng->txt('lso_mainbar_button_label_curriculum'),
            $f->symbol()->icon()->standard("lso", "Learning Sequence"),
            $this->ui_factory->legacy(
                $this->ui_renderer->render($curriculum)
            )
        );
    }

    public function buildToCSlate(LSTOCBuilder $toc, Icon $icon): Slate
    {
        $html = $this->toc_gui
            ->withStructure($toc->toJSON())
            ->getHTML();
        return $this->ui_factory->maincontrols()->slate()->legacy(
            $this->lng->txt('lso_mainbar_button_label_toc'),
            $icon->withSize("small"),
            $this->ui_factory->legacy($html)
        );
    }

    protected function getToastIfCompleted(): string
    {
        $completion_alert = '';
        $lp = $this->lso_lp_state_completed;
        if ($lp()) {
            $toast = $this->ui_factory->toast()->standard(
                $this->lng->txt('lso_toast_completed_title'),
                $this->ui_factory->symbol()->icon()->standard('lso', 'Learning Sequence completed')
                    ->withSize('large')
            )
            ->withDescription(
                $this->lng->txt('lso_toast_completed_desc')
            );
            $toast_container = $this->ui_factory->toast()->container()->withAdditionalToast($toast);
            $completion_alert = $this->ui_renderer->render($toast_container);
        }
        return $completion_alert;
    }

    public function render(
        LSControlBuilder $control_builder,
        string $obj_title,
        Component $icon,
        array $content
    ): string {
        $this->tpl->setVariable(
            "OBJECT_ICON",
            $this->ui_renderer->render($icon)
        );
        $this->tpl->setVariable("OBJECT_TITLE", $obj_title);

        $this->tpl->setVariable(
            "PLAYER_NAVIGATION",
            $this->ui_renderer->render([
                $control_builder->getPreviousControl(),
                $control_builder->getNextControl()
            ])
        );

        $controls = $control_builder->getControls();

        //ensure done control is first element
        if ($control_builder->getDoneControl()) {
            array_unshift($controls, $control_builder->getDoneControl());
        }
        //also shift start control up front - this is for legacy-views only!
        if ($control_builder->getStartControl()) {
            array_unshift($controls, $control_builder->getStartControl());
            $this->tpl->setVariable("JS_INLINE", $control_builder->getAdditionalJS());
        }

        //TODO: insert toggles

        $this->tpl->setVariable(
            "OBJ_NAVIGATION",
            $this->ui_renderer->render($controls)
        );


        $this->tpl->setVariable(
            "VIEW_MODES",
            $this->ui_renderer->render($control_builder->getModeControls())
        );

        if ($control_builder->getLocator()) {
            $this->tpl->setVariable(
                'LOCATOR',
                $this->ui_renderer->render(
                    $this->loc_gui
                        ->withItems($control_builder->getLocator()->getItems())
                        ->getComponent()
                )
            );
        }

        $this->tpl->setVariable(
            'CONTENT',
            $this->ui_renderer->render($content)
        );

        $completion_alert = $this->getToastIfCompleted();

        return $this->tpl->get() . $completion_alert;
    }
}
