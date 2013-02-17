<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionAbstractPageObjectCommandForwarder.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';

/**
 * class can be used as forwarder for hint page object contexts
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintPageObjectCommandForwarder extends ilAssQuestionAbstractPageObjectCommandForwarder
{
	/**
	 * presentation mode for authoring
	 */
	const PRESENTATION_MODE_AUTHOR = 'PRESENTATION_MODE_AUTHOR';
	
	/**
	 * presentation mode for requesting
	 */
	const PRESENTATION_MODE_REQUEST = 'PRESENTATION_MODE_REQUEST';
	
	/**
	 * currently set presentation mode
	 *
	 * @var string
	 */
	protected $presentationMode = null;
	
	/**
	 * object instance of question hint
	 *
	 * @access protected
	 * @var ilAssQuestionHint
	 */
	protected $questionHint = null;
	
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
		
		$this->questionHint = new ilAssQuestionHint();

		if( !isset($_GET['hint_id']) || !(int)$_GET['hint_id'] || !$this->questionHint->load((int)$_GET['hint_id']) )
		{
			ilUtil::sendFailure('invalid hint id given: '.(int)$_GET['hint_id'], true);
			$this->ctrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
		}
	}
	
	/**
	 * forward method
	 * 
	 * @throws ilTestQuestionPoolException
	 */
	public function forward()
	{
		switch( $this->getPresentationMode() )
		{
			case self::PRESENTATION_MODE_AUTHOR:
				
				$pageObjectGUI = $this->buildAuthorPresentationPageObjectGUI();
				break;
			
			case self::PRESENTATION_MODE_REQUEST:
				
				$pageObjectGUI = $this->buildRequestPresentationPageObjectGUI();
				break;
		}

		$this->ctrl->setParameter($pageObjectGUI, 'hint_id', $this->questionHint->getId());
		
		$this->ctrl->forwardCommand($pageObjectGUI);
	}
	
	/**
	 * forwards the command to page object gui for author presentation
	 * 
	 * @access private
	 * @return ilPageObjectGUI
	 */
	private function buildRequestPresentationPageObjectGUI()
	{
		$this->tabs->setBackTarget(
			$this->lng->txt('tst_question_hints_back_to_hint_list'),
			$this->ctrl->getLinkTargetByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST)
		);
		
		$pageObjectGUI = $this->getPageObjectGUI(
				$this->questionHint->getPageObjectType(), $this->questionHint->getId()
		);
		
		$pageObjectGUI->setEnabledTabs(false);
		
		$pageObjectGUI->setPresentationTitle(
				ilAssQuestionHint::getHintIndexLabel($this->lng, $this->questionHint->getIndex())
		);
		
		return $pageObjectGUI;
	}
	
	/**
	 * forwards the command to page object gui for author presentation
	 * 
	 * @access private
	 * @return ilPageObjectGUI
	 */
	private function buildAuthorPresentationPageObjectGUI()
	{
		$this->tabs->setBackTarget(
			$this->lng->txt('tst_question_hints_back_to_hint_list'),
			$this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST)
		);
		
		$this->ensurePageObjectExists(
				$this->questionHint->getPageObjectType(), $this->questionHint->getId()
		);
		
		$pageObjectGUI = $this->getPageObjectGUI(
				$this->questionHint->getPageObjectType(), $this->questionHint->getId()
		);
		
		$pageObjectGUI->setEnabledTabs(true);
		
		return $pageObjectGUI;
	}
	
	/**
	 * getter for presentation mode
	 * 
	 * @return string
	 */
	public function getPresentationMode()
	{
		return $this->presentationMode;
	}
	
	/**
	 * setter for presentation mode
	 * 
	 * @param string $presentationMode
	 * @throws ilTestQuestionPoolException
	 */
	public function setPresentationMode($presentationMode)
	{
		switch( $presentationMode )
		{
			case self::PRESENTATION_MODE_AUTHOR:
			case self::PRESENTATION_MODE_REQUEST:
				
				$this->presentationMode = $presentationMode;
				break;
			
			default: throw new ilTestQuestionPoolException('invalid presentation mode given: '.$presentationMode);
		}
	}
}