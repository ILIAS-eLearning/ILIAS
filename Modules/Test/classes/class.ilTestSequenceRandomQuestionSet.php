<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestSequence.php';
require_once 'Modules/Test/interfaces/interface.ilTestRandomQuestionSequence.php';


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSequenceRandomQuestionSet extends ilTestSequence implements ilTestRandomQuestionSequence
{
    private $responsibleSourcePoolDefinitionByQuestion = array();

    public function loadQuestions(ilTestQuestionSetConfig $testQuestionSetConfig = null, $taxonomyFilterSelection = array())
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $this->questions = array();

        $result = $ilDB->queryF(
            "SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = %s ORDER BY sequence",
            array('integer','integer'),
            array($this->active_id, $this->pass)
        );
        // The following is a fix for random tests prior to ILIAS 3.8. If someone started a random test in ILIAS < 3.8, there
        // is only one test pass (pass = 0) in tst_test_rnd_qst while with ILIAS 3.8 there are questions for every test pass.
        // To prevent problems with tests started in an older version and continued in ILIAS 3.8, the first pass should be taken if
        // no questions are present for a newer pass.
        if ($result->numRows() == 0) {
            $result = $ilDB->queryF(
                "SELECT tst_test_rnd_qst.* FROM tst_test_rnd_qst, qpl_questions WHERE tst_test_rnd_qst.active_fi = %s AND qpl_questions.question_id = tst_test_rnd_qst.question_fi AND tst_test_rnd_qst.pass = 0 ORDER BY sequence",
                array('integer'),
                array($this->active_id)
            );
        }

        $index = 1;

        while ($data = $ilDB->fetchAssoc($result)) {
            $this->questions[$index++] = $data["question_fi"];

            $this->responsibleSourcePoolDefinitionByQuestion[$data['question_fi']] = $data['src_pool_def_fi'];
        }
    }

    /**
     * !!! LEGACY CODE !!!
     *
     * Checkes wheather a random test has already created questions for a given pass or not
     *
     * @access private
     * @param $active_id Active id of the test
     * @param $pass Pass of the test
     * @return boolean TRUE if the test already contains questions, FALSE otherwise
     */
    public function hasRandomQuestionsForPass($active_id, $pass)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $result = $ilDB->queryF(
            "SELECT test_random_question_id FROM tst_test_rnd_qst WHERE active_fi = %s AND pass = %s",
            array('integer','integer'),
            array($active_id, $pass)
        );
        return ($result->numRows() > 0) ? true : false;
    }

    public function getResponsibleSourcePoolDefinitionId($questionId)
    {
        if (isset($this->responsibleSourcePoolDefinitionByQuestion[$questionId])) {
            return $this->responsibleSourcePoolDefinitionByQuestion[$questionId];
        }

        return null;
    }
}
