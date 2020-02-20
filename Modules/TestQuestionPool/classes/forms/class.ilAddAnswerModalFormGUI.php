<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAddAnswerModalFormGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAddAnswerModalFormGUI extends ilPropertyFormGUI
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $DIC;
    
    /**
     * @var int
     */
    protected $questionId = 0;
    
    /**
     * @var int
     */
    protected $questionIndex = 0;
    
    /**
     * @var string
     */
    protected $answerValue = '';
    
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->DIC = $DIC;
        
        parent::__construct();
    }
    
    public function setValuesByArray($a_values, $a_restrict_to_value_keys = false)
    {
        $this->getItemByPostVar('answer_presentation')->setValue($a_values['answer']);
        parent::setValuesByArray($a_values, $a_restrict_to_value_keys);
    }
    
    /**
     * @return int
     */
    public function getQuestionId() : int
    {
        return $this->questionId;
    }
    
    /**
     * @param int $questionId
     */
    public function setQuestionId(int $questionId)
    {
        $this->questionId = $questionId;
    }
    
    /**
     * @return int
     */
    public function getQuestionIndex() : int
    {
        return $this->questionIndex;
    }
    
    /**
     * @param int $questionIndex
     */
    public function setQuestionIndex(int $questionIndex)
    {
        $this->questionIndex = $questionIndex;
    }
    
    /**
     * @return string
     */
    public function getAnswerValue() : string
    {
        return $this->answerValue;
    }
    
    /**
     * @param string $answerValue
     */
    public function setAnswerValue(string $answerValue)
    {
        $this->answerValue = $answerValue;
    }

    public function build()
    {
        $answer = new ilNonEditableValueGUI($this->DIC->language()->txt('answer'));
        $answer->setPostVar('answer_presentation');
        $answer->setValue($this->getAnswerValue());
        $this->addItem($answer);
        
        $points = new ilNumberInputGUI($this->DIC->language()->txt('points'), 'points');
        $points->setRequired(true);
        $points->setSize(6);
        $points->allowDecimals(true);
        $points->setMinvalueShouldBeGreater(true);
        $points->setMinValue(0);
        $this->addItem($points);
        
        $hiddenAnswerValue = new ilHiddenInputGUI('answer');
        $hiddenAnswerValue->setValue($this->getAnswerValue());
        $this->addItem($hiddenAnswerValue);
        
        $hiddenQuestionId = new ilHiddenInputGUI('qid');
        $hiddenQuestionId->setValue($this->getQuestionId());
        $this->addItem($hiddenQuestionId);
        
        $hiddenQuestionIndex = new ilHiddenInputGUI('qindex');
        $hiddenQuestionIndex->setValue($this->getQuestionIndex());
        $this->addItem($hiddenQuestionIndex);
        
        $this->addCommandButton('addAnswerAsynch', $this->DIC->language()->txt('save'));
        $this->addCommandButton('cancel', $this->DIC->language()->txt('cancel'));
    }
}
