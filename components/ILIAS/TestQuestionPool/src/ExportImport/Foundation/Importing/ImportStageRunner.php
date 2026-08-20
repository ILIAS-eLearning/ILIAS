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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Importing;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\ImportStage;
use ILIAS\UI\Component\Listing\Workflow\Linear;
use ILIAS\UI\Component\Listing\Workflow\Step;
use ILIAS\UI\Factory as UIFactory;

/**
 * Orchestrates the execution of a list of ImportStage instances. It delegates each request to the currently active
 * stage and manages stage transitions based on StageResult outcomes. The session repository persists the current
 * position and accumulated context between HTTP requests.
 */
class ImportStageRunner
{
    /** @param list<ImportStage> $stages */
    public function __construct(
        private readonly array $stages,
        private readonly ImportSessionRepository $session,
        private readonly ?ImportStage $final_stage = null,
    ) {
    }

    /**
     * Run the import stage runner. It manages the state of the import process and delegates to the current stage.
     * It will return a StageResult that indicates the next action to take.
     */
    public function run(): StageResult
    {
        $index = $this->session->getCurrentStageIndex();
        $context = $this->session->getContext();

        if ($index >= count($this->stages)) {
            return StageResult::complete($context);
        }

        $result = $this->stages[$index]->process($context);

        switch ($result->type) {
            case StageResultType::ADVANCE:
                $next_index = $index + 1;
                $this->session->setContext($result->context);
                $this->session->setCurrentStageIndex($next_index);

                if ($next_index >= count($this->stages)) {
                    return StageResult::complete($result->context);
                }

                return $result;

            case StageResultType::ERROR:
                $this->session->setContext($result->context);
                $this->reset();
                return $result;

            case StageResultType::INTERACT:
                $this->session->setContext($result->context);
                return $result;

            case StageResultType::COMPLETE:
                $this->reset();
                return $result;
        }

        throw new \LogicException("Invalid stage result type: {$result->type->name}");
    }

    /**
     * Build the workflow UI component for the import process.
     */
    public function buildWorkflow(UIFactory $ui, string $title): Linear
    {
        $steps = [];
        $active_index = $this->session->getCurrentStageIndex();

        foreach ($this->stages as $i => $stage) {
            if ($stage->getLabel() === null) {
                continue;
            }

            $step = $ui->listing()->workflow()->step(
                $stage->getLabel(),
                $stage->getDescription()
            );

            if ($i < $active_index) {
                $step = $step->withStatus(Step::SUCCESSFULLY)->withAvailability(Step::NOT_ANYMORE);
            } elseif ($i === $active_index) {
                $step = $step->withStatus(Step::IN_PROGRESS)->withAvailability(Step::AVAILABLE);
            } else {
                $step = $step->withStatus(Step::NOT_STARTED)->withAvailability(Step::NOT_AVAILABLE);
            }

            $steps[] = $step;
        }

        return $ui->listing()->workflow()->linear($title, $steps)->withActive($active_index);
    }

    /**
     * Reset the import stage session. If a final stage is set, it will be processed.
     */
    public function reset(): void
    {
        if ($this->final_stage) {
            $this->final_stage->process($this->session->getContext());
        }

        $this->session->clear();
    }
}
