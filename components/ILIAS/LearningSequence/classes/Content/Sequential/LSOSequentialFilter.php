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

namespace ILIAS\LearningSequence\Content\Sequential;

use ilObjLearningSequenceContentGUI;

/**
 * Builds the filter for sequential learning sequence content.
 */
class LSOSequentialFilter
{
    /** @var \ILIAS\UI\Factory */
    private \ILIAS\UI\Factory $ui_factory;
    /** @var \ilLanguage */
    private \ilLanguage $lng;
    /** @var \ilCtrl */
    private \ilCtrl $ctrl;
    /** @var ilObjLearningSequenceContentGUI */
    private ilObjLearningSequenceContentGUI $parent_gui;

    /**
     * Creates a sequential content filter builder.
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
     * Builds a filter with links targeting the given action.
     */
    public function getFilter(string $action): \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
        $action_url = $this->ctrl->getLinkTarget($this->parent_gui, $action);

        $fields = [
            'name' => $this->ui_factory->input()->field()->text($this->lng->txt('name')),
            'condition' => $this->ui_factory->input()->field()->select(
                $this->lng->txt('table_may_proceed'),
                [
                    'always' => $this->lng->txt('always'),
                    'lp' => $this->lng->txt('condition_learning_progress'),
                ]
            ),
            'online_status' => $this->ui_factory->input()->field()->select(
                $this->lng->txt('status'),
                [
                    'online' => $this->lng->txt('online'),
                    'offline' => $this->lng->txt('offline'),
                ]
            ),
        ];

        return $this->ui_factory->input()->container()->filter()->standard(
            $action_url,
            $action_url,
            $action_url,
            $action_url,
            $action_url,
            $action_url,
            $fields,
            [true, true, true],
            true,
            true
        );
    }
}
