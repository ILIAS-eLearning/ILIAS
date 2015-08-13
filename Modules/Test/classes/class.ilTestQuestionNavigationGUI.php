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
	private $editAnswerCommand = '';

	/**
	 * @var string
	 */
	private $submitAnswerCommand = '';

	/**
	 * @var string
	 */
	private $discardAnswerCommand = '';

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
	 * @var bool
	 */
	private $buttonRendered = false;
	
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
	public function getEditAnswerCommand()
	{
		return $this->editAnswerCommand;
	}

	/**
	 * @param string $editAnswerCommand
	 */
	public function setEditAnswerCommand($editAnswerCommand)
	{
		$this->editAnswerCommand = $editAnswerCommand;
	}

	/**
	 * @return string
	 */
	public function getSubmitAnswerCommand()
	{
		return $this->submitAnswerCommand;
	}

	/**
	 * @param string $submitAnswerCommand
	 */
	public function setSubmitAnswerCommand($submitAnswerCommand)
	{
		$this->submitAnswerCommand = $submitAnswerCommand;
	}

	/**
	 * @return string
	 */
	public function getDiscardAnswerCommand()
	{
		return $this->discardAnswerCommand;
	}

	/**
	 * @param string $discardAnswerCommand
	 */
	public function setDiscardAnswerCommand($discardAnswerCommand)
	{
		$this->discardAnswerCommand = $discardAnswerCommand;
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
	 * @return boolean
	 */
	public function isAnyButtonRendered()
	{
		return $this->buttonRendered;
	}

	/**
	 * @param boolean $buttonRendered
	 */
	public function setButtonRendered()
	{
		$this->buttonRendered = true;
	}
	
	/**
	 * @return string
	 */
	public function getHTML()
	{
		$tpl = $this->getTemplate();
		
		if( $this->getEditAnswerCommand() )
		{
			$this->renderEditAnswerCommandButton($tpl);
		}
		
		if( $this->getSubmitAnswerCommand() )
		{
			$this->renderSubmitAnswerCommandButton($tpl);
		}

		if( $this->getDiscardAnswerCommand() )
		{
			$this->renderDiscardAnswerCommandButton($tpl);
		}

		if( $this->getInstantFeedbackCommand() )
		{
			$this->renderInstantFeedbackCommandButton($tpl);
		}

		if( $this->getRequestHintCommand() )
		{
			$this->renderRequestHintCommandButton($tpl);
		}

		if( $this->getShowHintsCommand() )
		{
			$this->renderShowHintsCommandButton($tpl);
		}
		
		if( $this->isAnyButtonRendered() )
		{
			$this->parseNavigation($tpl);
		}
		
		return $tpl->get();
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
	private function renderEditAnswerCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("edit_answer");
		$tpl->setVariable("CMD_EDIT_ANSWER", $this->getEditAnswerCommand());
		$tpl->setVariable("TEXT_EDIT_ANSWER", $this->lng->txt('edit_answer'));
		$tpl->parseCurrentBlock();

		$this->setButtonRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderSubmitAnswerCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("submit_answer");
		$tpl->setVariable("CMD_SUBMIT_ANSWER", $this->getSubmitAnswerCommand());
		$tpl->setVariable("TEXT_SUBMIT_ANSWER", $this->lng->txt('submit_answer'));
		$tpl->parseCurrentBlock();

		$this->setButtonRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderDiscardAnswerCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("discard_answer");
		$tpl->setVariable("CMD_DISCARD_ANSWER", $this->getDiscardAnswerCommand());
		$tpl->setVariable("TEXT_DISCARD_ANSWER", $this->lng->txt('discard_answer'));
		$tpl->parseCurrentBlock();

		$this->setButtonRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderInstantFeedbackCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("direct_feedback");
		$tpl->setVariable("CMD_SHOW_INSTANT_RESPONSE", $this->getInstantFeedbackCommand());
		$tpl->setVariable("TEXT_SHOW_INSTANT_RESPONSE", $this->lng->txt('check'));
		$tpl->parseCurrentBlock();
		
		$this->setButtonRendered();
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderRequestHintCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("button_request_next_question_hint");
		$tpl->setVariable("CMD_REQUEST_NEXT_QUESTION_HINT", $this->getRequestHintCommand());
		$tpl->setVariable("TEXT_REQUEST_NEXT_QUESTION_HINT", $this->getRequestHintButtonLabel());
		$tpl->parseCurrentBlock();
		
		$this->setButtonRendered();
	}
	
	private function getRequestHintButtonLabel()
	{
		if( $this->hintRequestsExist() )
		{
			return $this->lng->txt("button_request_next_question_hint");
		}
		
		return $this->lng->txt("button_request_question_hint");
	}

	/**
	 * @param ilTemplate $tpl
	 */
	private function renderShowHintsCommandButton(ilTemplate $tpl)
	{
		$tpl->setCurrentBlock("button_show_requested_question_hints");
		$tpl->setVariable("CMD_SHOW_REQUESTED_QUESTION_HINTS", $this->getShowHintsCommand());
		$tpl->setVariable("TEXT_SHOW_REQUESTED_QUESTION_HINTS", $this->lng->txt("button_show_requested_question_hints"));
		$tpl->parseCurrentBlock();

		$this->setButtonRendered();
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