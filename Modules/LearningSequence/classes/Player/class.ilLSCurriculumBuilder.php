<?php

declare(strict_types=1);

/**
 * Builds the overview (curriculum) of a LearningSequence.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSCurriculumBuilder
{
    public function __construct(
        array $ls_items,
        ILIAS\UI\Factory $ui_factory,
        ilLanguage $language,
        string $goto_command,
        LSUrlBuilder $url_builder = null
    ) {
        $this->items = $ls_items;
        $this->ui_factory = $ui_factory;
        $this->lng = $language;
        $this->goto_command = $goto_command;
        $this->url_builder = $url_builder;
    }

    public function getLearnerCurriculum(bool $with_action = false)//: ILIAS\UI\Component\Listing\Workflow
    {
        $steps = [];
        foreach ($this->items as $item) {
            $action = '#';
            if ($with_action) {
                $action = $this->query . $item->getRefId();
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
        return $workflow;
    }

    /*
        ILIAS\UI\Component\Listing\Workflow\Step
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
    protected function translateLPStatus(int $il_lp_status) : int
    {
        switch ($il_lp_status) {
            case \ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
                return ILIAS\UI\Component\Listing\Workflow\Step::IN_PROGRESS;
                break;
            case \ilLPStatus::LP_STATUS_COMPLETED_NUM:
                return ILIAS\UI\Component\Listing\Workflow\Step::SUCCESSFULLY;
                break;
            case \ilLPStatus::LP_STATUS_FAILED_NUM:
                return ILIAS\UI\Component\Listing\Workflow\Step::UNSUCCESSFULLY;
                break;
            case \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
            default:
                return ILIAS\UI\Component\Listing\Workflow\Step::NOT_STARTED;
        }
    }
}
