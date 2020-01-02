<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Survey evaluation answers
 *
 * @author	Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesSurvey
 */
class ilSurveyEvaluationResults
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $question; // [SurveyQuestion]
    protected $users_answered; // [int]
    protected $users_skipped; // [int]
    protected $mode_value; // [int]
    protected $mode_nr_of_selections; // [int]
    protected $arithmetic_mean; // [float]
    protected $median; // [string|float]
    protected $variables = array(); // [array]
    protected $answers = array(); // [array]
    
    public function __construct(SurveyQuestion $a_question)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->question = $a_question;
    }
    
    public function getQuestion()
    {
        return $this->question;
    }
    
    public function setUsersAnswered($a_value)
    {
        $this->users_answered = (int) $a_value;
    }
    
    public function getUsersAnswered()
    {
        return $this->users_answered;
    }
    
    public function setUsersSkipped($a_value)
    {
        $this->users_skipped = (int) $a_value;
    }
    
    public function getUsersSkipped()
    {
        return $this->users_skipped;
    }
    
    public function setMode($a_value, $a_nr_of_selections)
    {
        $this->mode_value = is_array($a_value)
            ? $a_value
            : trim($a_value);
        $this->mode_nr_of_selections = (int) $a_nr_of_selections;
    }
    
    public function getModeValue()
    {
        return $this->mode_value;
    }
    
    public function getModeValueAsText()
    {
        if ($this->mode_value === null) {
            return;
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
    
    public function getModeNrOfSelections()
    {
        return $this->mode_nr_of_selections;
    }
    
    public function setMean($a_mean)
    {
        $this->arithmetic_mean = (float) $a_mean;
    }
    
    public function getMean()
    {
        return $this->arithmetic_mean;
    }
    
    public function setMedian($a_value)
    {
        $this->median = is_array($a_value)
            ? $a_value
            : trim($a_value);
    }
    
    public function getMedian()
    {
        return $this->median;
    }
    
    public function getMedianAsText()
    {
        $lng = $this->lng;
        
        if ($this->median === null) {
            return;
        }
        
        if (!is_array($this->median)) {
            return $this->getScaleText($this->median);
        } else {
            return $lng->txt("median_between") . " " .
                $this->getScaleText($this->median[0]) . " " .
                $lng->txt("and") . " " .
                $this->getScaleText($this->median[1]);
        }
    }
    
    public function addVariable(ilSurveyEvaluationResultsVariable $a_variable)
    {
        $this->variables[] = $a_variable;
    }
    
    public function getVariables()
    {
        if (sizeof($this->variables)) {
            return $this->variables;
        }
    }
    
    public function addAnswer(ilSurveyEvaluationResultsAnswer $a_answer)
    {
        $this->answers[] = $a_answer;
    }
    
    public function getAnswers()
    {
        if (sizeof($this->answers)) {
            return $this->answers;
        }
    }
    
    protected function getScaleText($a_value)
    {
        if (!sizeof($this->variables)) {
            return $a_value;
        } else {
            foreach ($this->variables as $var) {
                if ($var->cat->scale == $a_value) {
                    return $var->cat->title . " [" . $a_value . "]";
                }
            }
        }
    }

    protected function getCatTitle($a_value)
    {
        if (!sizeof($this->variables)) {
            return $a_value;
        } else {
            foreach ($this->variables as $var) {
                if ($var->cat->scale == $a_value) {
                    return $var->cat->title;
                }
            }
        }
    }

    public function getMappedTextAnswers()
    {
        $res = array();
        
        foreach ($this->answers as $answer) {
            if ($answer->text) {
                $res[$this->getScaleText($answer->value)][] = $answer->text;
            }
        }
        
        return $res;
    }
        
    public function getUserResults($a_active_id)
    {
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

class ilSurveyEvaluationResultsVariable
{
    public $cat; // [SurveyCategory]
    public $abs; // [int]
    public $perc; // [float]
    
    public function __construct(ilSurveyCategory $a_cat, $a_abs, $a_perc)
    {
        $this->cat = $a_cat;
        $this->abs = (int) $a_abs;
        $this->perc = (float) $a_perc;
    }
}

class ilSurveyEvaluationResultsAnswer
{
    public $active_id; // [int]
    public $value; // [int|float]
    public $text; // [string]
    
    public function __construct($a_active_id, $a_value, $a_text)
    {
        $this->active_id = (int) $a_active_id;
        $this->value = $a_value;
        $this->text = trim($a_text);
    }
}
