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

/**
 * @package components\ILIAS/Test
 * Results for one user and pass
 */
class ilTestPassResult
{
    /**
     * @param ilQuestionResult[] $question_results
     */
    public function __construct(
        protected ilTestPassResultsSettings $settings,
        protected int $active_id,
        protected int $pass_id,
        protected array $question_results
    ) {
    }

    public function getSettings(): ilTestPassResultsSettings
    {
        return $this->settings;
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function getPass(): int
    {
        return $this->pass_id;
    }

    /**
     * @return ilQuestionResult[];
     */
    public function getQuestionResults(): array
    {
        return $this->question_results;
    }
}
