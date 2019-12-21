<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssLongmenuCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class ilAssLongmenuCorrectionsInputGUI extends ilAnswerWizardInputGUI
{
    public function checkInput()
    {
        return true;
    }
    
    public function insert($a_tpl)
    {
        $tpl = new ilTemplate('tst.longmenu_corrections_input.html', true, true, 'Modules/TestQuestionPool');
        
        $tpl->setVariable('ANSWERS_MODAL', $this->buildAnswersModal()->getHTML());
        
        $tpl->setVariable('TAG_INPUT', $this->buildTagInput()->render());
        
        $tpl->setVariable('NUM_ANSWERS', $this->values['answers_all_count']);
        
        $tpl->setVariable('TXT_ANSWERS', $this->lng->txt('answer_options'));
        $tpl->setVariable('TXT_SHOW', $this->lng->txt('show'));
        $tpl->setVariable('TXT_CORRECT_ANSWERS', $this->lng->txt('correct_answers'));
        
        $tpl->setVariable('POSTVAR', $this->getPostVar());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
    
    protected function buildAnswersModal()
    {
        $closeButton = ilJsLinkButton::getInstance();
        $closeButton->setCaption('close');
        $closeButton->setOnClick("$('#modal_{$this->getPostVar()}').modal('hide');");
        
        $modal = ilModalGUI::getInstance();
        $modal->setId('modal_' . $this->getPostVar());
        $modal->setHeading($this->lng->txt('answer_options'));
        $modal->addButton($closeButton);
        
        $inp = new ilTextWizardInputGUI('', '');
        $inp->setValues(current($this->values['answers_all']));
        $inp->setDisabled(true);
        
        $modal->setBody($inp->render());
        
        return $modal;
    }
    
    protected function buildTagInput()
    {
        $tagInput = new ilTagInputGUI('', $this->getPostVar() . '_tags');
        $tagInput->setTypeAhead(true);
        $tagInput->setTypeAheadMinLength(1);
        
        $tagInput->setOptions($this->values['answers_correct']);
        $tagInput->setTypeAheadList($this->values['answers_all']);
        
        return $tagInput;
    }
}
