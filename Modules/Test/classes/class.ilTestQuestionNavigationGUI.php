<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilTestQuestionNavigationGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var string
	 */
	private $editSolutionCommand = '';

	/**
	 * @var string
	 */
	private $submitSolutionCommand = '';

	/**
	 * @var string
	 */
	private $discardSolutionCommand = '';

	/**
	 * @var string
	 */
	private $instantFeedbackCommand = '';

	/**
	 * @var string
	 */
	private $requestHintCommand = '';

	/**
	 * @var string
	 */
	private $showHintsCommand = '';

	/**
	 * @var bool
	 */
	private $hintRequestsExist = false;

	/**
	 * @var string
	 */
	private $questionMarkCommand = '';

	/**
	 * @var bool
	 */
	private $questionMarked = false;

	/**
	 * @var bool
	 */
	private $anythingRendered = false;
	
	/**
	 * @param ilLanguage $lng
	 */
	public function __construct(ilLanguage $lng)
	{
		$this->lng = $lng;
	}

	/**
	 * @return string
	 */
	public function getEditSolutionCommand()
	{
		return $this->editSolutionCommand;
	}

	/**
	 * @param string $editSolutionCommand
	 */
	public function setEditSolutionCommand($editSolutionCommand)
	{
		$this->editSolutionCommand = $editSolutionCommand;
	}

	/**
	 * @return string
	 */
	public function getSubmitSolutionCommand()
	{
		return $this->submitSolutionCommand;
	}

	/**
	 * @param string $submitSolutionCommand
	 */
	public function setSubmitSolutionCommand($submitSolutionCommand)
	{
		$this->submitSolutionCommand = $submitSolutionCommand;
	}

	/**
	 * @return string
	 */
	public function getDiscardSolutionCommand()
	{
		return $this->discardSolutionCommand;
	}

	/**
	 * @param string $discardSolutionCommand
	 */
	public function setDiscardSolutionCommand($discardSolutionCommand)
	{
		$this->discardSolutionCommand = $discardSolutionCommand;
	}

	/**
	 * @return string
	 */
	public function getInstantFeedbackCommand()
	{
		return $this->instantFeedbackCommand;
	}

	/**
	 * @param string $instantFeedbackCommand
	 */
	public function setInstantFeedbackCommand($instantFeedbackCommand)
	{
		$this->instantFeedbackCommand = $instantFeedbackCommand;
	}

	/**
	 * @return string
	 */
	public function getRequestHintCommand()
	{
		return $this->requestHintCommand;
	}

	/**
	 * @param string $requestHintCommand
	 */
	public function setRequestHintCommand($requestHintCommand)
	{
		$this->requestHintCommand = $requestHintCommand;
	}

	/**
	 * @return string
	 */
	public function getShowHintsCommand()
	{
		return $this->showHintsCommand;
	}

	/**
	 * @param string $showHintsCommand
	 */
	public function setShowHintsCommand($showHintsCommand)
	{
		$this->showHintsCommand = $showHintsCommand;
	}

	/**
	 * @return boolean
	 */
	public function hintRequestsExist()
	{
		return $this->hintRequestsExist;
	}

	/**
	 * @param boolean $hintRequestsExist
	 */
	public function setHintRequestsExist($hintRequestsExist)
	{
		$this->hintRequestsExist = $hintRequestsExist;
	}

	/**
	 * @return string
	 */
	public function getQuestionMarkCommand()
	{
		return $this->questionMarkCommand;
	}

	/**
	 * @param string $questionMarkCommand
	 */
	public function setQuestionMarkCommand($questionMarkCommand)
	{
		$this->questionMarkCommand = $questionMarkCommand;
	}

	/**
	 * @return boolean
	 */
	public function isQuestionMarked()
	{
		return $this->questionMarked;
	}

	/**
	 * @param boolean $questionMarked
	 */
	public function setQuestionMarked($questionMarked)
	{
		$this->questionMarked = $questionMarked;
	}

	/**
	 * @return boolean
	 */
	public function isAnythingRendered()
	{
		return $this->anythingRendered;
	}

	/**
	 * @param boolean $buttonRendered
	 */
	public function setAnythingRendered()
	{
		$this->anythingRendered = true;
	}
	
	/**
	 * @return string
	 */
	public function getHTML()
	{
		$tpl = $this->getTemplate();
		
		if( $this->getEditSolutionCommand() )
		{
			$this->renderButton(
				$tpl, $this->getEditSolutionCommand(), $this->lng->txt('edit_answer')
			);
		}
		
		if( $this->getSubmitSolutionCommand() )
		{
			$this->renderButton(
				$tpl, $this->getSubmitSolutionCommand(), $this->lng->txt('submit_answer')
			);
		}

		if( $this->getDiscardSolutionCommand() )
		{
			$this->renderButton(
				$tpl, $this->getDiscardSolutionCommand(), $this->lng->txt('discard_answer')
			);
		}

		if( $this->getInstantFeedbackCommand() )
		{
			$this->renderButton(
				$tpl, $this->getInstantFeedbackCommand(), $this->lng->txt('check')
			);
		}

		if( $this->getRequestHintCommand() )
		{
			$this->renderButton(
				$tpl, $this->getRequestHintCommand(), $this->getRequestHintButtonLabel()
			);
		}

		if( $this->getShowHintsCommand() )
		{
			$this->renderButton(
				$tpl, $this->getShowHintsCommand(), $this->lng->txt('button_show_requested_question_hints')
			);
		}

		if( $this->getQuestionMarkCommand() )
		{
			$this->renderIcon(
				$tpl, $this->getQuestionMarkCommand(), $this->getQuestionMarkIconSource(),
				$this->getQuestionMarkIconLabel(), 'ilTstMarkQuestionButton'
			);
		}
		
		if( $this->isAnythingRendered() )
		{
			$this->parseNavigation($tpl);
		}
		
		return $tpl->get();
	}
	
	private function getRequestHintButtonLabel()
	{
		if( $this->hintRequestsExist() )
		{
			return $this->lng->txt("button_request_next_question_hint");
		}
		
		return $this->lng->txt("button_request_question_hint");
	}

	private function getQuestionMarkIconLabel()
	{
		if( $this->isQuestionMarked() )
		{
			return $this->lng->txt('tst_remove_mark');
		}

		return $this->lng->txt('tst_question_mark');
	}

	private function getQuestionMarkIconSource()
	{
		if( $this->isQuestionMarked() )
		{
			return ilUtil::getImagePath('marked.svg');
		}

		return ilUtil::getImagePath('marked_.svg');
	}

	/**
	 * @return ilTemplate
	 */
	private function getTemplate()
	{
		return new ilTemplate(
			'tpl.tst_question_navigation.html', true, true, 'Modules/Test'
		);
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderButton(ilTemplate $tpl, $command, $label)
	{
		$tpl->setCurrentBlock("submit_button");
		$tpl->setVariable("SUBMIT_BTN_CMD", $command);
		$tpl->setVariable("SUBMIT_BTN_TEXT", $label);
		$tpl->parseCurrentBlock();

		$this->setAnythingRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderIcon(ilTemplate $tpl, $command, $iconSrc, $label, $cssClass)
	{
		$tpl->setCurrentBlock("submit_icon");
		$tpl->setVariable("SUBMIT_ICON_CMD", $command);
		$tpl->setVariable("SUBMIT_ICON_SRC", $iconSrc);
		$tpl->setVariable("SUBMIT_ICON_TEXT", $label);
		$tpl->setVariable("SUBMIT_ICON_CLASS", $cssClass);
		$tpl->parseCurrentBlock();

		$this->setAnythingRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function parseNavigation(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock('question_related_navigation');
		$tpl->parseCurrentBlock();
	}
}