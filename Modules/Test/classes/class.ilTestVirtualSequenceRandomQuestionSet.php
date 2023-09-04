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
class ilTestVirtualSequenceRandomQuestionSet extends ilTestVirtualSequence implements ilTestRandomQuestionSequence
{
    private array $questions_source_pool_definition_map;

    public function __construct(ilDBInterface $db, ilObjTest $test_obj, ilTestSequenceFactory $test_sequence_facory)
    {
        parent::__construct($db, $test_obj, $test_sequence_facory);

        $this->questions_source_pool_definition_map = [];
    }

    public function getResponsibleSourcePoolDefinitionId(int $question_id): ?int
    {
        return $this->questions_source_pool_definition_map[$question_id];
    }

    protected function fetchQuestionsFromPasses(int $active_id, array $passes): void
    {
        $this->questions_pass_map = [];

        foreach ($passes as $pass) {
            $handled_source_pool_definitions = array_flip($this->questions_source_pool_definition_map);

            $test_sequence = $this->getTestSequence($active_id, $pass);

            foreach ($test_sequence->getOrderedSequenceQuestions() as $question_id) {
                $definition_id = $test_sequence->getResponsibleSourcePoolDefinitionId($question_id);

                if (isset($handled_source_pool_definitions[$definition_id])) {
                    continue;
                }

                if ($this->wasAnsweredInThisPass($test_sequence, $question_id)) {
                    $this->questions_pass_map[$questionId] = $pass;

                    $this->questions_source_pool_definition_map[$question_id] = $definition_id;
                }
            }
        }
    }
}
