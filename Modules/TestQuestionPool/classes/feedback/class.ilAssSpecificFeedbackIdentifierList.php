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
 * Class ilAssClozeTestFeedbackIdMap
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAssSpecificFeedbackIdentifierList implements Iterator
{
    /**
     * @var ilAssSpecificFeedbackIdentifier[]
     */
    protected array $map = array();

    protected function add(ilAssSpecificFeedbackIdentifier $identifier): void
    {
        $this->map[] = $identifier;
    }

    public function load(int $questionId): void
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $res = $DIC->database()->queryF(
            "SELECT feedback_id, question, answer FROM {$this->getSpecificFeedbackTableName()} WHERE question_fi = %s",
            array('integer'),
            array($questionId)
        );

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $identifier = new ilAssSpecificFeedbackIdentifier();

            $identifier->setQuestionId($questionId);

            $identifier->setQuestionIndex($row['question']);
            $identifier->setAnswerIndex($row['answer']);

            $identifier->setFeedbackId($row['feedback_id']);

            $this->add($identifier);
        }
    }

    /** @return false|ilAssSpecificFeedbackIdentifier */
    public function current()
    {
        return current($this->map);
    }

    /** @return false|ilAssSpecificFeedbackIdentifier */
    public function next()
    {
        return next($this->map);
    }

    /** @return int|null|string */
    public function key()
    {
        return key($this->map);
    }

    public function valid(): bool
    {
        return key($this->map) !== null;
    }

    /** @return false|ilAssSpecificFeedbackIdentifier */
    public function rewind()
    {
        return reset($this->map);
    }

    protected function getSpecificFeedbackTableName(): string
    {
        require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssClozeTestFeedback.php';
        return ilAssClozeTestFeedback::TABLE_NAME_SPECIFIC_FEEDBACK;
    }
}
