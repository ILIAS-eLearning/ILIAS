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

/**
 * Factory for test sequence
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 */
class ilTestSequenceFactory
{
    /** @var array<int, array<int, ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet|ilTestSequenceDynamicQuestionSet|ilTestSequenceSummaryProvider>> */
    private array $testSequences = [];
    private ilDBInterface $db;
    private ilLanguage $lng;
    private ilComponentRepository $component_repository;
    private ilObjTest $testOBJ;

    public function __construct(
        ilDBInterface $db,
        ilLanguage $lng,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ
    ) {
        $this->db = $db;
        $this->lng = $lng;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;
    }
    
    /**
     * creates and returns an instance of a test sequence
     * that corresponds to the current test mode and the pass stored in test session
     *
     * @param ilTestSession|ilTestSessionDynamicQuestionSet $testSession
     * @return ilTestSequence|ilTestSequenceDynamicQuestionSet
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
     * @return ilTestSequenceFixedQuestionSet|ilTestSequenceRandomQuestionSet|ilTestSequenceDynamicQuestionSet|ilTestSequenceSummaryProvider
     */
    public function getSequenceByActiveIdAndPass($activeId, $pass)
    {
        if (!isset($this->testSequences[$activeId]) || $this->testSequences[$activeId][$pass] === null) {
            if ($this->testOBJ->isFixedTest()) {
                $this->testSequences[$activeId][$pass] = new ilTestSequenceFixedQuestionSet(
                    $activeId,
                    $pass,
                    $this->testOBJ->isRandomTest()
                );
            }

            if ($this->testOBJ->isRandomTest()) {
                $this->testSequences[$activeId][$pass] = new ilTestSequenceRandomQuestionSet(
                    $activeId,
                    $pass,
                    $this->testOBJ->isRandomTest()
                );
            }
            
            if ($this->testOBJ->isDynamicTest()) {
                $questionSet = new ilTestDynamicQuestionSet(
                    $this->db,
                    $this->lng,
                    $this->component_repository,
                    $this->testOBJ
                );
                $this->testSequences[$activeId][$pass] = new ilTestSequenceDynamicQuestionSet(
                    $this->db,
                    $questionSet,
                    $activeId
                );
            }
        }

        return $this->testSequences[$activeId][$pass];
    }
}
