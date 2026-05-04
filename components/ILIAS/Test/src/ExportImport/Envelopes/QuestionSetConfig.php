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

namespace ILIAS\Test\ExportImport\Envelopes;

use ilTestQuestionSetConfig;
use ilTestRandomQuestionSetConfig;
use ilTestRandomQuestionSetSourcePoolDefinition;

class QuestionSetConfig
{
    public function __construct(
        private readonly ilTestQuestionSetConfig $config,
        /** @var list<ilTestRandomQuestionSetSourcePoolDefinition> */
        private array $definitions = [],
        /** @var array<int, list<int>> */
        private array $staging_pools = [],
    ) {
    }

    public function getConfig(): ilTestQuestionSetConfig
    {
        return $this->config;
    }

    public function isRandom(): bool
    {
        return $this->config instanceof ilTestRandomQuestionSetConfig;
    }

    /**
     * @return list<ilTestRandomQuestionSetSourcePoolDefinition>
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param list<ilTestRandomQuestionSetSourcePoolDefinition> $definitions
     */
    public function setDefinitions(array $definitions): void
    {
        $this->definitions = $definitions;
    }

    /**
     * @return array<int, list<int>>
     */
    public function getStagingPools(): array
    {
        return $this->staging_pools;
    }

    /**
     * @param list<int> $questions
     */
    public function addStagingPoolQuestions(int $pool_id, array $questions): void
    {
        $this->staging_pools[$pool_id] = $questions;
    }
}
