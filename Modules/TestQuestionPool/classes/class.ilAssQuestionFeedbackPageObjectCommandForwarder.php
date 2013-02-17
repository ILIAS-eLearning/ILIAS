<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionAbstractPageObjectCommandForwarder.php';

/**
 * class can be used as forwarder for feedback page object contexts
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionFeedbackPageObjectCommandForwarder extends ilAssQuestionAbstractPageObjectCommandForwarder
{
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param assQuestion $questionOBJ
	 * @param ilCtrl $ctrl
	 * @param ilTabsGUI $tabs
	 * @param ilLanguage $lng
	 */
	public function __construct(assQuestion $questionOBJ, ilCtrl $ctrl, ilTabsGUI $tabs, ilLanguage $lng)
	{
		parent::__construct($questionOBJ, $ctrl, $tabs, $lng);
		
		if( !isset($_GET['feedback_id']) || !(int)$_GET['feedback_id'] )
		{
			ilUtil::sendFailure('invalid feedback id given: '.(int)$_GET['feedback_id'], true);
			$this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
		}
		
		if( !isset($_GET['feedback_type']) || !ilAssQuestionFeedback::isValidFeedbackPageObjectType($_GET['feedback_type']) )
		{
			ilUtil::sendFailure('invalid feedback type given: '.$_GET['feedback_type'], true);
			$this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
		}
	}
	
	/**
	 * forward method
	 */
	public function forward()
	{
		//$this->ensurePageObjectExists($_GET['feedback_type'], $_GET['feedback_id']);
		
		$pageObjectGUI = $this->getPageObjectGUI($_GET['feedback_type'], $_GET['feedback_id']);
		$pageObjectGUI->setEnabledTabs(true);
		
		$this->tabs->setBackTarget(
			$this->lng->txt('tst_question_feedback_back_to_feedback_form'),
			$this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW)
		);
		
		$this->ctrl->setParameter($pageObjectGUI, 'feedback_id', $_GET['feedback_id']);
		$this->ctrl->setParameter($pageObjectGUI, 'feedback_type', $_GET['feedback_type']);
		
		$this->ctrl->forwardCommand($pageObjectGUI);
	}	
}