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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestVirtualSequence implements ilTestQuestionSequence
{
    protected ?int $active_id;

    protected array $questions_pass_map;

    public function __construct(
        protected ilDBInterface $db,
        protected ilObjTest $test_obj,
        protected ilTestSequenceFactory $test_sequence_factory
    ) {
        $this->active_id = null;

        $this->questions_pass_map = [];
    }

    public function getActiveId(): ?int
    {
        return $this->active_id;
    }

    public function setActiveId(int $active_id): void
    {
        $this->active_id = $active_id;
    }

    public function getQuestionIds(): array
    {
        return array_keys($this->questions_pass_map);
    }

    public function getQuestionsPassMap(): array
    {
        return $this->questions_pass_map;
    }

    public function getUniquePasses(): array
    {
        return array_unique(array_values($this->questions_pass_map));
    }

    public function init(): void
    {
        $passes = $this->getExistingPassesDescendent($this->getActiveId());
        $this->fetchQuestionsFromPasses($this->getActiveId(), $passes);
    }

    private function getExistingPassesDescendent(int $active_id): array
    {
        $passes_selector = new ilTestPassesSelector($this->db, $this->test_obj);
        $passes_selector->setActiveId($active_id);

        $passes = $passes_selector->getExistingPasses();

        rsort($passes, SORT_NUMERIC);

        return $passes;
    }

    protected function getTestSequence(int $active_id, int $pass): ilTestSequence
    {
        $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $pass);

        $test_sequence->loadFromDb();
        $test_sequence->loadQuestions();

        $test_sequence->setConsiderHiddenQuestionsEnabled(true);
        $test_sequence->setConsiderOptionalQuestionsEnabled(true);
        return $test_sequence;
    }

    protected function wasAnsweredInThisPass(ilTestSequence $test_sequence, int $question_id): bool
    {
        if ($test_sequence->isHiddenQuestion($question_id)) {
            return false;
        }

        if (!$test_sequence->isQuestionOptional($question_id)) {
            return true;
        }

        if ($test_sequence->isAnsweringOptionalQuestionsConfirmed()) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int> $passes
     */
    protected function fetchQuestionsFromPasses(int $active_id, array $passes): void
    {
        $this->questions_pass_map = [];

        foreach ($passes as $pass) {
            $test_sequence = $this->getTestSequence($active_id, $pass);

            foreach ($test_sequence->getOrderedSequenceQuestions() as $question_id) {
                if (isset($this->questions_pass_map[$question_id])) {
                    continue;
                }

                if ($this->wasAnsweredInThisPass($test_sequence, $question_id)) {
                    $this->questions_pass_map[$question_id] = $pass;
                }
            }
        }
    }
}
