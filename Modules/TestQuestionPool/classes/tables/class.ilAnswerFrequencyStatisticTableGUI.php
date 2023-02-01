<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAnswerFrequencyStatisticTableGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilAnswerFrequencyStatisticTableGUI extends ilTable2GUI
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $DIC;
    
    /**
     * @var assQuestion
     */
    protected $question;
    
    /**
     * @var int
     */
    protected $questionIndex;
    
    /**
     * @var bool
     */
    protected $actionsColumnEnabled = false;
    
    /**
     * @var string
     */
    protected $additionalHtml = '';
    
    /**
     * ilAnswerFrequencyStatisticTableGUI constructor.
     * @param object $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $question
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $question)
    {
        global $DIC; /* @var ILIAS\DI\Container $this->DIC */
        
        $this->DIC = $DIC;
        
        $this->question = $question;
        
        $this->setId('tstAnswerStatistic');
        $this->setPrefix('tstAnswerStatistic');
        $this->setTitle($this->DIC->language()->txt('tst_corrections_answers_tbl'));
        
        $this->setRowTemplate('tpl.tst_corrections_answer_row.html', 'Modules/Test');
        
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context = '');
        
        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('answer');
    }
    
    /**
     * @return bool
     */
    public function isActionsColumnEnabled() : bool
    {
        return $this->actionsColumnEnabled;
    }
    
    /**
     * @param bool $actionsColumnEnabled
     */
    public function setActionsColumnEnabled(bool $actionsColumnEnabled)
    {
        $this->actionsColumnEnabled = $actionsColumnEnabled;
    }
    
    /**
     * @return string
     */
    public function getAdditionalHtml() : string
    {
        return $this->additionalHtml;
    }
    
    /**
     * @param string $additionalHtml
     */
    public function setAdditionalHtml(string $additionalHtml)
    {
        $this->additionalHtml = $additionalHtml;
    }
    
    /**
     * @param string $additionalHtml
     */
    public function addAdditionalHtml(string $additionalHtml)
    {
        $this->additionalHtml .= $additionalHtml;
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
    
    public function initColumns()
    {
        $this->addColumn($this->DIC->language()->txt('tst_corr_answ_stat_tbl_header_answer'), '');
        $this->addColumn($this->DIC->language()->txt('tst_corr_answ_stat_tbl_header_frequency'), '');
        
        foreach ($this->getData() as $row) {
            if (isset($row['addable'])) {
                $this->setActionsColumnEnabled(true);
                $this->addColumn('', '', '1%');
                break;
            }
        }
    }
    
    public function fillRow($data)
    {
        $this->tpl->setCurrentBlock('answer');
        $this->tpl->setVariable('ANSWER', $data['answer']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('frequency');
        $this->tpl->setVariable('FREQUENCY', $data['frequency']);
        $this->tpl->parseCurrentBlock();
        
        if ($this->isActionsColumnEnabled()) {
            if (isset($data['addable'])) {
                $this->tpl->setCurrentBlock('actions');
                $this->tpl->setVariable('ACTIONS', $this->buildAddAnswerAction($data));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock('actions');
                $this->tpl->touchBlock('actions');
                $this->tpl->parseCurrentBlock();
            }
        }
    }
    
    protected function buildAddAnswerAction($data)
    {
        $uid = md5($this->getQuestionIndex() . $data['answer']);
        
        $modal = $this->buildAddAnswerModalGui($uid, $data);
    
        $showModalButton = ilJsLinkButton::getInstance();
        $showModalButton->setId('btnShow_' . $uid);
        $showModalButton->setCaption('tst_corr_add_as_answer_btn');
        $showModalButton->setOnClick("$('#{$modal->getId()}').modal('show')");
            
        // TODO: migrate stuff above to ui components when ui-form supports
        // - presentation in ui-roundtrip
        // - submit signals
        
        $uiFactory = $this->DIC->ui()->factory();
        $uiRenderer = $this->DIC->ui()->renderer();
        
        $modal = $uiFactory->legacy($modal->getHTML());
        $showModalButton = $uiFactory->legacy($showModalButton->render());
        
        $this->addAdditionalHtml($uiRenderer->render($modal));
        
        return $uiRenderer->render($showModalButton);
    }
    
    protected function buildAddAnswerModalGui($uid, $data)
    {
        $formAction = $this->DIC->ctrl()->getFormAction(
            $this->getParentObject(),
            'addAnswerAsynch'
        );
        
        $form = new ilAddAnswerModalFormGUI();
        $form->setId($uid);
        $form->setFormAction($formAction);
        $form->setQuestionId($this->question->getId());
        $form->setQuestionIndex($this->getQuestionIndex());
        $form->setAnswerValue($data['answer']);
        $form->build();
        
        $bodyTpl = new ilTemplate('tpl.tst_corr_addanswermodal.html', true, true, 'Modules/TestQuestionPool');
        $bodyTpl->setVariable('BODY_UID', $uid);
        $bodyTpl->setVariable('FORM', $form->getHTML());
        $bodyTpl->setVariable('JS_UID', $uid);
        
        $modal = ilModalGUI::getInstance();
        $modal->setId('modal_' . $uid);
        $modal->setHeading($this->DIC->language()->txt('tst_corr_add_as_answer_btn'));
        
        $modal->setBody($bodyTpl->get());
        
        return $modal;
    }
}
