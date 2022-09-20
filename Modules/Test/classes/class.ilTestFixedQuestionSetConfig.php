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
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestFixedQuestionSetConfig extends ilTestQuestionSetConfig
{
    /**
     * returns the fact wether a useable question set config exists or not
     *
     * @return boolean
     */
    public function isQuestionSetConfigured(): bool
    {
        if ($this->testOBJ->getQuestionCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * returns the fact wether a useable question set config exists or not
     *
     * @return boolean
     */
    public function doesQuestionSetRelatedDataExist(): bool
    {
        return $this->isQuestionSetConfigured();
    }

    public function removeQuestionSetRelatedData(): void
    {
        $res = $this->db->queryF(
            'SELECT question_fi FROM tst_test_question WHERE test_fi = %s',
            ['integer'],
            [$this->testOBJ->getTestId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->testOBJ->removeQuestion((int) $row['question_fi']);
        }

        $this->db->manipulateF(
            'DELETE FROM tst_test_question WHERE test_fi = %s',
            ['integer'],
            [$this->testOBJ->getTestId()]
        );

        $this->testOBJ->questions = [];

        $this->testOBJ->saveCompleteStatus($this);
    }

    public function resetQuestionSetRelatedTestSettings()
    {
        // nothing to do
    }

    /**
     * removes all question set config related data for cloned/copied test
     *
     * @param ilObjTest $cloneTestOBJ
     */
    public function cloneQuestionSetRelatedData(ilObjTest $cloneTestOBJ)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];

        require_once 'Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

        $cwo = ilCopyWizardOptions::_getInstance($cloneTestOBJ->getTmpCopyWizardCopyId());

        foreach ($this->testOBJ->questions as $key => $question_id) {
            $question = assQuestion::instantiateQuestion($question_id);
            $cloneTestOBJ->questions[$key] = $question->duplicate(true, null, null, null, $cloneTestOBJ->getId());

            $original_id = assQuestion::_getOriginalId($question_id);

            $question = assQuestion::instantiateQuestion($cloneTestOBJ->questions[$key]);
            $question->saveToDb($original_id);

            // Save the mapping of old question id <-> new question id
            // This will be used in class.ilObjCourse::cloneDependencies to copy learning objectives
            $originalKey = $this->testOBJ->getRefId() . '_question_' . $question_id;
            $mappedKey = $cloneTestOBJ->getRefId() . '_question_' . $cloneTestOBJ->questions[$key];
            $cwo->appendMapping($originalKey, $mappedKey);
            $ilLog->write(__METHOD__ . ": Added question id mapping $originalKey <-> $mappedKey");
        }
    }

    /**
     * loads the question set config for current test from the database
     */
    public function loadFromDb()
    {
        // TODO: Implement loadFromDb() method.
    }

    /**
     * saves the question set config for current test to the database
     */
    public function saveToDb()
    {
        // TODO: Implement saveToDb() method.
    }

    public function reindexQuestionOrdering(): ilTestReindexedSequencePositionMap
    {
        $query = "
			SELECT question_fi, sequence FROM tst_test_question
			WHERE test_fi = %s
			ORDER BY sequence ASC
		";

        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->testOBJ->getTestId()]
        );

        $sequenceIndex = 0;

        $reindexedSequencePositionMap = new ilTestReindexedSequencePositionMap();

        while ($row = $this->db->fetchAssoc($res)) {
            $sequenceIndex++; // start with 1

            $reindexedSequencePositionMap->addPositionMapping((int) $row['sequence'], $sequenceIndex);

            $this->db->update(
                'tst_test_question',
                ['sequence' => ['integer', $sequenceIndex]],
                ['question_fi' => ['integer', $row['question_fi']]]
            );
        }

        return $reindexedSequencePositionMap;
    }

    /**
     * saves the question set config for test with given id to the database
     *
     * @param $testId
     */
    public function cloneToDbForTestId($testId)
    {
        // TODO: Implement saveToDbByTestId() method.
    }

    /**
     * deletes the question set config for current test from the database
     */
    public function deleteFromDb()
    {
        // TODO: Implement deleteFromDb() method.
    }

    public function isResultTaxonomyFilterSupported(): bool
    {
        return false;
    }
}
