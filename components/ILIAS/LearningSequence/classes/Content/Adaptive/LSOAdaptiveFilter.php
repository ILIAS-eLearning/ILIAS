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

namespace ILIAS\LearningSequence\Content\Adaptive;

use ilObjLearningSequenceContentGUI;

/**
 * Creates the filter for adaptive learning sequence content.
 */
class LSOAdaptiveFilter
{
    /**
     * UI factory.
     */
    private \ILIAS\UI\Factory $ui_factory;
    /**
     * Language service.
     */
    private \ilLanguage $lng;
    /**
     * Controller service.
     */
    private \ilCtrl $ctrl;
    /**
     * Parent content GUI.
     */
    private ilObjLearningSequenceContentGUI $parent_gui;

    /**
     * Creates the adaptive content filter.
     */
    public function __construct(
        \ILIAS\UI\Factory $ui_factory,
        \ilLanguage $lng,
        \ilCtrl $ctrl,
        ilObjLearningSequenceContentGUI $parent_gui
    ) {
        $this->ui_factory = $ui_factory;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->parent_gui = $parent_gui;
    }

    /**
     * Gets the adaptive content filter.
     *
     * @param array<string, string> $input_options Input condition options.
     * @param array<string, string> $output_options Output condition options.
     */
    public function getFilter(string $action, array $input_options, array $output_options): \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
        // The filter needs fully qualified URLs (toggle_on/off, expand,
        // collapse, apply and the form action). Passing the bare command name
        // ("manageContent") made the browser submit to a relative URL like
        // "/manageContent", which does not exist. Build a proper link target.
        $action_url = $this->ctrl->getLinkTarget($this->parent_gui, $action);

        $fields = [
            'name' => $this->ui_factory->input()->field()->text($this->lng->txt('name')),
            'input_conditions' => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('input_conditions'),
                $input_options
            ),
            'output_conditions' => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('output_conditions'),
                $output_options
            ),
            'online_status' => $this->ui_factory->input()->field()->select(
                $this->lng->txt('status'),
                [
                    'online' => $this->lng->txt('online'),
                    'offline' => $this->lng->txt('offline'),
                ]
            ),
            'position' => $this->ui_factory->input()->field()->multiselect(
                $this->lng->txt('position'),
                [
                    'start' => $this->lng->txt('lso_adaptive_start'),
                    'end' => $this->lng->txt('lso_adaptive_end'),
                ]
            ),
        ];

        return $this->ui_factory->input()->container()->filter()->standard(
            $action_url, // toggle_on
            $action_url, // toggle_off
            $action_url, // expand
            $action_url, // collapse
            $action_url, // apply
            $action_url,  // form action
            $fields,
            [true, true, true, true, true],
            true,
            true
        );
    }
}
