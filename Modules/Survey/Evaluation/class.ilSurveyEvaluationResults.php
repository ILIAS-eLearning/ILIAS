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
 * Survey evaluation answers
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyEvaluationResults
{
    protected ilLanguage $lng;
    protected SurveyQuestion $question;
    protected int $users_answered = 0;
    protected int $users_skipped = 0;
    /**
     * @var array|string
     */
    protected $mode_value;
    protected int $mode_nr_of_selections = 0;
    protected float $arithmetic_mean = 0;
    /**
     * @var string|array
     */
    protected $median;
    protected array $variables = array();
    protected array $answers = array();

    public function __construct(
        SurveyQuestion $a_question
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->question = $a_question;
    }

    public function getQuestion(): SurveyQuestion
    {
        return $this->question;
    }

    public function setUsersAnswered(int $a_value): void
    {
        $this->users_answered = $a_value;
    }

    public function getUsersAnswered(): int
    {
        return $this->users_answered;
    }

    public function setUsersSkipped(int $a_value): void
    {
        $this->users_skipped = $a_value;
    }

    public function getUsersSkipped(): int
    {
        return $this->users_skipped;
    }

    /**
     * @param array|string $a_value
     */
    public function setMode(
        $a_value,
        int $a_nr_of_selections
    ): void {
        $this->mode_value = is_array($a_value)
            ? $a_value
            : trim($a_value);
        $this->mode_nr_of_selections = $a_nr_of_selections;
    }

    /**
     * @return array|string
     */
    public function getModeValue()
    {
        return $this->mode_value;
    }

    public function getModeValueAsText(): string
    {
        if ($this->mode_value === null) {
            return "";
        }

        $res = array();

        $mvalues = $this->mode_value;
        if (!is_array($mvalues)) {
            $mvalues = array($mvalues);
        }
        sort($mvalues, SORT_NUMERIC);
        foreach ($mvalues as $value) {
            $res[] = $this->getScaleText($value);
        }

        return implode(", ", $res);
    }

    public function getModeNrOfSelections(): int
    {
        return $this->mode_nr_of_selections;
    }

    public function setMean(float $a_mean): void
    {
        $this->arithmetic_mean = $a_mean;
    }

    public function getMean(): float
    {
        return $this->arithmetic_mean;
    }

    /**
     * @param string|array $a_value
     */
    public function setMedian($a_value): void
    {
        $this->median = is_array($a_value)
            ? $a_value
            : trim($a_value);
    }

    /**
     * @return array|string
     */
    public function getMedian()
    {
        return $this->median;
    }

    public function getMedianAsText(): string
    {
        $lng = $this->lng;

        if ($this->median === null) {
            return "";
        }

        if (!is_array($this->median)) {
            return $this->getScaleText($this->median);
        }

        return $lng->txt("median_between") . " " .
            $this->getScaleText($this->median[0]) . " " .
            $lng->txt("and") . " " .
            $this->getScaleText($this->median[1]);
    }

    public function addVariable(
        ilSurveyEvaluationResultsVariable $a_variable
    ): void {
        $this->variables[] = $a_variable;
    }

    public function getVariables(): array
    {
        return $this->variables ?? [];
    }

    public function addAnswer(
        ilSurveyEvaluationResultsAnswer $a_answer
    ): void {
        $this->answers[] = $a_answer;
    }

    public function getAnswers(): array
    {
        return $this->answers ?? [];
    }

    public function getScaleText(
        int $a_value
    ): string {
        if (!count($this->variables)) {
            return $a_value;
        } else {
            foreach ($this->variables as $var) {
                if ($var->cat->scale == $a_value) {
                    return $var->cat->title . " [" . $a_value . "]";
                }
            }
        }
        return "";
    }

    protected function getCatTitle(
        int $a_value
    ): string {
        if (!count($this->variables)) {
            return $a_value;
        } else {
            foreach ($this->variables as $var) {
                if ($var->cat->scale == $a_value) {
                    return $var->cat->title;
                }
            }
        }
        return "";
    }

    public function getMappedTextAnswers(): array
    {
        $res = array();

        foreach ($this->answers as $answer) {
            if ($answer->text) {
                $res[$this->getScaleText($answer->value)][] = $answer->text;
            }
        }

        return $res;
    }

    public function getUserResults(
        int $a_active_id
    ): array {
        $res = array();

        $answers = $this->getAnswers();
        if ($answers) {
            foreach ($answers as $answer) {
                if ($answer->active_id == $a_active_id) {
                    $res[] = array(
                        $this->getScaleText($answer->value),
                        $answer->text,
                        $answer->value,
                        $this->getCatTitle($answer->value)
                    );
                }
            }
        }

        return $res;
    }
}
