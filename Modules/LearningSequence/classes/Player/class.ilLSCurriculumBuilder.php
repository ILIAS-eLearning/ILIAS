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

use ILIAS\UI\Component\Listing\Workflow\Step;

/**
 * Builds the overview (curriculum) of a LearningSequence.
 */
class ilLSCurriculumBuilder
{
    protected ilLSLearnerItemsQueries $ls_items;
    protected ILIAS\UI\Factory $ui_factory;
    protected ilLanguage $lng;
    protected string $goto_command;
    protected ?LSUrlBuilder $url_builder;

    public function __construct(
        ilLSLearnerItemsQueries $ls_items,
        ILIAS\UI\Factory $ui_factory,
        ilLanguage $language,
        string $goto_command,
        LSUrlBuilder $url_builder = null
    ) {
        $this->ls_items = $ls_items;
        $this->ui_factory = $ui_factory;
        $this->lng = $language;
        $this->goto_command = $goto_command;
        $this->url_builder = $url_builder;
    }

    public function getLearnerCurriculum(bool $with_action = false): ILIAS\UI\Component\Listing\Workflow\Linear
    {
        $steps = [];
        $items = $this->ls_items->getItems();
        foreach ($items as $item) {
            $action = '#';
            if ($with_action) {
                $action = $this->url_builder->getHref($this->goto_command, $item->getRefId());
            }

            $steps[] = $this->ui_factory->listing()->workflow()->step(
                $item->getTitle(),
                $item->getDescription(),
                $action
            )
            ->withAvailability($item->getAvailability())
            ->withStatus(
                $this->translateLPStatus(
                    $item->getLearningProgressStatus()
                )
            );
        }

        $workflow = $this->ui_factory->listing()->workflow()->linear(
            $this->lng->txt('curriculum'),
            $steps
        );

        if ($steps !== []) {
            $current_position = max(0, $this->ls_items->getCurrentItemPosition());
            if ($items[$current_position]->getAvailability() === Step::AVAILABLE) {
                $workflow = $workflow->withActive($current_position);
            }
        }

        return $workflow;
    }

    /*
        Step
            const NOT_STARTED	= 1;
            const IN_PROGRESS	= 2;
            const SUCCESSFULLY	= 3;
            const UNSUCCESSFULLY= 4;

        Services/Tracking/class.ilLPStatus.php
            const LP_STATUS_NOT_ATTEMPTED_NUM = 0;
            const LP_STATUS_IN_PROGRESS_NUM = 1;
            const LP_STATUS_COMPLETED_NUM = 2;
            const LP_STATUS_FAILED_NUM = 3;
    */
    protected function translateLPStatus(int $il_lp_status): int
    {
        switch ($il_lp_status) {
            case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return Step::IN_PROGRESS;
            case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return Step::SUCCESSFULLY;
            case \ilLPStatus::LP_STATUS_FAILED_NUM:
                return Step::UNSUCCESSFULLY;
            case \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
            default:
                return Step::NOT_STARTED;
        }
    }
}
