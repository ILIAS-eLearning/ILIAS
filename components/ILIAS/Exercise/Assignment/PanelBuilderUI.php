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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Link\Link;

use function PHPUnit\Framework\isInstanceOf;

class PanelBuilderUI
{
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected PropertyAndActionBuilderUI $prop_builder;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        PropertyAndActionBuilderUI $prop_builder,
        \ILIAS\UI\Factory $ui_factory,
        \ILIAS\UI\Renderer $ui_renderer,
        \ilCtrl $ctrl,
        \ilLanguage $lng
    ) {
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->prop_builder = $prop_builder;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    protected function addPropertyToItemProperties(array &$props, ?array $prop): void
    {
        if ($prop) {
            $props[$prop["prop"]] = $prop["val"];
        }
    }

    public function getPanel(Assignment $ass, int $user_id): \ILIAS\UI\Component\Panel\Standard
    {
        $pb = $this->prop_builder;
        $pb->build($ass, $user_id);

        // schedule card
        $sections = [];
        foreach ($pb->getProperties($pb::SEC_SCHEDULE) as $prop) {
            $sections[] = $this->ui_factory->legacy($prop["prop"] . ": " . $prop["val"]);
        }
        $schedule_card = $this->ui_factory->card()->standard($this->lng->txt("exc_schedule"))
            ->withSections($sections);

        $sub_panels = [];
        $include_schedule = $pb->getInstructionsHidden();
        foreach ($pb->getSections($include_schedule) as $sec => $title) {
            $sec_empty = true;
            $ctpl = new \ilTemplate(
                "tpl.panel_content.html",
                true,
                true,
                "components/ILIAS/Exercise/Assignment"
            );

            // properties
            foreach ($pb->getProperties($sec) as $prop) {
                if ($prop["prop"] === "") {
                    $ctpl->setCurrentBlock("entry_no_label");
                    $ctpl->setVariable("VALUE_NO_LABEL", $prop["val"]);
                    $ctpl->parseCurrentBlock();
                } else {
                    $ctpl->setCurrentBlock("entry");
                    $ctpl->setVariable("LABEL", $prop["prop"]);
                    $ctpl->setVariable("VALUE", $prop["val"]);
                    $ctpl->parseCurrentBlock();
                }
                $sec_empty = false;
            }

            // actions
            $this->renderActionButton($ctpl, $pb->getMainAction($sec));
            foreach ($pb->getActions($sec) as $action) {
                $this->renderActionButton($ctpl, $action);
                $sec_empty = false;
            }

            if (count($pb->getActions($sec)) > 0) {
                $sec_empty = false;
            }

            // links
            $this->renderLinkList($ctpl, $sec);

            $sub_panel = $this->ui_factory->panel()->sub(
                $title,
                $this->ui_factory->legacy($ctpl->get())
            );
            if ($sec === $pb::SEC_INSTRUCTIONS && !$pb->getInstructionsHidden()) {
                $sub_panel = $sub_panel->withFurtherInformation($schedule_card);
                $sec_empty = false;
            }
            if (!$sec_empty) {
                $sub_panels[] = $sub_panel;
            }
        }

        $panel = $this->ui_factory->panel()->standard(
            $ass->getTitle(),
            $sub_panels
        );

        return $panel;
    }

    protected function renderActionButton(\ilTemplate $tpl, ?Component $c): void
    {
        if (!is_null($c) && $c instanceof Button) {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("BUTTON", $this->ui_renderer->render($c));
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderLinkList(\ilTemplate $tpl, string $sec): void
    {
        foreach ($this->prop_builder->getActions($sec) as $action) {
            if ($action instanceof Link) {
                $tpl->setCurrentBlock("link");
                $tpl->setVariable("LINK", $this->ui_renderer->render($action));
                $tpl->parseCurrentBlock();
            }
        }
    }

    public function getPanelViews(Assignment $ass, int $user_id): array
    {
        $pb = $this->prop_builder;
        $pb->build($ass, $user_id);
        return $pb->getViews();
    }
}
