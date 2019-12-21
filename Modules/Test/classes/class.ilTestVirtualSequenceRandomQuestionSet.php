<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestVirtualSequence.php';
require_once 'Modules/Test/interfaces/interface.ilTestRandomQuestionSequence.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestVirtualSequenceRandomQuestionSet extends ilTestVirtualSequence implements ilTestRandomQuestionSequence
{
    private $questionsSourcePoolDefinitionMap;
    
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ, ilTestSequenceFactory $testSequenceFactory)
    {
        parent::__construct($db, $testOBJ, $testSequenceFactory);
        
        $this->questionsSourcePoolDefinitionMap = array();
    }
    
    public function getResponsibleSourcePoolDefinitionId($questionId)
    {
        return $this->questionsSourcePoolDefinitionMap[$questionId];
    }

    protected function fetchQuestionsFromPasses($activeId, $passes)
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
