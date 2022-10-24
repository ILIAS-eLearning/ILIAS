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
 * Class ilParserQuestionProvider
 *
 * Date: 04.12.13
 * Time: 15:04
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacQuestionProvider
{
    /*
     * @var iQuestionCondition
     */
    protected $question;

    /**
     * @var integer
     */
    protected $questionId;

    /**
     * @param integer $questionId
     */
    public function setQuestionId($questionId): void
    {
        $this->questionId = $questionId;
    }

    /**
     * @param iQuestionCondition $question
     */
    public function setQuestion(iQuestionCondition $question): void
    {
        $this->question = $question;
    }

    public function getQuestion(): assQuestion
    {
        if ($this->question === null && $this->questionId) {
            require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
            $this->question = assQuestion::_instantiateQuestion($this->questionId);
        }

        return $this->question;
    }
}
