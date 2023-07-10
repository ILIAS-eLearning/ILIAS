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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestVirtualSequenceRandomQuestionSet extends ilTestVirtualSequence implements ilTestRandomQuestionSequence
{
    private array $questionsSourcePoolDefinitionMap;

    public function __construct(ilDBInterface $db, ilObjTest $testOBJ, ilTestSequenceFactory $testSequenceFactory)
    {
        parent::__construct($db, $testOBJ, $testSequenceFactory);

        $this->questionsSourcePoolDefinitionMap = array();
    }

    public function getResponsibleSourcePoolDefinitionId($questionId)
    {
        return $this->questionsSourcePoolDefinitionMap[$questionId];
    }

    protected function fetchQuestionsFromPasses(int $activeId, array $passes): void
    {
        $this->questionsPassMap = array();

        foreach ($passes as $pass) {
            $handledSourcePoolDefinitions = array_flip($this->questionsSourcePoolDefinitionMap);

            $testSequence = $this->getTestSequence($activeId, $pass);

            foreach ($testSequence->getOrderedSequenceQuestions() as $questionId) {
                $definitionId = $testSequence->getResponsibleSourcePoolDefinitionId($questionId);

                if (isset($handledSourcePoolDefinitions[$definitionId])) {
                    continue;
                }

                if ($this->wasAnsweredInThisPass($testSequence, $questionId)) {
                    $this->questionsPassMap[$questionId] = $pass;

                    $this->questionsSourcePoolDefinitionMap[$questionId] = $definitionId;
                }
            }
        }
    }
}
