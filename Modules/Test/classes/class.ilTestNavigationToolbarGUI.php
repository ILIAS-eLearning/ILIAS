<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestNavigationToolbarGUI extends ilToolbarGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTestPlayerAbstractGUI
	 */
	protected $playerGUI;

	/**
	 * @var bool
	 */
	private $suspendTestEnabled = false;

	/**
	 * @var bool
	 */
	private $questionListEnabled = false;

	/**
	 * @param ilCtrl $ctrl
	 * @param ilLanguage $lng
	 * @param ilTestPlayerAbstractGUI $playerGUI
	 */
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilTestPlayerAbstractGUI $playerGUI)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->playerGUI = $playerGUI;
		
		parent::__construct();
	}

	/**
	 * @return boolean
	 */
	public function isSuspendTestEnabled()
	{
		return $this->suspendTestEnabled;
	}

	/**
	 * @param boolean $suspendTestEnabled
	 */
	public function setSuspendTestEnabled($suspendTestEnabled)
	{
		$this->suspendTestEnabled = $suspendTestEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionListEnabled()
	{
		return $this->questionListEnabled;
	}

	/**
	 * @param boolean $questionListEnabled
	 */
	public function setQuestionListEnabled($questionListEnabled)
	{
		$this->questionListEnabled = $questionListEnabled;
	}
	
	public function build()
	{
		if( $this->isSuspendTestEnabled() )
		{
			$this->addSuspendTestButton();
		}
		
		if( $this->isQuestionListEnabled() )
		{
			$this->addQuestionListButton();
		}
	}
	
	private function addSuspendTestButton()
	{
		$btn = ilSubmitButton::getInstance();
		$btn->setCommand('outIntroductionPage');
		$btn->setCaption('cancel_test');
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionListButton()
	{
		$btn = ilSubmitButton::getInstance();
		$btn->setCommand('showQuestionList');
		$btn->setCaption('question_summary');
		$this->addButtonInstance($btn);
	}
}