<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';

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
	private $questionSelectionButtonEnabled = false;

	/**
	 * @var bool
	 */
	private $finishTestButtonEnabled = false;
	
	/**
	 * @var string
	 */
	private $finishTestCommand = '';

	/**
	 * @var bool
	 */
	private $disabledStateEnabled = false;
	
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
	public function isQuestionSelectionButtonEnabled()
	{
		return $this->questionSelectionButtonEnabled;
	}

	/**
	 * @param boolean $questionSelectionButtonEnabled
	 */
	public function setQuestionSelectionButtonEnabled($questionSelectionButtonEnabled)
	{
		$this->questionSelectionButtonEnabled = $questionSelectionButtonEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isFinishTestButtonEnabled()
	{
		return $this->finishTestButtonEnabled;
	}

	/**
	 * @param boolean $finishTestButtonEnabled
	 */
	public function setFinishTestButtonEnabled($finishTestButtonEnabled)
	{
		$this->finishTestButtonEnabled = $finishTestButtonEnabled;
	}

	/**
	 * @return string
	 */
	public function getFinishTestCommand()
	{
		return $this->finishTestCommand;
	}

	/**
	 * @param string $finishTestCommand
	 */
	public function setFinishTestCommand($finishTestCommand)
	{
		$this->finishTestCommand = $finishTestCommand;
	}

	/**
	 * @return boolean
	 */
	public function isDisabledStateEnabled()
	{
		return $this->disabledStateEnabled;
	}

	/**
	 * @param boolean $disabledStateEnabled
	 */
	public function setDisabledStateEnabled($disabledStateEnabled)
	{
		$this->disabledStateEnabled = $disabledStateEnabled;
	}
	
	public function build()
	{
		if( $this->isQuestionTreeButtonEnabled() )
		{
			$this->addQuestionTreeButton();
		}

		if( $this->isQuestionListButtonEnabled() )
		{
			$this->addQuestionListButton();
		}

		if( $this->isQuestionSelectionButtonEnabled() )
		{
			$this->addQuestionSelectionButton();
		}

		if( $this->isSuspendTestButtonEnabled() )
		{
			$this->addSuspendTestButton();
		}

		if( $this->isFinishTestButtonEnabled() )
		{
			$this->addFinishTestButton();
		}
	}
	
	private function addSuspendTestButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::SUSPEND_TEST
		));
		$btn->setCaption('cancel_test');
		$btn->setDisabled($this->isDisabledStateEnabled());
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionListButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::QUESTION_SUMMARY
		));
		$btn->setCaption('question_summary_btn');
		$btn->setDisabled($this->isDisabledStateEnabled());
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionSelectionButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::SHOW_QUESTION_SELECTION
		));
		$btn->setCaption('tst_change_dyn_test_question_selection');
		$btn->setDisabled($this->isDisabledStateEnabled());
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionTreeButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::TOGGLE_SIDE_LIST
		));
		if( $this->isQuestionTreeVisible() )
		{
			$btn->setCaption('tst_hide_side_list');
		}
		else
		{
			$btn->setCaption('tst_show_side_list');
		}
		$btn->setDisabled($this->isDisabledStateEnabled());
		$this->addButtonInstance($btn);
	}

	private function addFinishTestButton()
	{
		$btn = ilLinkButton::getInstance();
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, $this->getFinishTestCommand()
		));
		$btn->setCaption('finish_test');
		$btn->setDisabled($this->isDisabledStateEnabled());
		
		$this->addButtonInstance($btn);
	}
}