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
 * Factory for test sequence
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
class ilTestSequenceFactory
{
    /** @var array<int, array<int, ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet|ilTestSequenceSummaryProvider>> */
    private array $test_sequences = [];

    public function __construct(
        private ilObjTest $test_obj,
        private ilDBInterface $db,
        private \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo
    ) {
    }

    /**
     * creates and returns an instance of a test sequence
     * that corresponds to the current test mode and the pass stored in test session
     *
     * @param ilTestSession $testSession
     * @return ilTestSequence
     */
    public function getSequenceByTestSession($testSession)
    {
        return $this->getSequenceByActiveIdAndPass($testSession->getActiveId(), $testSession->getPass());
    }

    /**
     * creates and returns an instance of a test sequence
     * that corresponds to the current test mode and given active/pass
     *
     * @param integer $activeId
     * @param integer $pass
     * @return ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet|ilTestSequenceSummaryProvider
     */
    public function getSequenceByActiveIdAndPass($activeId, $pass)
    {
        if (!isset($this->test_sequences[$activeId][$pass])) {
            if ($this->test_obj->isFixedTest()) {
                $this->test_sequences[$activeId][$pass] = new ilTestSequenceFixedQuestionSet(
                    $this->db,
                    $activeId,
                    $pass,
                    $this->questioninfo
                );
            }

            if ($this->test_obj->isRandomTest()) {
                $this->test_sequences[$activeId][$pass] = new ilTestSequenceRandomQuestionSet(
                    $this->db,
                    $activeId,
                    $pass,
                    $this->questioninfo
                );
            }
        }

        return $this->test_sequences[$activeId][$pass];
    }
}
