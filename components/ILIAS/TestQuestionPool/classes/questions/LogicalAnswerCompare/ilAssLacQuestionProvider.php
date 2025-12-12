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
 * Class ilParserQuestionProvider
 *
 * Date: 04.12.13
 * Time: 15:04
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacQuestionProvider
{
    protected ?assQuestion $question = null;

    protected ?int $question_id = null;

    public function setQuestionId(int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function setQuestion(assQuestion $question): void
    {
        $this->question = $question;
    }

    public function getQuestion(): ?assQuestion
    {
        if ($this->question === null && $this->question_id !== null) {
            $this->question = assQuestion::instantiateQuestion($this->question_id);
        }

        return $this->question;
    }
}
