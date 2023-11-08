<?php

declare(strict_types=1);

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
 ********************************************************************
 */

class ilPollAnswersRenderer
{
    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public function render(
        ilTemplate $tpl,
        ilPollAnswersHandler $answers,
        bool $disable_input
    ): void {
        $single_answer = ($answers->getAnswerLimit() === 1);

        if (!is_null($last_vote = $answers->popLastVoteFromSession())) {
            if (!$single_answer && empty($last_vote)) {
                $error = $this->lng->txt("poll_vote_error_multi_no_answer");
            } elseif (!$single_answer) {
                $error = sprintf(
                    $this->lng->txt("poll_vote_error_multi"),
                    $answers->getAnswerLimit()
                );
            } else {
                $error = $this->lng->txt("poll_vote_error_single");
            }

            $tpl->setCurrentBlock("error_bl");
            $tpl->setVariable("FORM_ERROR", $error);
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock('answer');
        foreach ($answers->getAnswers() as $id => $answer) {
            $this->renderAnswer(
                $tpl,
                $id,
                $answer,
                $last_vote,
                $single_answer,
                $disable_input
            );
        }

        $tpl->setVariable("URL_FORM", $answers->getVoteURL());
        $tpl->setVariable("CMD_FORM", $answers->getVoteCommand());
        $tpl->setVariable("TXT_SUBMIT", $this->lng->txt("poll_vote"));
    }

    protected function renderAnswer(
        ilTemplate $tpl,
        int $id,
        string $answer,
        ?array $last_vote,
        bool $single_answer,
        bool $disable_input
    ): void {
        if ($single_answer) {
            $tpl->setVariable("ANSWER_INPUT", "radio");
            $tpl->setVariable("ANSWER_NAME", "aw");
        } else {
            $tpl->setVariable("ANSWER_INPUT", "checkbox");
            $tpl->setVariable("ANSWER_NAME", "aw[]");

            $status = [];
            if (!empty($last_vote) && is_array($last_vote) && in_array($id, $last_vote)) {
                $status[] = 'checked="checked"';
            }
            if ($disable_input) {
                $status[] = 'disabled';
            }
            if ($status) {
                $tpl->setVariable("ANSWER_STATUS", implode(' ', $status));
            }
        }
        $tpl->setVariable("VALUE_ANSWER", $id);
        $tpl->setVariable("TXT_ANSWER_VOTE", nl2br($answer));
        $tpl->parseCurrentBlock();
    }
}
