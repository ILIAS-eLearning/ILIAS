<?php

declare(strict_types=1);

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Listing\Workflow\Workflow;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/**
 * Class ilKioskPageRenderer
 */
class ilKioskPageRenderer
{
    public function __construct(
        ilGlobalPageTemplate $il_global_template,
        MetaContent $layout_meta_content,
        Factory $ui_factory,
        Renderer $ui_renderer,
        ilLanguage $lng,
        ilTemplate $kiosk_template,
        ilLSTOCGUI $toc_gui,
        ilLSLocatorGUI $loc_gui,
        string $window_base_title
    ) {
        $this->il_tpl = $il_global_template;
        $this->layout_meta_content = $layout_meta_content;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->lng = $lng;
        $this->tpl = $kiosk_template;
        $this->toc_gui = $toc_gui;
        $this->loc_gui = $loc_gui;
        $this->window_base_title = $window_base_title;
    }

    public function buildCurriculumSlate(Workflow $curriculum) : Slate
    {
        $f = $this->ui_factory;
        return $this->ui_factory->maincontrols()->slate()->legacy(
            $this->lng->txt('lso_mainbar_button_label_curriculum'),
            $f->symbol()->icon()->standard("lso", "Learning Sequence")
                ->withIsOutlined(true),
            $this->ui_factory->legacy(
                $this->ui_renderer->render($curriculum)
            )
        );
    }


    public function buildToCSlate($toc, Icon $icon) : Slate
    {
        $html = $this->toc_gui
            ->withStructure($toc->toJSON())
            ->getHTML();
        return $this->ui_factory->maincontrols()->slate()->legacy(
            $this->lng->txt('lso_mainbar_button_label_toc'),
            $icon->withSize("small")->withIsOutlined(true),
            $this->ui_factory->legacy($html)
        );
    }


    public function render(
        string $lso_title,
        LSControlBuilder $control_builder,
        string $obj_title,
        Component $icon,
        array $content
    ) : string {
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

        return $this->tpl->get();
    }
}
