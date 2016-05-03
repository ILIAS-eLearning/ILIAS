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
	 * @var bool
	 */
	private $questionWorkedThrough = false;

	/**
	 * @var string
	 */
	private $submitSolutionCommand = '';

	/**
	 * @var bool
	 */
	private $discardSolutionButtonEnabled = false;

	/**
	 * @var string
	 */
	private $skipQuestionLinkTarget = '';

	/**
	 * @var string
	 */
	private $instantFeedbackCommand = '';

	/**
	 * @var bool
	 */
	private $answerFreezingEnabled = false;

	/**
	 * @var bool
	 */
	private $forceInstantResponseEnabled = false;

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
	private $charSelectorEnabled = false;

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
	 * @return boolean
	 */
	public function isQuestionWorkedThrough()
	{
		return $this->questionWorkedThrough;
	}

	/**
	 * @param boolean $questionWorkedThrough
	 */
	public function setQuestionWorkedThrough($questionWorkedThrough)
	{
		$this->questionWorkedThrough = $questionWorkedThrough;
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
	 * @return bool
	 */
	public function isDiscardSolutionButtonEnabled()
	{
		return $this->discardSolutionButtonEnabled;
	}

	/**
	 * @param bool $discardSolutionButtonEnabled
	 */
	public function setDiscardSolutionButtonEnabled($discardSolutionButtonEnabled)
	{
		$this->discardSolutionButtonEnabled = $discardSolutionButtonEnabled;
	}

	/**
	 * @return string
	 */
	public function getSkipQuestionLinkTarget()
	{
		return $this->skipQuestionLinkTarget;
	}

	/**
	 * @param string $skipQuestionLinkTarget
	 */
	public function setSkipQuestionLinkTarget($skipQuestionLinkTarget)
	{
		$this->skipQuestionLinkTarget = $skipQuestionLinkTarget;
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
	 * @return boolean
	 */
	public function isAnswerFreezingEnabled()
	{
		return $this->answerFreezingEnabled;
	}

	/**
	 * @return boolean
	 */
	public function isForceInstantResponseEnabled()
	{
		return $this->forceInstantResponseEnabled;
	}

	/**
	 * @param boolean $forceInstantResponseEnabled
	 */
	public function setForceInstantResponseEnabled($forceInstantResponseEnabled)
	{
		$this->forceInstantResponseEnabled = $forceInstantResponseEnabled;
	}

	/**
	 * @param boolean $answerFreezingEnabled
	 */
	public function setAnswerFreezingEnabled($answerFreezingEnabled)
	{
		$this->answerFreezingEnabled = $answerFreezingEnabled;
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
	 * @return boolean
	 */
	public function isCharSelectorEnabled()
	{
		return $this->charSelectorEnabled;
	}

	/**
	 * @param boolean $charSelectorEnabled
	 */
	public function setCharSelectorEnabled($charSelectorEnabled)
	{
		$this->charSelectorEnabled = $charSelectorEnabled;
	}
	
	/**
	 * @return string
	 */
	public function getHTML()
	{
		$tpl = $this->getTemplate();
		
		if( $this->getEditSolutionCommand() )
		{
			$this->renderSubmitButton(
				$tpl, $this->getEditSolutionCommand(), $this->getEditSolutionButtonLabel()
			);
		}
		
		if( $this->getSubmitSolutionCommand() )
		{
			$this->renderSubmitButton(
				$tpl, $this->getSubmitSolutionCommand(), $this->getSubmitSolutionButtonLabel(), true
			);
		}

		if( $this->isDiscardSolutionButtonEnabled() )
		{
			$this->renderJsLinkedButton($tpl, 'tst_discard_answer_button', 'discard_answer', '');
		}
		
		if( $this->getSkipQuestionLinkTarget() )
		{
			$this->renderLinkButton($tpl, $this->getSkipQuestionLinkTarget(), 'skip_question');
		}

		if( $this->getInstantFeedbackCommand() && !$this->isForceInstantResponseEnabled() )
		{
			$this->renderSubmitButton(
				$tpl, $this->getInstantFeedbackCommand(), $this->getCheckButtonLabel()
			);
		}

		if( $this->getRequestHintCommand() )
		{
			$this->renderSubmitButton(
				$tpl, $this->getRequestHintCommand(), $this->getRequestHintButtonLabel()
			);
		}

		if( $this->getShowHintsCommand() )
		{
			$this->renderSubmitButton(
				$tpl, $this->getShowHintsCommand(), 'button_show_requested_question_hints'
			);
		}

		if( $this->getQuestionMarkCommand() )
		{
			$this->renderIcon(
				$tpl, $this->getQuestionMarkCommand(), $this->getQuestionMarkIconSource(),
				$this->getQuestionMarkIconLabel(), 'ilTstMarkQuestionButton'
			);
		}
		
		if( $this->isCharSelectorEnabled() )
		{
			$this->renderJsLinkedButton($tpl,
				'charselectorbutton', 'char_selector_btn_label', 'ilCharSelectorToggle'
			);
		}
		
		if( $this->isAnythingRendered() )
		{
			$this->parseNavigation($tpl);
		}
		
		return $tpl->get();
	}

	private function getEditSolutionButtonLabel()
	{
		if( $this->isQuestionWorkedThrough() )
		{
			return 'edit_answer';
		}

		return 'answer_question';
	}

	private function getSubmitSolutionButtonLabel()
	{
		if( $this->isForceInstantResponseEnabled() )
		{
			return 'submit_and_check';
		}

		return 'submit_answer';
	}
	
	private function getCheckButtonLabel()
	{
		if( $this->isAnswerFreezingEnabled() )
		{
			return 'submit_and_check';
		}
		
		return 'check';
	}
	
	private function getRequestHintButtonLabel()
	{
		if( $this->hintRequestsExist() )
		{
			return 'button_request_next_question_hint';
		}
		
		return 'button_request_question_hint';
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
	private function parseNavigation(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock('question_related_navigation');
		$tpl->parseCurrentBlock();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function parseButtonsBlock(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock('buttons');
		$tpl->parseCurrentBlock();
	}

	/**
	 * @param ilTemplate $tpl
	 * @param $button
	 */
	private function renderButtonInstance(ilTemplate $tpl, $button)
	{
		$tpl->setCurrentBlock("button_instance");
		$tpl->setVariable("BUTTON_INSTANCE", $button->render());
		$tpl->parseCurrentBlock();

		$this->parseButtonsBlock($tpl);
		$this->setAnythingRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 * @param $command
	 * @param $label
	 * @param bool|false $primary
	 */
	private function renderSubmitButton(ilTemplate $tpl, $command, $label, $primary = false)
	{
		$button = ilSubmitButton::getInstance();
		$button->setCommand($command);
		$button->setCaption($label);
		$button->setPrimary($primary);

		$this->renderButtonInstance($tpl, $button);
	}

	/**
	 * @param ilTemplate $tpl
	 * @param $htmlId
	 * @param $label
	 * @param $cssClass
	 */
	private function renderLinkButton(ilTemplate $tpl, $href, $label)
	{
		$button = ilLinkButton::getInstance();
		$button->setUrl($href);
		$button->setCaption($label);

		$this->renderButtonInstance($tpl, $button);
	}

	/**
	 * @param ilTemplate $tpl
	 * @param $htmlId
	 * @param $label
	 * @param $cssClass
	 */
	private function renderJsLinkedButton(ilTemplate $tpl, $htmlId, $label, $cssClass)
	{
		$button = ilLinkButton::getInstance();
		$button->setId($htmlId);
		$button->addCSSClass($cssClass);
		$button->setCaption($label);

		$this->renderButtonInstance($tpl, $button);
	}

	/**
	 * @param ilTemplate $tpl
	 * @param $command
	 * @param $iconSrc
	 * @param $label
	 * @param $cssClass
	 */
	private function renderIcon(ilTemplate $tpl, $command, $iconSrc, $label, $cssClass)
	{
		$tpl->setCurrentBlock("submit_icon");
		$tpl->setVariable("SUBMIT_ICON_CMD", $command);
		$tpl->setVariable("SUBMIT_ICON_SRC", $iconSrc);
		$tpl->setVariable("SUBMIT_ICON_TEXT", $label);
		$tpl->setVariable("SUBMIT_ICON_CLASS", $cssClass);
		$tpl->parseCurrentBlock();

		$this->parseButtonsBlock($tpl);
		$this->setAnythingRendered();
	}
}