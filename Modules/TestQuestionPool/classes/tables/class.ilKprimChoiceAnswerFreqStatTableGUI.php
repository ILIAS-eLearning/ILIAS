<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/tables/class.ilAnswerFrequencyStatisticTableGUI.php';

/**
 * Class ilKprimChoiceAnswerFreqStatTableGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilKprimChoiceAnswerFreqStatTableGUI extends ilAnswerFrequencyStatisticTableGUI
{
    /**
     * @var assKprimChoice
     */
    protected $question;
    
    protected function getTrueOptionLabel()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return $this->question->getTrueOptionLabelTranslation(
            $DIC->language(),
            $this->question->getOptionLabel()
        );
    }
    
    protected function getFalseOptionLabel()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        return $this->question->getFalseOptionLabelTranslation(
            $DIC->language(),
            $this->question->getOptionLabel()
        );
    }
    
    public function initColumns()
    {
        $this->addColumn('Answer', '');
        
        $this->addColumn(
            'Frequency: ' . $this->getTrueOptionLabel(),
            '',
            '25%'
        );
        
        $this->addColumn(
            'Frequency: ' . $this->getFalseOptionLabel(),
            '',
            '25%'
        );
    }
    
    public function fillRow($data)
    {
        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', $data['answer']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $data['frequency_true']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $data['frequency_false']);
        $this->tpl->parseCurrentBlock();
    }
}
