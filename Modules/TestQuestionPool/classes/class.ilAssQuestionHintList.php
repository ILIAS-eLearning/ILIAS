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
 * Model class for managing lists of hints for a question
 *
 * @author         Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/TestQuestionPool
 * @implements Iterator<ilAssQuestionHint>
 */
class ilAssQuestionHintList implements Iterator
{
    /** @var list<ilAssQuestionHint> */
    private array $questionHints = [];

    public function current()
    {
        return current($this->questionHints);
    }

    public function rewind(): void
    {
        reset($this->questionHints);
    }

    public function next(): void
    {
        next($this->questionHints);
    }

    public function key(): int
    {
        return key($this->questionHints);
    }

    public function valid(): bool
    {
        return key($this->questionHints) !== null;
    }

    public function __construct()
    {
    }

    public function addHint(ilAssQuestionHint $questionHint): void
    {
        $this->questionHints[] = $questionHint;
    }

    /**
     * @param int $hintId
     */
    public function getHint($hintId): ilAssQuestionHint
    {
        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() == $hintId) {
                return $questionHint;
            }
        }

        throw new ilTestQuestionPoolException("hint with id $hintId does not exist in this list");
    }

    /**
     * @param int $hintId
     */
    public function hintExists($hintId): bool
    {
        foreach ($this as $questionHint) {
            /* @var ilAssQuestionHint $questionHint */
            if ($questionHint->getId() == $hintId) {
                return true;
            }
        }

        return false;
    }

    /**
     * re-indexes the list's hints sequentially by current order (starting with index "1")
     * ATTENTION: it also persists this index to db by performing an update of hint object via id.
     * do not re-index any hint list objects unless this lists contain ALL hint objects for a SINGLE question
     * and no more hints apart of that.
     */
    public function reIndex(): void
    {
        $counter = 0;

        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            $questionHint->setIndex(++$counter);
            $questionHint->save();
        }
    }

    /**
     * duplicates a hint list from given original question id to
     * given duplicate question id and returns an array of duplicate hint ids
     * mapped to the corresponding original hint ids
     * @param int $originalQuestionId
     * @param int $duplicateQuestionId
     * @return array<int, int> $hintIds containing the map from original hint ids to duplicate hint ids
     */
    public static function duplicateListForQuestion($originalQuestionId, $duplicateQuestionId): array
    {
        $hintIds = [];

        $questionHintList = self::getListByQuestionId($originalQuestionId);

        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            $originalHintId = $questionHint->getId();

            $questionHint->setId(0);
            $questionHint->setQuestionId($duplicateQuestionId);

            $questionHint->save();

            $duplicateHintId = $questionHint->getId();

            $hintIds[$originalHintId] = $duplicateHintId;
        }

        return $hintIds;
    }

    /**
     * returns an array with data of the hints in this list
     * that is adopted to be used as table gui data
     * @return list<array{hint_id: null|int, hint_index: null|int, hint_points: null|float, hint_text: null|int}>
     */
    public function getTableData(): array
    {
        $tableData = [];

        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            $tableData[] = [
                'hint_id' => $questionHint->getId(),
                'hint_index' => $questionHint->getIndex(),
                'hint_points' => $questionHint->getPoints(),
                'hint_text' => $questionHint->getText()
            ];
        }

        return $tableData;
    }

    /**
     * instantiates a question hint list for the passed question id
     * @param int $questionId
     */
    public static function getListByQuestionId($questionId): ilAssQuestionHintList
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
			
			ORDER BY	qht_hint_index ASC
		";

        $res = $ilDB->queryF(
            $query,
            ['integer'],
            [(int) $questionId]
        );

        $questionHintList = new self();

        while ($row = $ilDB->fetchAssoc($res)) {
            $questionHint = new ilAssQuestionHint();

            ilAssQuestionHint::assignDbRow($questionHint, $row);

            $questionHintList->addHint($questionHint);
        }

        return $questionHintList;
    }

    /**
     * instantiates a question hint list for the passed hint ids
     * @param list<int> $hintIds
     */
    public static function getListByHintIds($hintIds): ilAssQuestionHintList
    {
        global $DIC;
        $ilDB = $DIC->database();

        $qht_hint_id__IN__hintIds = $ilDB->in('qht_hint_id', (array) $hintIds, false, 'integer');

        $query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		$qht_hint_id__IN__hintIds
			
			ORDER BY	qht_hint_index ASC
		";

        $res = $ilDB->query($query);

        $questionHintList = new self();

        while ($row = $ilDB->fetchAssoc($res)) {
            $questionHint = new ilAssQuestionHint();

            ilAssQuestionHint::assignDbRow($questionHint, $row);

            $questionHintList->addHint($questionHint);
        }

        return $questionHintList;
    }

    /**
     * determines the next index to be used for a new hint
     * that is to be added to the list of existing hints
     * regarding to the question with passed question id
     * @param int $questionId
     */
    public static function getNextIndexByQuestionId($questionId): int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "
			SELECT		1 + COALESCE( MAX(qht_hint_index), 0 ) next_index
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
		";

        $res = $ilDB->queryF(
            $query,
            ['integer'],
            [(int) $questionId]
        );
        $row = $ilDB->fetchAssoc($res);

        return is_array($row) ? (int) $row['next_index'] : 1;
    }

    /**
     * Deletes all question hints relating to questions included in given question ids
     * @param list<int> $questionIds
     */
    public static function deleteHintsByQuestionIds(array $questionIds): int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $__qht_question_fi__IN__questionIds = $ilDB->in('qht_question_fi', $questionIds, false, 'integer');

        $query = "
			DELETE FROM		qpl_hints
			WHERE			$__qht_question_fi__IN__questionIds
		";

        return $ilDB->manipulate($query);
    }
}
