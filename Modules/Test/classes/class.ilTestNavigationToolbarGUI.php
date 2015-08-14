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
	private $suspendTestButtonEnabled = false;

	/**
	 * @var bool
	 */
	private $questionListButtonEnabled = false;

	/**
	 * @var bool
	 */
	private $questionTreeButtonEnabled = false;

	/**
	 * @var bool
	 */
	private $questionTreeVisible = false;

	/**
	 * @var bool
	 */
	private $charSelectorButtonEnabled = false;
	
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
	public function isSuspendTestButtonEnabled()
	{
		return $this->suspendTestButtonEnabled;
	}

	/**
	 * @param boolean $suspendTestButtonEnabled
	 */
	public function setSuspendTestButtonEnabled($suspendTestButtonEnabled)
	{
		$this->suspendTestButtonEnabled = $suspendTestButtonEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionListButtonEnabled()
	{
		return $this->questionListButtonEnabled;
	}

	/**
	 * @param boolean $questionListButtonEnabled
	 */
	public function setQuestionListButtonEnabled($questionListButtonEnabled)
	{
		$this->questionListButtonEnabled = $questionListButtonEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionTreeButtonEnabled()
	{
		return $this->questionTreeButtonEnabled;
	}

	/**
	 * @param boolean $questionTreeButtonEnabled
	 */
	public function setQuestionTreeButtonEnabled($questionTreeButtonEnabled)
	{
		$this->questionTreeButtonEnabled = $questionTreeButtonEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionTreeVisible()
	{
		return $this->questionTreeVisible;
	}

	/**
	 * @param boolean $questionTreeVisible
	 */
	public function setQuestionTreeVisible($questionTreeVisible)
	{
		$this->questionTreeVisible = $questionTreeVisible;
	}

	/**
	 * @return boolean
	 */
	public function isCharSelectorButtonEnabled()
	{
		return $this->charSelectorButtonEnabled;
	}

	/**
	 * @param boolean $charSelectorButtonEnabled
	 */
	public function setCharSelectorButtonEnabled($charSelectorButtonEnabled)
	{
		$this->charSelectorButtonEnabled = $charSelectorButtonEnabled;
	}
	
	public function build()
	{
		if( $this->isSuspendTestButtonEnabled() )
		{
			$this->addSuspendTestButton();
		}
		
		if( $this->isQuestionListButtonEnabled() )
		{
			$this->addQuestionListButton();
		}

		if( $this->isQuestionTreeButtonEnabled() )
		{
			$this->addQuestionTreeButton();
		}

		if( $this->isCharSelectorButtonEnabled() )
		{
			$this->addCharSelectorButton();
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
	
	private function addQuestionTreeButton()
	{
		$btn = ilSubmitButton::getInstance();
		$btn->setCommand('togglesidelist');
		if( $this->isQuestionTreeVisible() )
		{
			$btn->setCaption('tst_hide_side_list');
		}
		else
		{
			$btn->setCaption('tst_show_side_list');
		}
		$this->addButtonInstance($btn);
	}

	private function addCharSelectorButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setId('charselectorbutton');
		$btn->addCSSClass('ilCharSelectorToggle');
		$btn->setCaption('char_selector_btn_label');
		$this->addButtonInstance($btn);
	}
}