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
// hey: fixRandomTestBuildable - iterator interface for collection
class ilTestRandomQuestionSetQuestionCollection implements
    Iterator
// hey.
{
    private $questions = array();

    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function addQuestion(ilTestRandomQuestionSetQuestion $question)
    {
        $this->questions[] = $question;
    }

    /**
     * @return ilTestRandomQuestionSetQuestion|false
     */
    public function current()
    {
        return current($this->questions);
    }

    /**
     * @return ilTestRandomQuestionSetQuestion|false
     */
    public function next()
    {
        return next($this->questions);
    }

    public function key(): string
    {
        return key($this->questions);
    }

    public function valid(): bool
    {
        return key($this->questions) !== null;
    }

    /**
     * @return ilTestRandomQuestionSetQuestion|false
     */
    public function rewind()
    {
        return reset($this->questions);
    }
    // hey.

    public function isGreaterThan($amount): bool
    {
        return count($this->questions) > $amount;
    }

    public function isSmallerThan($amount): bool
    {
        return count($this->questions) < $amount;
    }

    /**
     * @param int $requiredAmount
     */
    public function getMissingCount($requiredAmount): int
    {
        // hey: fixRandomTestBuildable - fix returning missing count instead of difference (neg values!)
        $difference = $requiredAmount - count($this->questions);
        $missingCount = $difference < 0 ? 0 : $difference;
        return $missingCount;
        // hey.
    }

    public function shuffleQuestions()
    {
        shuffle($this->questions);
    }

    public function mergeQuestionCollection(self $questionCollection)
    {
        $this->questions = array_merge($this->questions, $questionCollection->getQuestions());
    }

    public function getUniqueQuestionCollection(): ilTestRandomQuestionSetQuestionCollection
    {
        $uniqueQuestions = array();

        foreach ($this->getQuestions() as $question) {
            /* @var ilTestRandomQuestionSetQuestion $question */

            if (!isset($uniqueQuestions[$question->getQuestionId()])) {
                $uniqueQuestions[$question->getQuestionId()] = $question;
            }
        }

        $uniqueQuestionCollection = new self();
        $uniqueQuestionCollection->setQuestions($uniqueQuestions);

        return $uniqueQuestionCollection;
    }

    public function getRelativeComplementCollection(self $questionCollection): ilTestRandomQuestionSetQuestionCollection
    {
        // hey: fixRandomTestBuildable - comment for refactoring
        /**
         * actually i would like to consider $this as quantity A
         * passed $questionCollection is should be considered as quantity B
         *
         * --> relative complement usually means all element from B missing in A
         *
         * indeed we are considering $questionCollection as A and $this as B currently (!)
         * when changing, do not forget to switch caller and param for all usages (!)
         */
        // hey.

        $questionIds = array_flip($questionCollection->getInvolvedQuestionIds());

        $relativeComplementCollection = new self();

        foreach ($this->getQuestions() as $question) {
            if (!isset($questionIds[$question->getQuestionId()])) {
                $relativeComplementCollection->addQuestion($question);
            }
        }

        return $relativeComplementCollection;
    }

    public function getIntersectionCollection(self $questionCollection): ilTestRandomQuestionSetQuestionCollection
    {
        $questionIds = array_flip($questionCollection->getInvolvedQuestionIds());

        $intersectionCollection = new self();

        foreach ($this->getQuestions() as $question) {
            if (!isset($questionIds[$question->getQuestionId()])) {
                continue;
            }

            $intersectionCollection->addQuestion($question);
        }

        return $intersectionCollection;
    }

    public function getQuestionAmount(): int
    {
        return count($this->getQuestions());
    }
    // hey.

    public function getInvolvedQuestionIds(): array
    {
        $questionIds = array();

        foreach ($this->getQuestions() as $question) {
            $questionIds[] = $question->getQuestionId();
        }

        return $questionIds;
    }

    public function getRandomQuestionCollection($requiredAmount): ilTestRandomQuestionSetQuestionCollection
    {
        $randomKeys = $this->getRandomArrayKeys($this->questions, $requiredAmount);

        $randomQuestionCollection = new self();

        foreach ($randomKeys as $randomKey) {
            $randomQuestionCollection->addQuestion($this->questions[$randomKey]);
        }

        return $randomQuestionCollection;
    }

    private function getRandomArrayKeys($array, $numKeys)
    {
        if ($numKeys < 1) {
            return array();
        }

        if ($numKeys > 1) {
            return array_rand($array, $numKeys);
        }

        return array( array_rand($array, $numKeys) );
    }
}
