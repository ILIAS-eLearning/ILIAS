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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Import;

/**
 * Runs import stages until one needs user interaction, reports an error, or every stage has finished. The session
 * repository persists the current position and the accumulated import session between HTTP requests.
 */
class ImportWizard
{
    /** @param list<ImportStage> $stages */
    public function __construct(
        private readonly array $stages,
        private readonly ImportSessionRepository $session_repository,
        private readonly ?ImportStage $cleanup_stage = null,
    ) {
    }

    public function run(): StageResult
    {
        $index = $this->session_repository->getCurrentStageIndex();
        $session = $this->session_repository->getSession();

        while ($index < count($this->stages)) {
            $result = $this->stages[$index]->process($session);

            switch ($result->type) {
                case StageResultType::CompletedSuccessfully:
                    $session = $result->session;
                    $index++;
                    $this->session_repository->setSession($session);
                    $this->session_repository->setCurrentStageIndex($index);
                    break;

                case StageResultType::RequiresUserInteraction:
                    $this->session_repository->setSession($result->session);
                    return $result;

                case StageResultType::Error:
                    $this->session_repository->setSession($result->session);
                    $this->reset();
                    return $result;

                default:
                    throw new \LogicException("Invalid stage result type: {$result->type->name}");
            }
        }

        $this->reset();

        return StageResult::completedSuccessfully($session);
    }

    /**
     * Clears the stored import session. The cleanup stage runs first when one is configured.
     */
    public function reset(): void
    {
        if ($this->cleanup_stage) {
            $this->cleanup_stage->process($this->session_repository->getSession());
        }

        $this->session_repository->clear();
    }
}
