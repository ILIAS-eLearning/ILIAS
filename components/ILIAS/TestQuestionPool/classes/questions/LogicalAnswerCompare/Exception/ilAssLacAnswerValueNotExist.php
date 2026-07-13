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
 * Class ilAssLacQuestionNotReachable
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacAnswerValueNotExist extends ilAssLacException implements ilAssLacFormAlertProvider
{
    public function __construct(
        protected ?int $question_index,
        protected string $value,
        protected ?int $answer_index = null
    ) {
        if ($this->getQuestionIndex() === null && $this->getAnswerIndex() === null) {
            $msg = "The value \"{$value}\" does not exist for the current question";
        } elseif ($this->getQuestionIndex() === null) {
            $msg = "The value \"{$value}\" does not exist for the answer with index \"{$this->getAnswerIndex()}\" of the current question";
        } elseif ($this->getAnswerIndex() === null) {
            $msg = "The value \"{$value}\" does not exist for the question Q{$this->getQuestionIndex()}";
        } else {
            $msg = "The value \"{$value}\" does not exist for the question Q{$this->getQuestionIndex()}[{$this->getAnswerIndex()}]";
        }

        parent::__construct($msg);
    }

    public function getQuestionIndex(): ?int
    {
        return $this->question_index;
    }

    public function getAnswerIndex(): ?int
    {
        return $this->answer_index;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormAlert(ilLanguage $lng): string
    {
        if ($this->getQuestionIndex() === null && $this->getAnswerIndex() === null) {
            return sprintf(
                $lng->txt('ass_lac_answer_value_not_exists_cur_qst_one_answer'),
                $this->getValue()
            );
        }

        if ($this->getQuestionIndex() === null) {
            return sprintf(
                $lng->txt('ass_lac_answer_value_not_exists_cur_qst'),
                $this->getValue(),
                $this->getAnswerIndex()
            );
        }

        if ($this->getAnswerIndex() === null) {
            return sprintf(
                $lng->txt('ass_lac_answer_value_not_exists_one_answer'),
                $this->getValue(),
                $this->getQuestionIndex()
            );
        }

        return sprintf(
            $lng->txt('ass_lac_answer_value_not_exists'),
            $this->getValue(),
            $this->getQuestionIndex(),
            $this->getAnswerIndex()
        );
    }
}
