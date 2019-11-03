<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Toolbar;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ilTestPlayerNavButton;
use ilToolbarGUI;

/**
 * Class QuestionProcessingToolbarGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionProcessingToolbarGUI extends ilToolbarGUI
{

    /**
     * QuestionConfig
     */
    protected $question_config;

	/**
	 * @var bool
	 */
	//private $suspend_test_button_enabled = false;

	/**
	 * @var bool
	 */
	//private $question_list_button_enabled = false;

	/**
	 * @var bool
	 */
	//private $questionTreeButtonEnabled = false;

	/**
	 * @var bool
	 */
	//private $questionTreeVisible = false;

	/**
	 * @var bool
	 */
	//private $questionSelectionButtonEnabled = false;

	/**
	 * @var bool
	 */
	//private $finishTestButtonEnabled = false;
	
	/**
	 * @var string
	 */
	//private $finishTestCommand = '';

	/**
	 * @var bool
	 */
	//private $finishTestButtonPrimary = false;

	/**
	 * @var bool
	 */
	//private $disabledStateEnabled = false;


    /**
     * QuestionProcessingToolbarGUI constructor.
     *
     * @param QuestionConfig $question_config
     */
	public function __construct(QuestionConfig $question_config)
	{
		parent::__construct();

		$this->question_config = $question_config;
		$this->build();
	}

	
	protected function build()
	{
		/*if( $this->isQuestionTreeButtonEnabled() )
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
		}*/

		if(is_object($this->question_config->getShowNextQuestionAction()))
		{
			$this->addFinishTestButton();
		}
	}
	
	private function addSuspendTestButton()
	{
		$btn = ilTestPlayerNavButton::getInstance();
		$btn->setNextCommand(ilTestPlayerCommands::SUSPEND_TEST);
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::SUSPEND_TEST
		));
		$btn->setCaption('cancel_test');
		//$btn->setDisabled($this->isDisabledStateEnabled());
		$btn->addCSSClass('ilTstNavElem');
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionListButton()
	{
		$btn = ilTestPlayerNavButton::getInstance();
		$btn->setNextCommand(ilTestPlayerCommands::QUESTION_SUMMARY);
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::QUESTION_SUMMARY
		));
		$btn->setCaption('question_summary_btn');
		//$btn->setDisabled($this->isDisabledStateEnabled());
		$btn->addCSSClass('ilTstNavElem');
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionSelectionButton()
	{
		$btn = ilTestPlayerNavButton::getInstance();
		$btn->setNextCommand(ilTestPlayerCommands::SHOW_QUESTION_SELECTION);
		$btn->setUrl($this->ctrl->getLinkTarget(
			$this->playerGUI, ilTestPlayerCommands::SHOW_QUESTION_SELECTION
		));
		$btn->setCaption('tst_change_dyn_test_question_selection');
		//$btn->setDisabled($this->isDisabledStateEnabled());
		$btn->addCSSClass('ilTstNavElem');
		$this->addButtonInstance($btn);
	}
	
	private function addQuestionTreeButton()
	{
		$btn = ilTestPlayerNavButton::getInstance();
		$btn->setNextCommand(ilTestPlayerCommands::TOGGLE_SIDE_LIST);
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
		//$btn->setDisabled($this->isDisabledStateEnabled());
		$btn->addCSSClass('ilTstNavElem');
		$this->addButtonInstance($btn);
	}

	protected function addFinishTestButton()
	{
	    global $DIC;

		$btn = ilTestPlayerNavButton::getInstance();
		$btn->setNextCommand($this->question_config->getShowFinishTestSessionAction()->getCommand());
		$btn->setUrl($DIC->ctrl()->getLinkTargetByClass(
			$this->question_config->getShowFinishTestSessionAction()->getCtrlStack(), $this->question_config->getShowFinishTestSessionAction()->getCommand()
		));
		$btn->setCaption('finish_test');
		//$btn->setDisabled($this->isDisabledStateEnabled());
		//$btn->setPrimary($this->isFinishTestButtonPrimary());
		$btn->addCSSClass('ilTstNavElem');
		$this->addButtonInstance($btn);
	}
}