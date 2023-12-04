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
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestFixedQuestionSetConfig extends ilTestQuestionSetConfig
{
    public function isQuestionSetConfigured(): bool
    {
        if ($this->test_obj->getQuestionCountWithoutReloading() > 0) {
            return true;
        }
        return false;
    }

    public function doesQuestionSetRelatedDataExist(): bool
    {
        return $this->isQuestionSetConfigured();
    }

    public function removeQuestionSetRelatedData(): void
    {
        $res = $this->db->queryF(
            'SELECT question_fi FROM tst_test_question WHERE test_fi = %s',
            ['integer'],
            [$this->test_obj->getTestId()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->test_obj->removeQuestion((int) $row['question_fi']);
        }

        $this->db->manipulateF(
            'DELETE FROM tst_test_question WHERE test_fi = %s',
            ['integer'],
            [$this->test_obj->getTestId()]
        );

        $this->test_obj->questions = [];

        $this->test_obj->saveCompleteStatus($this);
    }

    public function cloneQuestionSetRelatedData(ilObjTest $clone_test_obj): void
    {
        $cwo = ilCopyWizardOptions::_getInstance($clone_test_obj->getTmpCopyWizardCopyId());

        foreach ($this->test_obj->questions as $key => $question_id) {
            $question_orig = assQuestion::instantiateQuestion($question_id);

            $clone_test_obj->questions[$key] = $question_orig->duplicate(true, '', '', -1, $clone_test_obj->getId());

            $original_id = $this->questioninfo->getOriginalId($question_id);

            $question_clone = assQuestion::instantiateQuestion($clone_test_obj->questions[$key]);
            $question_clone->saveToDb($original_id);

            // Save the mapping of old question id <-> new question id
            // This will be used in class.ilObjCourse::cloneDependencies to copy learning objectives
            $original_key = $this->test_obj->getRefId() . '_question_' . $question_id;
            $mapped_key = $clone_test_obj->getRefId() . '_question_' . $clone_test_obj->questions[$key];
            $cwo->appendMapping($original_key, $mapped_key);
            $this->log->write(__METHOD__ . ": Added question id mapping $original_key <-> $mapped_key");
        }
    }

    public function loadFromDb(): void
    {
        // TODO: Implement loadFromDb() method.
    }

    public function saveToDb(): void
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
            [$this->test_obj->getTestId()]
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

    public function cloneToDbForTestId(int $test_id): void
    {
        // TODO: Implement saveToDbByTestId() method.
    }

    public function deleteFromDb(): void
    {
        // TODO: Implement deleteFromDb() method.
    }

    public function isResultTaxonomyFilterSupported(): bool
    {
        return false;
    }
}
