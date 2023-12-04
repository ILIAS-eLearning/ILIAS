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
use ILIAS\UI\Component\Button\Standard as StandardButton;

class ItemBuilderUI
{
    protected \ilCtrl $ctrl;
    protected PropertyAndActionBuilderUI $prop_builder;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        PropertyAndActionBuilderUI $prop_builder,
        \ILIAS\UI\Factory $ui_factory,
        \ilCtrl $ctrl
    ) {
        $this->ui_factory = $ui_factory;
        $this->prop_builder = $prop_builder;
        $this->ctrl = $ctrl;
    }

    protected function addPropertyToItemProperties(array &$props, ?array $prop): void
    {
        if ($prop) {
            $props[$prop["prop"]] = $prop["val"];
        }
    }

    public function getItem(Assignment $ass, int $user_id): \ILIAS\UI\Component\Item\Standard
    {
        $pb = $this->prop_builder;
        $pb->build($ass, $user_id);

        $props = [];
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_DEADLINE));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_REQUIREMENT));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_SUBMISSION));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_TYPE));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_GRADING));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_MARK));
        foreach ($pb->getAdditionalHeadProperties() as $p) {
            $this->addPropertyToItemProperties($props, $p);
        }

        // actions
        $actions = [];
        $main_action = null;
        foreach ($pb->getSections() as $sec => $txt) {
            if ($act = $pb->getMainAction($sec)) {
                $main_action = $this->ui_factory->button()->standard(
                    $act->getLabel(),
                    $act->getAction()
                );
            }
        }

        foreach ($pb->getSections(true, true) as $sec => $txt) {
            foreach ($pb->getActions($sec) as $act) {
                $actions[] = $act;
            }
        }


        $this->ctrl->setParameterByClass(\ilAssignmentPresentationGUI::class, "ass_id", $ass->getId());
        $title = $this->ui_factory->link()->standard(
            $ass->getTitle(),
            $this->ctrl->getLinkTargetByClass(\ilAssignmentPresentationGUI::class, "")
        );
        $item = $this->ui_factory->item()->standard(
            $title
        )->withProperties($props)->withLeadText($pb->getLeadText() . " ");
        if (count($actions) > 0) {
            $ks_actions = [];
            foreach ($actions as $act) {
                if ($act instanceof StandardButton) {
                    $ks_actions[] = $this->ui_factory->button()->shy(
                        $act->getLabel(),
                        $act->getAction()
                    );
                } else {
                    $ks_actions[] = $act;
                }
            }
            $item = $item->withActions($this->ui_factory->dropdown()->standard($ks_actions));
        }
        if ($main_action) {
            $item = $item->withMainAction($main_action);
        }
        $this->ctrl->setParameterByClass(\ilAssignmentPresentationGUI::class, "ass_id", null);
        return $item;
    }


}
